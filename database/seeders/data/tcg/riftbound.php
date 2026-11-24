<?php

return [
    'game' => [
        'slug' => 'riftbound',
        'name' => 'Riftbound',
        'is_visible' => true,
        'display_name' => ['fr' => 'Riftbound', 'en' => 'Riftbound'],
    ],
    'sources' => ['name' => 'riftbound-sets', 'display_name.fr' => 'riftbound-sets', 'display_name.en' => 'riftbound-sets'],
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
                        'key' => 'origins',
                        'name' => 'Origins',
                        'type' => 'main',
                        'sort_order' => 0,
                        'is_visible' => true,
                        'display_name' => ['en' => 'Origins', 'fr' => 'Origines'],
                    ],
                    'series' => null,
                    'editions' => [
                        [
                            'attributes' => ['name' => 'Origins', 'status' => 'released', 'is_visible' => true, 'official_code' => 'OGN'],
                            'language' => 'en',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2025-10-31', 'status' => 'released'],
                                    'market' => 'north-america',
                                    'sources' => [
                                        'market' => 'riftbound-release',
                                        'channel' => 'riftbound-release',
                                        'released_on' => 'riftbound-release',
                                        'status' => 'riftbound-release',
                                    ],
                                ],
                            ],
                            'sources' => [
                                'name' => 'riftbound-sets',
                                'language' => 'riftbound-release',
                                'status' => 'riftbound-launch',
                                'official_code' => 'riftbound-sets',
                            ],
                        ],
                        [
                            'attributes' => ['name' => 'Origines', 'status' => 'released', 'is_visible' => true],
                            'language' => 'fr',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2026-05-29', 'status' => 'released'],
                                    'market' => 'france',
                                    'sources' => ['market' => 'riftbound-france-launch', 'channel' => 'riftbound-roadmap-fr', 'released_on' => 'riftbound-roadmap-fr', 'status' => 'riftbound-roadmap-fr'],
                                ],
                            ],
                            'sources' => ['name' => 'riftbound-roadmap-fr', 'language' => 'riftbound-roadmap-fr', 'status' => 'riftbound-roadmap-fr'],
                        ],
                        [
                            'attributes' => ['name' => '오리진', 'status' => 'released', 'is_visible' => true],
                            'language' => 'ko',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2026-09-18', 'status' => 'released'],
                                    'market' => 'south-korea',
                                    'sources' => ['market' => 'riftbound-korea-launch', 'channel' => 'riftbound-korea-launch', 'released_on' => 'riftbound-korea-launch', 'status' => 'riftbound-korea-launch'],
                                ],
                            ],
                            'sources' => ['name' => 'riftbound-korea-launch', 'language' => 'riftbound-korea-launch', 'status' => 'riftbound-korea-launch'],
                        ],
                        [
                            'attributes' => ['name' => '起源', 'status' => 'released', 'is_visible' => true],
                            'language' => 'zh-Hans',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2025-08-01', 'status' => 'released'],
                                    'market' => 'mainland-china',
                                    'sources' => ['market' => 'riftbound-origins-cn-launch', 'channel' => 'riftbound-origins-cn-launch', 'released_on' => 'riftbound-origins-cn-launch', 'status' => 'riftbound-origins-cn-launch'],
                                ],
                            ],
                            'sources' => ['name' => 'riftbound-origins-cn-launch', 'language' => 'riftbound-origins-cn-launch', 'status' => 'riftbound-origins-cn-launch'],
                        ],
                        [
                            'attributes' => ['name' => '起源', 'status' => 'released', 'is_visible' => true],
                            'language' => 'zh-Hant',
                            'asset' => null,
                            'releases' => [],
                            'sources' => ['name' => 'riftbound-origins-zh-hant', 'language' => 'riftbound-origins-zh-hant', 'status' => 'riftbound-origins-zh-hant'],
                        ],
                    ],
                    'sources' => ['name' => 'riftbound-sets', 'display_name.en' => 'riftbound-sets', 'display_name.fr' => 'riftbound-roadmap-fr'],
                ],
                [
                    'attributes' => [
                        'key' => 'proving-grounds',
                        'name' => 'Proving Grounds',
                        'type' => 'deck',
                        'sort_order' => 1,
                        'is_visible' => true,
                        'display_name' => ['en' => 'Proving Grounds', 'fr' => 'Premiers pas'],
                    ],
                    'series' => null,
                    'editions' => [
                        [
                            'attributes' => ['name' => 'Origins: Proving Grounds', 'status' => 'released', 'is_visible' => true, 'official_code' => 'OGS'],
                            'language' => 'en',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2025-10-31', 'status' => 'released'],
                                    'market' => 'north-america',
                                    'sources' => [
                                        'market' => 'riftbound-release',
                                        'channel' => 'riftbound-release',
                                        'released_on' => 'riftbound-release',
                                        'status' => 'riftbound-release',
                                    ],
                                ],
                            ],
                            'sources' => ['name' => 'riftbound-release', 'language' => 'riftbound-release', 'status' => 'riftbound-launch', 'official_code' => 'riftbound-sets'],
                        ],
                        [
                            'attributes' => ['name' => 'Premiers pas', 'status' => 'released', 'is_visible' => true],
                            'language' => 'fr',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2026-05-29', 'status' => 'released'],
                                    'market' => 'france',
                                    'sources' => ['market' => 'riftbound-france-launch', 'channel' => 'riftbound-roadmap-fr', 'released_on' => 'riftbound-roadmap-fr', 'status' => 'riftbound-roadmap-fr'],
                                ],
                            ],
                            'sources' => ['name' => 'riftbound-roadmap-fr', 'language' => 'riftbound-roadmap-fr', 'status' => 'riftbound-roadmap-fr'],
                        ],
                        [
                            'attributes' => ['name' => '증명의 전장', 'status' => 'released', 'is_visible' => true],
                            'language' => 'ko',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2026-09-18', 'status' => 'released'],
                                    'market' => 'south-korea',
                                    'sources' => ['market' => 'riftbound-korea-launch', 'channel' => 'riftbound-korea-launch', 'released_on' => 'riftbound-korea-launch', 'status' => 'riftbound-korea-launch'],
                                ],
                            ],
                            'sources' => ['name' => 'riftbound-korea-launch', 'language' => 'riftbound-korea-launch', 'status' => 'riftbound-korea-launch'],
                        ],
                        [
                            'attributes' => ['name' => '试炼之地', 'status' => 'released', 'is_visible' => true],
                            'language' => 'zh-Hans',
                            'asset' => null,
                            'releases' => [],
                            'sources' => ['name' => 'riftbound-proving-grounds-zh-hans', 'language' => 'riftbound-proving-grounds-zh-hans', 'status' => 'riftbound-proving-grounds-zh-hans'],
                        ],
                        [
                            'attributes' => ['name' => '試煉之地', 'status' => 'released', 'is_visible' => true],
                            'language' => 'zh-Hant',
                            'asset' => null,
                            'releases' => [],
                            'sources' => ['name' => 'riftbound-proving-grounds-zh-hant', 'language' => 'riftbound-proving-grounds-zh-hant', 'status' => 'riftbound-proving-grounds-zh-hant'],
                        ],
                    ],
                    'sources' => ['name' => 'riftbound-sets', 'type' => 'riftbound-sets', 'display_name.en' => 'riftbound-sets', 'display_name.fr' => 'riftbound-roadmap-fr'],
                ],
                [
                    'attributes' => [
                        'key' => 'spiritforged',
                        'name' => 'Spiritforged',
                        'type' => 'main',
                        'sort_order' => 2,
                        'is_visible' => true,
                        'display_name' => ['en' => 'Spiritforged', 'fr' => 'Armes spirituelles'],
                    ],
                    'series' => null,
                    'editions' => [
                        [
                            'attributes' => ['name' => 'Spiritforged', 'status' => 'released', 'is_visible' => true],
                            'language' => 'en',
                            'asset' => null,
                            'releases' => [],
                            'sources' => ['name' => 'riftbound-spiritforged-en', 'language' => 'riftbound-spiritforged-en', 'status' => 'riftbound-spiritforged-en'],
                        ],
                        [
                            'attributes' => ['name' => 'Armes spirituelles', 'status' => 'released', 'is_visible' => true],
                            'language' => 'fr',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2026-07-31', 'status' => 'released'],
                                    'market' => 'france',
                                    'sources' => ['market' => 'riftbound-france-launch', 'channel' => 'riftbound-roadmap-fr', 'released_on' => 'riftbound-roadmap-fr', 'status' => 'riftbound-roadmap-fr'],
                                ],
                            ],
                            'sources' => ['name' => 'riftbound-roadmap-fr', 'language' => 'riftbound-roadmap-fr', 'status' => 'riftbound-roadmap-fr'],
                        ],
                        [
                            'attributes' => ['name' => '铸魂淬炼', 'status' => 'released', 'is_visible' => true],
                            'language' => 'zh-Hans',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2025-12-12', 'status' => 'released'],
                                    'market' => 'mainland-china',
                                    'sources' => ['market' => 'riftbound-spiritforged-zh-hans', 'channel' => 'riftbound-spiritforged-zh-hans', 'released_on' => 'riftbound-spiritforged-zh-hans', 'status' => 'riftbound-spiritforged-zh-hans'],
                                ],
                            ],
                            'sources' => ['name' => 'riftbound-spiritforged-zh-hans', 'language' => 'riftbound-spiritforged-zh-hans', 'status' => 'riftbound-spiritforged-zh-hans'],
                        ],
                    ],
                    'sources' => ['name' => 'riftbound-spiritforged-en', 'display_name.en' => 'riftbound-spiritforged-en', 'display_name.fr' => 'riftbound-roadmap-fr'],
                ],
                [
                    'attributes' => [
                        'key' => 'unleashed',
                        'name' => 'Unleashed',
                        'type' => 'main',
                        'sort_order' => 3,
                        'is_visible' => true,
                        'display_name' => ['en' => 'Unleashed', 'fr' => 'Déchaînement'],
                    ],
                    'series' => null,
                    'editions' => [
                        [
                            'attributes' => ['name' => 'Unleashed', 'status' => 'released', 'is_visible' => true],
                            'language' => 'en',
                            'asset' => null,
                            'releases' => [],
                            'sources' => ['name' => 'riftbound-unleashed-en', 'language' => 'riftbound-unleashed-en', 'status' => 'riftbound-unleashed-en'],
                        ],
                        [
                            'attributes' => ['name' => 'Déchaînement', 'status' => 'released', 'is_visible' => true],
                            'language' => 'fr',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2026-07-31', 'status' => 'released'],
                                    'market' => 'france',
                                    'sources' => ['market' => 'riftbound-france-launch', 'channel' => 'riftbound-roadmap-fr', 'released_on' => 'riftbound-roadmap-fr', 'status' => 'riftbound-roadmap-fr'],
                                ],
                            ],
                            'sources' => ['name' => 'riftbound-roadmap-fr', 'language' => 'riftbound-roadmap-fr', 'status' => 'riftbound-roadmap-fr'],
                        ],
                        [
                            'attributes' => ['name' => '破限', 'status' => 'released', 'is_visible' => true],
                            'language' => 'zh-Hans',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2026-04-10', 'status' => 'released'],
                                    'market' => 'mainland-china',
                                    'sources' => ['market' => 'riftbound-unleashed-en', 'channel' => 'riftbound-unleashed-en', 'released_on' => 'riftbound-unleashed-en', 'status' => 'riftbound-unleashed-en'],
                                ],
                            ],
                            'sources' => ['name' => 'riftbound-unleashed-zh-hans', 'language' => 'riftbound-unleashed-zh-hans', 'status' => 'riftbound-unleashed-zh-hans'],
                        ],
                    ],
                    'sources' => ['name' => 'riftbound-unleashed-en', 'display_name.en' => 'riftbound-unleashed-en', 'display_name.fr' => 'riftbound-roadmap-fr'],
                ],
                [
                    'attributes' => [
                        'key' => 'vendetta',
                        'name' => 'Vendetta',
                        'type' => 'main',
                        'sort_order' => 4,
                        'is_visible' => true,
                        'display_name' => ['en' => 'Vendetta', 'fr' => 'Vendetta'],
                    ],
                    'series' => null,
                    'editions' => [
                        [
                            'attributes' => ['name' => 'Vendetta', 'status' => 'released', 'is_visible' => true],
                            'language' => 'en',
                            'asset' => null,
                            'releases' => [],
                            'sources' => ['name' => 'riftbound-vendetta-en', 'language' => 'riftbound-vendetta-en', 'status' => 'riftbound-vendetta-en'],
                        ],
                        [
                            'attributes' => ['name' => 'Vendetta', 'status' => 'released', 'is_visible' => true],
                            'language' => 'fr',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2026-10-23', 'status' => 'released'],
                                    'market' => 'france',
                                    'sources' => ['market' => 'riftbound-france-launch', 'channel' => 'riftbound-roadmap-fr', 'released_on' => 'riftbound-roadmap-fr', 'status' => 'riftbound-roadmap-fr'],
                                ],
                            ],
                            'sources' => ['name' => 'riftbound-roadmap-fr', 'language' => 'riftbound-roadmap-fr', 'status' => 'riftbound-roadmap-fr'],
                        ],
                        [
                            'attributes' => ['name' => '化神争锋', 'status' => 'released', 'is_visible' => true],
                            'language' => 'zh-Hans',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2026-07-31', 'status' => 'released'],
                                    'market' => 'mainland-china',
                                    'sources' => ['market' => 'riftbound-vendetta-zh-hans', 'channel' => 'riftbound-vendetta-zh-hans', 'released_on' => 'riftbound-vendetta-zh-hans', 'status' => 'riftbound-vendetta-zh-hans'],
                                ],
                            ],
                            'sources' => ['name' => 'riftbound-vendetta-zh-hans', 'language' => 'riftbound-vendetta-zh-hans', 'status' => 'riftbound-vendetta-zh-hans'],
                        ],
                    ],
                    'sources' => ['name' => 'riftbound-vendetta-en', 'display_name.en' => 'riftbound-vendetta-en', 'display_name.fr' => 'riftbound-roadmap-fr'],
                ],
                [
                    'attributes' => [
                        'key' => 'radiance',
                        'name' => 'Radiance',
                        'type' => 'main',
                        'sort_order' => 5,
                        'is_visible' => true,
                        'display_name' => ['en' => 'Radiance', 'fr' => 'Radiance'],
                    ],
                    'series' => null,
                    'editions' => [
                        [
                            'attributes' => ['name' => 'Radiance', 'status' => 'released', 'is_visible' => true],
                            'language' => 'en',
                            'asset' => null,
                            'releases' => [],
                            'sources' => ['name' => 'riftbound-radiance-en', 'language' => 'riftbound-radiance-en', 'status' => 'riftbound-radiance-en'],
                        ],
                        [
                            'attributes' => ['name' => 'Radiance', 'status' => 'released', 'is_visible' => true],
                            'language' => 'fr',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2026-10-23', 'status' => 'released'],
                                    'market' => 'france',
                                    'sources' => ['market' => 'riftbound-france-launch', 'channel' => 'riftbound-roadmap-fr', 'released_on' => 'riftbound-roadmap-fr', 'status' => 'riftbound-roadmap-fr'],
                                ],
                            ],
                            'sources' => ['name' => 'riftbound-roadmap-fr', 'language' => 'riftbound-roadmap-fr', 'status' => 'riftbound-roadmap-fr'],
                        ],
                        [
                            'attributes' => ['name' => '辉耀群星', 'status' => 'released', 'is_visible' => true],
                            'language' => 'zh-Hans',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2026-10-24', 'status' => 'released'],
                                    'market' => 'mainland-china',
                                    'sources' => ['market' => 'riftbound-radiance-zh-hans', 'channel' => 'riftbound-radiance-zh-hans', 'released_on' => 'riftbound-radiance-zh-hans', 'status' => 'riftbound-radiance-zh-hans'],
                                ],
                            ],
                            'sources' => ['name' => 'riftbound-radiance-zh-hans', 'language' => 'riftbound-radiance-zh-hans', 'status' => 'riftbound-radiance-zh-hans'],
                        ],
                    ],
                    'sources' => ['name' => 'riftbound-radiance-en', 'display_name.en' => 'riftbound-radiance-en', 'display_name.fr' => 'riftbound-roadmap-fr'],
                ],
            ],
        ],
    ],
];
