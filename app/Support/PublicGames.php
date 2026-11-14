<?php

namespace App\Support;

use App\Models\Game;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

/** @phpstan-type PublicGame array{id: int, name: string, slug: string, imageUrl: string|null, url: string} */
class PublicGames
{
    /** @return Collection<int, PublicGame> */
    public static function all(): Collection
    {
        return Game::query()
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
            'imageUrl' => $game->image ? Storage::disk('public')->url($game->image) : null,
            'url' => route('games.show', ['game' => $game->slug], absolute: false),
        ];
    }
}
