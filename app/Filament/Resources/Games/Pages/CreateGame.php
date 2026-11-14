<?php

namespace App\Filament\Resources\Games\Pages;

use App\Filament\Resources\Games\GameResource;
use App\Filament\Resources\Games\Pages\Concerns\SavesGame;
use App\Models\Game;
use Filament\Resources\Pages\CreateRecord;

class CreateGame extends CreateRecord
{
    use SavesGame;

    protected static string $resource = GameResource::class;

    /** @param array<string, mixed> $data */
    protected function handleRecordCreation(array $data): Game
    {
        return $this->saveGame(new Game, $data);
    }
}
