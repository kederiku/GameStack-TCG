<?php

namespace App\Actions\Catalog;

use App\Models\CatalogRecord;
use App\Models\ExpansionEdition;
use App\Models\ExpansionRelease;
use App\Models\SeriesLocalization;
use App\Services\CatalogImages;
use App\Support\Catalog\CatalogRules;
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
            throw new RuntimeException('L’enregistrement de la fiche a été annulé.');
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

        try {
            return $record->getConnection()->transaction(function () use ($record, $apply, $write, $changes, $acceptImageUpload, &$current): bool {
                // Take the edition lock before the release lock, in that order for every release write.
                // An edition write locks the same edition before checking its published releases.
                $edition = $record instanceof ExpansionRelease ? $this->lockEdition($record, $changes) : null;

                if ($record->exists) {
                    $current = $record->newQuery()->lockForUpdate()->findOrFail((int) $record->getRawOriginal('id'));
                    $record->setRawAttributes($current->getAttributes(), sync: true);
                    $record->unsetRelations();
                }

                $apply();
                CatalogRules::validate($record);
                $uploaded = $this->saveImage($record, $current, $changes, $acceptImageUpload);

                if (! $write()) {
                    if ($uploaded) {
                        throw new RuntimeException('L’enregistrement de la fiche a été annulé.');
                    }

                    return false;
                }

                if ($record instanceof ExpansionRelease && $record->status === 'released'
                    && $edition !== null && $edition->status !== 'released'
                    && ! $edition->update(['status' => 'released'])) {
                    throw new RuntimeException('La publication de l’édition a été annulée.');
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

        if (! $record instanceof SeriesLocalization && ! $record instanceof ExpansionEdition) {
            throw ValidationException::withMessages(['image' => 'Cette fiche ne peut pas recevoir d’image.']);
        }

        $image = $attributes['image'] ?? null;

        if ($acceptImageUpload && $image instanceof UploadedFile && ($changes['image'] ?? null) === $image) {
            $record->image = $this->images->store($image, $record->name, $record->getConnection());

            return true;
        }

        if ($image !== null && $image !== $current?->getRawOriginal('image')) {
            throw ValidationException::withMessages(['image' => 'Cette image ne correspond plus à celle de la fiche. Rechargez le formulaire.']);
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
