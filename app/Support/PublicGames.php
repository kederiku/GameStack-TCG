<?php

namespace App\Support;

use App\Models\Game;
use App\Services\GameImages;
use App\Support\Catalog\CatalogPresentation;
use App\Support\Catalog\CatalogRules;
use App\Support\Localization\Locales;
use App\Support\Localization\LocalizedRoutes;
use Illuminate\Support\Collection;

/** @phpstan-type PublicGame array{id: int, name: string, nameLanguage: ?string, nameFallback: bool, slug: string, imageUrl: string|null, url: string} */
class PublicGames
{
    /** @return Collection<int, PublicGame> */
    public static function all(): Collection
    {
        return Game::query()
            ->where('is_visible', true)
            ->whereNotIn('slug', GameSlug::reserved())
            ->orderByRaw('coalesce(display_name ->> ?::text, display_name ->> ?::text, name)', [
                CatalogRules::languageCode(LocalizedRoutes::locale(request())), Locales::default(),
            ])->orderBy('id')
            ->get(['id', 'name', 'display_name', 'slug', 'image'])
            ->map(fn (Game $game): array => self::present($game));
    }

    /** @return PublicGame */
    public static function present(Game $game): array
    {
        return [
            'id' => $game->id,
            ...CatalogPresentation::displayName($game),
            'slug' => $game->slug,
            'imageUrl' => $game->image ? GameImages::disk()->url($game->image) : null,
            'url' => LocalizedRoutes::url('games.show', ['game' => $game->slug], absolute: false),
        ];
    }
}
