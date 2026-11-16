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
use Illuminate\Database\Eloquent\Model;

class RelationsRelationManager extends CatalogRelationManager
{
    protected static string $relationship = 'relations';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('admin.labels.relationships');
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('source_expansion_id')->label(__('admin.labels.source-expansion'))
                ->visibleOn('edit')->disabled()->native(false)
                ->helperText(__('admin.relationships.source_help'))
                ->getOptionLabelUsing(fn (mixed $value): ?string => $this->expansionLabel($value)),
            Select::make('target_expansion_id')->label(__('admin.labels.target-expansion'))->required()->searchable()->disabledOn('edit')
                ->helperText(__('admin.relationships.target_help'))
                ->getSearchResultsUsing(fn (string $search): array => $this->candidateExpansions($search))
                ->getOptionLabelUsing(fn (mixed $value): ?string => $this->expansionLabel($value)),
            CatalogFields::enum('type', __('admin.labels.type'), CatalogLabels::relationTypes())->disabledOn('edit')
                ->helperText(__('admin.relationships.type_help')),
            Textarea::make('notes')->label(__('admin.labels.justification'))->required()->rows(4)->columnSpanFull()
                ->helperText(__('admin.relationships.justification_help')),
            CatalogFields::sourceUrl()->required(),
            CatalogFields::help(__('admin.relationships.help')),
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
            ->description(__('admin.relationships.description'))
            ->searchPlaceholder(__('admin.labels.search'))
            ->emptyStateHeading(__('admin.labels.no-relationships'))
            ->emptyStateDescription(__('admin.relationships.empty_help'))
            ->columns([
                TextColumn::make('sourceExpansion.name')->label(__('admin.labels.source-expansion'))->searchable()
                    ->description(fn (ExpansionRelation $record): string => $record->sourceExpansion->catalog->name)
                    ->url(fn (ExpansionRelation $record): string => ExpansionResource::getUrl('edit', ['record' => $record->source_expansion_id])),
                CatalogTables::enum('type', __('admin.labels.type'), CatalogLabels::relationTypes()),
                TextColumn::make('targetExpansion.name')->label(__('admin.labels.target-expansion'))->searchable()
                    ->description(fn (ExpansionRelation $record): string => $record->targetExpansion->catalog->name)
                    ->url(fn (ExpansionRelation $record): string => ExpansionResource::getUrl('edit', ['record' => $record->target_expansion_id])),
                TextColumn::make('notes')->label(__('admin.labels.justification'))->limit(60),
                TextColumn::make('source_url')->label(__('admin.labels.source'))->limit(40)->url(fn (ExpansionRelation $record): string => $record->source_url, true),
            ])
            ->headerActions([$this->createCatalogAction(__('admin.labels.create-a-relationship'), 'source_expansion_id')])
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
