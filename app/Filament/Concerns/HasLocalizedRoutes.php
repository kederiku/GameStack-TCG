<?php

namespace App\Filament\Concerns;

use App\Support\Localization\AdminPanels;
use App\Support\Localization\Locales;
use App\Support\Localization\TranslationFiles;
use Filament\Facades\Filament;
use Filament\Panel;
use UnitEnum;

trait HasLocalizedRoutes
{
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
