<?php

namespace App\Models;

use App\Support\Localization\HasContentTranslations;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\ValidationException;

/**
 * @property int $catalog_id
 * @property string $key
 * @property string $name
 * @property string $type
 * @property int $sort_order
 * @property bool $is_visible
 * @property-read Catalog $catalog
 * @property-read Collection<int, SeriesLocalization> $localizations
 * @property-read Collection<int, Expansion> $expansions
 */
#[Fillable(['catalog_id', 'key', 'name', 'type', 'sort_order', 'is_visible', 'display_name', 'description'])]
class Series extends CatalogRecord
{
    use HasContentTranslations;

    /** @var list<string> */
    public array $translatable = ['display_name', 'description'];

    public const TYPES = ['series', 'block'];

    protected $table = 'series';

    /** @var array<string, mixed> */
    protected $attributes = ['is_visible' => true, 'sort_order' => 0];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['is_visible' => 'boolean', 'sort_order' => 'integer'];
    }

    public function isEffectivelyVisible(): bool
    {
        return $this->is_visible && ($this->catalog()->first()?->isEffectivelyVisible() ?? false);
    }

    protected function performDeleteOnModel(): void
    {
        // Run image lifecycles so cleanup follows the enclosing transaction.
        foreach ($this->images()->get() as $localization) {
            if (! $localization->delete()) {
                throw ValidationException::withMessages(['record' => __('validation.images.delete-series-localization')]);
            }
        }

        parent::performDeleteOnModel();
    }

    /** @return BelongsTo<Catalog, $this> */
    public function catalog(): BelongsTo
    {
        return $this->belongsTo(Catalog::class);
    }

    /** @return HasMany<SeriesLocalization, $this> */
    public function localizations(): HasMany
    {
        return $this->hasMany(SeriesLocalization::class);
    }

    /** @return HasMany<SeriesImage, $this> */
    public function images(): HasMany
    {
        return $this->hasMany(SeriesImage::class);
    }

    public function imageForLocale(string $locale): ?SeriesImage
    {
        return $this->images()->whereHas('language', fn ($query) => $query->whereRaw('lower(code) = lower(?)', [$locale]))->first();
    }

    /** @return HasMany<Expansion, $this> */
    public function expansions(): HasMany
    {
        return $this->hasMany(Expansion::class);
    }
}
