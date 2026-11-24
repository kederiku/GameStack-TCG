<?php

namespace App\Models;

use App\Support\Localization\HasContentTranslations;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $game_id
 * @property int $default_language_id
 * @property string $key
 * @property string $name
 * @property ?string $description
 * @property int $sort_order
 * @property bool $is_visible
 * @property-read Game $game
 * @property-read Language $defaultLanguage
 * @property-read Collection<int, Series> $series
 * @property-read Collection<int, Expansion> $expansions
 */
#[Fillable(['game_id', 'default_language_id', 'key', 'name', 'description', 'sort_order', 'is_visible', 'display_name', 'reference_description'])]
class Catalog extends CatalogRecord
{
    use HasContentTranslations;

    /** @var list<string> */
    public array $translatable = ['display_name', 'description'];

    /** @var array<string, mixed> */
    protected $attributes = ['is_visible' => true, 'sort_order' => 0];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['is_visible' => 'boolean', 'sort_order' => 'integer'];
    }

    public function isEffectivelyVisible(): bool
    {
        return $this->is_visible && $this->game()->where('is_visible', true)->exists();
    }

    /** @return BelongsTo<Game, $this> */
    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    /** @return BelongsTo<Language, $this> */
    public function defaultLanguage(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }

    /** @return HasMany<Series, $this> */
    public function series(): HasMany
    {
        return $this->hasMany(Series::class);
    }

    /** @return HasMany<Expansion, $this> */
    public function expansions(): HasMany
    {
        return $this->hasMany(Expansion::class);
    }
}
