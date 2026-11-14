<?php

namespace App\Filament\Resources\Games\Pages;

use App\Filament\Resources\Games\GameResource;
use App\Filament\Resources\Games\Pages\Concerns\SavesGame;
use App\Models\Game;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditGame extends EditRecord
{
    use SavesGame;

    protected static string $resource = GameResource::class;

    /** @return array<DeleteAction> */
    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()->label('Supprimer')];
    }

    /** @param array<string, mixed> $data */
    protected function handleRecordUpdate(Model $record, array $data): Game
    {
        assert($record instanceof Game);

        $saved = $this->saveGame($record, $data);
        $record->setRawAttributes($saved->getAttributes(), sync: true);

        return $record;
    }

    protected function afterSave(): void
    {
        $this->refreshFormData(['image']);
    }
}
