<?php

namespace App\Filament\Resources\Games\Pages;

use App\Filament\Resources\Catalogs\CatalogResource;
use App\Filament\Resources\Games\GameResource;
use App\Filament\Resources\Games\Pages\Concerns\SavesGame;
use App\Filament\Support\Catalog\CatalogActions;
use App\Models\Game;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Model;

class EditGame extends EditRecord
{
    public static bool $formActionsAreSticky = true;

    use SavesGame;

    protected static string $resource = GameResource::class;

    /** @return list<Action> */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('catalogues')->label('Catalogues du jeu')->icon(Heroicon::OutlinedCircleStack)
                ->visible(fn (): bool => CatalogResource::canAccess())
                ->url(fn (): string => CatalogResource::getUrl('index', [
                    'filters' => ['game' => ['value' => $this->getRecord()->getKey()]],
                ])),
            CatalogActions::delete(),
        ];
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
