<?php

namespace App\Models;

use App\Actions\Catalog\SaveCatalogRecord;
use App\Support\Catalog\CatalogRules;
use App\Support\Localization\ContentLanguageUsage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;

/**
 * @property int $id
 */
abstract class CatalogRecord extends Model
{
    /** @param array<string, mixed> $options */
    public function save(array $options = []): bool
    {
        $original = $this->getRawOriginal();
        // Compare raw inputs before validation, especially dates that may not parse.
        $pending = array_filter(
            $this->getAttributes(),
            fn (mixed $value, string $field): bool => ! array_key_exists($field, $original) || $original[$field] !== $value,
            ARRAY_FILTER_USE_BOTH,
        );

        return app(SaveCatalogRecord::class)->persist(
            $this,
            fn () => $this->setRawAttributes(array_replace($this->getAttributes(), $pending)),
            fn () => parent::save($options),
            $pending,
        );
    }

    /**
     * @param  array<string, mixed>  $data
     *
     * @internal Called by SaveCatalogRecord.
     */
    public function saveCatalogAttributes(array $data): bool
    {
        return app(SaveCatalogRecord::class)->persist(
            $this,
            fn () => $this->fill($data),
            fn () => parent::save(),
            $data,
            acceptImageUpload: true,
        );
    }

    public function delete(): ?bool
    {
        if (! $this->exists) {
            return parent::delete();
        }

        try {
            return $this->getConnection()->transaction(function (): ?bool {
                $current = $this->newQuery()->lockForUpdate()->find($this->id);

                if ($current === null) {
                    return null;
                }

                $this->setRawAttributes($current->getAttributes(), sync: true);
                $this->unsetRelations();

                if ($this instanceof Language) {
                    ContentLanguageUsage::assertDeletable($this);
                }

                return parent::delete();
            });
        } catch (QueryException $exception) {
            CatalogRules::rejectRestrictedDelete($exception, $this);
        }
    }

    public function isEffectivelyVisible(): bool
    {
        return true;
    }
}
