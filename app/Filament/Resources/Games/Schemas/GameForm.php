<?php

namespace App\Filament\Resources\Games\Schemas;

use App\Support\GameSlug;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class GameForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(['default' => 1, 'lg' => 1, 'xl' => 3])
            ->components([
                Group::make([
                    Section::make('Présentation')
                        ->description('Les informations qui permettent d’identifier le jeu.')
                        ->icon(Heroicon::OutlinedIdentification)
                        ->columns(['default' => 1, 'md' => 2])
                        ->schema([
                            TextInput::make('name')
                                ->label('Nom')
                                ->required()
                                ->maxLength(255)
                                ->live(onBlur: true)
                                ->afterStateUpdated(function (Get $get, Set $set, ?string $old, ?string $state, string $operation): void {
                                    if ($operation === 'create' && (blank($get('slug')) || $get('slug') === Str::slug($old ?? ''))) {
                                        $set('slug', Str::slug($state ?? ''));
                                    }
                                }),
                            TextInput::make('slug')
                                ->label('Slug')
                                ->required()
                                ->maxLength(255)
                                ->regex('/\A[a-z0-9]+(?:-[a-z0-9]+)*\z/')
                                ->unique(ignoreRecord: true)
                                ->rules(fn (): array => [Rule::notIn(GameSlug::reserved())])
                                ->validationMessages(['not_in' => 'Ce slug est réservé à une page de l’application. Choisissez un autre identifiant.'])
                                ->helperText('Identifiant de l’URL : minuscules, chiffres et tirets. Conservé si le jeu est renommé.'),
                            TextInput::make('publisher')
                                ->label('Éditeur')
                                ->maxLength(255)
                                ->placeholder('Wizards of the Coast')
                                ->columnSpanFull(),
                            Textarea::make('description')
                                ->label('Description')
                                ->placeholder('Présentez l’univers et le principe du jeu en quelques phrases…')
                                ->rows(5)
                                ->columnSpanFull(),
                        ]),
                    Section::make('Historique')
                        ->description('Renseignez uniquement les années connues. Ces champs sont facultatifs.')
                        ->icon(Heroicon::OutlinedCalendarDays)
                        ->columns(['default' => 1, 'md' => 2])
                        ->schema([
                            TextInput::make('start_year')
                                ->label('Année de création')
                                ->integer()
                                ->minValue(1000)
                                ->maxValue(9999)
                                ->placeholder('1993'),
                            TextInput::make('end_year')
                                ->label('Année de fin')
                                ->integer()
                                ->minValue(1000)
                                ->maxValue(9999)
                                ->helperText('À renseigner si le TCG n’existe plus.'),
                        ]),
                ])->columnSpan(['xl' => 2]),
                Group::make([
                    Section::make('Visuel')
                        ->description('Le logo ou l’image qui représente le jeu.')
                        ->icon(Heroicon::OutlinedPhoto)
                        ->schema([
                            FileUpload::make('image')
                                ->label('Image du jeu')
                                ->hiddenLabel()
                                ->image()
                                ->disk('public')
                                ->visibility('public')
                                ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/svg+xml'])
                                ->maxSize(5120)
                                ->rules(['dimensions:max_width=4096,max_height=4096'])
                                ->preventFilePathTampering()
                                ->storeFiles(false)
                                ->helperText('JPEG, PNG, WebP ou SVG. 5 Mio maximum ; 4096 × 4096 px maximum hors SVG.'),
                        ]),
                    Section::make('Publication')
                        ->description('Gérez la présence du jeu sur le site.')
                        ->icon(Heroicon::OutlinedGlobeAlt)
                        ->schema([
                            Toggle::make('is_visible')
                                ->label('Afficher sur le site')
                                ->default(true)
                                ->helperText('Un jeu masqué et sa page sont retirés du site. Il reste modifiable ici.'),
                        ]),
                ])->columnSpan(['xl' => 1]),
            ]);
    }
}
