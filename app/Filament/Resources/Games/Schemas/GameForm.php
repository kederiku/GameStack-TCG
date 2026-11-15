<?php

namespace App\Filament\Resources\Games\Schemas;

use App\Support\GameSlug;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class GameForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
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
                ->helperText('Identifiant unique : lettres minuscules, chiffres et tirets. Il reste inchangé lorsque le jeu est renommé.'),
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
            TextInput::make('publisher')
                ->label('Éditeur')
                ->maxLength(255)
                ->placeholder('Wizards of the Coast')
                ->columnSpanFull(),
            Textarea::make('description')
                ->label('Description')
                ->rows(4)
                ->columnSpanFull(),
            FileUpload::make('image')
                ->label('Image du jeu')
                ->image()
                ->disk('public')
                ->visibility('public')
                ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/svg+xml'])
                ->maxSize(5120)
                ->rules(['dimensions:max_width=4096,max_height=4096'])
                ->preventFilePathTampering()
                ->storeFiles(false)
                ->helperText('Optionnelle, 5 Mio maximum. JPEG, PNG et WebP : 4096 × 4096 pixels maximum, conversion en WebP. SVG : format vectoriel conservé et contenu nettoyé. Les changements prennent effet à l’enregistrement.')
                ->columnSpanFull(),
        ]);
    }
}
