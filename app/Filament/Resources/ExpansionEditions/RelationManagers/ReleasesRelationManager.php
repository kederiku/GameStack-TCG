<?php

namespace App\Filament\Resources\ExpansionEditions\RelationManagers;

use App\Filament\Support\Catalog\CatalogActions;
use App\Filament\Support\Catalog\CatalogFields;
use App\Filament\Support\Catalog\CatalogLabels;
use App\Filament\Support\Catalog\CatalogRelationManager;
use App\Filament\Support\Catalog\CatalogTables;
use App\Filament\Support\Catalog\TranslationFields;
use App\Models\ExpansionEdition;
use App\Models\ExpansionRelease;
use App\Models\Market;
use App\Support\Catalog\CatalogPresentation;
use App\Support\Localization\TranslatedContent;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ReleasesRelationManager extends CatalogRelationManager
{
    protected static string $relationship = 'releases';

    protected static bool $isLazy = false;

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('admin.labels.releases');
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('market_id')->label(__('admin.labels.market'))->required()->searchable()->disabledOn('edit')
                ->helperText(__('admin.releases.market_help'))
                ->options(fn (): array => Market::query()->get()->mapWithKeys(fn (Market $market): array => [$market->id => CatalogLabels::name($market)])->sort()->all()),
            CatalogFields::enum('channel', __('admin.labels.channel'), CatalogLabels::channels())->default('general')->disabledOn('edit')
                ->helperText(__('admin.releases.channel_help')),
            CatalogFields::enum('status', __('admin.labels.status'), CatalogLabels::statuses())->default('announced')
                ->helperText(__('admin.releases.status_help')),
            DatePicker::make('released_on')->label(__('admin.labels.release-date'))
                ->helperText(__('admin.releases.date_help')),
            ...TranslationFields::schema(ExpansionRelease::class),
            Section::make(__('admin.translation.reference'))->schema([
                TextInput::make('reference_announced_period')->label(__('admin.labels.announced-period'))->maxLength(255)
                    ->helperText(__('admin.releases.period_help')),
            ]),
            CatalogFields::sourceUrl(),
            CatalogFields::help(__('admin.releases.help')),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table->recordTitleAttribute('status')
            ->description(__('admin.releases.description'))
            ->searchPlaceholder(__('admin.labels.search'))
            ->emptyStateHeading(__('admin.labels.no-releases'))
            ->emptyStateDescription(__('admin.releases.empty_help'))
            ->columns([
                CatalogTables::relatedName('market', __('admin.labels.market'))
                    ->sortable(query: fn (Builder $query, string $direction): Builder => $query->orderBy(
                        Market::query()->selectSub(CatalogPresentation::nameForQuery(new Market, CatalogLabels::locale()), 'label')
                            ->whereColumn('markets.id', 'expansion_releases.market_id'), $direction === 'desc' ? 'desc' : 'asc')),
                CatalogTables::enum('channel', __('admin.labels.channel'), CatalogLabels::channels()),
                CatalogTables::enum('status', __('admin.labels.status'), CatalogLabels::statuses()),
                TextColumn::make('released_on')->label(__('admin.labels.release-date'))->isoDate('L')->sortable(),
                TextColumn::make('announced_period')->state(fn (ExpansionRelease $record): ?string => TranslatedContent::field($record, 'announced_period', CatalogLabels::locale())['value'])->label(__('admin.labels.announced-period')),
                TextColumn::make('source_url')->label(__('admin.labels.source'))->limit(40)->url(fn (ExpansionRelease $record): ?string => $record->source_url, true),
            ])
            ->defaultSort('released_on', 'desc')
            ->headerActions([
                $this->createCatalogAction(__('admin.labels.create-a-release'), 'edition_id')->after($this->notifyEditionStatusChange(...)),
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
