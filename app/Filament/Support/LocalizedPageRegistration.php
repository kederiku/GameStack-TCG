<?php

namespace App\Filament\Support;

use App\Support\Localization\AdminPanels;
use App\Support\Localization\TranslationFiles;
use Filament\Panel;
use Filament\Resources\Pages\Page;
use Filament\Resources\Pages\PageRegistration;
use Illuminate\Routing\Route;

final class LocalizedPageRegistration
{
    /** @param class-string<Page> $pageClass */
    public static function make(string $pageClass, string $pathKey): PageRegistration
    {
        return new PageRegistration($pageClass, fn (Panel $panel): ?Route => $pageClass::route(
            '/'.app(TranslationFiles::class)->routes(AdminPanels::locale($panel->getId()))['admin.pages.'.$pathKey],
        )->registerRoute($panel));
    }
}
