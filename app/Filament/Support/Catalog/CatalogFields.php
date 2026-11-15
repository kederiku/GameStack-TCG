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
            ->label('Clé')
            ->disabledOn('edit')
            ->maxLength(255)
            ->helperText('Identifiant stable de la fiche, par exemple ecarlate-violet. Laissez vide pour le générer. Minuscules, chiffres et tirets uniquement ; non modifiable après création.');
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
            ->helperText('Classement souhaité : les petits nombres passent avant les grands. Laissez 0 si aucun ordre n’est prévu. Le tri des listes d’administration reste indépendant.');
    }

    public static function visibility(): Toggle
    {
        return Toggle::make('is_visible')->label('Afficher sur le site')->default(true)
            ->helperText('Masquer conserve la fiche dans l’administration. Les niveaux au-dessus d’elle doivent aussi être visibles pour permettre son affichage public.');
    }

    /** @param array<string, string> $options */
    public static function enum(string $field, string $label, array $options): Select
    {
        return Select::make($field)->label($label)->options($options)->required()->native(false);
    }

    public static function game(): Select
    {
        return Select::make('game_id')->label('Jeu')->required()->searchable()->disabledOn('edit')
            ->helperText('Le TCG auquel appartient ce catalogue, par exemple Pokémon. Ce rattachement est définitif après création.')
            ->options(fn (): array => Game::query()->orderBy('name')->pluck('name', 'id')->all());
    }

    public static function catalogue(): Select
    {
        return Select::make('catalog_id')->label('Catalogue')->required()->searchable()->disabledOn('edit')
            ->helperText('Choisissez la version du catalogue du jeu, par exemple Pokémon — International ou Japonais. Ce rattachement est définitif après création.')
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
            ->helperText('Facultatif : choisissez une série ou un bloc du même catalogue. Laissez vide si cet ensemble n’en fait pas partie.')
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
     */
    public static function layout(Schema $schema, array $presentation, array $publication = [], ?FileUpload $image = null, ?string $description = null): Schema
    {
        $main = Section::make('Présentation')->icon(Heroicon::OutlinedIdentification)
            ->description($description)
            ->columns(['default' => 1, 'md' => 2])->schema($presentation)->columnSpanFull();

        if ($publication === [] && $image === null) {
            return $schema->components([$main]);
        }

        $sidebar = [];
        if ($image !== null) {
            $sidebar[] = Section::make('Visuel')->icon(Heroicon::OutlinedPhoto)->schema([$image]);
        }
        if ($publication !== []) {
            $sidebar[] = Section::make('Publication')->icon(Heroicon::OutlinedGlobeAlt)
                ->description('L’affichage public du catalogue est en préparation. Ces réglages sont déjà conservés.')
                ->schema($publication);
        }

        return $schema->columns(['default' => 1, 'lg' => 1, 'xl' => 3])->components([
            Group::make([$main])->columnSpan(['xl' => 2]),
            Group::make($sidebar)->columnSpan(['xl' => 1]),
        ]);
    }
}
