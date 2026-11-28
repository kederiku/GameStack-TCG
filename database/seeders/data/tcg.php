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
        'de' => [
            'attributes' => ['code' => 'de', 'name' => 'Allemand'],
            'sources' => ['code' => 'language-tags'],
        ],
        'es' => [
            'attributes' => ['code' => 'es', 'name' => 'Espagnol'],
            'sources' => ['code' => 'language-tags'],
        ],
        'it' => [
            'attributes' => ['code' => 'it', 'name' => 'Italien'],
            'sources' => ['code' => 'language-tags'],
        ],
        'pt' => [
            'attributes' => ['code' => 'pt', 'name' => 'Portugais'],
            'sources' => ['code' => 'language-tags'],
        ],
        'th' => [
            'attributes' => ['code' => 'th', 'name' => 'Thaïlandais'],
            'sources' => ['code' => 'language-tags', 'name' => 'pokemon-doc-languages'],
        ],
        'id' => [
            'attributes' => ['code' => 'id', 'name' => 'Indonésien'],
            'sources' => ['code' => 'language-tags', 'name' => 'pokemon-doc-languages'],
        ],
        'nl' => [
            'attributes' => ['code' => 'nl', 'name' => 'Néerlandais'],
            'sources' => ['code' => 'language-tags', 'name' => 'pokemon-doc-languages'],
        ],
        'pl' => [
            'attributes' => ['code' => 'pl', 'name' => 'Polonais'],
            'sources' => ['code' => 'language-tags', 'name' => 'pokemon-doc-languages'],
        ],
        'ru' => [
            'attributes' => ['code' => 'ru', 'name' => 'Russe'],
            'sources' => ['code' => 'language-tags', 'name' => 'pokemon-doc-languages'],
        ],
        'es-419' => [
            'attributes' => ['code' => 'es-419', 'name' => 'Espagnol (Amérique latine)'],
            'sources' => ['code' => 'language-tags', 'name' => 'pokemon-doc-languages'],
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
        'canada' => [
            'attributes' => ['key' => 'canada', 'name' => 'Canada', 'display_name' => ['fr' => 'Canada', 'en' => 'Canada']],
        ],
        'quebec' => [
            'attributes' => ['key' => 'quebec', 'name' => 'Québec', 'display_name' => ['fr' => 'Québec', 'en' => 'Quebec']],
        ],
        'united-kingdom' => [
            'attributes' => ['key' => 'united-kingdom', 'name' => 'Royaume-Uni', 'display_name' => ['fr' => 'Royaume-Uni', 'en' => 'United Kingdom']],
        ],
        'australia' => [
            'attributes' => ['key' => 'australia', 'name' => 'Australie', 'display_name' => ['fr' => 'Australie', 'en' => 'Australia']],
        ],
        'thailand' => [
            'attributes' => ['key' => 'thailand', 'name' => 'Thaïlande', 'display_name' => ['fr' => 'Thaïlande', 'en' => 'Thailand']],
        ],
        'indonesia' => [
            'attributes' => ['key' => 'indonesia', 'name' => 'Indonésie', 'display_name' => ['fr' => 'Indonésie', 'en' => 'Indonesia']],
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
