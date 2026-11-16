<?php

namespace App\Actions\Games;

use App\Models\Game;
use App\Services\GameImages;
use App\Support\GameSlug;
use App\Support\Localization\ContentLanguageUsage;
use App\Support\Localization\TranslationPatch;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class SaveGame
{
    public function __construct(private readonly GameImages $images) {}

    /** @param array<string, mixed> $data */
    public function handle(Game $game, array $data): Game
    {
        $proposal = clone $game;
        $proposal->fill(array_intersect_key($data, array_flip($game->getTranslatableAttributes())));
        $operations = TranslationPatch::forRecord($game, $proposal->getAttributes());
        try {
            return $game->getConnection()->transaction(function () use ($game, $data, $operations): Game {
                ContentLanguageUsage::lock(array_column($operations, 'locale'), $game->getConnection());
                // Reload under a lock so cleanup always targets the current image.
                $record = $game->exists
                    ? Game::on($game->getConnectionName())->lockForUpdate()->findOrFail($game->id)
                    : (new Game)->setConnection($game->getConnectionName());

                $name = is_string($data['name'] ?? null) ? trim($data['name']) : ($data['name'] ?? null);
                $slug = $data['slug'] ?? ($record->exists ? $record->slug : null);
                $slug = is_string($slug) ? trim($slug) : $slug;

                if (blank($slug) && is_string($name)) {
                    $slug = Str::slug($name);
                }

                $details = [];
                foreach (['start_year', 'end_year', 'publisher', 'reference_description'] as $field) {
                    $value = array_key_exists($field, $data) ? $data[$field] : $record->getAttribute($field);
                    $value = is_string($value) ? trim($value) : $value;
                    $details[$field] = $value === '' ? null : $value;
                }

                $image = array_key_exists('image', $data) ? $data['image'] : $record->image;
                $isVisible = array_key_exists('is_visible', $data) ? $data['is_visible'] : $record->is_visible;
                $attributes = Validator::make(['name' => $name, 'slug' => $slug, 'is_visible' => $isVisible, ...$details], [
                    'name' => ['required', 'string', 'max:255'],
                    'slug' => ['required', 'string', 'max:255', 'regex:/\A[a-z0-9]+(?:-[a-z0-9]+)*\z/', Rule::notIn(GameSlug::reserved()), Rule::unique(Game::class, 'slug')->ignore($record)],
                    'start_year' => ['nullable', 'integer', 'between:1000,9999'],
                    'end_year' => ['nullable', 'integer', 'between:1000,9999', Rule::when($details['start_year'] !== null, ['gte:start_year'])],
                    'publisher' => ['nullable', 'string', 'max:255'],
                    'reference_description' => ['nullable', 'string'],
                    'is_visible' => ['required', 'boolean'],
                ], [
                    'slug.not_in' => __('validation.catalog.reserved_slug'),
                    'end_year.gte' => __('validation.catalog.end_year'),
                ])->validate();

                if ($image instanceof UploadedFile) {
                    GameImages::validateUpload($image);
                    $image = $this->images->store($image, $attributes['name'], $record->getConnection());
                } elseif ($image !== null && (! is_string($image) || $image !== $record->image)) {
                    throw ValidationException::withMessages(['image' => __('validation.images.stale_game')]);
                } elseif ($image !== null && $name !== $record->name) {
                    $image = $this->images->rename($image, $attributes['name'], $record->getConnection());
                }

                $record->fill($attributes);
                $record->image = $image;
                TranslationPatch::apply($record, $operations);

                if (! $record->save()) {
                    throw new RuntimeException(__('validation.catalog.game_save_cancelled'));
                }

                return $record;
            });
        } catch (UniqueConstraintViolationException) {
            // Also handle two concurrent creates passing validation together.
            throw ValidationException::withMessages(['slug' => __('validation.catalog.duplicate_slug')]);
        }
    }
}
