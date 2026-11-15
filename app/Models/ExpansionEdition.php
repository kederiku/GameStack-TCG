<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $expansion_id
 * @property int $language_id
 * @property string $name
 * @property ?string $official_code
 * @property ?string $image
 * @property bool $is_visible
 * @property string $status
 * @property-read Expansion $expansion
 * @property-read Language $language
 * @property-read Collection<int, ExpansionRelease> $releases
 */
#[Fillable(['expansion_id', 'language_id', 'name', 'official_code', 'image', 'is_visible', 'status'])]
class ExpansionEdition extends CatalogRecord
{
    public const STATUSES = ['announced', 'released', 'cancelled'];

    /** @var array<string, mixed> */
    protected $attributes = ['is_visible' => true];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['is_visible' => 'boolean'];
    }

    public function isEffectivelyVisible(): bool
    {
        return $this->is_visible && ($this->expansion()->first()?->isEffectivelyVisible() ?? false);
    }

    /** @return BelongsTo<Expansion, $this> */
    public function expansion(): BelongsTo
    {
        return $this->belongsTo(Expansion::class);
    }

    /** @return BelongsTo<Language, $this> */
    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }

    /** @return HasMany<ExpansionRelease, $this> */
    public function releases(): HasMany
    {
        return $this->hasMany(ExpansionRelease::class, 'edition_id');
    }
}
