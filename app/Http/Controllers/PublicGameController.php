<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Support\GameSlug;
use App\Support\PublicGames;
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
        abort_if(! $game->is_visible || in_array($game->slug, GameSlug::reserved(), true), 404);

        return Inertia::render('games/show', $this->pageProps($game));
    }

    /** @return array<string, mixed> */
    private function pageProps(?Game $currentGame = null): array
    {
        return [
            'games' => PublicGames::all(),
            'currentGame' => $currentGame ? PublicGames::present($currentGame) : null,
            'canRegister' => Features::enabled(Features::registration()),
        ];
    }
}
