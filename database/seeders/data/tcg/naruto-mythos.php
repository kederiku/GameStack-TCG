<?php

// Les sorties renseignées concernent le premier tirage. L’annonce Bandai porte sur un autre jeu.
return [
    'game' => ['slug' => 'naruto-mythos', 'name' => 'NARUTO Mythos TCG', 'is_visible' => true],
    'sources' => ['name' => 'mythos-set'],
    'catalogs' => [
        [
            'attributes' => ['key' => 'international', 'name' => 'International', 'sort_order' => 0, 'is_visible' => true],
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
            ],
        ],
    ],
];
