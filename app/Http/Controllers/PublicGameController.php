<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Support\Catalog\CatalogPresentation;
use App\Support\Catalog\PublicCatalog;
use App\Support\GameSlug;
use App\Support\PublicGames;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Fortify\Features;

class PublicGameController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('welcome', $this->pageProps());
    }

    public function show(Game $game, Request $request, PublicCatalog $catalog): Response
    {
        abort_if(! $game->is_visible || in_array($game->slug, GameSlug::reserved(), true), 404);

        return Inertia::render('games/show', [
            ...$this->pageProps($game),
            'gameDetails' => ['publisher' => $game->publisher, 'startYear' => $game->start_year, 'endYear' => $game->end_year, ...CatalogPresentation::description($game)],
            'catalogData' => $catalog->index($game, $request),
        ]);
    }

    public function expansion(Game $game, string $catalogKey, string $expansionKey, Request $request, PublicCatalog $catalog): Response
    {
        abort_if(! $game->is_visible || in_array($game->slug, GameSlug::reserved(), true), 404);

        return Inertia::render('games/expansion', [
            ...$this->pageProps($game),
            'expansion' => $catalog->show($game, $catalogKey, $expansionKey, $request),
        ]);
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
