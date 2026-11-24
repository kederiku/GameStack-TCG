<?php

// Les libellés d’interface, clés, ordres et visibilités sont des choix de catalogue.
// Les champs documentés indiquent une source dans tcg-sources.php.
return [
    'languages' => [
        'en' => [
            'attributes' => ['code' => 'en', 'name' => 'English'],
            'sources' => ['code' => 'language-tags'],
        ],
        'fr' => [
            'attributes' => ['code' => 'fr', 'name' => 'Français'],
            'sources' => ['code' => 'language-tags'],
        ],
        'ja' => [
            'attributes' => ['code' => 'ja', 'name' => 'Japonais'],
            'sources' => ['code' => 'language-tags'],
        ],
        'ko' => [
            'attributes' => ['code' => 'ko', 'name' => 'Coréen'],
            'sources' => ['code' => 'language-tags'],
        ],
        'zh-Hans' => [
            'attributes' => ['code' => 'zh-Hans', 'name' => 'Chinois simplifié'],
            'sources' => ['code' => 'language-tags'],
        ],
        'zh-Hant' => [
            'attributes' => ['code' => 'zh-Hant', 'name' => 'Chinois traditionnel'],
            'sources' => ['code' => 'language-tags'],
        ],
    ],
    'markets' => [
        'france' => [
            'attributes' => [
                'key' => 'france',
                'name' => 'France',
                'display_name' => ['en' => 'France', 'fr' => 'France'],
            ],
        ],
        'united-states' => [
            'attributes' => [
                'key' => 'united-states',
                'name' => 'États-Unis',
                'display_name' => ['en' => 'United States', 'fr' => 'États-Unis'],
            ],
        ],
        'north-america' => [
            'attributes' => [
                'key' => 'north-america',
                'name' => 'Amérique du Nord',
                'display_name' => ['en' => 'North America', 'fr' => 'Amérique du Nord'],
            ],
        ],
        'europe' => [
            'attributes' => [
                'key' => 'europe',
                'name' => 'Europe',
                'display_name' => ['en' => 'Europe', 'fr' => 'Europe'],
            ],
        ],
        'japan' => [
            'attributes' => [
                'key' => 'japan',
                'name' => 'Japon',
                'display_name' => ['en' => 'Japan', 'fr' => 'Japon'],
            ],
        ],
        'south-korea' => [
            'attributes' => [
                'key' => 'south-korea',
                'name' => 'Corée du Sud',
                'display_name' => ['en' => 'South Korea', 'fr' => 'Corée du Sud'],
            ],
        ],
        'taiwan' => [
            'attributes' => [
                'key' => 'taiwan',
                'name' => 'Taïwan',
                'display_name' => ['en' => 'Taiwan', 'fr' => 'Taïwan'],
            ],
        ],
        'mainland-china' => [
            'attributes' => [
                'key' => 'mainland-china',
                'name' => 'Chine continentale',
                'display_name' => ['en' => 'Mainland China', 'fr' => 'Chine continentale'],
            ],
        ],
        'worldwide' => [
            'attributes' => [
                'key' => 'worldwide',
                'name' => 'Monde',
                'display_name' => ['en' => 'Worldwide', 'fr' => 'Monde'],
            ],
        ],
    ],
    'games' => [
        'tcg/pokemon.php',
        'tcg/magic.php',
        'tcg/one-piece.php',
        'tcg/yu-gi-oh.php',
        'tcg/lorcana.php',
        'tcg/cyberpunk.php',
        'tcg/flesh-and-blood.php',
        'tcg/riftbound.php',
        'tcg/naruto-mythos.php',
        'tcg/final-fantasy-tcg.php',
        'tcg/star-wars-unlimited.php',
        'tcg/altered.php',
    ],
];
