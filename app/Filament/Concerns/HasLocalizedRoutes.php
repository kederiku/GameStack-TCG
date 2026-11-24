<?php

namespace App\Filament\Concerns;

use App\Filament\Support\Catalog\CatalogLabels;
use App\Support\Localization\AdminPanels;
use App\Support\Localization\Locales;
use App\Support\Localization\TranslationFiles;
use Filament\Facades\Filament;
use Filament\Panel;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

trait HasLocalizedRoutes
{
    public static function getRecordTitle(?Model $record): string|Htmlable|null
    {
        return $record === null ? parent::getRecordTitle(null) : CatalogLabels::name($record);
    }

    /**
     * @template T of Model
     *
     * @param  Builder<T>  $query
     * @param  array<string>  $searchAttributes
     * @return Builder<T>
     */
    protected static function applyGlobalSearchAttributeConstraint(Builder $query, string $search, array $searchAttributes, bool &$isFirst): Builder
    {
        if ($searchAttributes !== ['name']) {
            return parent::applyGlobalSearchAttributeConstraint($query, $search, $searchAttributes, $isFirst);
        }

        $clause = $isFirst ? 'where' : 'orWhere';
        $isFirst = false;

        return $query->{$clause}(fn (Builder $names) => CatalogLabels::searchName($names, $search));
    }

    public static function getSlug(?Panel $panel = null): string
    {
        $panel ??= Filament::getCurrentOrDefaultPanel();

        return app(TranslationFiles::class)->routes(AdminPanels::locale($panel->getId()))['admin.resources.'.static::$localizationKey];
    }

    protected static function localizedLabel(string $key): string
    {
        $locale = AdminPanels::locale(Filament::getCurrentOrDefaultPanel()->getId());

        return __('admin.'.$key, [], Locales::definition($locale)['laravelLocale']);
    }

    public static function getModelLabel(): string
    {
        return static::localizedLabel('resources.'.static::$localizationKey.'.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return static::localizedLabel('resources.'.static::$localizationKey.'.plural');
    }

    public static function getNavigationLabel(): string
    {
        return static::localizedLabel('navigation.'.static::$localizationKey);
    }

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return static::localizedLabel('groups.'.(in_array(static::$localizationKey, ['languages', 'markets'], true) ? 'references' : 'catalog'));
    }
}
