<?php

return [
    'game' => [
        'slug' => 'yu-gi-oh',
        'name' => 'Yu-Gi-Oh!',
        'is_visible' => true,
        'display_name' => ['fr' => 'Yu-Gi-Oh!', 'en' => 'Yu-Gi-Oh!'],
    ],
    'sources' => ['name' => 'Y3', 'display_name.fr' => 'Y3', 'display_name.en' => 'Y3'],
    'catalogs' => [
        [
            'attributes' => [
                'key' => 'tcg',
                'name' => 'TCG',
                'sort_order' => 0,
                'is_visible' => true,
                'display_name' => ['fr' => 'TCG', 'en' => 'TCG'],
            ],
            'default_language' => 'fr',
            'series' => [],
            'expansions' => [
                [
                    'attributes' => [
                        'key' => 'darkwing-blast',
                        'name' => 'Explosion de l’Aile des Ténèbres',
                        'type' => 'main',
                        'sort_order' => 0,
                        'is_visible' => true,
                        'display_name' => ['fr' => 'Explosion de l’Aile des Ténèbres', 'en' => 'Darkwing Blast'],
                    ],
                    'series' => null,
                    'editions' => [
                        [
                            'attributes' => ['name' => 'Explosion de l’Aile des Ténèbres', 'status' => 'released', 'is_visible' => true],
                            'language' => 'fr',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2022-10-20', 'status' => 'released'],
                                    'market' => 'europe',
                                    'sources' => ['market' => 'Y2', 'channel' => 'Y2', 'released_on' => 'Y2', 'status' => 'Y2'],
                                ],
                            ],
                            'sources' => ['name' => 'Y2', 'language' => 'yugioh-fr-card', 'status' => 'Y2'],
                        ],
                        [
                            'attributes' => ['name' => 'Darkwing Blast', 'status' => 'released', 'is_visible' => true],
                            'language' => 'en',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2022-10-21', 'status' => 'released'],
                                    'market' => 'north-america',
                                    'sources' => ['market' => 'Y3', 'channel' => 'Y3', 'released_on' => 'Y3', 'status' => 'Y3'],
                                ],
                                [
                                    'attributes' => ['channel' => 'specialist', 'released_on' => '2022-10-19', 'status' => 'released'],
                                    'market' => 'north-america',
                                    'sources' => ['market' => 'Y3', 'channel' => 'Y3', 'released_on' => 'Y3', 'status' => 'Y3'],
                                ],
                            ],
                            'sources' => ['name' => 'Y3', 'language' => 'yugioh-en-card', 'status' => 'Y3'],
                        ],
                    ],
                    'sources' => ['name' => 'Y2', 'display_name.fr' => 'Y2', 'display_name.en' => 'Y3'],
                ],
            ],
        ],
        [
            'attributes' => [
                'key' => 'ocg',
                'name' => 'OCG japonais',
                'sort_order' => 1,
                'is_visible' => true,
                'display_name' => ['fr' => 'OCG japonais', 'en' => 'Japanese OCG'],
            ],
            'default_language' => 'ja',
            'series' => [],
            'expansions' => [
                [
                    'attributes' => [
                        'key' => 'darkwing-blast',
                        'name' => 'DARKWING BLAST',
                        'type' => 'main',
                        'sort_order' => 0,
                        'is_visible' => true,
                        'display_name' => ['ja' => 'DARKWING BLAST'],
                    ],
                    'series' => null,
                    'editions' => [
                        [
                            'attributes' => ['name' => 'DARKWING BLAST', 'status' => 'released', 'is_visible' => true],
                            'language' => 'ja',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2022-07-16', 'status' => 'released'],
                                    'market' => 'japan',
                                    'sources' => ['market' => 'Y1', 'channel' => 'Y1', 'released_on' => 'Y1', 'status' => 'Y1'],
                                ],
                            ],
                            'sources' => ['name' => 'Y1', 'language' => 'yugioh-ja-card', 'status' => 'Y1'],
                        ],
                    ],
                    'sources' => ['name' => 'Y1', 'display_name.ja' => 'Y1'],
                ],
            ],
        ],
    ],
];
