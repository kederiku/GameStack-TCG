<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $source_expansion_id
 * @property int $target_expansion_id
 * @property string $type
 * @property string $notes
 * @property string $source_url
 * @property-read Expansion $sourceExpansion
 * @property-read Expansion $targetExpansion
 */
#[Fillable(['source_expansion_id', 'target_expansion_id', 'type', 'notes', 'source_url'])]
class ExpansionRelation extends CatalogRecord
{
    public const TYPES = ['related_to', 'adapted_from', 'includes_content_from'];

    public function isEffectivelyVisible(): bool
    {
        return ($this->sourceExpansion()->first()?->isEffectivelyVisible() ?? false)
            && ($this->targetExpansion()->first()?->isEffectivelyVisible() ?? false);
    }

    /** @return BelongsTo<Expansion, $this> */
    public function sourceExpansion(): BelongsTo
    {
        return $this->belongsTo(Expansion::class);
    }

    /** @return BelongsTo<Expansion, $this> */
    public function targetExpansion(): BelongsTo
    {
        return $this->belongsTo(Expansion::class);
    }
}
