<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Support\GameSlug;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Fortify\Features;

class PublicGameController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('welcome', $this->pageProps());
    }

    public function show(Game $game): Response
    {
        abort_if(in_array($game->slug, GameSlug::reserved(), true), 404);

        return Inertia::render('games/show', $this->pageProps($game));
    }

    /** @return array<string, mixed> */
    private function pageProps(?Game $currentGame = null): array
    {
        return [
            'games' => Game::query()
                ->whereNotIn('slug', GameSlug::reserved())
                ->orderBy('name')->orderBy('id')
                ->get(['id', 'name', 'slug', 'image'])
                ->map(fn (Game $game): array => $this->publicGame($game)),
            'currentGame' => $currentGame ? $this->publicGame($currentGame) : null,
            'canRegister' => Features::enabled(Features::registration()),
        ];
    }

    /** @return array{id: int, name: string, slug: string, imageUrl: string|null, url: string} */
    private function publicGame(Game $game): array
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
