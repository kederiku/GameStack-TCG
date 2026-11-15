<?php

namespace App\Actions\Games;

use App\Models\Game;
use App\Services\GameImages;
use App\Support\GameSlug;
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
        try {
            return $game->getConnection()->transaction(function () use ($game, $data): Game {
                // Reload under a lock so cleanup always targets the current image.
                $record = $game->exists
                    ? Game::query()->lockForUpdate()->findOrFail($game->id)
                    : new Game;

                $name = is_string($data['name'] ?? null) ? trim($data['name']) : ($data['name'] ?? null);
                $slug = $data['slug'] ?? ($record->exists ? $record->slug : null);
                $slug = is_string($slug) ? trim($slug) : $slug;

                if (blank($slug) && is_string($name)) {
                    $slug = Str::slug($name);
                }

                $details = [];
                foreach (['start_year', 'end_year', 'publisher', 'description'] as $field) {
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
                    'description' => ['nullable', 'string'],
                    'is_visible' => ['required', 'boolean'],
                ], [
                    'slug.not_in' => 'Ce slug est réservé à une page de l’application. Choisissez un autre identifiant.',
                    'end_year.gte' => 'L’année de fin doit être supérieure ou égale à l’année de création.',
                ])->validate();

                if ($image instanceof UploadedFile) {
                    Validator::make(['image' => $image], [
                        'image' => ['required', 'image:allow_svg', 'mimetypes:image/jpeg,image/png,image/webp,image/avif,image/svg+xml', 'max:5120', 'dimensions:max_width=4096,max_height=4096'],
                    ])->validate();
                    $image = $this->images->store($image, $attributes['name'], $record->getConnection());
                } elseif ($image !== null && (! is_string($image) || $image !== $record->image)) {
                    throw ValidationException::withMessages(['image' => 'Cette image ne correspond plus à celle du jeu. Rechargez le formulaire.']);
                } elseif ($image !== null && $name !== $record->name) {
                    $image = $this->images->rename($image, $attributes['name'], $record->getConnection());
                }

                $record->fill($attributes);
                $record->image = $image;

                if (! $record->save()) {
                    throw new RuntimeException('L’enregistrement du jeu a été annulé.');
                }

                return $record;
            });
        } catch (UniqueConstraintViolationException) {
            // Also handle two concurrent creates passing validation together.
            throw ValidationException::withMessages(['slug' => 'Ce slug est déjà utilisé par un autre jeu.']);
        }
    }
}
