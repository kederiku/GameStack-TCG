<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Support\Catalog\PublicCatalog;
use App\Support\GameSlug;
use App\Support\Localization\LocalizedRoutes;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class LegacyRouteController extends Controller
{
    public function page(Request $request): RedirectResponse
    {
        return $this->redirect($request, (string) $request->route()?->getName());
    }

    public function game(Game $game, Request $request): RedirectResponse
    {
        $this->visible($game);

        return $this->redirect($request, 'games.show', ['game' => $game->slug]);
    }

    public function expansion(Game $game, string $catalogKey, string $expansionKey, Request $request, PublicCatalog $catalog): RedirectResponse
    {
        $this->visible($game);
        $catalog->show($game, $catalogKey, $expansionKey, $request);

        return $this->redirect($request, 'games.expansions.show', ['game' => $game->slug, 'catalogKey' => $catalogKey, 'expansionKey' => $expansionKey]);
    }

    private function visible(Game $game): void
    {
        abort_if(! $game->is_visible || in_array($game->slug, GameSlug::reserved(), true), 404);
    }

    /** @param array<string, string> $parameters */
    private function redirect(Request $request, string $key, array $parameters = []): RedirectResponse
    {
        return redirect(LocalizedRoutes::url($key, [...$parameters, ...LocalizedRoutes::query($request)]), 302)
            ->withHeaders(['Cache-Control' => 'private, no-store']);
    }
}
