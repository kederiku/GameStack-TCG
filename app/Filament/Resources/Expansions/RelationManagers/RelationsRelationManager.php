<?php

namespace App\Filament\Resources\Expansions\RelationManagers;

use App\Filament\Resources\Expansions\ExpansionResource;
use App\Filament\Support\Catalog\CatalogActions;
use App\Filament\Support\Catalog\CatalogFields;
use App\Filament\Support\Catalog\CatalogLabels;
use App\Filament\Support\Catalog\CatalogRelationManager;
use App\Filament\Support\Catalog\CatalogTables;
use App\Models\Expansion;
use App\Models\ExpansionRelation;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class RelationsRelationManager extends CatalogRelationManager
{
    protected static string $relationship = 'relations';

    protected static ?string $title = 'Rapprochements';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('source_expansion_id')->label('Extension source')
                ->visibleOn('edit')->disabled()->native(false)
                ->helperText('Point de départ du lien : lisez « source → type de lien → cible ».')
                ->getOptionLabelUsing(fn (mixed $value): ?string => $this->expansionLabel($value)),
            Select::make('target_expansion_id')->label('Extension cible')->required()->searchable()->disabledOn('edit')
                ->helperText('Un ensemble du même jeu, y compris dans un autre catalogue. Choix définitif après création.')
                ->getSearchResultsUsing(fn (string $search): array => $this->candidateExpansions($search))
                ->getOptionLabelUsing(fn (mixed $value): ?string => $this->expansionLabel($value)),
            CatalogFields::enum('type', 'Type', CatalogLabels::RELATION_TYPES)->disabledOn('edit')
                ->helperText('Type et ensembles définitifs après création. Consultez l’aide pour le sens du lien.'),
            Textarea::make('notes')->label('Justification')->required()->rows(4)->columnSpanFull()
                ->helperText('Expliquez le lien et ses limites à partir d’une source vérifiable.'),
            CatalogFields::sourceUrl()->required(),
            CatalogFields::help('Lisez le lien : source → type de lien → cible. « Liée à » est réciproque. « Adaptée de » et « Inclut du contenu de » se lisent de la source vers la cible ; à la création, l’extension ouverte est la source. Des noms proches ne suffisent pas à établir que deux ensembles contiennent les mêmes cartes.'),
        ]);
    }

    public function table(Table $table): Table
    {
        $owner = $this->getOwnerRecord();
        assert($owner instanceof Expansion);

        // A symmetric relation may have this extension as either endpoint after canonicalization.
        return $table->query(ExpansionRelation::query()
            ->where(fn (Builder $query): Builder => $query->where('source_expansion_id', $owner->id)->orWhere('target_expansion_id', $owner->id))
            ->with(['sourceExpansion.catalog', 'targetExpansion.catalog']))
            ->recordTitleAttribute('type')
            ->description('Documentez les liens entre ensembles sans les fusionner ni supposer un contenu identique. Une justification et une source sont nécessaires.')
            ->searchPlaceholder('Rechercher')
            ->emptyStateHeading('Aucun rapprochement')
            ->emptyStateDescription('Ajoutez un lien seulement si vous disposez d’une source qui le confirme.')
            ->columns([
                TextColumn::make('sourceExpansion.name')->label('Extension source')->searchable()
                    ->description(fn (ExpansionRelation $record): string => $record->sourceExpansion->catalog->name)
                    ->url(fn (ExpansionRelation $record): string => ExpansionResource::getUrl('edit', ['record' => $record->source_expansion_id])),
                CatalogTables::enum('type', 'Type', CatalogLabels::RELATION_TYPES),
                TextColumn::make('targetExpansion.name')->label('Extension cible')->searchable()
                    ->description(fn (ExpansionRelation $record): string => $record->targetExpansion->catalog->name)
                    ->url(fn (ExpansionRelation $record): string => ExpansionResource::getUrl('edit', ['record' => $record->target_expansion_id])),
                TextColumn::make('notes')->label('Justification')->limit(60),
                TextColumn::make('source_url')->label('Source')->limit(40)->url(fn (ExpansionRelation $record): string => $record->source_url, true),
            ])
            ->headerActions([$this->createCatalogAction('Créer un rapprochement', 'source_expansion_id')])
            ->recordActions([$this->editCatalogAction(), CatalogActions::delete()]);
    }

    /** @return array<int, string> */
    private function candidateExpansions(string $search): array
    {
        $owner = $this->getOwnerRecord();
        assert($owner instanceof Expansion);

        return Expansion::query()->with('catalog')
            ->whereKeyNot($owner->id)
            ->whereRelation('catalog', 'game_id', $owner->catalog->game_id)
            ->where(fn (Builder $query): Builder => $query
                ->whereLike('name', "%{$search}%")
                ->orWhereLike('key', "%{$search}%")
                ->orWhereRelation('catalog', fn (Builder $catalog): Builder => $catalog->whereLike('name', "%{$search}%")))
            ->orderBy('name')->limit(50)->get()
            ->mapWithKeys(fn (Expansion $expansion): array => [$expansion->id => $this->label($expansion)])->all();
    }

    private function expansionLabel(mixed $value): ?string
    {
        $id = CatalogFields::positiveId($value);
        $expansion = $id === null ? null : Expansion::query()->with('catalog')->find($id);

        return $expansion === null ? null : $this->label($expansion);
    }

    private function label(Expansion $expansion): string
    {
        return "{$expansion->name} — {$expansion->catalog->name} ({$expansion->key})";
    }
}
