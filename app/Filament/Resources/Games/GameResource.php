<?php

namespace App\Filament\Resources\Games;

use App\Filament\Concerns\HasLocalizedRoutes;
use App\Filament\Resources\Games\Pages\CreateGame;
use App\Filament\Resources\Games\Pages\EditGame;
use App\Filament\Resources\Games\Pages\ListGames;
use App\Filament\Resources\Games\Schemas\GameForm;
use App\Filament\Resources\Games\Tables\GamesTable;
use App\Filament\Support\LocalizedPageRegistration;
use App\Models\Game;
use BackedEnum;
use Filament\Resources\Pages\PageRegistration;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

/** @extends resource<Game> */
class GameResource extends Resource
{
    use HasLocalizedRoutes;

    protected static string $localizationKey = 'games';

    protected static ?string $model = Game::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPuzzlePiece;

    protected static ?int $navigationSort = 10;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return GameForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return GamesTable::configure($table);
    }

    /** @return array<string, PageRegistration> */
    public static function getPages(): array
    {
        return [
            'index' => LocalizedPageRegistration::make(ListGames::class, 'index'),
            'create' => LocalizedPageRegistration::make(CreateGame::class, 'create'),
            'edit' => LocalizedPageRegistration::make(EditGame::class, 'edit'),
        ];
    }
}
