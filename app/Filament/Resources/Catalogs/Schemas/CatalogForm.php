<?php

namespace App\Filament\Resources\Catalogs\Schemas;

use App\Filament\Support\Catalog\CatalogFields;
use Filament\Schemas\Schema;

class CatalogForm
{
    public static function configure(Schema $schema): Schema
    {
        return CatalogFields::layout($schema, [
            CatalogFields::game(),
            CatalogFields::language('default_language_id', 'Langue par défaut')
                ->helperText('Langue de repli pour les noms lorsqu’une traduction manque. Elle ne limite pas les langues dans lesquelles les éditions peuvent être imprimées.'),
            CatalogFields::name()->helperText('Un nom qui distingue ce catalogue dans le jeu, par exemple International ou Japonais.'),
            CatalogFields::key(),
            CatalogFields::description(),
        ], [CatalogFields::visibility(), CatalogFields::order()], description: 'Un catalogue rassemble les extensions d’une même version du jeu : internationale, japonaise, etc. Créez-en un autre si les ensembles diffèrent selon les marchés ; une simple traduction peut rester dans le même catalogue.');
    }
}
