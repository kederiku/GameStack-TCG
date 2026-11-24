<?php

return [
    'game' => [
        'slug' => 'flesh-and-blood',
        'name' => 'Flesh and Blood',
        'is_visible' => true,
        'display_name' => ['fr' => 'Flesh and Blood', 'en' => 'Flesh and Blood'],
    ],
    'sources' => ['name' => 'fab-wtr', 'display_name.fr' => 'fab-wtr', 'display_name.en' => 'fab-wtr'],
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
                        'key' => 'welcome-to-rathe',
                        'name' => 'Welcome to Rathe',
                        'type' => 'main',
                        'sort_order' => 0,
                        'is_visible' => true,
                        'display_name' => ['en' => 'Welcome to Rathe'],
                    ],
                    'series' => null,
                    'editions' => [
                        [
                            'attributes' => ['name' => 'Welcome to Rathe', 'status' => 'released', 'is_visible' => true, 'official_code' => 'WTR'],
                            'language' => 'en',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'specialist', 'released_on' => '2019-10-11', 'status' => 'released'],
                                    'market' => 'united-states',
                                    'sources' => ['market' => 'fab-wtr', 'channel' => 'fab-wtr', 'released_on' => 'fab-wtr-date', 'status' => 'fab-wtr-date'],
                                ],
                            ],
                            'sources' => ['name' => 'fab-wtr', 'language' => 'fab-wtr-print', 'status' => 'fab-wtr-date', 'official_code' => 'fab-wtr'],
                        ],
                    ],
                    'sources' => ['name' => 'fab-wtr', 'display_name.en' => 'fab-wtr'],
                ],
                [
                    'attributes' => [
                        'key' => 'history-pack-1',
                        'name' => 'History Pack 1',
                        'type' => 'reprint',
                        'sort_order' => 1,
                        'is_visible' => true,
                        'display_name' => ['en' => 'History Pack 1', 'fr' => 'History Pack 1'],
                    ],
                    'series' => null,
                    'editions' => [
                        [
                            'attributes' => ['name' => 'History Pack 1', 'status' => 'released', 'is_visible' => true, 'official_code' => '1HP'],
                            'language' => 'en',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'specialist', 'released_on' => '2022-05-06', 'status' => 'released'],
                                    'market' => 'united-states',
                                    'sources' => [
                                        'market' => 'fab-history-markets',
                                        'channel' => 'fab-history-live',
                                        'released_on' => 'fab-history-live',
                                        'status' => 'fab-history-live',
                                    ],
                                ],
                            ],
                            'sources' => ['name' => 'fab-history', 'language' => 'fab-history', 'status' => 'fab-history-live', 'official_code' => 'fab-history'],
                        ],
                        [
                            'attributes' => ['name' => 'History Pack 1 - Black Label', 'status' => 'released', 'is_visible' => true, 'official_code' => '1HP'],
                            'language' => 'fr',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'specialist', 'released_on' => '2022-07-01', 'status' => 'released'],
                                    'market' => 'europe',
                                    'sources' => ['market' => 'fab-history-black-label-europe', 'channel' => 'fab-history-black-label-europe', 'released_on' => 'fab-history-black-label-europe', 'status' => 'fab-history-black-label-europe'],
                                ],
                            ],
                            'sources' => ['name' => 'fab-history-black-label', 'language' => 'fab-history-black-label', 'status' => 'fab-history-black-label', 'official_code' => 'fab-history-black-label'],
                        ],
                        [
                            'attributes' => ['name' => 'History Pack 1 - Black Label', 'status' => 'released', 'is_visible' => true, 'official_code' => '1HP'],
                            'language' => 'de',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'specialist', 'released_on' => '2022-07-01', 'status' => 'released'],
                                    'market' => 'europe',
                                    'sources' => ['market' => 'fab-history-black-label-europe', 'channel' => 'fab-history-black-label-europe', 'released_on' => 'fab-history-black-label-europe', 'status' => 'fab-history-black-label-europe'],
                                ],
                            ],
                            'sources' => ['name' => 'fab-history-black-label', 'language' => 'fab-history-black-label', 'status' => 'fab-history-black-label', 'official_code' => 'fab-history-black-label'],
                        ],
                        [
                            'attributes' => ['name' => 'History Pack 1 - Black Label', 'status' => 'released', 'is_visible' => true, 'official_code' => '1HP'],
                            'language' => 'it',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'specialist', 'released_on' => '2022-07-01', 'status' => 'released'],
                                    'market' => 'europe',
                                    'sources' => ['market' => 'fab-history-black-label-europe', 'channel' => 'fab-history-black-label-europe', 'released_on' => 'fab-history-black-label-europe', 'status' => 'fab-history-black-label-europe'],
                                ],
                            ],
                            'sources' => ['name' => 'fab-history-black-label', 'language' => 'fab-history-black-label', 'status' => 'fab-history-black-label', 'official_code' => 'fab-history-black-label'],
                        ],
                        [
                            'attributes' => ['name' => 'History Pack 1 - Black Label', 'status' => 'released', 'is_visible' => true, 'official_code' => '1HP'],
                            'language' => 'es',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'specialist', 'released_on' => '2022-07-01', 'status' => 'released'],
                                    'market' => 'europe',
                                    'sources' => ['market' => 'fab-history-black-label-europe', 'channel' => 'fab-history-black-label-europe', 'released_on' => 'fab-history-black-label-europe', 'status' => 'fab-history-black-label-europe'],
                                ],
                            ],
                            'sources' => ['name' => 'fab-history-black-label', 'language' => 'fab-history-black-label', 'status' => 'fab-history-black-label', 'official_code' => 'fab-history-black-label'],
                        ],
                    ],
                    'sources' => ['name' => 'fab-history', 'type' => 'fab-history-live', 'display_name.en' => 'fab-history', 'display_name.fr' => 'fab-history-black-label'],
                ],
                [
                    'attributes' => [
                        'key' => 'arcane-rising',
                        'name' => 'Arcane Rising',
                        'type' => 'main',
                        'sort_order' => 2,
                        'is_visible' => true,
                        'display_name' => ['en' => 'Arcane Rising'],
                    ],
                    'series' => null,
                    'editions' => [
                        [
                            'attributes' => ['name' => 'Arcane Rising', 'status' => 'released', 'is_visible' => true, 'official_code' => 'ARC'],
                            'language' => 'en',
                            'asset' => null,
                            'releases' => [],
                            'sources' => ['name' => 'fab-arc-product', 'language' => 'fab-arc-en', 'status' => 'fab-arc-en', 'official_code' => 'fab-arc-product'],
                        ],
                    ],
                    'sources' => ['name' => 'fab-arc-product', 'display_name.en' => 'fab-arc-product'],
                ],
                [
                    'attributes' => [
                        'key' => 'crucible-of-war',
                        'name' => 'Crucible of War',
                        'type' => 'special',
                        'sort_order' => 3,
                        'is_visible' => true,
                        'display_name' => ['en' => 'Crucible of War'],
                    ],
                    'series' => null,
                    'editions' => [
                        [
                            'attributes' => ['name' => 'Crucible of War', 'status' => 'released', 'is_visible' => true, 'official_code' => 'CRU'],
                            'language' => 'en',
                            'asset' => null,
                            'releases' => [],
                            'sources' => ['name' => 'fab-cru-product', 'language' => 'fab-cru-en', 'status' => 'fab-cru-en', 'official_code' => 'fab-cru-product'],
                        ],
                    ],
                    'sources' => ['name' => 'fab-cru-product', 'type' => 'fab-cru-product', 'display_name.en' => 'fab-cru-product'],
                ],
                [
                    'attributes' => [
                        'key' => 'monarch',
                        'name' => 'Monarch',
                        'type' => 'main',
                        'sort_order' => 4,
                        'is_visible' => true,
                        'display_name' => ['en' => 'Monarch'],
                    ],
                    'series' => null,
                    'editions' => [
                        [
                            'attributes' => ['name' => 'Monarch', 'status' => 'released', 'is_visible' => true, 'official_code' => 'MON'],
                            'language' => 'en',
                            'asset' => null,
                            'releases' => [],
                            'sources' => ['name' => 'fab-mon-product', 'language' => 'fab-mon-product', 'status' => 'fab-mon-product', 'official_code' => 'fab-mon-product'],
                        ],
                    ],
                    'sources' => ['name' => 'fab-mon-product', 'display_name.en' => 'fab-mon-product'],
                ],
                [
                    'attributes' => [
                        'key' => 'tales-of-aria',
                        'name' => 'Tales of Aria',
                        'type' => 'main',
                        'sort_order' => 5,
                        'is_visible' => true,
                        'display_name' => ['en' => 'Tales of Aria'],
                    ],
                    'series' => null,
                    'editions' => [
                        [
                            'attributes' => ['name' => 'Tales of Aria', 'status' => 'released', 'is_visible' => true, 'official_code' => 'ELE'],
                            'language' => 'en',
                            'asset' => null,
                            'releases' => [],
                            'sources' => ['name' => 'fab-ele-product', 'language' => 'fab-ele-en', 'status' => 'fab-ele-en', 'official_code' => 'fab-ele-product'],
                        ],
                    ],
                    'sources' => ['name' => 'fab-ele-product', 'display_name.en' => 'fab-ele-product'],
                ],
                [
                    'attributes' => [
                        'key' => 'uprising',
                        'name' => 'Uprising',
                        'type' => 'main',
                        'sort_order' => 6,
                        'is_visible' => true,
                        'display_name' => ['en' => 'Uprising'],
                    ],
                    'series' => null,
                    'editions' => [
                        [
                            'attributes' => ['name' => 'Uprising', 'status' => 'released', 'is_visible' => true, 'official_code' => 'UPR'],
                            'language' => 'en',
                            'asset' => null,
                            'releases' => [],
                            'sources' => ['name' => 'fab-upr-product', 'language' => 'fab-upr-product', 'status' => 'fab-upr-product', 'official_code' => 'fab-upr-product'],
                        ],
                    ],
                    'sources' => ['name' => 'fab-upr-product', 'display_name.en' => 'fab-upr-product'],
                ],
                [
                    'attributes' => [
                        'key' => 'outsiders',
                        'name' => 'Outsiders',
                        'type' => 'main',
                        'sort_order' => 7,
                        'is_visible' => true,
                        'display_name' => ['en' => 'Outsiders', 'fr' => 'Outsiders'],
                    ],
                    'series' => null,
                    'editions' => [
                        [
                            'attributes' => ['name' => 'Outsiders', 'status' => 'released', 'is_visible' => true, 'official_code' => 'OUT'],
                            'language' => 'en',
                            'asset' => null,
                            'releases' => [],
                            'sources' => ['name' => 'fab-out-product', 'language' => 'fab-out-print', 'status' => 'fab-out-print', 'official_code' => 'fab-out-product'],
                        ],
                        [
                            'attributes' => ['name' => 'Outsiders', 'status' => 'released', 'is_visible' => true, 'official_code' => 'OUT'],
                            'language' => 'fr',
                            'asset' => null,
                            'releases' => [],
                            'sources' => ['name' => 'fab-out-fr-product', 'language' => 'fab-out-print', 'status' => 'fab-out-print', 'official_code' => 'fab-out-product'],
                        ],
                        [
                            'attributes' => ['name' => 'Outsiders', 'status' => 'released', 'is_visible' => true, 'official_code' => 'OUT'],
                            'language' => 'de',
                            'asset' => null,
                            'releases' => [],
                            'sources' => ['name' => 'fab-out-product', 'language' => 'fab-out-print', 'status' => 'fab-out-print', 'official_code' => 'fab-out-product'],
                        ],
                        [
                            'attributes' => ['name' => 'Outsiders', 'status' => 'released', 'is_visible' => true, 'official_code' => 'OUT'],
                            'language' => 'it',
                            'asset' => null,
                            'releases' => [],
                            'sources' => ['name' => 'fab-out-product', 'language' => 'fab-out-print', 'status' => 'fab-out-print', 'official_code' => 'fab-out-product'],
                        ],
                        [
                            'attributes' => ['name' => 'Outsiders', 'status' => 'released', 'is_visible' => true, 'official_code' => 'OUT'],
                            'language' => 'es',
                            'asset' => null,
                            'releases' => [],
                            'sources' => ['name' => 'fab-out-product', 'language' => 'fab-out-print', 'status' => 'fab-out-print', 'official_code' => 'fab-out-product'],
                        ],
                    ],
                    'sources' => ['name' => 'fab-out-product', 'display_name.en' => 'fab-out-product', 'display_name.fr' => 'fab-out-fr-product'],
                ],
                [
                    'attributes' => [
                        'key' => 'bright-lights',
                        'name' => 'Bright Lights',
                        'type' => 'main',
                        'sort_order' => 8,
                        'is_visible' => true,
                        'display_name' => ['en' => 'Bright Lights', 'fr' => 'Bright Lights'],
                    ],
                    'series' => null,
                    'editions' => [
                        [
                            'attributes' => ['name' => 'Bright Lights', 'status' => 'released', 'is_visible' => true, 'official_code' => 'EVO'],
                            'language' => 'en',
                            'asset' => null,
                            'releases' => [],
                            'sources' => ['name' => 'fab-evo-product', 'language' => 'fab-evo-print', 'status' => 'fab-evo-print', 'official_code' => 'fab-evo-product'],
                        ],
                        [
                            'attributes' => ['name' => 'Bright Lights', 'status' => 'released', 'is_visible' => true, 'official_code' => 'EVO'],
                            'language' => 'fr',
                            'asset' => null,
                            'releases' => [],
                            'sources' => ['name' => 'fab-evo-fr-product', 'language' => 'fab-evo-print', 'status' => 'fab-evo-print', 'official_code' => 'fab-evo-product'],
                        ],
                        [
                            'attributes' => ['name' => 'Bright Lights', 'status' => 'released', 'is_visible' => true, 'official_code' => 'EVO'],
                            'language' => 'de',
                            'asset' => null,
                            'releases' => [],
                            'sources' => ['name' => 'fab-evo-product', 'language' => 'fab-evo-print', 'status' => 'fab-evo-print', 'official_code' => 'fab-evo-product'],
                        ],
                        [
                            'attributes' => ['name' => 'Bright Lights', 'status' => 'released', 'is_visible' => true, 'official_code' => 'EVO'],
                            'language' => 'it',
                            'asset' => null,
                            'releases' => [],
                            'sources' => ['name' => 'fab-evo-product', 'language' => 'fab-evo-print', 'status' => 'fab-evo-print', 'official_code' => 'fab-evo-product'],
                        ],
                        [
                            'attributes' => ['name' => 'Bright Lights', 'status' => 'released', 'is_visible' => true, 'official_code' => 'EVO'],
                            'language' => 'es',
                            'asset' => null,
                            'releases' => [],
                            'sources' => ['name' => 'fab-evo-product', 'language' => 'fab-evo-print', 'status' => 'fab-evo-print', 'official_code' => 'fab-evo-product'],
                        ],
                    ],
                    'sources' => ['name' => 'fab-evo-product', 'display_name.en' => 'fab-evo-product', 'display_name.fr' => 'fab-evo-fr-product'],
                ],
                [
                    'attributes' => [
                        'key' => 'heavy-hitters',
                        'name' => 'Heavy Hitters',
                        'type' => 'main',
                        'sort_order' => 9,
                        'is_visible' => true,
                        'display_name' => ['en' => 'Heavy Hitters', 'fr' => 'Heavy Hitters'],
                    ],
                    'series' => null,
                    'editions' => [
                        [
                            'attributes' => ['name' => 'Heavy Hitters', 'status' => 'released', 'is_visible' => true, 'official_code' => 'HVY'],
                            'language' => 'en',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'specialist', 'released_on' => '2024-02-02', 'status' => 'released'],
                                    'market' => 'worldwide',
                                    'sources' => ['market' => 'fab-hvy-worldwide', 'channel' => 'fab-hvy-in-stores', 'released_on' => 'fab-hvy-in-stores', 'status' => 'fab-hvy-in-stores'],
                                ],
                            ],
                            'sources' => ['name' => 'fab-hvy-product', 'language' => 'fab-hvy-print', 'status' => 'fab-hvy-print', 'official_code' => 'fab-hvy-product'],
                        ],
                        [
                            'attributes' => ['name' => 'Heavy Hitters', 'status' => 'released', 'is_visible' => true, 'official_code' => 'HVY'],
                            'language' => 'fr',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'specialist', 'released_on' => '2024-02-02', 'status' => 'released'],
                                    'market' => 'worldwide',
                                    'sources' => ['market' => 'fab-hvy-worldwide', 'channel' => 'fab-hvy-in-stores', 'released_on' => 'fab-hvy-in-stores', 'status' => 'fab-hvy-in-stores'],
                                ],
                            ],
                            'sources' => ['name' => 'fab-hvy-fr-product', 'language' => 'fab-hvy-print', 'status' => 'fab-hvy-print', 'official_code' => 'fab-hvy-product'],
                        ],
                        [
                            'attributes' => ['name' => 'Heavy Hitters', 'status' => 'released', 'is_visible' => true, 'official_code' => 'HVY'],
                            'language' => 'de',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'specialist', 'released_on' => '2024-02-02', 'status' => 'released'],
                                    'market' => 'worldwide',
                                    'sources' => ['market' => 'fab-hvy-worldwide', 'channel' => 'fab-hvy-in-stores', 'released_on' => 'fab-hvy-in-stores', 'status' => 'fab-hvy-in-stores'],
                                ],
                            ],
                            'sources' => ['name' => 'fab-hvy-product', 'language' => 'fab-hvy-print', 'status' => 'fab-hvy-print', 'official_code' => 'fab-hvy-product'],
                        ],
                        [
                            'attributes' => ['name' => 'Heavy Hitters', 'status' => 'released', 'is_visible' => true, 'official_code' => 'HVY'],
                            'language' => 'it',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'specialist', 'released_on' => '2024-02-02', 'status' => 'released'],
                                    'market' => 'worldwide',
                                    'sources' => ['market' => 'fab-hvy-worldwide', 'channel' => 'fab-hvy-in-stores', 'released_on' => 'fab-hvy-in-stores', 'status' => 'fab-hvy-in-stores'],
                                ],
                            ],
                            'sources' => ['name' => 'fab-hvy-product', 'language' => 'fab-hvy-print', 'status' => 'fab-hvy-print', 'official_code' => 'fab-hvy-product'],
                        ],
                        [
                            'attributes' => ['name' => 'Heavy Hitters', 'status' => 'released', 'is_visible' => true, 'official_code' => 'HVY'],
                            'language' => 'es',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'specialist', 'released_on' => '2024-02-02', 'status' => 'released'],
                                    'market' => 'worldwide',
                                    'sources' => ['market' => 'fab-hvy-worldwide', 'channel' => 'fab-hvy-in-stores', 'released_on' => 'fab-hvy-in-stores', 'status' => 'fab-hvy-in-stores'],
                                ],
                            ],
                            'sources' => ['name' => 'fab-hvy-product', 'language' => 'fab-hvy-print', 'status' => 'fab-hvy-print', 'official_code' => 'fab-hvy-product'],
                        ],
                    ],
                    'sources' => ['name' => 'fab-hvy-product', 'display_name.en' => 'fab-hvy-product', 'display_name.fr' => 'fab-hvy-fr-product'],
                ],
            ],
        ],
    ],
];
