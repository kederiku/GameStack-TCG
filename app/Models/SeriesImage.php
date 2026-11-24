<?php

namespace App\Models;

use App\Observers\CatalogImageObserver;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $series_id
 * @property int $language_id
 * @property ?string $image
 * @property-read Series $series
 * @property-read Language $language
 */
#[Fillable(['series_id', 'language_id', 'image'])]
#[ObservedBy([CatalogImageObserver::class])]
class SeriesImage extends CatalogRecord
{
    public function isEffectivelyVisible(): bool
    {
        return $this->series()->first()?->isEffectivelyVisible() ?? false;
    }

    /** @return BelongsTo<Series, $this> */
    public function series(): BelongsTo
    {
        return $this->belongsTo(Series::class);
    }

    /** @return BelongsTo<Language, $this> */
    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }
}
