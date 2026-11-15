<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $edition_id
 * @property int $market_id
 * @property string $channel
 * @property ?CarbonImmutable $released_on
 * @property ?string $announced_period
 * @property string $status
 * @property ?string $source_url
 * @property-read ExpansionEdition $edition
 * @property-read Market $market
 */
#[Fillable(['edition_id', 'market_id', 'channel', 'released_on', 'announced_period', 'status', 'source_url'])]
class ExpansionRelease extends CatalogRecord
{
    public const CHANNELS = ['general', 'specialist', 'prerelease', 'crowdfunding', 'event'];

    public const STATUSES = ExpansionEdition::STATUSES;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['released_on' => 'immutable_date:Y-m-d'];
    }

    protected function setReleasedOnAttribute(mixed $value): void
    {
        // Keep an input string intact until validation can reject invalid civil days.
        $this->attributes['released_on'] = $value instanceof DateTimeInterface ? $value->format('Y-m-d') : $value;
    }

    public function isEffectivelyVisible(): bool
    {
        return $this->edition()->first()?->isEffectivelyVisible() ?? false;
    }

    /** @return BelongsTo<ExpansionEdition, $this> */
    public function edition(): BelongsTo
    {
        return $this->belongsTo(ExpansionEdition::class);
    }

    /** @return BelongsTo<Market, $this> */
    public function market(): BelongsTo
    {
        return $this->belongsTo(Market::class);
    }
}
