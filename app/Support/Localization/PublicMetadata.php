<?php

namespace App\Support\Localization;

use App\Support\Catalog\CatalogRules;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;

final class PublicMetadata
{
    /** @return array{canonical: ?string, alternates: array<string, string>, robots: ?string} */
    public static function for(Request $request): array
    {
        $key = LocalizedRoutes::key($request);
        $route = $request->route();
        if (! $route instanceof Route || ! in_array($key, ['home', 'games.show', 'games.expansions.show'], true)
            || $request->has('signature') || in_array('signed', $route->gatherMiddleware(), true)) {
            return ['canonical' => null, 'alternates' => [], 'robots' => 'noindex,nofollow'];
        }

        $query = [];
        if ($key === 'games.show') {
            // Use the effective selections that produced the list, including catalog visibility.
            $filters = $request->attributes->get('public_catalog_filters', []);
            foreach (['catalog' => '*', 'language' => '*', 'type' => 'all', 'sort' => 'recent', 'series' => '', 'q' => ''] as $name => $default) {
                if (isset($filters[$name]) && $filters[$name] !== $default && $filters[$name] !== '') {
                    $query[$name] = $filters[$name];
                }
            }
        } elseif ($key === 'games.expansions.show') {
            $edition = LocalizedRoutes::query($request)['edition'] ?? '';
            if (CatalogRules::validLanguageCode($edition)) {
                $query['edition'] = CatalogRules::languageCode($edition);
            }
        }
        $filtered = $query !== [];
        $page = $request->attributes->getInt('public_catalog_page', 1);
        if ($key === 'games.show' && $page > 1) {
            $query['page'] = $page;
        }

        $urls = [];
        $parameters = $route->parameters();
        unset($parameters['_locale']);
        foreach (Locales::all() as $code => $definition) {
            $urls[$definition['htmlLang']] = LocalizedRoutes::url($key, [...$parameters, ...$query], $code, true);
        }
        $canonical = $urls[Locales::definition(LocalizedRoutes::locale($request))['htmlLang']];
        $urls['x-default'] = $urls[Locales::definition(Locales::default())['htmlLang']];

        return ['canonical' => $canonical, 'alternates' => $filtered ? [] : $urls, 'robots' => $filtered ? 'noindex,follow' : null];
    }
}
