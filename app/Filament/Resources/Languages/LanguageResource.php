<?php

namespace App\Filament\Resources\Languages;

use App\Filament\Concerns\HasLocalizedRoutes;
use App\Filament\Resources\Languages\Pages\CreateLanguage;
use App\Filament\Resources\Languages\Pages\EditLanguage;
use App\Filament\Resources\Languages\Pages\ListLanguages;
use App\Filament\Resources\Languages\Schemas\LanguageForm;
use App\Filament\Resources\Languages\Tables\LanguagesTable;
use App\Filament\Support\LocalizedPageRegistration;
use App\Models\Language;
use BackedEnum;
use Filament\Resources\Pages\PageRegistration;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

/** @extends resource<Language> */
class LanguageResource extends Resource
{
    use HasLocalizedRoutes;

    protected static string $localizationKey = 'languages';

    protected static ?string $model = Language::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedLanguage;

    protected static ?int $navigationSort = 10;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return LanguageForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LanguagesTable::configure($table);
    }

    /** @return array<string, PageRegistration> */
    public static function getPages(): array
    {
        return [
            'index' => LocalizedPageRegistration::make(ListLanguages::class, 'index'),
            'create' => LocalizedPageRegistration::make(CreateLanguage::class, 'create'),
            'edit' => LocalizedPageRegistration::make(EditLanguage::class, 'edit'),
        ];
    }
}
