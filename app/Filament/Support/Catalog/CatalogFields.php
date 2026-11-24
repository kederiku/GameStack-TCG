<?php

namespace App\Filament\Support\Catalog;

use App\Models\Catalog;
use App\Models\Game;
use App\Models\Language;
use App\Models\Series;
use App\Services\CatalogImages;
use App\Support\Catalog\CatalogPresentation;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class CatalogFields
{
    public static function name(?string $label = null): TextInput
    {
        $label ??= __('admin.labels.name');

        return TextInput::make('name')->label($label)->required()->maxLength(255);
    }

    public static function key(): TextInput
    {
        return TextInput::make('key')
            ->label(__('admin.labels.internal-identifier'))
            ->disabledOn('edit')
            ->maxLength(255)
            ->helperText(__('admin.fields.key_help'));
    }

    public static function description(string $field): Textarea
    {
        return Textarea::make($field)->label(__('admin.labels.description'))->rows(4)->columnSpanFull()
            ->helperText(__('admin.fields.description_help'));
    }

    public static function sourceUrl(): TextInput
    {
        return TextInput::make('source_url')->label(__('admin.labels.source-url'))->url()->maxLength(2048)->columnSpanFull()
            ->helperText(__('admin.fields.source_help'));
    }

    public static function order(): TextInput
    {
        return TextInput::make('sort_order')->label(__('admin.labels.display-order'))->integer()
            ->minValue(0)->maxValue(2147483647)->required()->default(0)
            ->helperText(__('admin.fields.order_help'));
    }

    public static function visibility(): Toggle
    {
        return Toggle::make('is_visible')->label(__('admin.labels.show-on-site'))->default(true)
            ->helperText(__('admin.fields.visibility_help'));
    }

    /** @param array<string, string> $options */
    public static function enum(string $field, string $label, array $options): Select
    {
        return Select::make($field)->label($label)->options($options)->required()->native(false);
    }

    public static function game(): Select
    {
        return Select::make('game_id')->label(__('admin.labels.game'))->required()->searchable()->disabledOn('edit')
            ->helperText(__('admin.fields.parent_help'))
            ->options(fn (): array => Game::query()->get()->mapWithKeys(fn (Game $game): array => [$game->id => CatalogLabels::name($game)])->sort()->all());
    }

    public static function catalogue(): Select
    {
        return Select::make('catalog_id')->label(__('admin.labels.catalog'))->required()->searchable()->disabledOn('edit')
            ->helperText(__('admin.fields.catalog_help'))
            ->options(fn (): array => Catalog::query()->with(['game', 'defaultLanguage'])
                ->orderBy(CatalogPresentation::nameForQuery(new Catalog, CatalogLabels::locale()))->orderBy('id')->get()
                ->mapWithKeys(fn (Catalog $catalog): array => [$catalog->id => self::catalogueLabel($catalog)])->all());
    }

    public static function catalogueLabel(Catalog $catalog): string
    {
        return CatalogLabels::name($catalog->game).' — '.CatalogLabels::name($catalog).' ('.$catalog->defaultLanguage->code.')';
    }

    public static function series(): Select
    {
        return Select::make('series_id')->label(__('admin.labels.series'))->searchable()
            ->helperText(__('admin.fields.series_help'))
            ->disabled(fn (Get $get): bool => self::positiveId($get('catalog_id')) === null)
            ->options(function (Get $get): array {
                $catalogId = self::positiveId($get('catalog_id'));

                return $catalogId === null ? [] : Series::query()->where('catalog_id', $catalogId)
                    ->with('catalog.defaultLanguage')->get()->mapWithKeys(fn (Series $series): array => [$series->id => CatalogLabels::name($series)])->sort()->all();
            });
    }

    public static function positiveId(mixed $value): ?int
    {
        if (! is_int($value) && ! is_string($value)) {
            return null;
        }

        $id = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

        return $id === false ? null : $id;
    }

    public static function language(string $field = 'language_id', ?string $label = null): Select
    {
        $label ??= __('admin.labels.language');

        return Select::make($field)->label($label)->required()->searchable()
            ->options(fn (): array => Language::query()->orderBy('name')->get()
                ->mapWithKeys(fn (Language $language): array => [$language->id => CatalogLabels::language($language)])->sort()->all());
    }

    public static function image(string $label, string $description = ''): FileUpload
    {
        return FileUpload::make('image')->label($label)
            ->image()
            ->disk(fn (): string => CatalogImages::diskName())
            ->visibility('public')
            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/avif', 'image/svg+xml'])
            ->maxSize(5120)
            ->rules(['dimensions:max_width=4096,max_height=4096'])
            ->preventFilePathTampering()
            ->storeFiles(false)
            ->helperText(trim($description.' '.__('admin.image-help')));
    }

    /**
     * @param  list<Component>  $presentation
     * @param  list<Component>  $publication
     * @param  list<Component>  $advanced
     */
    public static function layout(Schema $schema, array $presentation, array $publication = [], ?FileUpload $image = null, ?string $description = null, array $advanced = []): Schema
    {
        $main = Section::make(__('admin.labels.presentation'))->icon(Heroicon::OutlinedIdentification)
            ->columns(['default' => 1, 'md' => 2])->schema($presentation)->columnSpanFull();
        $content = [$main];
        if ($advanced !== []) {
            $content[] = Section::make(__('admin.labels.advanced-options'))->icon(Heroicon::OutlinedAdjustmentsHorizontal)
                ->description(__('admin.fields.advanced_help'))
                ->collapsed()->columns(['default' => 1, 'md' => 2])->schema($advanced);
        }
        if ($description !== null) {
            $content[] = self::help($description);
        }

        if ($publication === [] && $image === null) {
            return $schema->columns(1)->components($content);
        }

        $sidebar = [];
        if ($image !== null) {
            $sidebar[] = Section::make(__('admin.labels.artwork'))->icon(Heroicon::OutlinedPhoto)->schema([$image]);
        }
        if ($publication !== []) {
            $sidebar[] = Section::make(__('admin.labels.publication'))->icon(Heroicon::OutlinedGlobeAlt)
                ->description(__('admin.fields.publication_help'))
                ->schema($publication);
        }

        return $schema->columns(['default' => 1, 'lg' => 1, 'xl' => 3])->components([
            Group::make($content)->columnSpan(['xl' => 2]),
            Group::make($sidebar)->columnSpan(['xl' => 1]),
        ]);
    }

    public static function help(string $text): Section
    {
        return Section::make(__('admin.labels.understanding-this-record'))->icon(Heroicon::OutlinedQuestionMarkCircle)
            ->collapsed()->schema([Text::make($text)])->columnSpanFull();
    }
}
