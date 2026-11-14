<?php

namespace App\Filament\Resources\Games\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

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
                ->helperText('Identifiant unique : lettres minuscules, chiffres et tirets. Il reste inchangé lorsque le jeu est renommé.'),
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
