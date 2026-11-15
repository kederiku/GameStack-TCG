<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $expansion_id
 * @property int $language_id
 * @property string $name
 * @property ?string $description
 * @property-read Expansion $expansion
 * @property-read Language $language
 */
#[Fillable(['expansion_id', 'language_id', 'name', 'description'])]
class ExpansionLocalization extends CatalogRecord
{
    public function isEffectivelyVisible(): bool
    {
        return $this->expansion()->first()?->isEffectivelyVisible() ?? false;
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
}
