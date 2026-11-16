<?php

namespace App\Models;

use App\Support\Catalog\CatalogRules;
use App\Support\Localization\ContentLanguageUsage;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

/**
 * @property string $manifest_key
 * @property string $record_type
 * @property int $record_id
 * @property ?string $locale
 */
#[Fillable(['manifest_key', 'record_type', 'record_id', 'locale'])]
class CatalogExampleBinding extends Model
{
    protected $primaryKey = 'manifest_key';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    /** @param array<string, mixed> $options */
    public function save(array $options = []): bool
    {
        return $this->getConnection()->transaction(function () use ($options): bool {
            if ($this->locale !== null) {
                if (! CatalogRules::validLanguageCode($this->locale)) {
                    throw ValidationException::withMessages(['locale' => __('validation.catalog.regex')]);
                }
                $this->locale = CatalogRules::languageCode($this->locale);
                ContentLanguageUsage::lock([$this->locale], $this->getConnection());
            }

            return parent::save($options);
        });
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['record_id' => 'integer'];
    }
}
