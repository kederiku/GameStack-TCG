<?php

namespace App\Models;

use App\Support\Localization\HasContentTranslations;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $catalog_id
 * @property ?int $series_id
 * @property string $key
 * @property string $name
 * @property string $type
 * @property int $sort_order
 * @property bool $is_visible
 * @property-read Catalog $catalog
 * @property-read ?Series $series
 * @property-read Collection<int, ExpansionLocalization> $localizations
 * @property-read Collection<int, ExpansionEdition> $editions
 * @property-read Collection<int, ExpansionRelation> $relations
 * @property-read Collection<int, ExpansionRelation> $incomingRelations
 */
#[Fillable(['catalog_id', 'series_id', 'key', 'name', 'type', 'sort_order', 'is_visible', 'display_name', 'description'])]
class Expansion extends CatalogRecord
{
    use HasContentTranslations;

    /** @var list<string> */
    public array $translatable = ['display_name', 'description'];

    public const TYPES = ['main', 'special', 'promo', 'deck', 'reprint'];

    /** @var array<string, mixed> */
    protected $attributes = ['is_visible' => true, 'sort_order' => 0];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['is_visible' => 'boolean', 'sort_order' => 'integer'];
    }

    public function isEffectivelyVisible(): bool
    {
        return $this->is_visible && ($this->series_id === null
            ? ($this->catalog()->first()?->isEffectivelyVisible() ?? false)
            : ($this->series()->first()?->isEffectivelyVisible() ?? false));
    }

    /** @return BelongsTo<Catalog, $this> */
    public function catalog(): BelongsTo
    {
        return $this->belongsTo(Catalog::class);
    }

    /** @return BelongsTo<Series, $this> */
    public function series(): BelongsTo
    {
        return $this->belongsTo(Series::class);
    }

    /** @return HasMany<ExpansionLocalization, $this> */
    public function localizations(): HasMany
    {
        return $this->hasMany(ExpansionLocalization::class);
    }

    /** @return HasMany<ExpansionEdition, $this> */
    public function editions(): HasMany
    {
        return $this->hasMany(ExpansionEdition::class);
    }

    /** @return HasMany<ExpansionRelation, $this> */
    public function relations(): HasMany
    {
        return $this->hasMany(ExpansionRelation::class, 'source_expansion_id');
    }

    /** @return HasMany<ExpansionRelation, $this> */
    public function incomingRelations(): HasMany
    {
        return $this->hasMany(ExpansionRelation::class, 'target_expansion_id');
    }
}
