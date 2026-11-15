<?php

namespace App\Filament\Resources\ExpansionEditions\Schemas;

use App\Filament\Support\Catalog\CatalogFields;
use App\Filament\Support\Catalog\CatalogLabels;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ExpansionEditionForm
{
    public static function configure(Schema $schema): Schema
    {
        return CatalogFields::layout($schema, [
            CatalogFields::language()->disabledOn('edit')
                ->helperText('Langue imprimée, définitive après création. Une seule édition par langue.'),
            CatalogFields::name('Nom de l’édition')->helperText('Nom commercial de l’ensemble dans cette langue, tel qu’il est présenté par l’éditeur.'),
            TextInput::make('official_code')->label('Code officiel')->maxLength(255)
                ->helperText('Code de l’éditeur pour cette langue. Laissez vide s’il est inconnu.'),
        ], [
            CatalogFields::enum('status', 'État', CatalogLabels::STATUSES)->default('announced')
                ->helperText('Annoncée : à venir ; publiée : sortie ; annulée : abandonnée. Une sortie publiée publie aussi l’édition.'),
            CatalogFields::visibility(),
        ], CatalogFields::image('Image de l’édition', 'Utilisez le visuel propre à cette langue. Sans image, aucun visuel d’une autre édition n’est repris.'), description: 'Une édition correspond à une version réellement imprimée ou annoncée dans une langue. Ses lieux, canaux et dates de commercialisation se renseignent ensuite dans « Sorties ».');
    }
}
