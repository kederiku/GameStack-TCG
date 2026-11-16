<?php

namespace App\Support;

use App\Models\Game;
use App\Services\GameImages;
use App\Support\Localization\LocalizedRoutes;
use Illuminate\Support\Collection;

/** @phpstan-type PublicGame array{id: int, name: string, slug: string, imageUrl: string|null, url: string} */
class PublicGames
{
    /** @return Collection<int, PublicGame> */
    public static function all(): Collection
    {
        return Game::query()
            ->where('is_visible', true)
            ->whereNotIn('slug', GameSlug::reserved())
            ->orderBy('name')->orderBy('id')
            ->get(['id', 'name', 'slug', 'image'])
            ->map(fn (Game $game): array => self::present($game));
    }

    /** @return PublicGame */
    public static function present(Game $game): array
    {
        return [
            'id' => $game->id,
            'name' => $game->name,
            'slug' => $game->slug,
            'imageUrl' => $game->image ? GameImages::disk()->url($game->image) : null,
            'url' => LocalizedRoutes::url('games.show', ['game' => $game->slug], absolute: false),
        ];
    }
}
