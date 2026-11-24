<?php

namespace App\Actions\Catalog;

use App\Models\CatalogRecord;
use App\Models\ExpansionEdition;
use App\Models\ExpansionRelease;
use App\Models\Language;
use App\Models\SeriesImage;
use App\Services\CatalogImages;
use App\Support\Catalog\CatalogRules;
use App\Support\Localization\ContentLanguageUsage;
use App\Support\Localization\TranslationPatch;
use Closure;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class SaveCatalogRecord
{
    public function __construct(private readonly CatalogImages $images) {}

    /**
     * @template T of CatalogRecord
     *
     * @param  T  $record
     * @param  array<string, mixed>  $data
     * @return T
     */
    public function handle(CatalogRecord $record, array $data): CatalogRecord
    {
        $record = clone $record;

        if (! $record->saveCatalogAttributes($data)) {
            throw new RuntimeException(__('validation.catalog.save_cancelled'));
        }

        return $record;
    }

    /**
     * Writes from the action and from Eloquent share the same transaction.
     *
     * @param  Closure(): mixed  $apply
     * @param  Closure(): bool  $write
     * @param  array<string, mixed>  $changes
     *
     * @internal Called by CatalogRecord.
     */
    public function persist(CatalogRecord $record, Closure $apply, Closure $write, array $changes, bool $acceptImageUpload = false): bool
    {
        $current = null;
        $proposal = clone $record;
        if ($acceptImageUpload) {
            $proposal->fill($changes);
        }
        $operations = TranslationPatch::forRecord($record, $proposal->getAttributes());
        $codes = array_column($operations, 'locale');
        $languageId = $proposal->getAttributes()['language_id'] ?? $proposal->getAttributes()['default_language_id'] ?? null;
        if ($record instanceof ExpansionRelease) {
            $languageId = ExpansionEdition::on($record->getConnectionName())->whereKey($proposal->getAttribute('edition_id'))->value('language_id');
        }
        if ($languageId !== null) {
            $code = Language::on($record->getConnectionName())->whereKey($languageId)->value('code');
            if (is_string($code)) {
                $codes[] = $code;
            }
        }

        try {
            return $record->getConnection()->transaction(function () use ($record, $apply, $write, $changes, $acceptImageUpload, $operations, $codes, &$current): bool {
                ContentLanguageUsage::lock($codes, $record->getConnection());
                // Take the edition lock before the release lock, in that order for every release write.
                // An edition write locks the same edition before checking its published releases.
                $edition = $record instanceof ExpansionRelease ? $this->lockEdition($record, $changes) : null;

                if ($record->exists) {
                    $current = $record->newQuery()->lockForUpdate()->findOrFail((int) $record->getRawOriginal('id'));
                    $record->setRawAttributes($current->getAttributes(), sync: true);
                    $record->unsetRelations();
                }

                $apply();
                $attributes = $record->getAttributes();
                foreach (TranslationPatch::attributes($record) as $attribute) {
                    $attributes[$attribute] = $current?->getRawOriginal($attribute) ?? '{}';
                }
                $record->setRawAttributes($attributes);
                TranslationPatch::apply($record, $operations);
                CatalogRules::validate($record);
                $uploaded = $this->saveImage($record, $current, $changes, $acceptImageUpload);

                if (! $write()) {
                    if ($uploaded) {
                        throw new RuntimeException(__('validation.catalog.save_cancelled'));
                    }

                    return false;
                }

                if ($record instanceof ExpansionRelease && $record->status === 'released'
                    && $edition !== null && $edition->status !== 'released'
                    && ! $edition->update(['status' => 'released'])) {
                    throw new RuntimeException(__('validation.catalog.publish_cancelled'));
                }

                return true;
            });
        } catch (ValidationException|QueryException $exception) {
            if ($current instanceof CatalogRecord) {
                $record->setRawAttributes($current->getAttributes(), sync: true);
            }

            if ($exception instanceof UniqueConstraintViolationException) {
                CatalogRules::rejectDuplicate($record);
            }

            throw $exception;
        }
    }

    /** @param array<string, mixed> $changes */
    private function saveImage(CatalogRecord $record, ?CatalogRecord $current, array $changes, bool $acceptImageUpload): bool
    {
        $attributes = $record->getAttributes();

        if (! array_key_exists('image', $changes) && ! array_key_exists('image', $attributes)) {
            return false;
        }

        if (! $record instanceof SeriesImage && ! $record instanceof ExpansionEdition) {
            throw ValidationException::withMessages(['image' => __('validation.images.unsupported_record')]);
        }

        $image = $attributes['image'] ?? null;

        if ($acceptImageUpload && $image instanceof UploadedFile && ($changes['image'] ?? null) === $image) {
            $name = $record instanceof SeriesImage
                ? ($record->series->getTranslations('display_name')[CatalogRules::languageCode($record->language->code)] ?? $record->series->name)
                : $record->name;
            $record->image = $this->images->store($image, $name, $record->getConnection());

            return true;
        }

        if ($image !== null && $image !== $current?->getRawOriginal('image')) {
            throw ValidationException::withMessages(['image' => __('validation.images.stale_record')]);
        }

        return false;
    }

    /** @param array<string, mixed> $changes */
    private function lockEdition(ExpansionRelease $release, array $changes): ?ExpansionEdition
    {
        $editionId = $release->exists
            ? $release->getRawOriginal('edition_id')
            : ($changes['edition_id'] ?? $release->getAttributes()['edition_id'] ?? null);

        if ((! is_int($editionId) && ! is_string($editionId))
            || filter_var($editionId, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) === false) {
            return null;
        }

        return ExpansionEdition::on($release->getConnectionName())->lockForUpdate()->find($editionId);
    }
}
