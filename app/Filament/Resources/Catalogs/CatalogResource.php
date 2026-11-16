<?php

namespace App\Filament\Resources\Catalogs;

use App\Filament\Concerns\HasLocalizedRoutes;
use App\Filament\Resources\Catalogs\Pages\CreateCatalog;
use App\Filament\Resources\Catalogs\Pages\EditCatalog;
use App\Filament\Resources\Catalogs\Pages\ListCatalogs;
use App\Filament\Resources\Catalogs\Schemas\CatalogForm;
use App\Filament\Resources\Catalogs\Tables\CatalogsTable;
use App\Filament\Support\LocalizedPageRegistration;
use App\Models\Catalog;
use BackedEnum;
use Filament\Resources\Pages\PageRegistration;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

/** @extends resource<Catalog> */
class CatalogResource extends Resource
{
    use HasLocalizedRoutes;

    protected static string $localizationKey = 'catalogs';

    protected static ?string $model = Catalog::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCircleStack;

    protected static ?int $navigationSort = 20;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return CatalogForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CatalogsTable::configure($table);
    }

    /** @return array<string, PageRegistration> */
    public static function getPages(): array
    {
        return [
            'index' => LocalizedPageRegistration::make(ListCatalogs::class, 'index'),
            'create' => LocalizedPageRegistration::make(CreateCatalog::class, 'create'),
            'edit' => LocalizedPageRegistration::make(EditCatalog::class, 'edit'),
        ];
    }
}
