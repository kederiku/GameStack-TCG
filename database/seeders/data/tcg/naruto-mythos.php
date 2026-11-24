<?php

// Les sorties renseignées concernent le premier tirage. L’annonce Bandai porte sur un autre jeu.
return [
    'game' => [
        'slug' => 'naruto-mythos',
        'name' => 'NARUTO Mythos TCG',
        'is_visible' => true,
        'display_name' => ['fr' => 'NARUTO Mythos TCG', 'en' => 'NARUTO Mythos TCG'],
    ],
    'sources' => ['name' => 'mythos-set', 'display_name.fr' => 'mythos-set', 'display_name.en' => 'mythos-set'],
    'catalogs' => [
        [
            'attributes' => [
                'key' => 'international',
                'name' => 'International',
                'sort_order' => 0,
                'is_visible' => true,
                'display_name' => ['fr' => 'International', 'en' => 'International'],
            ],
            'default_language' => 'en',
            'series' => [],
            'expansions' => [
                [
                    'attributes' => [
                        'key' => 'konoha-shido',
                        'name' => 'Konoha Shidō',
                        'type' => 'main',
                        'sort_order' => 0,
                        'is_visible' => true,
                        'display_name' => ['en' => 'Konoha Shidō', 'fr' => 'Konoha Shidō'],
                    ],
                    'series' => null,
                    'editions' => [
                        [
                            'attributes' => ['name' => 'Konoha Shidō', 'status' => 'released', 'is_visible' => true],
                            'language' => 'en',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2026-03-13', 'status' => 'released'],
                                    'market' => 'europe',
                                    'sources' => [
                                        'market' => 'mythos-first-edition',
                                        'channel' => 'mythos-roadmap',
                                        'released_on' => 'mythos-roadmap',
                                        'status' => 'mythos-roadmap',
                                    ],
                                ],
                            ],
                            'sources' => ['name' => 'mythos-set', 'language' => 'mythos-language', 'status' => 'mythos-released'],
                        ],
                        [
                            'attributes' => ['name' => 'Konoha Shidō', 'status' => 'released', 'is_visible' => true],
                            'language' => 'fr',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2026-03-13', 'status' => 'released'],
                                    'market' => 'europe',
                                    'sources' => [
                                        'market' => 'mythos-first-edition',
                                        'channel' => 'mythos-roadmap',
                                        'released_on' => 'mythos-roadmap',
                                        'status' => 'mythos-roadmap',
                                    ],
                                ],
                            ],
                            'sources' => ['name' => 'mythos-set', 'language' => 'mythos-language', 'status' => 'mythos-released'],
                        ],
                    ],
                    'sources' => ['name' => 'mythos-set', 'display_name.en' => 'mythos-set', 'display_name.fr' => 'mythos-set'],
                ],
                [
                    'attributes' => [
                        'key' => 'shinobi-shiren',
                        'name' => 'Shinobi Shiren',
                        'type' => 'main',
                        'sort_order' => 1,
                        'is_visible' => true,
                        'display_name' => ['en' => 'Shinobi Shiren', 'fr' => 'Shinobi Shiren'],
                    ],
                    'series' => null,
                    'editions' => [
                        [
                            'attributes' => ['name' => 'Shinobi Shiren', 'status' => 'released', 'is_visible' => true],
                            'language' => 'en',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2026-09-04', 'status' => 'released'],
                                    'market' => 'europe',
                                    'sources' => ['market' => 'mythos-shinobi-europe', 'channel' => 'mythos-shinobi-date', 'released_on' => 'mythos-shinobi-date', 'status' => 'mythos-shinobi-date'],
                                ],
                            ],
                            'sources' => ['name' => 'mythos-shinobi-en', 'language' => 'mythos-shinobi-en', 'status' => 'mythos-shinobi-en'],
                        ],
                        [
                            'attributes' => ['name' => 'Shinobi Shiren', 'status' => 'released', 'is_visible' => true],
                            'language' => 'fr',
                            'asset' => null,
                            'releases' => [],
                            'sources' => ['name' => 'mythos-shinobi-fr', 'language' => 'mythos-shinobi-fr-print', 'status' => 'mythos-shinobi-fr-print'],
                        ],
                    ],
                    'sources' => ['name' => 'mythos-shinobi-en', 'display_name.en' => 'mythos-shinobi-en', 'display_name.fr' => 'mythos-shinobi-fr'],
                ],
            ],
        ],
    ],
];
