<?php

namespace App\Filament\Support\Catalog;

use App\Models\Language;
use App\Support\Catalog\CatalogPresentation;
use App\Support\Localization\AdminPanels;
use App\Support\Localization\Locales;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Locale;

class CatalogLabels
{
    public static function locale(): string
    {
        return AdminPanels::locale(Filament::getCurrentOrDefaultPanel()->getId());
    }

    public static function name(Model $record): string
    {
        if ($record instanceof Language) {
            $primary = Locale::getPrimaryLanguage($record->code);
            $known = Locale::getDisplayLanguage($record->code, 'en');
            if (! $known || strcasecmp($known, (string) $primary) === 0) {
                return $record->name;
            }

            return Locale::getDisplayName($record->code, Locales::definition(self::locale())['htmlLang']) ?: $record->name;
        }

        return method_exists($record, 'isTranslatableAttribute') && $record->isTranslatableAttribute('display_name')
            ? CatalogPresentation::displayName($record, self::locale())['name']
            : (string) $record->getAttribute('name');
    }

    public static function language(Language $language): string
    {
        return self::name($language).' ('.$language->code.')';
    }

    /** @param Builder<covariant Model> $query */
    public static function sortName(Builder $query, string $direction): void
    {
        $model = $query->getModel();
        $direction = $direction === 'desc' ? 'desc' : 'asc';
        if ($model instanceof Language) {
            $ids = Language::query()->get()->sortBy(self::name(...))->modelKeys();
            $query->orderByRaw('array_position(?::bigint[], id) '.$direction, ['{'.implode(',', $ids).'}']);
        } elseif (method_exists($model, 'isTranslatableAttribute') && $model->isTranslatableAttribute('display_name')) {
            $query->orderBy(CatalogPresentation::nameForQuery($model, self::locale()), $direction);
        } else {
            $query->orderBy('name', $direction);
        }
    }

    /** @param Builder<covariant Model> $query */
    public static function searchName(Builder $query, string $search): void
    {
        $query->where(function (Builder $names) use ($search): void {
            $pattern = '%'.$search.'%';
            $names->whereLike('name', $pattern);
            $model = $names->getModel();
            if (method_exists($model, 'isTranslatableAttribute') && $model->isTranslatableAttribute('display_name')) {
                $names->orWhereRaw('exists (select 1 from jsonb_each_text(display_name) as translation where translation.value ilike ?)', [$pattern]);
            }
            if ($model instanceof Language) {
                $ids = Language::query()->get()->filter(fn (Language $language): bool => mb_stripos(self::language($language), $search) !== false)->modelKeys();
                $names->orWhereIn('id', $ids);
            }
        });
    }

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
