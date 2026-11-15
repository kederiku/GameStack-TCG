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
                ->helperText('Territoire où cette édition est commercialisée. Sa langue reste celle de l’édition ; le marché ne peut plus être modifié après création.')
                ->options(fn (): array => Market::query()->orderBy('name')->pluck('name', 'id')->all()),
            CatalogFields::enum('channel', 'Canal', CatalogLabels::CHANNELS)->default('general')->disabledOn('edit')
                ->helperText('Mode de distribution : une avant-première ou une sortie en boutiques spécialisées peut précéder la distribution générale. Ce choix est définitif après création.'),
            CatalogFields::enum('status', 'État', CatalogLabels::STATUSES)->default('announced')
                ->helperText('Annoncée : sortie à venir ; Publiée : sortie déjà effective ; Annulée : sortie abandonnée. Une sortie publiée fait aussi passer son édition à « Publiée ».'),
            DatePicker::make('released_on')->label('Date de sortie')
                ->helperText('Date exacte pour ce marché et ce canal, lorsqu’elle est confirmée. Laissez vide si seule une période est connue.'),
            TextInput::make('announced_period')->label('Période annoncée')->maxLength(255)
                ->helperText('Facultatif : indication de l’éditeur, par exemple « deuxième trimestre 2027 ». N’inventez pas de jour précis.'),
            CatalogFields::sourceUrl(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table->recordTitleAttribute('status')
            ->description('Une sortie indique où, comment et quand cette édition est commercialisée. Une fiche par marché et canal décrit la première sortie, pas les réapprovisionnements.')
            ->searchPlaceholder('Rechercher')
            ->emptyStateHeading('Aucune sortie')
            ->emptyStateDescription('Ajoutez les marchés et canaux confirmés. Une date exacte peut rester vide si elle n’est pas connue.')
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
