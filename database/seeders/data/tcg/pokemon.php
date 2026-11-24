<?php

// « Pokémon 151 (Japon) » est un libellé d’interface français associé au produit P6.
return [
    'game' => [
        'slug' => 'pokemon',
        'name' => 'Pokémon',
        'is_visible' => true,
        'display_name' => ['fr' => 'Pokémon', 'en' => 'Pokémon'],
    ],
    'sources' => ['name' => 'P1', 'display_name.fr' => 'P1', 'display_name.en' => 'P1'],
    'catalogs' => [
        [
            'attributes' => [
                'key' => 'international',
                'name' => 'International',
                'sort_order' => 0,
                'is_visible' => true,
                'display_name' => ['fr' => 'International', 'en' => 'International'],
            ],
            'default_language' => 'fr',
            'series' => [
                [
                    'attributes' => [
                        'key' => 'scarlet-violet',
                        'name' => 'Écarlate et Violet',
                        'type' => 'series',
                        'sort_order' => 0,
                        'is_visible' => true,
                        'display_name' => ['fr' => 'Écarlate et Violet', 'en' => 'Scarlet & Violet'],
                    ],
                    'sources' => [
                        'name' => 'P1',
                        'display_name.fr' => 'P1',
                        'display_name.en' => 'P2',
                        'type' => 'P1',
                        'images.fr' => 'pokemon-logo-fr',
                        'images.en' => 'pokemon-logo-en',
                    ],
                    'images' => ['fr' => 'scarlet-violet-fr.avif', 'en' => 'scarlet-violet-en.avif'],
                ],
            ],
            'expansions' => [
                [
                    'attributes' => [
                        'key' => 'scarlet-violet',
                        'name' => 'Écarlate et Violet',
                        'type' => 'main',
                        'sort_order' => 0,
                        'is_visible' => true,
                        'display_name' => ['fr' => 'Écarlate et Violet', 'en' => 'Scarlet & Violet'],
                    ],
                    'series' => 'scarlet-violet',
                    'editions' => [
                        [
                            'attributes' => ['name' => 'Écarlate et Violet', 'status' => 'released', 'is_visible' => true],
                            'language' => 'fr',
                            'asset' => 'scarlet-violet-fr.avif',
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2023-03-31', 'status' => 'released'],
                                    'market' => 'france',
                                    'sources' => ['market' => 'P-FR-market', 'channel' => 'P1', 'released_on' => 'P1', 'status' => 'P1'],
                                ],
                            ],
                            'sources' => ['name' => 'P1', 'language' => 'P1', 'status' => 'P1', 'asset' => 'pokemon-logo-fr'],
                        ],
                        [
                            'attributes' => ['name' => 'Scarlet & Violet', 'status' => 'released', 'is_visible' => true],
                            'language' => 'en',
                            'asset' => 'scarlet-violet-en.avif',
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2023-03-31', 'status' => 'released'],
                                    'market' => 'united-states',
                                    'sources' => ['market' => 'P2', 'channel' => 'P2', 'released_on' => 'P2', 'status' => 'P2'],
                                ],
                            ],
                            'sources' => ['name' => 'P2', 'language' => 'P2', 'status' => 'P2', 'asset' => 'pokemon-logo-en'],
                        ],
                    ],
                    'sources' => ['name' => 'P1', 'display_name.fr' => 'P1', 'display_name.en' => 'P2', 'series' => 'P1'],
                ],
                [
                    'attributes' => [
                        'key' => '151',
                        'name' => 'Écarlate et Violet – 151',
                        'type' => 'special',
                        'sort_order' => 1,
                        'is_visible' => true,
                        'display_name' => ['fr' => 'Écarlate et Violet – 151', 'en' => 'Scarlet & Violet—151'],
                    ],
                    'series' => 'scarlet-violet',
                    'editions' => [
                        [
                            'attributes' => ['name' => 'Écarlate et Violet – 151', 'status' => 'released', 'is_visible' => true],
                            'language' => 'fr',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2023-09-22', 'status' => 'released'],
                                    'market' => 'worldwide',
                                    'sources' => [
                                        'market' => 'P-FR-151-release',
                                        'channel' => 'P-FR-151-release',
                                        'released_on' => 'P-FR-151-release',
                                        'status' => 'P-FR-151-release',
                                    ],
                                ],
                            ],
                            'sources' => ['name' => 'P3', 'language' => 'P3', 'status' => 'P-FR-151-release'],
                        ],
                        [
                            'attributes' => ['name' => 'Scarlet & Violet—151', 'status' => 'released', 'is_visible' => true],
                            'language' => 'en',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2023-09-22', 'status' => 'released'],
                                    'market' => 'north-america',
                                    'sources' => ['market' => 'P4', 'channel' => 'P4', 'released_on' => 'P4', 'status' => 'P4'],
                                ],
                            ],
                            'sources' => ['name' => 'P4', 'language' => 'P-EN-151-cards', 'status' => 'P4'],
                        ],
                    ],
                    'sources' => ['name' => 'P3', 'display_name.fr' => 'P3', 'display_name.en' => 'P4', 'series' => 'P3'],
                ],
            ],
        ],
        [
            'attributes' => [
                'key' => 'japanese',
                'name' => 'Japonais',
                'sort_order' => 1,
                'is_visible' => true,
                'display_name' => ['fr' => 'Japonais', 'en' => 'Japanese'],
            ],
            'default_language' => 'ja',
            'series' => [
                [
                    'attributes' => [
                        'key' => 'scarlet-violet',
                        'name' => 'スカーレット＆バイオレット',
                        'type' => 'series',
                        'sort_order' => 0,
                        'is_visible' => true,
                        'display_name' => ['ja' => 'スカーレット＆バイオレット'],
                    ],
                    'sources' => ['name' => 'P5', 'type' => 'P5', 'display_name.ja' => 'P5'],
                ],
            ],
            'expansions' => [
                [
                    'attributes' => [
                        'key' => 'scarlet-ex',
                        'name' => 'スカーレットex',
                        'type' => 'main',
                        'sort_order' => 0,
                        'is_visible' => true,
                        'display_name' => ['ja' => 'スカーレットex'],
                    ],
                    'series' => 'scarlet-violet',
                    'editions' => [
                        [
                            'attributes' => ['name' => 'スカーレットex', 'status' => 'released', 'is_visible' => true],
                            'language' => 'ja',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2023-01-20', 'status' => 'released'],
                                    'market' => 'japan',
                                    'sources' => ['market' => 'P-JP-products', 'channel' => 'P-JP-products', 'released_on' => 'P-JP-products', 'status' => 'P-JP-products'],
                                ],
                            ],
                            'sources' => ['name' => 'P5', 'language' => 'P5', 'status' => 'P5'],
                        ],
                    ],
                    'sources' => ['name' => 'P5', 'series' => 'P5', 'display_name.ja' => 'P5'],
                ],
                [
                    'attributes' => [
                        'key' => 'violet-ex',
                        'name' => 'バイオレットex',
                        'type' => 'main',
                        'sort_order' => 1,
                        'is_visible' => true,
                        'display_name' => ['ja' => 'バイオレットex'],
                    ],
                    'series' => 'scarlet-violet',
                    'editions' => [
                        [
                            'attributes' => ['name' => 'バイオレットex', 'status' => 'released', 'is_visible' => true],
                            'language' => 'ja',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2023-01-20', 'status' => 'released'],
                                    'market' => 'japan',
                                    'sources' => ['market' => 'P-JP-products', 'channel' => 'P-JP-products', 'released_on' => 'P-JP-products', 'status' => 'P-JP-products'],
                                ],
                            ],
                            'sources' => ['name' => 'P5', 'language' => 'P5', 'status' => 'P5'],
                        ],
                    ],
                    'sources' => ['name' => 'P5', 'series' => 'P5', 'display_name.ja' => 'P5'],
                ],
                [
                    'attributes' => [
                        'key' => '151',
                        'name' => 'ポケモンカード151',
                        'type' => 'special',
                        'sort_order' => 2,
                        'is_visible' => true,
                        'display_name' => ['ja' => 'ポケモンカード151', 'fr' => 'Pokémon 151 (Japon)'],
                    ],
                    'series' => 'scarlet-violet',
                    'editions' => [
                        [
                            'attributes' => ['name' => 'ポケモンカード151', 'status' => 'released', 'is_visible' => true],
                            'language' => 'ja',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2023-06-16', 'status' => 'released'],
                                    'market' => 'japan',
                                    'sources' => ['market' => 'P6', 'channel' => 'P-JP-151-date', 'released_on' => 'P-JP-151-date', 'status' => 'P-JP-151-date'],
                                ],
                            ],
                            'sources' => ['name' => 'P6', 'language' => 'P6', 'status' => 'P6'],
                        ],
                    ],
                    'sources' => ['name' => 'P6', 'display_name.fr' => 'P6', 'series' => 'P6', 'display_name.ja' => 'P6'],
                ],
            ],
        ],
        [
            'attributes' => [
                'key' => 'korean',
                'name' => 'Coréen',
                'sort_order' => 2,
                'is_visible' => true,
                'display_name' => ['fr' => 'Coréen', 'en' => 'Korean'],
            ],
            'default_language' => 'ko',
            'series' => [
                [
                    'attributes' => [
                        'key' => 'scarlet-violet',
                        'name' => '스칼렛&바이올렛',
                        'type' => 'series',
                        'sort_order' => 0,
                        'is_visible' => true,
                        'display_name' => ['ko' => '스칼렛&바이올렛'],
                    ],
                    'sources' => ['name' => 'P7', 'type' => 'P7', 'display_name.ko' => 'P7'],
                ],
            ],
            'expansions' => [
                [
                    'attributes' => [
                        'key' => '151',
                        'name' => '포켓몬 카드 151',
                        'type' => 'special',
                        'sort_order' => 0,
                        'is_visible' => true,
                        'display_name' => ['ko' => '포켓몬 카드 151'],
                    ],
                    'series' => 'scarlet-violet',
                    'editions' => [
                        [
                            'attributes' => ['name' => '포켓몬 카드 151', 'status' => 'released', 'is_visible' => true],
                            'language' => 'ko',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2023-07-28', 'status' => 'released'],
                                    'market' => 'south-korea',
                                    'sources' => ['market' => 'P7', 'channel' => 'P7', 'released_on' => 'P7', 'status' => 'P7'],
                                ],
                            ],
                            'sources' => ['name' => 'P7', 'language' => 'P7', 'status' => 'P7'],
                        ],
                    ],
                    'sources' => ['name' => 'P7', 'series' => 'P7', 'display_name.ko' => 'P7'],
                ],
            ],
        ],
        [
            'attributes' => [
                'key' => 'traditional-chinese',
                'name' => 'Chinois traditionnel',
                'sort_order' => 3,
                'is_visible' => true,
                'display_name' => ['fr' => 'Chinois traditionnel', 'en' => 'Traditional Chinese'],
            ],
            'default_language' => 'zh-Hant',
            'series' => [
                [
                    'attributes' => [
                        'key' => 'scarlet-violet',
                        'name' => '朱＆紫',
                        'type' => 'series',
                        'sort_order' => 0,
                        'is_visible' => true,
                        'display_name' => ['zh-Hant' => '朱＆紫'],
                    ],
                    'sources' => ['name' => 'P-TW-series', 'type' => 'P-TW-series', 'display_name.zh-Hant' => 'P-TW-series'],
                ],
                [
                    'attributes' => [
                        'key' => 'sun-moon',
                        'name' => '太陽 & 月亮',
                        'type' => 'series',
                        'sort_order' => 1,
                        'is_visible' => true,
                        'display_name' => ['zh-Hant' => '太陽 & 月亮'],
                    ],
                    'sources' => ['name' => 'P9', 'type' => 'P9', 'display_name.zh-Hant' => 'P9'],
                ],
            ],
            'expansions' => [
                [
                    'attributes' => [
                        'key' => '151',
                        'name' => '寶可夢卡牌151',
                        'type' => 'special',
                        'sort_order' => 0,
                        'is_visible' => true,
                        'display_name' => ['zh-Hant' => '寶可夢卡牌151'],
                    ],
                    'series' => 'scarlet-violet',
                    'editions' => [
                        [
                            'attributes' => ['name' => '寶可夢卡牌151', 'status' => 'released', 'is_visible' => true],
                            'language' => 'zh-Hant',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2023-06-30', 'status' => 'released'],
                                    'market' => 'taiwan',
                                    'sources' => ['market' => 'P8', 'channel' => 'P8', 'released_on' => 'P8', 'status' => 'P8'],
                                ],
                            ],
                            'sources' => ['name' => 'P8', 'language' => 'P8', 'status' => 'P8'],
                        ],
                    ],
                    'sources' => ['name' => 'P8', 'series' => 'P-TW-series', 'display_name.zh-Hant' => 'P8'],
                ],
                [
                    'attributes' => [
                        'key' => 'set-b',
                        'name' => '眾星雲集組合篇 SET B',
                        'type' => 'special',
                        'sort_order' => 1,
                        'is_visible' => true,
                        'display_name' => ['zh-Hant' => '眾星雲集組合篇 SET B'],
                    ],
                    'series' => 'sun-moon',
                    'editions' => [
                        [
                            'attributes' => ['name' => '眾星雲集組合篇 SET B', 'status' => 'released', 'is_visible' => true],
                            'language' => 'zh-Hant',
                            'asset' => null,
                            'releases' => [],
                            'sources' => ['name' => 'P9', 'language' => 'P9', 'status' => 'P9'],
                        ],
                    ],
                    'sources' => ['name' => 'P9', 'series' => 'P9', 'display_name.zh-Hant' => 'P9'],
                ],
            ],
        ],
        [
            'attributes' => [
                'key' => 'simplified-chinese',
                'name' => 'Chinois simplifié',
                'sort_order' => 4,
                'is_visible' => true,
                'display_name' => ['fr' => 'Chinois simplifié', 'en' => 'Simplified Chinese'],
            ],
            'default_language' => 'zh-Hans',
            'series' => [
                [
                    'attributes' => [
                        'key' => 'scarlet-violet',
                        'name' => '朱&紫',
                        'type' => 'series',
                        'sort_order' => 0,
                        'is_visible' => true,
                        'display_name' => ['zh-Hans' => '朱&紫'],
                    ],
                    'sources' => ['name' => 'P-CN-series', 'type' => 'P-CN-series', 'display_name.zh-Hans' => 'P-CN-series'],
                ],
            ],
            'expansions' => [
                [
                    'attributes' => [
                        'key' => '151-jing',
                        'name' => '收集啦151 惊',
                        'type' => 'special',
                        'sort_order' => 0,
                        'is_visible' => true,
                        'display_name' => ['zh-Hans' => '收集啦151 惊'],
                    ],
                    'series' => 'scarlet-violet',
                    'editions' => [
                        [
                            'attributes' => ['name' => '收集啦151 惊', 'status' => 'released', 'is_visible' => true],
                            'language' => 'zh-Hans',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2025-07-18', 'status' => 'released'],
                                    'market' => 'mainland-china',
                                    'sources' => ['market' => 'P10', 'channel' => 'P10', 'released_on' => 'P10', 'status' => 'P10'],
                                ],
                            ],
                            'sources' => ['name' => 'P10', 'language' => 'P10', 'status' => 'P10'],
                        ],
                    ],
                    'sources' => ['name' => 'P10', 'series' => 'P-CN-series', 'display_name.zh-Hans' => 'P10'],
                ],
                [
                    'attributes' => [
                        'key' => '151-ju',
                        'name' => '收集啦151 聚',
                        'type' => 'special',
                        'sort_order' => 1,
                        'is_visible' => true,
                        'display_name' => ['zh-Hans' => '收集啦151 聚'],
                    ],
                    'series' => 'scarlet-violet',
                    'editions' => [
                        [
                            'attributes' => ['name' => '收集啦151 聚', 'status' => 'released', 'is_visible' => true],
                            'language' => 'zh-Hans',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2025-10-17', 'status' => 'released'],
                                    'market' => 'mainland-china',
                                    'sources' => ['market' => 'P10', 'channel' => 'P10', 'released_on' => 'P10', 'status' => 'P10'],
                                ],
                            ],
                            'sources' => ['name' => 'P10', 'language' => 'P10', 'status' => 'P10'],
                        ],
                    ],
                    'sources' => ['name' => 'P10', 'series' => 'P-CN-series', 'display_name.zh-Hans' => 'P10'],
                ],
            ],
        ],
    ],
];
