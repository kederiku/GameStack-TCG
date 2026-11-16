<?php

namespace App\Providers\Filament;

use App\Filament\Pages\DevelopmentServices;
use App\Filament\Widgets\CatalogOverview;
use App\Http\Controllers\AdminLoginController;
use App\Http\Middleware\SetAdminLocale;
use App\Support\Development\ServiceCatalog;
use App\Support\Localization\AdminPanels;
use App\Support\Localization\Locales;
use App\Support\Localization\LocalizedRoutes;
use App\Support\Localization\TranslationFiles;
use Filament\Facades\Filament;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Icons\Heroicon;
use Filament\View\PanelsRenderHook;
use Illuminate\Contracts\View\View;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function register(): void
    {
        foreach (AdminPanels::all() as $id => $locale) {
            Filament::registerPanel(fn (): Panel => $this->localizedPanel(Panel::make(), $id, $locale));
        }
    }

    public function panel(Panel $panel): Panel
    {
        return $this->localizedPanel($panel, AdminPanels::id(Locales::default()), Locales::default());
    }

    private function localizedPanel(Panel $panel, string $id, string $locale): Panel
    {
        $paths = app(TranslationFiles::class)->routes($locale);

        return $panel
            ->default($locale === Locales::default())
            ->id($id)
            ->path($locale.'/'.$paths['admin.dashboard'])
            ->loginRouteSlug($paths['admin.login'])
            ->unsavedChangesAlerts()
            ->login(AdminLoginController::class)
            ->brandName('GameStackr Admin')
            ->brandLogo(fn (): View => view('filament.brand-logo'))
            ->brandLogoHeight('2rem')
            ->renderHook(PanelsRenderHook::TOPBAR_END, fn (): View => view('filament.language-switcher'))
            ->renderHook(PanelsRenderHook::STYLES_AFTER, fn (): View => view('filament.brand-styles'))
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->navigationGroups([
                NavigationGroup::make(fn (): string => __('admin.groups.catalog'))->collapsible(false),
                NavigationGroup::make(fn (): string => __('admin.groups.references')),
                NavigationGroup::make(fn (): string => __('admin.groups.development'))->collapsed(),
            ])
            ->navigationItems([
                NavigationItem::make('view-site')->label(fn (): string => __('admin.view_site'))
                    ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                    ->sort(10)
                    ->url(fn (): string => LocalizedRoutes::url('home', [], $locale), shouldOpenInNewTab: true),
                NavigationItem::make('Mailpit')->group(fn (): string => __('admin.groups.development'))->sort(20)
                    ->icon(Heroicon::OutlinedEnvelope)
                    ->url(fn (): string => app(ServiceCatalog::class)->links()['mailpit']['url'], shouldOpenInNewTab: true)
                    ->visible(fn (): bool => DevelopmentServices::canAccess()),
                NavigationItem::make('Garage Web UI')->group(fn (): string => __('admin.groups.development'))->sort(30)
                    ->icon(Heroicon::OutlinedArchiveBox)
                    ->url(fn (): string => app(ServiceCatalog::class)->links()['garage-webui']['url'], shouldOpenInNewTab: true)
                    ->visible(fn (): bool => DevelopmentServices::canAccess()),
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                CatalogOverview::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ], isPersistent: true)
            ->middleware([SetAdminLocale::class], isPersistent: true);
    }
}
