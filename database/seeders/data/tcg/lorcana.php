<?php

return [
    'game' => [
        'slug' => 'lorcana',
        'name' => 'Disney Lorcana',
        'is_visible' => true,
        'display_name' => ['fr' => 'Disney Lorcana', 'en' => 'Disney Lorcana'],
    ],
    'sources' => ['name' => 'L2', 'display_name.fr' => 'L2', 'display_name.en' => 'L2'],
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
            'series' => [],
            'expansions' => [
                [
                    'attributes' => [
                        'key' => 'first-chapter',
                        'name' => 'Premier Chapitre',
                        'type' => 'main',
                        'sort_order' => 0,
                        'is_visible' => true,
                        'display_name' => ['fr' => 'Premier Chapitre', 'en' => 'The First Chapter'],
                    ],
                    'series' => null,
                    'editions' => [
                        [
                            'attributes' => ['name' => 'Premier Chapitre', 'status' => 'released', 'is_visible' => true],
                            'language' => 'fr',
                            'asset' => null,
                            'releases' => [],
                            'sources' => ['name' => 'lorcana-fr-notes', 'language' => 'lorcana-fr-notes', 'status' => 'lorcana-first-chapter-faq'],
                        ],
                        [
                            'attributes' => ['name' => 'The First Chapter', 'status' => 'released', 'is_visible' => true],
                            'language' => 'en',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2023-09-01', 'status' => 'released'],
                                    'market' => 'united-states',
                                    'sources' => [
                                        'market' => 'lorcana-us-launch',
                                        'channel' => 'lorcana-us-launch',
                                        'released_on' => 'lorcana-us-launch',
                                        'status' => 'lorcana-us-launch',
                                    ],
                                ],
                            ],
                            'sources' => ['name' => 'L2', 'language' => 'lorcana-en-checklist', 'status' => 'L2'],
                        ],
                        [
                            'attributes' => ['name' => 'THE FIRST CHAPTER 物語のはじまり', 'status' => 'released', 'is_visible' => true],
                            'language' => 'ja',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2025-01-25', 'status' => 'released'],
                                    'market' => 'japan',
                                    'sources' => ['market' => 'lorcana-first-chapter-ja', 'channel' => 'lorcana-first-chapter-ja', 'released_on' => 'lorcana-first-chapter-ja', 'status' => 'lorcana-first-chapter-ja'],
                                ],
                            ],
                            'sources' => ['name' => 'lorcana-first-chapter-ja', 'language' => 'lorcana-first-chapter-ja', 'status' => 'lorcana-first-chapter-ja'],
                        ],
                        [
                            'attributes' => ['name' => 'Das erste Kapitel', 'status' => 'released', 'is_visible' => true],
                            'language' => 'de',
                            'asset' => null,
                            'releases' => [],
                            'sources' => ['name' => 'lorcana-first-chapter-de', 'language' => 'lorcana-first-chapter-de', 'status' => 'lorcana-first-chapter-de'],
                        ],
                        [
                            'attributes' => ['name' => '泼墨第一章', 'status' => 'released', 'is_visible' => true],
                            'language' => 'zh-Hans',
                            'asset' => null,
                            'releases' => [],
                            'sources' => ['name' => 'lorcana-first-chapter-zh-hans', 'language' => 'lorcana-first-chapter-zh-hans', 'status' => 'lorcana-first-chapter-zh-hans'],
                        ],
                    ],
                    'sources' => ['name' => 'lorcana-fr-notes', 'display_name.fr' => 'lorcana-fr-notes', 'display_name.en' => 'L2'],
                ],
                [
                    'attributes' => [
                        'key' => 'rise-of-the-floodborn',
                        'name' => 'L’Ascension des Floodborn',
                        'type' => 'main',
                        'sort_order' => 1,
                        'is_visible' => true,
                        'display_name' => ['fr' => 'L’Ascension des Floodborn', 'en' => 'Rise of the Floodborn'],
                    ],
                    'series' => null,
                    'editions' => [
                        [
                            'attributes' => ['name' => 'L’Ascension des Floodborn', 'status' => 'released', 'is_visible' => true],
                            'language' => 'fr',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2023-12-01', 'status' => 'released'],
                                    'market' => 'worldwide',
                                    'sources' => ['market' => 'lorcana-rise-of-the-floodborn-release', 'channel' => 'lorcana-rise-of-the-floodborn-release', 'released_on' => 'lorcana-rise-of-the-floodborn-release', 'status' => 'lorcana-rise-of-the-floodborn-release'],
                                ],
                                [
                                    'attributes' => ['channel' => 'specialist', 'released_on' => '2023-11-17', 'status' => 'released'],
                                    'market' => 'worldwide',
                                    'sources' => ['market' => 'lorcana-rise-of-the-floodborn-release', 'channel' => 'lorcana-rise-of-the-floodborn-release', 'released_on' => 'lorcana-rise-of-the-floodborn-release', 'status' => 'lorcana-rise-of-the-floodborn-release'],
                                ],
                            ],
                            'sources' => ['name' => 'lorcana-rise-of-the-floodborn-fr', 'language' => 'lorcana-rise-of-the-floodborn-fr', 'status' => 'lorcana-rise-of-the-floodborn-fr'],
                        ],
                        [
                            'attributes' => ['name' => 'Rise of the Floodborn', 'status' => 'released', 'is_visible' => true],
                            'language' => 'en',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2023-12-01', 'status' => 'released'],
                                    'market' => 'worldwide',
                                    'sources' => ['market' => 'lorcana-rise-of-the-floodborn-release', 'channel' => 'lorcana-rise-of-the-floodborn-release', 'released_on' => 'lorcana-rise-of-the-floodborn-release', 'status' => 'lorcana-rise-of-the-floodborn-release'],
                                ],
                                [
                                    'attributes' => ['channel' => 'specialist', 'released_on' => '2023-11-17', 'status' => 'released'],
                                    'market' => 'worldwide',
                                    'sources' => ['market' => 'lorcana-rise-of-the-floodborn-release', 'channel' => 'lorcana-rise-of-the-floodborn-release', 'released_on' => 'lorcana-rise-of-the-floodborn-release', 'status' => 'lorcana-rise-of-the-floodborn-release'],
                                ],
                            ],
                            'sources' => ['name' => 'lorcana-rise-of-the-floodborn-en', 'language' => 'lorcana-rise-of-the-floodborn-en', 'status' => 'lorcana-rise-of-the-floodborn-en'],
                        ],
                        [
                            'attributes' => ['name' => 'フラッドボーンの渾沌', 'status' => 'released', 'is_visible' => true],
                            'language' => 'ja',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2025-03-22', 'status' => 'released'],
                                    'market' => 'japan',
                                    'sources' => ['market' => 'lorcana-rise-of-the-floodborn-ja', 'channel' => 'lorcana-rise-of-the-floodborn-ja', 'released_on' => 'lorcana-rise-of-the-floodborn-ja', 'status' => 'lorcana-rise-of-the-floodborn-ja'],
                                ],
                            ],
                            'sources' => ['name' => 'lorcana-rise-of-the-floodborn-ja', 'language' => 'lorcana-rise-of-the-floodborn-ja', 'status' => 'lorcana-rise-of-the-floodborn-ja'],
                        ],
                        [
                            'attributes' => ['name' => 'Aufstieg der Flutgestalten', 'status' => 'released', 'is_visible' => true],
                            'language' => 'de',
                            'asset' => null,
                            'releases' => [],
                            'sources' => ['name' => 'lorcana-rise-of-the-floodborn-de', 'language' => 'lorcana-rise-of-the-floodborn-de', 'status' => 'lorcana-rise-of-the-floodborn-de'],
                        ],
                        [
                            'attributes' => ['name' => '洪源大涌现', 'status' => 'released', 'is_visible' => true],
                            'language' => 'zh-Hans',
                            'asset' => null,
                            'releases' => [],
                            'sources' => ['name' => 'lorcana-rise-of-the-floodborn-zh-hans', 'language' => 'lorcana-rise-of-the-floodborn-zh-hans', 'status' => 'lorcana-rise-of-the-floodborn-zh-hans'],
                        ],
                    ],
                    'sources' => ['name' => 'lorcana-rise-of-the-floodborn-fr', 'display_name.fr' => 'lorcana-rise-of-the-floodborn-fr', 'display_name.en' => 'lorcana-rise-of-the-floodborn-en'],
                ],
                [
                    'attributes' => [
                        'key' => 'into-the-inklands',
                        'name' => 'Les Terres d’Encres',
                        'type' => 'main',
                        'sort_order' => 2,
                        'is_visible' => true,
                        'display_name' => ['fr' => 'Les Terres d’Encres', 'en' => 'Into the Inklands'],
                    ],
                    'series' => null,
                    'editions' => [
                        [
                            'attributes' => ['name' => 'Les Terres d’Encres', 'status' => 'released', 'is_visible' => true],
                            'language' => 'fr',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2024-03-08', 'status' => 'released'],
                                    'market' => 'worldwide',
                                    'sources' => ['market' => 'lorcana-into-the-inklands-release', 'channel' => 'lorcana-into-the-inklands-release', 'released_on' => 'lorcana-into-the-inklands-release', 'status' => 'lorcana-into-the-inklands-release'],
                                ],
                            ],
                            'sources' => ['name' => 'lorcana-into-the-inklands-fr', 'language' => 'lorcana-into-the-inklands-fr', 'status' => 'lorcana-into-the-inklands-fr'],
                        ],
                        [
                            'attributes' => ['name' => 'Into the Inklands', 'status' => 'released', 'is_visible' => true],
                            'language' => 'en',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2024-03-08', 'status' => 'released'],
                                    'market' => 'worldwide',
                                    'sources' => ['market' => 'lorcana-into-the-inklands-release', 'channel' => 'lorcana-into-the-inklands-release', 'released_on' => 'lorcana-into-the-inklands-release', 'status' => 'lorcana-into-the-inklands-release'],
                                ],
                            ],
                            'sources' => ['name' => 'lorcana-into-the-inklands-en', 'language' => 'lorcana-into-the-inklands-en', 'status' => 'lorcana-into-the-inklands-en'],
                        ],
                        [
                            'attributes' => ['name' => 'インクランド探訪', 'status' => 'released', 'is_visible' => true],
                            'language' => 'ja',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2025-05-17', 'status' => 'released'],
                                    'market' => 'japan',
                                    'sources' => ['market' => 'lorcana-into-the-inklands-ja', 'channel' => 'lorcana-into-the-inklands-ja', 'released_on' => 'lorcana-into-the-inklands-ja', 'status' => 'lorcana-into-the-inklands-ja'],
                                ],
                            ],
                            'sources' => ['name' => 'lorcana-into-the-inklands-ja', 'language' => 'lorcana-into-the-inklands-ja', 'status' => 'lorcana-into-the-inklands-ja'],
                        ],
                        [
                            'attributes' => ['name' => 'Die Tintenlande', 'status' => 'released', 'is_visible' => true],
                            'language' => 'de',
                            'asset' => null,
                            'releases' => [],
                            'sources' => ['name' => 'lorcana-into-the-inklands-de', 'language' => 'lorcana-into-the-inklands-de', 'status' => 'lorcana-into-the-inklands-de'],
                        ],
                    ],
                    'sources' => ['name' => 'lorcana-into-the-inklands-fr', 'display_name.fr' => 'lorcana-into-the-inklands-fr', 'display_name.en' => 'lorcana-into-the-inklands-en'],
                ],
                [
                    'attributes' => [
                        'key' => 'ursulas-return',
                        'name' => 'Le Retour d’Ursula',
                        'type' => 'main',
                        'sort_order' => 3,
                        'is_visible' => true,
                        'display_name' => ['fr' => 'Le Retour d’Ursula', 'en' => 'Ursula’s Return'],
                    ],
                    'series' => null,
                    'editions' => [
                        [
                            'attributes' => ['name' => 'Le Retour d’Ursula', 'status' => 'released', 'is_visible' => true],
                            'language' => 'fr',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2024-05-31', 'status' => 'released'],
                                    'market' => 'worldwide',
                                    'sources' => ['market' => 'lorcana-ursulas-return-release', 'channel' => 'lorcana-ursulas-return-release', 'released_on' => 'lorcana-ursulas-return-release', 'status' => 'lorcana-ursulas-return-release'],
                                ],
                            ],
                            'sources' => ['name' => 'lorcana-ursulas-return-fr', 'language' => 'lorcana-ursulas-return-fr', 'status' => 'lorcana-ursulas-return-fr'],
                        ],
                        [
                            'attributes' => ['name' => 'Ursula’s Return', 'status' => 'released', 'is_visible' => true],
                            'language' => 'en',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2024-05-31', 'status' => 'released'],
                                    'market' => 'worldwide',
                                    'sources' => ['market' => 'lorcana-ursulas-return-release', 'channel' => 'lorcana-ursulas-return-release', 'released_on' => 'lorcana-ursulas-return-release', 'status' => 'lorcana-ursulas-return-release'],
                                ],
                            ],
                            'sources' => ['name' => 'lorcana-ursulas-return-en', 'language' => 'lorcana-ursulas-return-en', 'status' => 'lorcana-ursulas-return-en'],
                        ],
                        [
                            'attributes' => ['name' => '逆襲のアースラ', 'status' => 'released', 'is_visible' => true],
                            'language' => 'ja',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2025-07-12', 'status' => 'released'],
                                    'market' => 'japan',
                                    'sources' => ['market' => 'lorcana-ursulas-return-ja', 'channel' => 'lorcana-ursulas-return-ja', 'released_on' => 'lorcana-ursulas-return-ja', 'status' => 'lorcana-ursulas-return-ja'],
                                ],
                            ],
                            'sources' => ['name' => 'lorcana-ursulas-return-ja', 'language' => 'lorcana-ursulas-return-ja', 'status' => 'lorcana-ursulas-return-ja'],
                        ],
                    ],
                    'sources' => ['name' => 'lorcana-ursulas-return-fr', 'display_name.fr' => 'lorcana-ursulas-return-fr', 'display_name.en' => 'lorcana-ursulas-return-en'],
                ],
                [
                    'attributes' => [
                        'key' => 'shimmering-skies',
                        'name' => 'Ciel Scintillant',
                        'type' => 'main',
                        'sort_order' => 4,
                        'is_visible' => true,
                        'display_name' => ['fr' => 'Ciel Scintillant', 'en' => 'Shimmering Skies'],
                    ],
                    'series' => null,
                    'editions' => [
                        [
                            'attributes' => ['name' => 'Ciel Scintillant', 'status' => 'released', 'is_visible' => true],
                            'language' => 'fr',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2024-08-23', 'status' => 'released'],
                                    'market' => 'worldwide',
                                    'sources' => ['market' => 'lorcana-shimmering-skies-release', 'channel' => 'lorcana-shimmering-skies-release', 'released_on' => 'lorcana-shimmering-skies-release', 'status' => 'lorcana-shimmering-skies-release'],
                                ],
                            ],
                            'sources' => ['name' => 'lorcana-shimmering-skies-fr', 'language' => 'lorcana-shimmering-skies-fr', 'status' => 'lorcana-shimmering-skies-fr'],
                        ],
                        [
                            'attributes' => ['name' => 'Shimmering Skies', 'status' => 'released', 'is_visible' => true],
                            'language' => 'en',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2024-08-23', 'status' => 'released'],
                                    'market' => 'worldwide',
                                    'sources' => ['market' => 'lorcana-shimmering-skies-release', 'channel' => 'lorcana-shimmering-skies-release', 'released_on' => 'lorcana-shimmering-skies-release', 'status' => 'lorcana-shimmering-skies-release'],
                                ],
                            ],
                            'sources' => ['name' => 'lorcana-shimmering-skies-en', 'language' => 'lorcana-shimmering-skies-en', 'status' => 'lorcana-shimmering-skies-en'],
                        ],
                        [
                            'attributes' => ['name' => '星々の輝き', 'status' => 'released', 'is_visible' => true],
                            'language' => 'ja',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2025-09-06', 'status' => 'released'],
                                    'market' => 'japan',
                                    'sources' => ['market' => 'lorcana-shimmering-skies-ja', 'channel' => 'lorcana-shimmering-skies-ja', 'released_on' => 'lorcana-shimmering-skies-ja', 'status' => 'lorcana-shimmering-skies-ja'],
                                ],
                            ],
                            'sources' => ['name' => 'lorcana-shimmering-skies-ja', 'language' => 'lorcana-shimmering-skies-ja', 'status' => 'lorcana-shimmering-skies-ja'],
                        ],
                    ],
                    'sources' => ['name' => 'lorcana-shimmering-skies-fr', 'display_name.fr' => 'lorcana-shimmering-skies-fr', 'display_name.en' => 'lorcana-shimmering-skies-en'],
                ],
                [
                    'attributes' => [
                        'key' => 'azurite-sea',
                        'name' => 'La Mer Azurite',
                        'type' => 'main',
                        'sort_order' => 5,
                        'is_visible' => true,
                        'display_name' => ['fr' => 'La Mer Azurite', 'en' => 'Azurite Sea'],
                    ],
                    'series' => null,
                    'editions' => [
                        [
                            'attributes' => ['name' => 'La Mer Azurite', 'status' => 'released', 'is_visible' => true],
                            'language' => 'fr',
                            'asset' => null,
                            'releases' => [],
                            'sources' => ['name' => 'lorcana-azurite-sea-fr', 'language' => 'lorcana-azurite-sea-fr', 'status' => 'lorcana-azurite-sea-fr'],
                        ],
                        [
                            'attributes' => ['name' => 'Azurite Sea', 'status' => 'released', 'is_visible' => true],
                            'language' => 'en',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'specialist', 'released_on' => '2024-11-15', 'status' => 'released'],
                                    'market' => 'united-states',
                                    'sources' => ['market' => 'lorcana-azurite-us-specialist', 'channel' => 'lorcana-azurite-us-specialist', 'released_on' => 'lorcana-azurite-us-specialist', 'status' => 'lorcana-azurite-us-specialist'],
                                ],
                            ],
                            'sources' => ['name' => 'lorcana-azurite-sea-en', 'language' => 'lorcana-azurite-sea-en', 'status' => 'lorcana-azurite-sea-en'],
                        ],
                        [
                            'attributes' => ['name' => '大いなるアズライト', 'status' => 'released', 'is_visible' => true],
                            'language' => 'ja',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2025-10-31', 'status' => 'released'],
                                    'market' => 'japan',
                                    'sources' => ['market' => 'lorcana-azurite-sea-ja', 'channel' => 'lorcana-azurite-sea-ja', 'released_on' => 'lorcana-azurite-sea-ja', 'status' => 'lorcana-azurite-sea-ja'],
                                ],
                            ],
                            'sources' => ['name' => 'lorcana-azurite-sea-ja', 'language' => 'lorcana-azurite-sea-ja', 'status' => 'lorcana-azurite-sea-ja'],
                        ],
                    ],
                    'sources' => ['name' => 'lorcana-azurite-sea-fr', 'display_name.fr' => 'lorcana-azurite-sea-fr', 'display_name.en' => 'lorcana-azurite-sea-en'],
                ],
                [
                    'attributes' => [
                        'key' => 'archazias-island',
                        'name' => 'L’Île d’Archazia',
                        'type' => 'main',
                        'sort_order' => 6,
                        'is_visible' => true,
                        'display_name' => ['fr' => 'L’Île d’Archazia', 'en' => 'Archazia’s Island'],
                    ],
                    'series' => null,
                    'editions' => [
                        [
                            'attributes' => ['name' => 'L’Île d’Archazia', 'status' => 'released', 'is_visible' => true],
                            'language' => 'fr',
                            'asset' => null,
                            'releases' => [],
                            'sources' => ['name' => 'lorcana-archazias-island-fr', 'language' => 'lorcana-archazias-island-fr', 'status' => 'lorcana-archazias-island-fr'],
                        ],
                        [
                            'attributes' => ['name' => 'Archazia’s Island', 'status' => 'released', 'is_visible' => true],
                            'language' => 'en',
                            'asset' => null,
                            'releases' => [],
                            'sources' => ['name' => 'lorcana-archazias-island-en', 'language' => 'lorcana-archazias-island-en', 'status' => 'lorcana-archazias-island-en'],
                        ],
                        [
                            'attributes' => ['name' => 'アーケイジアと魔法の島', 'status' => 'released', 'is_visible' => true],
                            'language' => 'ja',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2025-12-27', 'status' => 'released'],
                                    'market' => 'japan',
                                    'sources' => ['market' => 'lorcana-archazias-island-ja', 'channel' => 'lorcana-archazias-island-ja', 'released_on' => 'lorcana-archazias-island-ja', 'status' => 'lorcana-archazias-island-ja'],
                                ],
                            ],
                            'sources' => ['name' => 'lorcana-archazias-island-ja', 'language' => 'lorcana-archazias-island-ja', 'status' => 'lorcana-archazias-island-ja'],
                        ],
                    ],
                    'sources' => ['name' => 'lorcana-archazias-island-fr', 'display_name.fr' => 'lorcana-archazias-island-fr', 'display_name.en' => 'lorcana-archazias-island-en'],
                ],
                [
                    'attributes' => [
                        'key' => 'reign-of-jafar',
                        'name' => 'Le Règne de Jafar',
                        'type' => 'main',
                        'sort_order' => 7,
                        'is_visible' => true,
                        'display_name' => ['fr' => 'Le Règne de Jafar', 'en' => 'Reign of Jafar'],
                    ],
                    'series' => null,
                    'editions' => [
                        [
                            'attributes' => ['name' => 'Le Règne de Jafar', 'status' => 'released', 'is_visible' => true],
                            'language' => 'fr',
                            'asset' => null,
                            'releases' => [],
                            'sources' => ['name' => 'lorcana-reign-of-jafar-fr', 'language' => 'lorcana-reign-of-jafar-fr', 'status' => 'lorcana-reign-of-jafar-fr'],
                        ],
                        [
                            'attributes' => ['name' => 'Reign of Jafar', 'status' => 'released', 'is_visible' => true],
                            'language' => 'en',
                            'asset' => null,
                            'releases' => [],
                            'sources' => ['name' => 'lorcana-reign-of-jafar-en', 'language' => 'lorcana-reign-of-jafar-en', 'status' => 'lorcana-reign-of-jafar-en'],
                        ],
                        [
                            'attributes' => ['name' => 'ジャファーの王権', 'status' => 'released', 'is_visible' => true],
                            'language' => 'ja',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2026-02-21', 'status' => 'released'],
                                    'market' => 'japan',
                                    'sources' => ['market' => 'lorcana-reign-of-jafar-ja', 'channel' => 'lorcana-reign-of-jafar-ja', 'released_on' => 'lorcana-reign-of-jafar-ja', 'status' => 'lorcana-reign-of-jafar-ja'],
                                ],
                            ],
                            'sources' => ['name' => 'lorcana-reign-of-jafar-ja', 'language' => 'lorcana-reign-of-jafar-ja', 'status' => 'lorcana-reign-of-jafar-ja'],
                        ],
                    ],
                    'sources' => ['name' => 'lorcana-reign-of-jafar-fr', 'display_name.fr' => 'lorcana-reign-of-jafar-fr', 'display_name.en' => 'lorcana-reign-of-jafar-en'],
                ],
                [
                    'attributes' => [
                        'key' => 'fabled',
                        'name' => 'Fabuleux',
                        'type' => 'main',
                        'sort_order' => 8,
                        'is_visible' => true,
                        'display_name' => ['fr' => 'Fabuleux', 'en' => 'Fabled'],
                    ],
                    'series' => null,
                    'editions' => [
                        [
                            'attributes' => ['name' => 'Fabuleux', 'status' => 'released', 'is_visible' => true],
                            'language' => 'fr',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2025-09-05', 'status' => 'released'],
                                    'market' => 'worldwide',
                                    'sources' => ['market' => 'lorcana-fabled-release', 'channel' => 'lorcana-fabled-release', 'released_on' => 'lorcana-fabled-release', 'status' => 'lorcana-fabled-release'],
                                ],
                            ],
                            'sources' => ['name' => 'lorcana-fabled-fr', 'language' => 'lorcana-fabled-fr', 'status' => 'lorcana-fabled-fr'],
                        ],
                        [
                            'attributes' => ['name' => 'Fabled', 'status' => 'released', 'is_visible' => true],
                            'language' => 'en',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2025-09-05', 'status' => 'released'],
                                    'market' => 'worldwide',
                                    'sources' => ['market' => 'lorcana-fabled-release', 'channel' => 'lorcana-fabled-release', 'released_on' => 'lorcana-fabled-release', 'status' => 'lorcana-fabled-release'],
                                ],
                            ],
                            'sources' => ['name' => 'lorcana-fabled-en', 'language' => 'lorcana-fabled-en', 'status' => 'lorcana-fabled-en'],
                        ],
                    ],
                    'sources' => ['name' => 'lorcana-fabled-fr', 'display_name.fr' => 'lorcana-fabled-fr', 'display_name.en' => 'lorcana-fabled-en'],
                ],
                [
                    'attributes' => [
                        'key' => 'whispers-in-the-well',
                        'name' => 'Lueurs dans les Profondeurs',
                        'type' => 'main',
                        'sort_order' => 9,
                        'is_visible' => true,
                        'display_name' => ['fr' => 'Lueurs dans les Profondeurs', 'en' => 'Whispers in the Well'],
                    ],
                    'series' => null,
                    'editions' => [
                        [
                            'attributes' => ['name' => 'Lueurs dans les Profondeurs', 'status' => 'released', 'is_visible' => true],
                            'language' => 'fr',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2025-11-14', 'status' => 'released'],
                                    'market' => 'worldwide',
                                    'sources' => ['market' => 'lorcana-whispers-in-the-well-release', 'channel' => 'lorcana-whispers-in-the-well-release', 'released_on' => 'lorcana-whispers-in-the-well-release', 'status' => 'lorcana-whispers-in-the-well-release'],
                                ],
                            ],
                            'sources' => ['name' => 'lorcana-whispers-in-the-well-fr', 'language' => 'lorcana-whispers-in-the-well-fr', 'status' => 'lorcana-whispers-in-the-well-fr'],
                        ],
                        [
                            'attributes' => ['name' => 'Whispers in the Well', 'status' => 'released', 'is_visible' => true],
                            'language' => 'en',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2025-11-14', 'status' => 'released'],
                                    'market' => 'worldwide',
                                    'sources' => ['market' => 'lorcana-whispers-in-the-well-release', 'channel' => 'lorcana-whispers-in-the-well-release', 'released_on' => 'lorcana-whispers-in-the-well-release', 'status' => 'lorcana-whispers-in-the-well-release'],
                                ],
                            ],
                            'sources' => ['name' => 'lorcana-whispers-in-the-well-en', 'language' => 'lorcana-whispers-in-the-well-en', 'status' => 'lorcana-whispers-in-the-well-en'],
                        ],
                    ],
                    'sources' => ['name' => 'lorcana-whispers-in-the-well-fr', 'display_name.fr' => 'lorcana-whispers-in-the-well-fr', 'display_name.en' => 'lorcana-whispers-in-the-well-en'],
                ],
            ],
        ],
    ],
];
