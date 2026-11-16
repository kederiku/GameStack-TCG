<?php

namespace App\Support\Localization;

use App\Filament\Pages\DevelopmentServices;
use App\Filament\Resources\Catalogs\CatalogResource;
use App\Filament\Resources\ExpansionEditions\ExpansionEditionResource;
use App\Filament\Resources\Expansions\ExpansionResource;
use App\Filament\Resources\Games\GameResource;
use App\Filament\Resources\Languages\LanguageResource;
use App\Filament\Resources\Markets\MarketResource;
use App\Filament\Resources\Series\SeriesResource;
use Filament\Facades\Filament;
use Filament\Pages\Dashboard;
use Filament\Resources\Resource;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use InvalidArgumentException;

final class AdminPanels
{
    /** @return array<string, string> */
    public static function all(): array
    {
        $panels = [];
        foreach (array_keys(Locales::all()) as $locale) {
            $panels['admin-'.$locale] = $locale;
        }

        return $panels;
    }

    public static function locale(string $panelId): string
    {
        return self::all()[$panelId] ?? throw new InvalidArgumentException("Unknown admin panel [{$panelId}].");
    }

    public static function id(string $locale): string
    {
        Locales::definition($locale);

        return 'admin-'.$locale;
    }

    /** @return array<string, class-string<resource>> */
    public static function resources(): array
    {
        return [
            'games' => GameResource::class, 'catalogs' => CatalogResource::class,
            'series' => SeriesResource::class, 'expansions' => ExpansionResource::class,
            'expansion-editions' => ExpansionEditionResource::class,
            'languages' => LanguageResource::class, 'markets' => MarketResource::class,
        ];
    }

    /** Stable page identities are independent of the translated route names.
     * @return array<string, string>
     */
    public static function pages(string $locale): array
    {
        $panel = Filament::getPanel(self::id($locale));
        $pages = [
            'dashboard' => Dashboard::getRouteName($panel),
            'development-services' => DevelopmentServices::getRouteName($panel),
            'login' => $panel->generateRouteName('auth.login'),
        ];
        foreach (self::resources() as $key => $resource) {
            foreach (array_keys($resource::getPages()) as $page) {
                $pages[$key.'.'.$page] = $resource::getRouteBaseName($panel).'.'.$page;
            }
        }

        return $pages;
    }

    /** @param array<string, mixed> $parameters */
    public static function url(string $page, array $parameters, string $locale): string
    {
        $name = self::pages($locale)[$page] ?? throw new InvalidArgumentException("Unknown admin page [{$page}].");

        return route($name, $parameters, false);
    }

    /** @return array{locale: string, page: string}|null */
    public static function identity(?Route $route): ?array
    {
        foreach (self::all() as $locale) {
            $page = array_search($route?->getName(), self::pages($locale), true);
            if ($page !== false) {
                return ['locale' => $locale, 'page' => $page];
            }
        }

        return null;
    }

    /** Only Filament's table navigation state, never arbitrary redirect fields.
     * @return array<string, mixed>
     */
    public static function query(Request $request): array
    {
        $query = [];
        foreach (['tableFilters', 'tableSearch', 'tableSort', 'tableGrouping', 'tableGroupingDirection', 'page', 'recordsPerPage', 'activeTab', 'relation'] as $key) {
            $value = $request->query($key);
            if ($key === 'tableFilters' && is_array($value) && strlen(json_encode($value, JSON_THROW_ON_ERROR)) <= 4096) {
                $query[$key] = $value;
            } elseif (is_string($value) && strlen($value) <= 255) {
                $query[$key] = $value;
            }
        }

        return $query;
    }

    /** @return array<string, string> */
    public static function alternates(Request $request): array
    {
        $route = $request->route();
        $identity = $route instanceof Route ? self::identity($route) : null;
        if ($identity === null) {
            return [];
        }
        $urls = [];
        foreach (self::all() as $locale) {
            $urls[$locale] = self::url($identity['page'], [...$route->parameters(), ...self::query($request)], $locale);
        }

        return $urls;
    }
}
