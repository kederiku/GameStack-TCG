<?php

return [
    'game' => [
        'slug' => 'one-piece',
        'name' => 'ONE PIECE CARD GAME',
        'is_visible' => true,
        'display_name' => ['fr' => 'ONE PIECE CARD GAME', 'en' => 'ONE PIECE CARD GAME'],
    ],
    'sources' => ['name' => 'O2', 'display_name.fr' => 'O2', 'display_name.en' => 'O2'],
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
                        'key' => 'romance-dawn',
                        'name' => 'ROMANCE DAWN',
                        'type' => 'main',
                        'sort_order' => 0,
                        'is_visible' => true,
                        'display_name' => ['en' => 'ROMANCE DAWN'],
                    ],
                    'series' => null,
                    'editions' => [
                        [
                            'attributes' => ['name' => 'ROMANCE DAWN', 'status' => 'released', 'is_visible' => true, 'official_code' => 'OP01'],
                            'language' => 'en',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2022-12-02', 'status' => 'released'],
                                    'market' => 'north-america',
                                    'sources' => ['market' => 'one-piece-rules', 'channel' => 'O2', 'released_on' => 'O2', 'status' => 'O2'],
                                ],
                            ],
                            'sources' => ['name' => 'O2', 'language' => 'one-piece-en-cards', 'status' => 'O2', 'official_code' => 'O2'],
                        ],
                    ],
                    'sources' => ['name' => 'O2', 'display_name.en' => 'O2'],
                ],
                [
                    'attributes' => [
                        'key' => 'paramount-war',
                        'name' => 'PARAMOUNT WAR',
                        'type' => 'main',
                        'sort_order' => 1,
                        'is_visible' => true,
                        'display_name' => ['en' => 'PARAMOUNT WAR'],
                    ],
                    'series' => null,
                    'editions' => [
                        [
                            'attributes' => ['name' => 'PARAMOUNT WAR', 'status' => 'released', 'is_visible' => true, 'official_code' => 'OP-02'],
                            'language' => 'en',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2023-03-10', 'status' => 'released'],
                                    'market' => 'north-america',
                                    'sources' => ['market' => 'one-piece-rules', 'channel' => 'one-piece-op02-en', 'released_on' => 'one-piece-op02-en', 'status' => 'one-piece-op02-en'],
                                ],
                            ],
                            'sources' => ['name' => 'one-piece-op02-en', 'language' => 'one-piece-op02-en-cards', 'status' => 'one-piece-op02-en-cards', 'official_code' => 'one-piece-op02-en'],
                        ],
                    ],
                    'sources' => ['name' => 'one-piece-op02-en', 'display_name.en' => 'one-piece-op02-en'],
                ],
                [
                    'attributes' => [
                        'key' => 'pillars-of-strength',
                        'name' => 'PILLARS OF STRENGTH',
                        'type' => 'main',
                        'sort_order' => 2,
                        'is_visible' => true,
                        'display_name' => ['en' => 'PILLARS OF STRENGTH'],
                    ],
                    'series' => null,
                    'editions' => [
                        [
                            'attributes' => ['name' => 'PILLARS OF STRENGTH', 'status' => 'released', 'is_visible' => true, 'official_code' => 'OP-03'],
                            'language' => 'en',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2023-06-30', 'status' => 'released'],
                                    'market' => 'north-america',
                                    'sources' => ['market' => 'one-piece-rules', 'channel' => 'one-piece-op03-en', 'released_on' => 'one-piece-op03-en', 'status' => 'one-piece-op03-en'],
                                ],
                            ],
                            'sources' => ['name' => 'one-piece-op03-en', 'language' => 'one-piece-op03-en-cards', 'status' => 'one-piece-op03-en-cards', 'official_code' => 'one-piece-op03-en'],
                        ],
                    ],
                    'sources' => ['name' => 'one-piece-op03-en', 'display_name.en' => 'one-piece-op03-en'],
                ],
                [
                    'attributes' => [
                        'key' => 'kingdoms-of-intrigue',
                        'name' => 'KINGDOMS OF INTRIGUE',
                        'type' => 'main',
                        'sort_order' => 3,
                        'is_visible' => true,
                        'display_name' => ['en' => 'KINGDOMS OF INTRIGUE'],
                    ],
                    'series' => null,
                    'editions' => [
                        [
                            'attributes' => ['name' => 'KINGDOMS OF INTRIGUE', 'status' => 'released', 'is_visible' => true, 'official_code' => 'OP-04'],
                            'language' => 'en',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2023-09-22', 'status' => 'released'],
                                    'market' => 'north-america',
                                    'sources' => ['market' => 'one-piece-rules', 'channel' => 'one-piece-op04-en', 'released_on' => 'one-piece-op04-en', 'status' => 'one-piece-op04-en'],
                                ],
                            ],
                            'sources' => ['name' => 'one-piece-op04-en', 'language' => 'one-piece-op04-en-cards', 'status' => 'one-piece-op04-en-cards', 'official_code' => 'one-piece-op04-en'],
                        ],
                    ],
                    'sources' => ['name' => 'one-piece-op04-en', 'display_name.en' => 'one-piece-op04-en'],
                ],
                [
                    'attributes' => [
                        'key' => 'awakening-of-the-new-era',
                        'name' => 'AWAKENING OF THE NEW ERA',
                        'type' => 'main',
                        'sort_order' => 4,
                        'is_visible' => true,
                        'display_name' => ['en' => 'AWAKENING OF THE NEW ERA'],
                    ],
                    'series' => null,
                    'editions' => [
                        [
                            'attributes' => ['name' => 'AWAKENING OF THE NEW ERA', 'status' => 'released', 'is_visible' => true, 'official_code' => 'OP-05'],
                            'language' => 'en',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2023-12-08', 'status' => 'released'],
                                    'market' => 'north-america',
                                    'sources' => ['market' => 'one-piece-rules', 'channel' => 'one-piece-op05-en', 'released_on' => 'one-piece-op05-en', 'status' => 'one-piece-op05-en'],
                                ],
                            ],
                            'sources' => ['name' => 'one-piece-op05-en', 'language' => 'one-piece-op05-en-cards', 'status' => 'one-piece-op05-en-cards', 'official_code' => 'one-piece-op05-en'],
                        ],
                    ],
                    'sources' => ['name' => 'one-piece-op05-en', 'display_name.en' => 'one-piece-op05-en'],
                ],
                [
                    'attributes' => [
                        'key' => 'wings-of-the-captain',
                        'name' => 'WINGS OF THE CAPTAIN',
                        'type' => 'main',
                        'sort_order' => 5,
                        'is_visible' => true,
                        'display_name' => ['en' => 'WINGS OF THE CAPTAIN'],
                    ],
                    'series' => null,
                    'editions' => [
                        [
                            'attributes' => ['name' => 'WINGS OF THE CAPTAIN', 'status' => 'released', 'is_visible' => true, 'official_code' => 'OP-06'],
                            'language' => 'en',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2024-03-15', 'status' => 'released'],
                                    'market' => 'north-america',
                                    'sources' => ['market' => 'one-piece-rules', 'channel' => 'one-piece-op06-en', 'released_on' => 'one-piece-op06-en', 'status' => 'one-piece-op06-en'],
                                ],
                            ],
                            'sources' => ['name' => 'one-piece-op06-en', 'language' => 'one-piece-op06-en-cards', 'status' => 'one-piece-op06-en-cards', 'official_code' => 'one-piece-op06-en'],
                        ],
                    ],
                    'sources' => ['name' => 'one-piece-op06-en', 'display_name.en' => 'one-piece-op06-en'],
                ],
                [
                    'attributes' => [
                        'key' => 'emperors-in-the-new-world',
                        'name' => 'LES NOUVEAUX EMPEREURS',
                        'type' => 'main',
                        'sort_order' => 6,
                        'is_visible' => true,
                        'display_name' => ['fr' => 'LES NOUVEAUX EMPEREURS', 'en' => 'EMPERORS IN THE NEW WORLD'],
                    ],
                    'series' => null,
                    'editions' => [
                        [
                            'attributes' => ['name' => 'EMPERORS IN THE NEW WORLD', 'status' => 'released', 'is_visible' => true, 'official_code' => 'OP-09'],
                            'language' => 'en',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2024-12-13', 'status' => 'released'],
                                    'market' => 'north-america',
                                    'sources' => ['market' => 'one-piece-rules', 'channel' => 'one-piece-op09-en', 'released_on' => 'one-piece-op09-en', 'status' => 'one-piece-op09-en'],
                                ],
                            ],
                            'sources' => ['name' => 'one-piece-op09-en', 'language' => 'one-piece-op09-en-cards', 'status' => 'one-piece-op09-en-cards', 'official_code' => 'one-piece-op09-en'],
                        ],
                        [
                            'attributes' => ['name' => 'LES NOUVEAUX EMPEREURS', 'status' => 'released', 'is_visible' => true, 'official_code' => 'OP-09'],
                            'language' => 'fr',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2025-02-07', 'status' => 'released'],
                                    'market' => 'france',
                                    'sources' => ['market' => 'one-piece-launch-fr', 'channel' => 'one-piece-op09-fr', 'released_on' => 'one-piece-op09-fr', 'status' => 'one-piece-op09-fr'],
                                ],
                            ],
                            'sources' => ['name' => 'one-piece-op09-fr', 'language' => 'one-piece-op09-fr', 'status' => 'one-piece-op09-fr', 'official_code' => 'one-piece-op09-fr'],
                        ],
                    ],
                    'sources' => ['name' => 'one-piece-op09-fr', 'display_name.fr' => 'one-piece-op09-fr', 'display_name.en' => 'one-piece-op09-en'],
                ],
                [
                    'attributes' => [
                        'key' => 'royal-blood',
                        'name' => 'SANG ROYAL',
                        'type' => 'main',
                        'sort_order' => 7,
                        'is_visible' => true,
                        'display_name' => ['fr' => 'SANG ROYAL', 'en' => 'ROYAL BLOOD'],
                    ],
                    'series' => null,
                    'editions' => [
                        [
                            'attributes' => ['name' => 'ROYAL BLOOD', 'status' => 'released', 'is_visible' => true, 'official_code' => 'OP-10'],
                            'language' => 'en',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2025-03-21', 'status' => 'released'],
                                    'market' => 'north-america',
                                    'sources' => ['market' => 'one-piece-rules', 'channel' => 'one-piece-op10-en', 'released_on' => 'one-piece-op10-en', 'status' => 'one-piece-op10-en'],
                                ],
                            ],
                            'sources' => ['name' => 'one-piece-op10-en', 'language' => 'one-piece-op10-en-cards', 'status' => 'one-piece-op10-en-cards', 'official_code' => 'one-piece-op10-en'],
                        ],
                        [
                            'attributes' => ['name' => 'SANG ROYAL', 'status' => 'released', 'is_visible' => true, 'official_code' => 'OP-10'],
                            'language' => 'fr',
                            'asset' => null,
                            'releases' => [],
                            'sources' => ['name' => 'one-piece-op10-fr', 'language' => 'one-piece-op10-fr-cards', 'status' => 'one-piece-op10-fr-cards', 'official_code' => 'one-piece-op10-fr'],
                        ],
                    ],
                    'sources' => ['name' => 'one-piece-op10-fr', 'display_name.fr' => 'one-piece-op10-fr', 'display_name.en' => 'one-piece-op10-en'],
                ],
                [
                    'attributes' => [
                        'key' => 'a-fist-of-divine-speed',
                        'name' => 'DES POINGS VIFS COMME L’ÉCLAIR',
                        'type' => 'main',
                        'sort_order' => 8,
                        'is_visible' => true,
                        'display_name' => ['fr' => 'DES POINGS VIFS COMME L’ÉCLAIR', 'en' => 'A FIST OF DIVINE SPEED'],
                    ],
                    'series' => null,
                    'editions' => [
                        [
                            'attributes' => ['name' => 'A FIST OF DIVINE SPEED', 'status' => 'released', 'is_visible' => true, 'official_code' => 'OP-11'],
                            'language' => 'en',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2025-06-06', 'status' => 'released'],
                                    'market' => 'north-america',
                                    'sources' => ['market' => 'one-piece-rules', 'channel' => 'one-piece-op11-en', 'released_on' => 'one-piece-op11-en', 'status' => 'one-piece-op11-en'],
                                ],
                            ],
                            'sources' => ['name' => 'one-piece-op11-en', 'language' => 'one-piece-op11-en-cards', 'status' => 'one-piece-op11-en-cards', 'official_code' => 'one-piece-op11-en'],
                        ],
                        [
                            'attributes' => ['name' => 'DES POINGS VIFS COMME L’ÉCLAIR', 'status' => 'released', 'is_visible' => true, 'official_code' => 'OP-11'],
                            'language' => 'fr',
                            'asset' => null,
                            'releases' => [],
                            'sources' => ['name' => 'one-piece-op11-fr', 'language' => 'one-piece-op11-fr-cards', 'status' => 'one-piece-op11-fr-cards', 'official_code' => 'one-piece-op11-fr'],
                        ],
                    ],
                    'sources' => ['name' => 'one-piece-op11-fr', 'display_name.fr' => 'one-piece-op11-fr', 'display_name.en' => 'one-piece-op11-en'],
                ],
                [
                    'attributes' => [
                        'key' => 'legacy-of-the-master',
                        'name' => 'L’HÉRITAGE DU MAÎTRE',
                        'type' => 'main',
                        'sort_order' => 9,
                        'is_visible' => true,
                        'display_name' => ['fr' => 'L’HÉRITAGE DU MAÎTRE', 'en' => 'LEGACY OF THE MASTER'],
                    ],
                    'series' => null,
                    'editions' => [
                        [
                            'attributes' => ['name' => 'LEGACY OF THE MASTER', 'status' => 'released', 'is_visible' => true, 'official_code' => 'OP-12'],
                            'language' => 'en',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2025-08-22', 'status' => 'released'],
                                    'market' => 'north-america',
                                    'sources' => ['market' => 'one-piece-rules', 'channel' => 'one-piece-op12-en', 'released_on' => 'one-piece-op12-en', 'status' => 'one-piece-op12-en'],
                                ],
                            ],
                            'sources' => ['name' => 'one-piece-op12-en', 'language' => 'one-piece-op12-en-cards', 'status' => 'one-piece-op12-en-cards', 'official_code' => 'one-piece-op12-en'],
                        ],
                        [
                            'attributes' => ['name' => 'L’HÉRITAGE DU MAÎTRE', 'status' => 'released', 'is_visible' => true, 'official_code' => 'OP-12'],
                            'language' => 'fr',
                            'asset' => null,
                            'releases' => [],
                            'sources' => ['name' => 'one-piece-op12-fr', 'language' => 'one-piece-op12-fr-cards', 'status' => 'one-piece-op12-fr-cards', 'official_code' => 'one-piece-op12-fr'],
                        ],
                    ],
                    'sources' => ['name' => 'one-piece-op12-fr', 'display_name.fr' => 'one-piece-op12-fr', 'display_name.en' => 'one-piece-op12-en'],
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
            'series' => [],
            'expansions' => [
                [
                    'attributes' => [
                        'key' => 'romance-dawn',
                        'name' => 'ROMANCE DAWN',
                        'type' => 'main',
                        'sort_order' => 0,
                        'is_visible' => true,
                        'display_name' => ['ja' => 'ROMANCE DAWN', 'en' => 'ROMANCE DAWN'],
                    ],
                    'series' => null,
                    'editions' => [
                        [
                            'attributes' => ['name' => 'ROMANCE DAWN', 'status' => 'released', 'is_visible' => true, 'official_code' => 'OP-01'],
                            'language' => 'ja',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2022-07-22', 'status' => 'released'],
                                    'market' => 'japan',
                                    'sources' => ['market' => 'O1', 'channel' => 'O1', 'released_on' => 'O1', 'status' => 'O1'],
                                ],
                            ],
                            'sources' => ['name' => 'O1', 'language' => 'one-piece-ja-cards', 'status' => 'O1', 'official_code' => 'O1'],
                        ],
                    ],
                    'sources' => ['name' => 'O1', 'display_name.ja' => 'O1', 'display_name.en' => 'O2'],
                ],
                [
                    'attributes' => [
                        'key' => 'paramount-war',
                        'name' => '頂上決戦',
                        'type' => 'main',
                        'sort_order' => 1,
                        'is_visible' => true,
                        'display_name' => ['ja' => '頂上決戦', 'en' => 'PARAMOUNT WAR'],
                    ],
                    'series' => null,
                    'editions' => [
                        [
                            'attributes' => ['name' => '頂上決戦', 'status' => 'released', 'is_visible' => true, 'official_code' => 'OP-02'],
                            'language' => 'ja',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2022-11-04', 'status' => 'released'],
                                    'market' => 'japan',
                                    'sources' => ['market' => 'one-piece-op02-ja', 'channel' => 'one-piece-op02-ja', 'released_on' => 'one-piece-op02-ja', 'status' => 'one-piece-op02-ja'],
                                ],
                            ],
                            'sources' => ['name' => 'one-piece-op02-ja', 'language' => 'one-piece-op02-ja-cards', 'status' => 'one-piece-op02-ja-cards', 'official_code' => 'one-piece-op02-ja'],
                        ],
                    ],
                    'sources' => ['name' => 'one-piece-op02-ja', 'display_name.ja' => 'one-piece-op02-ja', 'display_name.en' => 'one-piece-op02-en'],
                ],
                [
                    'attributes' => [
                        'key' => 'pillars-of-strength',
                        'name' => '強大な敵',
                        'type' => 'main',
                        'sort_order' => 2,
                        'is_visible' => true,
                        'display_name' => ['ja' => '強大な敵', 'en' => 'PILLARS OF STRENGTH'],
                    ],
                    'series' => null,
                    'editions' => [
                        [
                            'attributes' => ['name' => '強大な敵', 'status' => 'released', 'is_visible' => true, 'official_code' => 'OP-03'],
                            'language' => 'ja',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2023-02-11', 'status' => 'released'],
                                    'market' => 'japan',
                                    'sources' => ['market' => 'one-piece-op03-ja', 'channel' => 'one-piece-op03-ja', 'released_on' => 'one-piece-op03-ja', 'status' => 'one-piece-op03-ja'],
                                ],
                            ],
                            'sources' => ['name' => 'one-piece-op03-ja', 'language' => 'one-piece-op03-ja-cards', 'status' => 'one-piece-op03-ja-cards', 'official_code' => 'one-piece-op03-ja'],
                        ],
                    ],
                    'sources' => ['name' => 'one-piece-op03-ja', 'display_name.ja' => 'one-piece-op03-ja', 'display_name.en' => 'one-piece-op03-en'],
                ],
                [
                    'attributes' => [
                        'key' => 'kingdoms-of-intrigue',
                        'name' => '謀略の王国',
                        'type' => 'main',
                        'sort_order' => 3,
                        'is_visible' => true,
                        'display_name' => ['ja' => '謀略の王国', 'en' => 'KINGDOMS OF INTRIGUE'],
                    ],
                    'series' => null,
                    'editions' => [
                        [
                            'attributes' => ['name' => '謀略の王国', 'status' => 'released', 'is_visible' => true, 'official_code' => 'OP-04'],
                            'language' => 'ja',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2023-05-27', 'status' => 'released'],
                                    'market' => 'japan',
                                    'sources' => ['market' => 'one-piece-op04-ja', 'channel' => 'one-piece-op04-ja', 'released_on' => 'one-piece-op04-ja', 'status' => 'one-piece-op04-ja'],
                                ],
                            ],
                            'sources' => ['name' => 'one-piece-op04-ja', 'language' => 'one-piece-op04-ja-cards', 'status' => 'one-piece-op04-ja-cards', 'official_code' => 'one-piece-op04-ja'],
                        ],
                    ],
                    'sources' => ['name' => 'one-piece-op04-ja', 'display_name.ja' => 'one-piece-op04-ja', 'display_name.en' => 'one-piece-op04-en'],
                ],
                [
                    'attributes' => [
                        'key' => 'awakening-of-the-new-era',
                        'name' => '新時代の主役',
                        'type' => 'main',
                        'sort_order' => 4,
                        'is_visible' => true,
                        'display_name' => ['ja' => '新時代の主役', 'en' => 'AWAKENING OF THE NEW ERA'],
                    ],
                    'series' => null,
                    'editions' => [
                        [
                            'attributes' => ['name' => '新時代の主役', 'status' => 'released', 'is_visible' => true, 'official_code' => 'OP-05'],
                            'language' => 'ja',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2023-08-26', 'status' => 'released'],
                                    'market' => 'japan',
                                    'sources' => ['market' => 'one-piece-op05-ja', 'channel' => 'one-piece-op05-ja', 'released_on' => 'one-piece-op05-ja', 'status' => 'one-piece-op05-ja'],
                                ],
                            ],
                            'sources' => ['name' => 'one-piece-op05-ja', 'language' => 'one-piece-op05-ja-cards', 'status' => 'one-piece-op05-ja-cards', 'official_code' => 'one-piece-op05-ja'],
                        ],
                    ],
                    'sources' => ['name' => 'one-piece-op05-ja', 'display_name.ja' => 'one-piece-op05-ja', 'display_name.en' => 'one-piece-op05-en'],
                ],
                [
                    'attributes' => [
                        'key' => 'wings-of-the-captain',
                        'name' => '双璧の覇者',
                        'type' => 'main',
                        'sort_order' => 5,
                        'is_visible' => true,
                        'display_name' => ['ja' => '双璧の覇者', 'en' => 'WINGS OF THE CAPTAIN'],
                    ],
                    'series' => null,
                    'editions' => [
                        [
                            'attributes' => ['name' => '双璧の覇者', 'status' => 'released', 'is_visible' => true, 'official_code' => 'OP-06'],
                            'language' => 'ja',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2023-11-25', 'status' => 'released'],
                                    'market' => 'japan',
                                    'sources' => ['market' => 'one-piece-op06-ja', 'channel' => 'one-piece-op06-ja', 'released_on' => 'one-piece-op06-ja', 'status' => 'one-piece-op06-ja'],
                                ],
                            ],
                            'sources' => ['name' => 'one-piece-op06-ja', 'language' => 'one-piece-op06-ja-cards', 'status' => 'one-piece-op06-ja-cards', 'official_code' => 'one-piece-op06-ja'],
                        ],
                    ],
                    'sources' => ['name' => 'one-piece-op06-ja', 'display_name.ja' => 'one-piece-op06-ja', 'display_name.en' => 'one-piece-op06-en'],
                ],
                [
                    'attributes' => [
                        'key' => 'emperors-in-the-new-world',
                        'name' => '新たなる皇帝',
                        'type' => 'main',
                        'sort_order' => 6,
                        'is_visible' => true,
                        'display_name' => ['ja' => '新たなる皇帝', 'en' => 'EMPERORS IN THE NEW WORLD'],
                    ],
                    'series' => null,
                    'editions' => [
                        [
                            'attributes' => ['name' => '新たなる皇帝', 'status' => 'released', 'is_visible' => true, 'official_code' => 'OP-09'],
                            'language' => 'ja',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2024-08-31', 'status' => 'released'],
                                    'market' => 'japan',
                                    'sources' => ['market' => 'one-piece-op09-ja', 'channel' => 'one-piece-op09-ja', 'released_on' => 'one-piece-op09-ja', 'status' => 'one-piece-op09-ja'],
                                ],
                            ],
                            'sources' => ['name' => 'one-piece-op09-ja', 'language' => 'one-piece-op09-ja-cards', 'status' => 'one-piece-op09-ja-cards', 'official_code' => 'one-piece-op09-ja'],
                        ],
                    ],
                    'sources' => ['name' => 'one-piece-op09-ja', 'display_name.ja' => 'one-piece-op09-ja', 'display_name.en' => 'one-piece-op09-en'],
                ],
                [
                    'attributes' => [
                        'key' => 'royal-blood',
                        'name' => '王族の血統',
                        'type' => 'main',
                        'sort_order' => 7,
                        'is_visible' => true,
                        'display_name' => ['ja' => '王族の血統', 'en' => 'ROYAL BLOOD'],
                    ],
                    'series' => null,
                    'editions' => [
                        [
                            'attributes' => ['name' => '王族の血統', 'status' => 'released', 'is_visible' => true, 'official_code' => 'OP-10'],
                            'language' => 'ja',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2024-11-30', 'status' => 'released'],
                                    'market' => 'japan',
                                    'sources' => ['market' => 'one-piece-op10-ja', 'channel' => 'one-piece-op10-ja', 'released_on' => 'one-piece-op10-ja', 'status' => 'one-piece-op10-ja'],
                                ],
                            ],
                            'sources' => ['name' => 'one-piece-op10-ja', 'language' => 'one-piece-op10-ja-cards', 'status' => 'one-piece-op10-ja-cards', 'official_code' => 'one-piece-op10-ja'],
                        ],
                    ],
                    'sources' => ['name' => 'one-piece-op10-ja', 'display_name.ja' => 'one-piece-op10-ja', 'display_name.en' => 'one-piece-op10-en'],
                ],
                [
                    'attributes' => [
                        'key' => 'a-fist-of-divine-speed',
                        'name' => '神速の拳',
                        'type' => 'main',
                        'sort_order' => 8,
                        'is_visible' => true,
                        'display_name' => ['ja' => '神速の拳', 'en' => 'A FIST OF DIVINE SPEED'],
                    ],
                    'series' => null,
                    'editions' => [
                        [
                            'attributes' => ['name' => '神速の拳', 'status' => 'released', 'is_visible' => true, 'official_code' => 'OP-11'],
                            'language' => 'ja',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2025-03-01', 'status' => 'released'],
                                    'market' => 'japan',
                                    'sources' => ['market' => 'one-piece-op11-ja', 'channel' => 'one-piece-op11-ja', 'released_on' => 'one-piece-op11-ja', 'status' => 'one-piece-op11-ja'],
                                ],
                            ],
                            'sources' => ['name' => 'one-piece-op11-ja', 'language' => 'one-piece-op11-ja-cards', 'status' => 'one-piece-op11-ja-cards', 'official_code' => 'one-piece-op11-ja'],
                        ],
                    ],
                    'sources' => ['name' => 'one-piece-op11-ja', 'display_name.ja' => 'one-piece-op11-ja', 'display_name.en' => 'one-piece-op11-en'],
                ],
                [
                    'attributes' => [
                        'key' => 'legacy-of-the-master',
                        'name' => '師弟の絆',
                        'type' => 'main',
                        'sort_order' => 9,
                        'is_visible' => true,
                        'display_name' => ['ja' => '師弟の絆', 'en' => 'LEGACY OF THE MASTER'],
                    ],
                    'series' => null,
                    'editions' => [
                        [
                            'attributes' => ['name' => '師弟の絆', 'status' => 'released', 'is_visible' => true, 'official_code' => 'OP-12'],
                            'language' => 'ja',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2025-05-31', 'status' => 'released'],
                                    'market' => 'japan',
                                    'sources' => ['market' => 'one-piece-op12-ja', 'channel' => 'one-piece-op12-ja', 'released_on' => 'one-piece-op12-ja', 'status' => 'one-piece-op12-ja'],
                                ],
                            ],
                            'sources' => ['name' => 'one-piece-op12-ja', 'language' => 'one-piece-op12-ja-cards', 'status' => 'one-piece-op12-ja-cards', 'official_code' => 'one-piece-op12-ja'],
                        ],
                    ],
                    'sources' => ['name' => 'one-piece-op12-ja', 'display_name.ja' => 'one-piece-op12-ja', 'display_name.en' => 'one-piece-op12-en'],
                ],
            ],
        ],
    ],
];
