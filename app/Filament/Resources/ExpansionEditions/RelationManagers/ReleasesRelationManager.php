<?php

namespace App\Filament\Resources\ExpansionEditions\RelationManagers;

use App\Filament\Support\Catalog\CatalogActions;
use App\Filament\Support\Catalog\CatalogFields;
use App\Filament\Support\Catalog\CatalogLabels;
use App\Filament\Support\Catalog\CatalogRelationManager;
use App\Filament\Support\Catalog\CatalogTables;
use App\Models\ExpansionEdition;
use App\Models\ExpansionRelease;
use App\Models\Market;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ReleasesRelationManager extends CatalogRelationManager
{
    protected static string $relationship = 'releases';

    protected static bool $isLazy = false;

    protected static ?string $title = 'Sorties';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('market_id')->label('Marché')->required()->searchable()->disabledOn('edit')
                ->options(fn (): array => Market::query()->orderBy('name')->pluck('name', 'id')->all()),
            CatalogFields::enum('channel', 'Canal', CatalogLabels::CHANNELS)->default('general')->disabledOn('edit'),
            CatalogFields::enum('status', 'État', CatalogLabels::STATUSES)->default('announced'),
            DatePicker::make('released_on')->label('Date de sortie'),
            TextInput::make('announced_period')->label('Période annoncée')->maxLength(255),
            CatalogFields::sourceUrl(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table->recordTitleAttribute('status')
            ->searchPlaceholder('Rechercher')
            ->emptyStateHeading('Aucune sortie')
            ->columns([
                TextColumn::make('market.name')->label('Marché')->searchable()->sortable(),
                CatalogTables::enum('channel', 'Canal', CatalogLabels::CHANNELS),
                CatalogTables::enum('status', 'État', CatalogLabels::STATUSES),
                TextColumn::make('released_on')->label('Date de sortie')->date('d/m/Y')->sortable(),
                TextColumn::make('announced_period')->label('Période annoncée'),
                TextColumn::make('source_url')->label('Source')->limit(40)->url(fn (ExpansionRelease $record): ?string => $record->source_url, true),
            ])
            ->defaultSort('released_on', 'desc')
            ->headerActions([
                $this->createCatalogAction('Créer une sortie', 'edition_id')->after($this->notifyEditionStatusChange(...)),
            ])
            ->recordActions([
                $this->editCatalogAction()->after($this->notifyEditionStatusChange(...)),
                CatalogActions::delete(),
            ]);
    }

    private function notifyEditionStatusChange(): void
    {
        $edition = $this->getOwnerRecord();
        assert($edition instanceof ExpansionEdition);

        $previousStatus = $edition->status;

        if ($edition->refresh()->status !== $previousStatus) {
            $this->dispatch('expansion-edition-status-changed');
        }
    }
}
