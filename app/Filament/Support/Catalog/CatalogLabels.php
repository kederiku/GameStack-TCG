<?php

namespace App\Filament\Support\Catalog;

class CatalogLabels
{
    public const SERIES_TYPES = ['series' => 'Série', 'block' => 'Bloc'];

    public const EXPANSION_TYPES = [
        'main' => 'Extension principale', 'special' => 'Extension spéciale',
        'promo' => 'Promotion', 'deck' => 'Deck', 'reprint' => 'Réimpression',
    ];

    public const RELATION_TYPES = [
        'related_to' => 'Liée à', 'adapted_from' => 'Adaptée de', 'includes_content_from' => 'Inclut du contenu de',
    ];

    public const STATUSES = ['announced' => 'Annoncée', 'released' => 'Publiée', 'cancelled' => 'Annulée'];

    public const CHANNELS = [
        'general' => 'Distribution générale', 'specialist' => 'Boutiques spécialisées',
        'prerelease' => 'Avant-première', 'crowdfunding' => 'Financement participatif', 'event' => 'Événement',
    ];
}
