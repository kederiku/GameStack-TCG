<?php

namespace App\Filament\Support\Catalog;

class CatalogLabels
{
    /** @return array<string, string> */
    public static function seriesTypes(): array
    {
        return ['series' => __('admin.labels.series'), 'block' => __('admin.labels.block')];
    }

    /** @return array<string, string> */
    public static function expansionTypes(): array
    {
        return [
            'main' => __('admin.labels.main-expansion'), 'special' => __('admin.labels.special-expansion'),
            'promo' => __('admin.labels.promo'), 'deck' => __('admin.labels.deck'), 'reprint' => __('admin.labels.reprint'),
        ];
    }

    /** @return array<string, string> */
    public static function relationTypes(): array
    {
        return [
            'related_to' => __('admin.labels.related-to'), 'adapted_from' => __('admin.labels.adapted-from'), 'includes_content_from' => __('admin.labels.includes-content-from'),
        ];
    }

    /** @return array<string, string> */
    public static function statuses(): array
    {
        return ['announced' => __('admin.labels.announced'), 'released' => __('admin.labels.released'), 'cancelled' => __('admin.labels.cancelled')];
    }

    /** @return array<string, string> */
    public static function channels(): array
    {
        return [
            'general' => __('admin.labels.general-distribution'), 'specialist' => __('admin.labels.specialist-stores'),
            'prerelease' => __('admin.labels.prerelease'), 'crowdfunding' => __('admin.labels.crowdfunding'), 'event' => __('admin.labels.event'),
        ];
    }
}
