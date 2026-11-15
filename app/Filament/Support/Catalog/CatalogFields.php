<?php

namespace App\Filament\Support\Catalog;

use App\Models\Catalog;
use App\Models\Game;
use App\Models\Language;
use App\Models\Series;
use App\Services\CatalogImages;
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
    public static function name(string $label = 'Nom'): TextInput
    {
        return TextInput::make('name')->label($label)->required()->maxLength(255);
    }

    public static function key(): TextInput
    {
        return TextInput::make('key')
            ->label('Identifiant interne')
            ->disabledOn('edit')
            ->maxLength(255)
            ->helperText('Généré si laissé vide. Minuscules, chiffres et tirets ; définitif après création.');
    }

    public static function description(): Textarea
    {
        return Textarea::make('description')->label('Description')->rows(4)->columnSpanFull()
            ->helperText('Facultatif : présentez cette fiche en quelques phrases.');
    }

    public static function sourceUrl(): TextInput
    {
        return TextInput::make('source_url')->label('URL source')->url()->maxLength(2048)->columnSpanFull()
            ->helperText('Lien vers la page qui confirme ces informations, de préférence sur le site de l’éditeur.');
    }

    public static function order(): TextInput
    {
        return TextInput::make('sort_order')->label('Ordre d’affichage')->integer()
            ->minValue(0)->maxValue(2147483647)->required()->default(0)
            ->helperText('Les petits nombres passent en premier. 0 par défaut ; sans effet sur le tri de l’admin.');
    }

    public static function visibility(): Toggle
    {
        return Toggle::make('is_visible')->label('Afficher sur le site')->default(true)
            ->helperText('Une fiche masquée reste modifiable ici. Ses parents doivent aussi être visibles pour l’affichage public.');
    }

    /** @param array<string, string> $options */
    public static function enum(string $field, string $label, array $options): Select
    {
        return Select::make($field)->label($label)->options($options)->required()->native(false);
    }

    public static function game(): Select
    {
        return Select::make('game_id')->label('Jeu')->required()->searchable()->disabledOn('edit')
            ->helperText('Rattachement définitif après création.')
            ->options(fn (): array => Game::query()->orderBy('name')->pluck('name', 'id')->all());
    }

    public static function catalogue(): Select
    {
        return Select::make('catalog_id')->label('Catalogue')->required()->searchable()->disabledOn('edit')
            ->helperText('Version du jeu, par exemple Pokémon — International. Rattachement définitif après création.')
            ->options(fn (): array => Catalog::query()->with(['game', 'defaultLanguage'])->orderBy('name')->get()
                ->mapWithKeys(fn (Catalog $catalog): array => [$catalog->id => self::catalogueLabel($catalog)])->all());
    }

    public static function catalogueLabel(Catalog $catalog): string
    {
        return "{$catalog->game->name} — {$catalog->name} ({$catalog->defaultLanguage->code})";
    }

    public static function series(): Select
    {
        return Select::make('series_id')->label('Série')->searchable()
            ->helperText('Facultatif. Une série ou un bloc du même catalogue.')
            ->disabled(fn (Get $get): bool => self::positiveId($get('catalog_id')) === null)
            ->options(function (Get $get): array {
                $catalogId = self::positiveId($get('catalog_id'));

                return $catalogId === null ? [] : Series::query()->where('catalog_id', $catalogId)
                    ->orderBy('name')->pluck('name', 'id')->all();
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

    public static function language(string $field = 'language_id', string $label = 'Langue'): Select
    {
        return Select::make($field)->label($label)->required()->searchable()
            ->options(fn (): array => Language::query()->orderBy('name')->get()
                ->mapWithKeys(fn (Language $language): array => [$language->id => "{$language->name} ({$language->code})"])->all());
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
            ->helperText(trim($description.' JPEG, PNG, WebP, AVIF ou SVG. Taille maximale de 5 Mio et 4096 × 4096 px hors SVG.'));
    }

    /**
     * @param  list<Component>  $presentation
     * @param  list<Component>  $publication
     * @param  list<Component>  $advanced
     */
    public static function layout(Schema $schema, array $presentation, array $publication = [], ?FileUpload $image = null, ?string $description = null, array $advanced = []): Schema
    {
        $main = Section::make('Présentation')->icon(Heroicon::OutlinedIdentification)
            ->columns(['default' => 1, 'md' => 2])->schema($presentation)->columnSpanFull();
        $content = [$main];
        if ($advanced !== []) {
            $content[] = Section::make('Options avancées')->icon(Heroicon::OutlinedAdjustmentsHorizontal)
                ->description('Identifiant et classement : les valeurs proposées conviennent dans la plupart des cas.')
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
            $sidebar[] = Section::make('Visuel')->icon(Heroicon::OutlinedPhoto)->schema([$image]);
        }
        if ($publication !== []) {
            $sidebar[] = Section::make('Publication')->icon(Heroicon::OutlinedGlobeAlt)
                ->description('Affichage public en préparation. Réglages conservés.')
                ->schema($publication);
        }

        return $schema->columns(['default' => 1, 'lg' => 1, 'xl' => 3])->components([
            Group::make($content)->columnSpan(['xl' => 2]),
            Group::make($sidebar)->columnSpan(['xl' => 1]),
        ]);
    }

    public static function help(string $text): Section
    {
        return Section::make('Comprendre cette fiche')->icon(Heroicon::OutlinedQuestionMarkCircle)
            ->collapsed()->schema([Text::make($text)])->columnSpanFull();
    }
}
