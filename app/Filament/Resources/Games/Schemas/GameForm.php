<?php

namespace App\Filament\Resources\Games\Schemas;

use App\Filament\Support\Catalog\TranslationFields;
use App\Models\Game;
use App\Services\GameImages;
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
                    ...TranslationFields::schema(Game::class),
                    Section::make(__('admin.translation.reference'))
                        ->description(__('admin.games.presentation_help'))
                        ->icon(Heroicon::OutlinedIdentification)
                        ->columns(['default' => 1, 'md' => 2])
                        ->schema([
                            TextInput::make('name')
                                ->label(__('admin.translation.reference_name'))
                                ->required()
                                ->maxLength(255)
                                ->live(onBlur: true)
                                ->afterStateUpdated(function (Get $get, Set $set, ?string $old, ?string $state, string $operation): void {
                                    if ($operation === 'create' && (blank($get('slug')) || $get('slug') === Str::slug($old ?? ''))) {
                                        $set('slug', Str::slug($state ?? ''));
                                    }
                                }),
                            TextInput::make('publisher')
                                ->label(__('admin.labels.publisher'))
                                ->maxLength(255)
                                ->placeholder('Wizards of the Coast')
                                ->columnSpanFull(),
                            Textarea::make('reference_description')
                                ->label(__('admin.labels.description'))
                                ->placeholder(__('admin.games.description_placeholder'))
                                ->rows(5)
                                ->columnSpanFull(),
                        ]),
                    Section::make(__('admin.labels.advanced-options'))
                        ->icon(Heroicon::OutlinedAdjustmentsHorizontal)
                        ->collapsed()
                        ->schema([
                            TextInput::make('slug')
                                ->label(__('admin.labels.url-identifier'))
                                ->required()
                                ->maxLength(255)
                                ->regex('/\A[a-z0-9]+(?:-[a-z0-9]+)*\z/')
                                ->unique(ignoreRecord: true)
                                ->rules(fn (): array => [Rule::notIn(GameSlug::reserved())])
                                ->validationMessages(['not_in' => __('validation.catalog.reserved_slug')])
                                ->helperText(__('admin.games.slug_help')),
                        ]),
                    Section::make(__('admin.labels.history'))->collapsed()
                        ->description(__('admin.games.history_help'))
                        ->icon(Heroicon::OutlinedCalendarDays)
                        ->columns(['default' => 1, 'md' => 2])
                        ->schema([
                            TextInput::make('start_year')
                                ->label(__('admin.labels.creation-year'))
                                ->integer()
                                ->minValue(1000)
                                ->maxValue(9999)
                                ->placeholder('1993'),
                            TextInput::make('end_year')
                                ->label(__('admin.labels.end-year'))
                                ->integer()
                                ->minValue(1000)
                                ->maxValue(9999)
                                ->helperText(__('admin.games.end_year_help')),
                        ]),
                ])->columnSpan(['xl' => 2]),
                Group::make([
                    Section::make(__('admin.labels.artwork'))
                        ->description(__('admin.games.artwork_help'))
                        ->icon(Heroicon::OutlinedPhoto)
                        ->schema([
                            FileUpload::make('image')
                                ->label(__('admin.labels.game-image'))
                                ->hiddenLabel()
                                ->image()
                                ->disk(fn (): string => GameImages::diskName())
                                ->visibility('public')
                                ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/avif', 'image/svg+xml'])
                                ->maxSize(5120)
                                ->rules(['dimensions:max_width=4096,max_height=4096'])
                                ->preventFilePathTampering()
                                ->storeFiles(false)
                                ->helperText(__('admin.games.upload_help')),
                        ]),
                    Section::make(__('admin.labels.publication'))
                        ->description(__('admin.games.publication_help'))
                        ->icon(Heroicon::OutlinedGlobeAlt)
                        ->schema([
                            Toggle::make('is_visible')
                                ->label(__('admin.labels.show-on-site'))
                                ->default(true)
                                ->helperText(__('admin.games.visibility_help')),
                        ]),
                ])->columnSpan(['xl' => 1]),
            ]);
    }
}
