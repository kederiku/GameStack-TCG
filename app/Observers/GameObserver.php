<?php

namespace App\Observers;

use App\Models\Game;
use App\Services\GameImages;

class GameObserver
{
    public function updated(Game $game): void
    {
        if ($game->wasChanged('image')) {
            $this->deleteAfterCommit($game, $game->getRawOriginal('image'));
        }
    }

    public function deleted(Game $game): void
    {
        $this->deleteAfterCommit($game, $game->image);
    }

    private function deleteAfterCommit(Game $game, ?string $path): void
    {
        $game->getConnection()->afterCommit(
            fn () => rescue(fn () => app(GameImages::class)->deleteUnused($path)),
        );
    }
}
