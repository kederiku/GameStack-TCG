<?php

return [
    'game' => [
        'slug' => 'magic',
        'name' => 'Magic: The Gathering',
        'is_visible' => true,
        'display_name' => ['fr' => 'Magic: The Gathering', 'en' => 'Magic: The Gathering'],
    ],
    'sources' => ['name' => 'M2', 'display_name.fr' => 'M2', 'display_name.en' => 'M2'],
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
            'series' => [
                [
                    'attributes' => [
                        'key' => 'khans-of-tarkir',
                        'name' => 'Khans of Tarkir',
                        'type' => 'block',
                        'sort_order' => 0,
                        'is_visible' => true,
                        'display_name' => ['en' => 'Khans of Tarkir', 'fr' => 'Les Khans de Tarkir'],
                    ],
                    'sources' => ['name' => 'M1', 'type' => 'M1', 'display_name.en' => 'M1', 'display_name.fr' => 'magic-tarkir-fr-names'],
                ],
            ],
            'expansions' => [
                [
                    'attributes' => [
                        'key' => 'khans-of-tarkir',
                        'name' => 'Khans of Tarkir',
                        'type' => 'main',
                        'sort_order' => 0,
                        'is_visible' => true,
                        'display_name' => ['en' => 'Khans of Tarkir', 'fr' => 'Les Khans de Tarkir'],
                    ],
                    'series' => 'khans-of-tarkir',
                    'editions' => [
                        [
                            'attributes' => ['name' => 'Khans of Tarkir', 'status' => 'released', 'is_visible' => true],
                            'language' => 'en',
                            'asset' => null,
                            'releases' => [],
                            'sources' => ['name' => 'M1', 'language' => 'magic-ktk-cards', 'status' => 'magic-tarkir-releases'],
                        ],
                        [
                            'attributes' => ['name' => 'Les Khans de Tarkir', 'status' => 'released', 'is_visible' => true],
                            'language' => 'fr',
                            'asset' => null,
                            'releases' => [],
                            'sources' => ['name' => 'magic-tarkir-fr-names', 'language' => 'magic-ktk-cig-en-fr-cards', 'status' => 'magic-tarkir-releases'],
                        ],
                    ],
                    'sources' => ['name' => 'M1', 'series' => 'M1', 'display_name.en' => 'M1', 'display_name.fr' => 'magic-tarkir-fr-names'],
                ],
                [
                    'attributes' => [
                        'key' => 'fate-reforged',
                        'name' => 'Fate Reforged',
                        'type' => 'main',
                        'sort_order' => 1,
                        'is_visible' => true,
                        'display_name' => ['en' => 'Fate Reforged', 'fr' => 'Destin reforgé'],
                    ],
                    'series' => 'khans-of-tarkir',
                    'editions' => [
                        [
                            'attributes' => ['name' => 'Fate Reforged', 'status' => 'released', 'is_visible' => true],
                            'language' => 'en',
                            'asset' => null,
                            'releases' => [],
                            'sources' => ['name' => 'M1', 'language' => 'magic-frf-cards', 'status' => 'magic-tarkir-releases'],
                        ],
                        [
                            'attributes' => ['name' => 'Destin reforgé', 'status' => 'released', 'is_visible' => true],
                            'language' => 'fr',
                            'asset' => null,
                            'releases' => [],
                            'sources' => ['name' => 'magic-tarkir-fr-names', 'language' => 'magic-frf-cig-en-fr-cards', 'status' => 'magic-tarkir-releases'],
                        ],
                    ],
                    'sources' => ['name' => 'M1', 'series' => 'M1', 'display_name.en' => 'M1', 'display_name.fr' => 'magic-tarkir-fr-names'],
                ],
                [
                    'attributes' => [
                        'key' => 'dragons-of-tarkir',
                        'name' => 'Dragons of Tarkir',
                        'type' => 'main',
                        'sort_order' => 2,
                        'is_visible' => true,
                        'display_name' => ['en' => 'Dragons of Tarkir', 'fr' => 'Les Dragons de Tarkir'],
                    ],
                    'series' => 'khans-of-tarkir',
                    'editions' => [
                        [
                            'attributes' => ['name' => 'Dragons of Tarkir', 'status' => 'released', 'is_visible' => true],
                            'language' => 'en',
                            'asset' => null,
                            'releases' => [],
                            'sources' => ['name' => 'M1', 'language' => 'magic-dtk-cards', 'status' => 'magic-tarkir-releases'],
                        ],
                        [
                            'attributes' => ['name' => 'Les Dragons de Tarkir', 'status' => 'released', 'is_visible' => true],
                            'language' => 'fr',
                            'asset' => null,
                            'releases' => [],
                            'sources' => ['name' => 'magic-tarkir-fr-names', 'language' => 'magic-dragonsoftarkir-fr-cards', 'status' => 'magic-tarkir-releases'],
                        ],
                    ],
                    'sources' => ['name' => 'M1', 'series' => 'M1', 'display_name.en' => 'M1', 'display_name.fr' => 'magic-tarkir-fr-names'],
                ],
                [
                    'attributes' => [
                        'key' => 'foundations',
                        'name' => 'Foundations',
                        'type' => 'main',
                        'sort_order' => 3,
                        'is_visible' => true,
                        'display_name' => ['en' => 'Foundations', 'fr' => 'Fondations', 'de' => 'Magic: The Gathering Grundstein', 'it' => 'Fondamenti di Magic: The Gathering', 'ja' => 'マジック：ザ・ギャザリング ファウンデーションズ', 'es' => 'Magic: The Gathering - Cimientos'],
                    ],
                    'series' => null,
                    'editions' => [
                        [
                            'attributes' => ['name' => 'Magic: The Gathering Foundations', 'status' => 'released', 'is_visible' => true, 'official_code' => 'FDN'],
                            'language' => 'en',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2024-11-15', 'status' => 'released'],
                                    'market' => 'worldwide',
                                    'sources' => ['market' => 'magic-fdn-worldwide', 'channel' => 'magic-fdn-worldwide', 'released_on' => 'magic-fdn-worldwide', 'status' => 'magic-fdn-worldwide'],
                                ],
                            ],
                            'sources' => ['name' => 'M2', 'language' => 'magic-foundations-print', 'status' => 'magic-foundations-release', 'official_code' => 'M2'],
                        ],
                        [
                            'attributes' => ['name' => 'Fondations de Magic: The Gathering', 'status' => 'released', 'is_visible' => true, 'official_code' => 'FDN'],
                            'language' => 'fr',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2024-11-15', 'status' => 'released'],
                                    'market' => 'worldwide',
                                    'sources' => ['market' => 'magic-fdn-worldwide', 'channel' => 'magic-fdn-worldwide', 'released_on' => 'magic-fdn-worldwide', 'status' => 'magic-fdn-worldwide'],
                                ],
                            ],
                            'sources' => ['name' => 'magic-fdn-fr', 'language' => 'magic-foundations-print', 'status' => 'magic-foundations-print', 'official_code' => 'magic-fdn-worldwide'],
                        ],
                        [
                            'attributes' => ['name' => 'Magic: The Gathering Grundstein', 'status' => 'released', 'is_visible' => true, 'official_code' => 'FDN'],
                            'language' => 'de',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2024-11-15', 'status' => 'released'],
                                    'market' => 'worldwide',
                                    'sources' => ['market' => 'magic-fdn-worldwide', 'channel' => 'magic-fdn-worldwide', 'released_on' => 'magic-fdn-worldwide', 'status' => 'magic-fdn-worldwide'],
                                ],
                            ],
                            'sources' => ['name' => 'magic-fdn-de', 'language' => 'magic-foundations-print', 'status' => 'magic-foundations-print', 'official_code' => 'magic-fdn-worldwide'],
                        ],
                        [
                            'attributes' => ['name' => 'Fondamenti di Magic: The Gathering', 'status' => 'released', 'is_visible' => true, 'official_code' => 'FDN'],
                            'language' => 'it',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2024-11-15', 'status' => 'released'],
                                    'market' => 'worldwide',
                                    'sources' => ['market' => 'magic-fdn-worldwide', 'channel' => 'magic-fdn-worldwide', 'released_on' => 'magic-fdn-worldwide', 'status' => 'magic-fdn-worldwide'],
                                ],
                            ],
                            'sources' => ['name' => 'magic-fdn-it', 'language' => 'magic-foundations-print', 'status' => 'magic-foundations-print', 'official_code' => 'magic-fdn-worldwide'],
                        ],
                        [
                            'attributes' => ['name' => 'マジック：ザ・ギャザリング ファウンデーションズ', 'status' => 'released', 'is_visible' => true, 'official_code' => 'FDN'],
                            'language' => 'ja',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2024-11-15', 'status' => 'released'],
                                    'market' => 'worldwide',
                                    'sources' => ['market' => 'magic-fdn-worldwide', 'channel' => 'magic-fdn-worldwide', 'released_on' => 'magic-fdn-worldwide', 'status' => 'magic-fdn-worldwide'],
                                ],
                            ],
                            'sources' => ['name' => 'magic-fdn-ja', 'language' => 'magic-foundations-print', 'status' => 'magic-foundations-print', 'official_code' => 'magic-fdn-worldwide'],
                        ],
                        [
                            'attributes' => ['name' => 'Magic: The Gathering - Cimientos', 'status' => 'released', 'is_visible' => true, 'official_code' => 'FDN'],
                            'language' => 'es',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2024-11-15', 'status' => 'released'],
                                    'market' => 'worldwide',
                                    'sources' => ['market' => 'magic-fdn-worldwide', 'channel' => 'magic-fdn-worldwide', 'released_on' => 'magic-fdn-worldwide', 'status' => 'magic-fdn-worldwide'],
                                ],
                            ],
                            'sources' => ['name' => 'magic-fdn-es', 'language' => 'magic-foundations-print', 'status' => 'magic-foundations-print', 'official_code' => 'magic-fdn-worldwide'],
                        ],
                    ],
                    'sources' => ['name' => 'M2', 'display_name.en' => 'M2', 'display_name.fr' => 'magic-fdn-fr', 'display_name.de' => 'magic-fdn-de', 'display_name.it' => 'magic-fdn-it', 'display_name.ja' => 'magic-fdn-ja', 'display_name.es' => 'magic-fdn-es'],
                ],
                [
                    'attributes' => [
                        'key' => 'dominaria-united',
                        'name' => 'Dominaria United',
                        'type' => 'main',
                        'sort_order' => 4,
                        'is_visible' => true,
                        'display_name' => ['en' => 'Dominaria United', 'fr' => 'Dominaria uni', 'de' => 'Dominarias Bund', 'it' => 'Dominaria Unita', 'ja' => '団結のドミナリア', 'zh-Hans' => '多明纳里亚：众志成', 'pt' => 'Dominária Unida', 'es' => 'Dominaria unida'],
                    ],
                    'series' => null,
                    'editions' => [
                        [
                            'attributes' => ['name' => 'Dominaria United', 'status' => 'released', 'is_visible' => true, 'official_code' => 'DMU'],
                            'language' => 'en',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2022-09-09', 'status' => 'released'],
                                    'market' => 'worldwide',
                                    'sources' => ['market' => 'magic-dmu-tabletop', 'channel' => 'magic-dmu-tabletop', 'released_on' => 'magic-dmu-tabletop', 'status' => 'magic-dmu-tabletop'],
                                ],
                            ],
                            'sources' => ['name' => 'magic-dmu-print', 'language' => 'magic-dmu-print', 'status' => 'magic-dmu-print', 'official_code' => 'magic-dmu-release'],
                        ],
                        [
                            'attributes' => ['name' => 'Dominaria uni', 'status' => 'released', 'is_visible' => true, 'official_code' => 'DMU'],
                            'language' => 'fr',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2022-09-09', 'status' => 'released'],
                                    'market' => 'worldwide',
                                    'sources' => ['market' => 'magic-dmu-tabletop', 'channel' => 'magic-dmu-tabletop', 'released_on' => 'magic-dmu-tabletop', 'status' => 'magic-dmu-tabletop'],
                                ],
                            ],
                            'sources' => ['name' => 'magic-dmu-fr', 'language' => 'magic-dmu-print', 'status' => 'magic-dmu-print', 'official_code' => 'magic-dmu-release'],
                        ],
                        [
                            'attributes' => ['name' => 'Dominarias Bund', 'status' => 'released', 'is_visible' => true, 'official_code' => 'DMU'],
                            'language' => 'de',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2022-09-09', 'status' => 'released'],
                                    'market' => 'worldwide',
                                    'sources' => ['market' => 'magic-dmu-tabletop', 'channel' => 'magic-dmu-tabletop', 'released_on' => 'magic-dmu-tabletop', 'status' => 'magic-dmu-tabletop'],
                                ],
                            ],
                            'sources' => ['name' => 'magic-dmu-de', 'language' => 'magic-dmu-print', 'status' => 'magic-dmu-print', 'official_code' => 'magic-dmu-release'],
                        ],
                        [
                            'attributes' => ['name' => 'Dominaria Unita', 'status' => 'released', 'is_visible' => true, 'official_code' => 'DMU'],
                            'language' => 'it',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2022-09-09', 'status' => 'released'],
                                    'market' => 'worldwide',
                                    'sources' => ['market' => 'magic-dmu-tabletop', 'channel' => 'magic-dmu-tabletop', 'released_on' => 'magic-dmu-tabletop', 'status' => 'magic-dmu-tabletop'],
                                ],
                            ],
                            'sources' => ['name' => 'magic-dmu-it', 'language' => 'magic-dmu-print', 'status' => 'magic-dmu-print', 'official_code' => 'magic-dmu-release'],
                        ],
                        [
                            'attributes' => ['name' => '団結のドミナリア', 'status' => 'released', 'is_visible' => true, 'official_code' => 'DMU'],
                            'language' => 'ja',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2022-09-09', 'status' => 'released'],
                                    'market' => 'worldwide',
                                    'sources' => ['market' => 'magic-dmu-tabletop', 'channel' => 'magic-dmu-tabletop', 'released_on' => 'magic-dmu-tabletop', 'status' => 'magic-dmu-tabletop'],
                                ],
                            ],
                            'sources' => ['name' => 'magic-dmu-ja', 'language' => 'magic-dmu-print', 'status' => 'magic-dmu-print', 'official_code' => 'magic-dmu-release'],
                        ],
                        [
                            'attributes' => ['name' => '多明纳里亚：众志成', 'status' => 'released', 'is_visible' => true, 'official_code' => 'DMU'],
                            'language' => 'zh-Hans',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2022-09-09', 'status' => 'released'],
                                    'market' => 'worldwide',
                                    'sources' => ['market' => 'magic-dmu-tabletop', 'channel' => 'magic-dmu-tabletop', 'released_on' => 'magic-dmu-tabletop', 'status' => 'magic-dmu-tabletop'],
                                ],
                            ],
                            'sources' => ['name' => 'magic-dmu-zh-hans', 'language' => 'magic-dmu-print', 'status' => 'magic-dmu-print', 'official_code' => 'magic-dmu-release'],
                        ],
                        [
                            'attributes' => ['name' => 'Dominária Unida', 'status' => 'released', 'is_visible' => true, 'official_code' => 'DMU'],
                            'language' => 'pt',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2022-09-09', 'status' => 'released'],
                                    'market' => 'worldwide',
                                    'sources' => ['market' => 'magic-dmu-tabletop', 'channel' => 'magic-dmu-tabletop', 'released_on' => 'magic-dmu-tabletop', 'status' => 'magic-dmu-tabletop'],
                                ],
                            ],
                            'sources' => ['name' => 'magic-dmu-pt', 'language' => 'magic-dmu-print', 'status' => 'magic-dmu-print', 'official_code' => 'magic-dmu-release'],
                        ],
                        [
                            'attributes' => ['name' => 'Dominaria unida', 'status' => 'released', 'is_visible' => true, 'official_code' => 'DMU'],
                            'language' => 'es',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2022-09-09', 'status' => 'released'],
                                    'market' => 'worldwide',
                                    'sources' => ['market' => 'magic-dmu-tabletop', 'channel' => 'magic-dmu-tabletop', 'released_on' => 'magic-dmu-tabletop', 'status' => 'magic-dmu-tabletop'],
                                ],
                            ],
                            'sources' => ['name' => 'magic-dmu-es', 'language' => 'magic-dmu-print', 'status' => 'magic-dmu-print', 'official_code' => 'magic-dmu-release'],
                        ],
                    ],
                    'sources' => ['name' => 'magic-dmu-print', 'display_name.en' => 'magic-dmu-print', 'display_name.fr' => 'magic-dmu-fr', 'display_name.de' => 'magic-dmu-de', 'display_name.it' => 'magic-dmu-it', 'display_name.ja' => 'magic-dmu-ja', 'display_name.zh-Hans' => 'magic-dmu-zh-hans', 'display_name.pt' => 'magic-dmu-pt', 'display_name.es' => 'magic-dmu-es'],
                ],
                [
                    'attributes' => [
                        'key' => 'the-brothers-war',
                        'name' => 'The Brothers\' War',
                        'type' => 'main',
                        'sort_order' => 5,
                        'is_visible' => true,
                        'display_name' => ['en' => 'The Brothers\' War', 'fr' => 'La Guerre Fratricide', 'de' => 'Krieg der Brüder', 'it' => 'La Guerra dei Fratelli', 'ja' => '兄弟戦争', 'zh-Hans' => '兄弟之战', 'pt' => 'A Guerra dos Irmãos', 'es' => 'La Guerra de los Hermanos'],
                    ],
                    'series' => null,
                    'editions' => [
                        [
                            'attributes' => ['name' => 'The Brothers\' War', 'status' => 'released', 'is_visible' => true, 'official_code' => 'BRO'],
                            'language' => 'en',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2022-11-18', 'status' => 'released'],
                                    'market' => 'worldwide',
                                    'sources' => ['market' => 'magic-bro-release', 'channel' => 'magic-bro-release', 'released_on' => 'magic-bro-release', 'status' => 'magic-bro-release'],
                                ],
                            ],
                            'sources' => ['name' => 'magic-bro-print', 'language' => 'magic-bro-print', 'status' => 'magic-bro-print', 'official_code' => 'magic-bro-release'],
                        ],
                        [
                            'attributes' => ['name' => 'La Guerre Fratricide', 'status' => 'released', 'is_visible' => true, 'official_code' => 'BRO'],
                            'language' => 'fr',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2022-11-18', 'status' => 'released'],
                                    'market' => 'worldwide',
                                    'sources' => ['market' => 'magic-bro-release', 'channel' => 'magic-bro-release', 'released_on' => 'magic-bro-release', 'status' => 'magic-bro-release'],
                                ],
                            ],
                            'sources' => ['name' => 'magic-bro-fr', 'language' => 'magic-bro-print', 'status' => 'magic-bro-print', 'official_code' => 'magic-bro-release'],
                        ],
                        [
                            'attributes' => ['name' => 'Krieg der Brüder', 'status' => 'released', 'is_visible' => true, 'official_code' => 'BRO'],
                            'language' => 'de',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2022-11-18', 'status' => 'released'],
                                    'market' => 'worldwide',
                                    'sources' => ['market' => 'magic-bro-release', 'channel' => 'magic-bro-release', 'released_on' => 'magic-bro-release', 'status' => 'magic-bro-release'],
                                ],
                            ],
                            'sources' => ['name' => 'magic-bro-de', 'language' => 'magic-bro-print', 'status' => 'magic-bro-print', 'official_code' => 'magic-bro-release'],
                        ],
                        [
                            'attributes' => ['name' => 'La Guerra dei Fratelli', 'status' => 'released', 'is_visible' => true, 'official_code' => 'BRO'],
                            'language' => 'it',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2022-11-18', 'status' => 'released'],
                                    'market' => 'worldwide',
                                    'sources' => ['market' => 'magic-bro-release', 'channel' => 'magic-bro-release', 'released_on' => 'magic-bro-release', 'status' => 'magic-bro-release'],
                                ],
                            ],
                            'sources' => ['name' => 'magic-bro-it', 'language' => 'magic-bro-print', 'status' => 'magic-bro-print', 'official_code' => 'magic-bro-release'],
                        ],
                        [
                            'attributes' => ['name' => '兄弟戦争', 'status' => 'released', 'is_visible' => true, 'official_code' => 'BRO'],
                            'language' => 'ja',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2022-11-18', 'status' => 'released'],
                                    'market' => 'worldwide',
                                    'sources' => ['market' => 'magic-bro-release', 'channel' => 'magic-bro-release', 'released_on' => 'magic-bro-release', 'status' => 'magic-bro-release'],
                                ],
                            ],
                            'sources' => ['name' => 'magic-bro-ja', 'language' => 'magic-bro-print', 'status' => 'magic-bro-print', 'official_code' => 'magic-bro-release'],
                        ],
                        [
                            'attributes' => ['name' => '兄弟之战', 'status' => 'released', 'is_visible' => true, 'official_code' => 'BRO'],
                            'language' => 'zh-Hans',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2022-11-18', 'status' => 'released'],
                                    'market' => 'worldwide',
                                    'sources' => ['market' => 'magic-bro-release', 'channel' => 'magic-bro-release', 'released_on' => 'magic-bro-release', 'status' => 'magic-bro-release'],
                                ],
                            ],
                            'sources' => ['name' => 'magic-bro-zh-hans', 'language' => 'magic-bro-print', 'status' => 'magic-bro-print', 'official_code' => 'magic-bro-release'],
                        ],
                        [
                            'attributes' => ['name' => 'A Guerra dos Irmãos', 'status' => 'released', 'is_visible' => true, 'official_code' => 'BRO'],
                            'language' => 'pt',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2022-11-18', 'status' => 'released'],
                                    'market' => 'worldwide',
                                    'sources' => ['market' => 'magic-bro-release', 'channel' => 'magic-bro-release', 'released_on' => 'magic-bro-release', 'status' => 'magic-bro-release'],
                                ],
                            ],
                            'sources' => ['name' => 'magic-bro-pt', 'language' => 'magic-bro-print', 'status' => 'magic-bro-print', 'official_code' => 'magic-bro-release'],
                        ],
                        [
                            'attributes' => ['name' => 'La Guerra de los Hermanos', 'status' => 'released', 'is_visible' => true, 'official_code' => 'BRO'],
                            'language' => 'es',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2022-11-18', 'status' => 'released'],
                                    'market' => 'worldwide',
                                    'sources' => ['market' => 'magic-bro-release', 'channel' => 'magic-bro-release', 'released_on' => 'magic-bro-release', 'status' => 'magic-bro-release'],
                                ],
                            ],
                            'sources' => ['name' => 'magic-bro-es', 'language' => 'magic-bro-print', 'status' => 'magic-bro-print', 'official_code' => 'magic-bro-release'],
                        ],
                    ],
                    'sources' => ['name' => 'magic-bro-print', 'display_name.en' => 'magic-bro-print', 'display_name.fr' => 'magic-bro-fr', 'display_name.de' => 'magic-bro-de', 'display_name.it' => 'magic-bro-it', 'display_name.ja' => 'magic-bro-ja', 'display_name.zh-Hans' => 'magic-bro-zh-hans', 'display_name.pt' => 'magic-bro-pt', 'display_name.es' => 'magic-bro-es'],
                ],
                [
                    'attributes' => [
                        'key' => 'phyrexia-all-will-be-one',
                        'name' => 'Phyrexia: All Will Be One',
                        'type' => 'main',
                        'sort_order' => 6,
                        'is_visible' => true,
                        'display_name' => ['en' => 'Phyrexia: All Will Be One', 'fr' => 'Tous Phyrexians', 'de' => 'Phyrexia: Alles wird eins', 'it' => 'Phyrexia: Tutto Diverrà Uno', 'ja' => 'ファイレクシア：完全なる統一', 'zh-Hans' => '非瑞克西亚：万界归一', 'pt' => 'Phyrexia: Tudo Será Um', 'es' => 'Pirexia: Todos serán uno'],
                    ],
                    'series' => null,
                    'editions' => [
                        [
                            'attributes' => ['name' => 'Phyrexia: All Will Be One', 'status' => 'released', 'is_visible' => true, 'official_code' => 'ONE'],
                            'language' => 'en',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2023-02-10', 'status' => 'released'],
                                    'market' => 'worldwide',
                                    'sources' => ['market' => 'magic-one-release', 'channel' => 'magic-one-release', 'released_on' => 'magic-one-release', 'status' => 'magic-one-release'],
                                ],
                            ],
                            'sources' => ['name' => 'magic-one-print', 'language' => 'magic-one-print', 'status' => 'magic-one-print', 'official_code' => 'magic-one-release'],
                        ],
                        [
                            'attributes' => ['name' => 'Tous Phyrexians', 'status' => 'released', 'is_visible' => true, 'official_code' => 'ONE'],
                            'language' => 'fr',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2023-02-10', 'status' => 'released'],
                                    'market' => 'worldwide',
                                    'sources' => ['market' => 'magic-one-release', 'channel' => 'magic-one-release', 'released_on' => 'magic-one-release', 'status' => 'magic-one-release'],
                                ],
                            ],
                            'sources' => ['name' => 'magic-one-fr', 'language' => 'magic-one-print', 'status' => 'magic-one-print', 'official_code' => 'magic-one-release'],
                        ],
                        [
                            'attributes' => ['name' => 'Phyrexia: Alles wird eins', 'status' => 'released', 'is_visible' => true, 'official_code' => 'ONE'],
                            'language' => 'de',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2023-02-10', 'status' => 'released'],
                                    'market' => 'worldwide',
                                    'sources' => ['market' => 'magic-one-release', 'channel' => 'magic-one-release', 'released_on' => 'magic-one-release', 'status' => 'magic-one-release'],
                                ],
                            ],
                            'sources' => ['name' => 'magic-one-de', 'language' => 'magic-one-print', 'status' => 'magic-one-print', 'official_code' => 'magic-one-release'],
                        ],
                        [
                            'attributes' => ['name' => 'Phyrexia: Tutto Diverrà Uno', 'status' => 'released', 'is_visible' => true, 'official_code' => 'ONE'],
                            'language' => 'it',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2023-02-10', 'status' => 'released'],
                                    'market' => 'worldwide',
                                    'sources' => ['market' => 'magic-one-release', 'channel' => 'magic-one-release', 'released_on' => 'magic-one-release', 'status' => 'magic-one-release'],
                                ],
                            ],
                            'sources' => ['name' => 'magic-one-it', 'language' => 'magic-one-print', 'status' => 'magic-one-print', 'official_code' => 'magic-one-release'],
                        ],
                        [
                            'attributes' => ['name' => 'ファイレクシア：完全なる統一', 'status' => 'released', 'is_visible' => true, 'official_code' => 'ONE'],
                            'language' => 'ja',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2023-02-10', 'status' => 'released'],
                                    'market' => 'worldwide',
                                    'sources' => ['market' => 'magic-one-release', 'channel' => 'magic-one-release', 'released_on' => 'magic-one-release', 'status' => 'magic-one-release'],
                                ],
                            ],
                            'sources' => ['name' => 'magic-one-ja', 'language' => 'magic-one-print', 'status' => 'magic-one-print', 'official_code' => 'magic-one-release'],
                        ],
                        [
                            'attributes' => ['name' => '非瑞克西亚：万界归一', 'status' => 'released', 'is_visible' => true, 'official_code' => 'ONE'],
                            'language' => 'zh-Hans',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2023-02-10', 'status' => 'released'],
                                    'market' => 'worldwide',
                                    'sources' => ['market' => 'magic-one-release', 'channel' => 'magic-one-release', 'released_on' => 'magic-one-release', 'status' => 'magic-one-release'],
                                ],
                            ],
                            'sources' => ['name' => 'magic-one-zh-hans', 'language' => 'magic-one-print', 'status' => 'magic-one-print', 'official_code' => 'magic-one-release'],
                        ],
                        [
                            'attributes' => ['name' => 'Phyrexia: Tudo Será Um', 'status' => 'released', 'is_visible' => true, 'official_code' => 'ONE'],
                            'language' => 'pt',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2023-02-10', 'status' => 'released'],
                                    'market' => 'worldwide',
                                    'sources' => ['market' => 'magic-one-release', 'channel' => 'magic-one-release', 'released_on' => 'magic-one-release', 'status' => 'magic-one-release'],
                                ],
                            ],
                            'sources' => ['name' => 'magic-one-pt', 'language' => 'magic-one-print', 'status' => 'magic-one-print', 'official_code' => 'magic-one-release'],
                        ],
                        [
                            'attributes' => ['name' => 'Pirexia: Todos serán uno', 'status' => 'released', 'is_visible' => true, 'official_code' => 'ONE'],
                            'language' => 'es',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2023-02-10', 'status' => 'released'],
                                    'market' => 'worldwide',
                                    'sources' => ['market' => 'magic-one-release', 'channel' => 'magic-one-release', 'released_on' => 'magic-one-release', 'status' => 'magic-one-release'],
                                ],
                            ],
                            'sources' => ['name' => 'magic-one-es', 'language' => 'magic-one-print', 'status' => 'magic-one-print', 'official_code' => 'magic-one-release'],
                        ],
                    ],
                    'sources' => ['name' => 'magic-one-print', 'display_name.en' => 'magic-one-print', 'display_name.fr' => 'magic-one-fr', 'display_name.de' => 'magic-one-de', 'display_name.it' => 'magic-one-it', 'display_name.ja' => 'magic-one-ja', 'display_name.zh-Hans' => 'magic-one-zh-hans', 'display_name.pt' => 'magic-one-pt', 'display_name.es' => 'magic-one-es'],
                ],
                [
                    'attributes' => [
                        'key' => 'march-of-the-machine',
                        'name' => 'March of the Machine',
                        'type' => 'main',
                        'sort_order' => 7,
                        'is_visible' => true,
                        'display_name' => ['en' => 'March of the Machine', 'fr' => 'L\'invasion des machines', 'de' => 'Marsch der Maschine', 'it' => 'L’Avanzata delle Macchine', 'ja' => '機械兵団の進軍', 'zh-Hans' => '邪军压境', 'pt' => 'Marcha das Máquinas', 'es' => 'Marcha de las máquinas'],
                    ],
                    'series' => null,
                    'editions' => [
                        [
                            'attributes' => ['name' => 'March of the Machine', 'status' => 'released', 'is_visible' => true, 'official_code' => 'MOM'],
                            'language' => 'en',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2023-04-21', 'status' => 'released'],
                                    'market' => 'worldwide',
                                    'sources' => ['market' => 'magic-mom-release', 'channel' => 'magic-mom-release', 'released_on' => 'magic-mom-release', 'status' => 'magic-mom-release'],
                                ],
                            ],
                            'sources' => ['name' => 'magic-mom-print', 'language' => 'magic-mom-print', 'status' => 'magic-mom-print', 'official_code' => 'magic-mom-release'],
                        ],
                        [
                            'attributes' => ['name' => 'L\'invasion des machines', 'status' => 'released', 'is_visible' => true, 'official_code' => 'MOM'],
                            'language' => 'fr',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2023-04-21', 'status' => 'released'],
                                    'market' => 'worldwide',
                                    'sources' => ['market' => 'magic-mom-release', 'channel' => 'magic-mom-release', 'released_on' => 'magic-mom-release', 'status' => 'magic-mom-release'],
                                ],
                            ],
                            'sources' => ['name' => 'magic-mom-fr', 'language' => 'magic-mom-print', 'status' => 'magic-mom-print', 'official_code' => 'magic-mom-release'],
                        ],
                        [
                            'attributes' => ['name' => 'Marsch der Maschine', 'status' => 'released', 'is_visible' => true, 'official_code' => 'MOM'],
                            'language' => 'de',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2023-04-21', 'status' => 'released'],
                                    'market' => 'worldwide',
                                    'sources' => ['market' => 'magic-mom-release', 'channel' => 'magic-mom-release', 'released_on' => 'magic-mom-release', 'status' => 'magic-mom-release'],
                                ],
                            ],
                            'sources' => ['name' => 'magic-mom-de', 'language' => 'magic-mom-print', 'status' => 'magic-mom-print', 'official_code' => 'magic-mom-release'],
                        ],
                        [
                            'attributes' => ['name' => 'L’Avanzata delle Macchine', 'status' => 'released', 'is_visible' => true, 'official_code' => 'MOM'],
                            'language' => 'it',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2023-04-21', 'status' => 'released'],
                                    'market' => 'worldwide',
                                    'sources' => ['market' => 'magic-mom-release', 'channel' => 'magic-mom-release', 'released_on' => 'magic-mom-release', 'status' => 'magic-mom-release'],
                                ],
                            ],
                            'sources' => ['name' => 'magic-mom-it', 'language' => 'magic-mom-print', 'status' => 'magic-mom-print', 'official_code' => 'magic-mom-release'],
                        ],
                        [
                            'attributes' => ['name' => '機械兵団の進軍', 'status' => 'released', 'is_visible' => true, 'official_code' => 'MOM'],
                            'language' => 'ja',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2023-04-21', 'status' => 'released'],
                                    'market' => 'worldwide',
                                    'sources' => ['market' => 'magic-mom-release', 'channel' => 'magic-mom-release', 'released_on' => 'magic-mom-release', 'status' => 'magic-mom-release'],
                                ],
                            ],
                            'sources' => ['name' => 'magic-mom-ja', 'language' => 'magic-mom-print', 'status' => 'magic-mom-print', 'official_code' => 'magic-mom-release'],
                        ],
                        [
                            'attributes' => ['name' => '邪军压境', 'status' => 'released', 'is_visible' => true, 'official_code' => 'MOM'],
                            'language' => 'zh-Hans',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2023-04-21', 'status' => 'released'],
                                    'market' => 'worldwide',
                                    'sources' => ['market' => 'magic-mom-release', 'channel' => 'magic-mom-release', 'released_on' => 'magic-mom-release', 'status' => 'magic-mom-release'],
                                ],
                            ],
                            'sources' => ['name' => 'magic-mom-zh-hans', 'language' => 'magic-mom-print', 'status' => 'magic-mom-print', 'official_code' => 'magic-mom-release'],
                        ],
                        [
                            'attributes' => ['name' => 'Marcha das Máquinas', 'status' => 'released', 'is_visible' => true, 'official_code' => 'MOM'],
                            'language' => 'pt',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2023-04-21', 'status' => 'released'],
                                    'market' => 'worldwide',
                                    'sources' => ['market' => 'magic-mom-release', 'channel' => 'magic-mom-release', 'released_on' => 'magic-mom-release', 'status' => 'magic-mom-release'],
                                ],
                            ],
                            'sources' => ['name' => 'magic-mom-pt', 'language' => 'magic-mom-print', 'status' => 'magic-mom-print', 'official_code' => 'magic-mom-release'],
                        ],
                        [
                            'attributes' => ['name' => 'Marcha de las máquinas', 'status' => 'released', 'is_visible' => true, 'official_code' => 'MOM'],
                            'language' => 'es',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2023-04-21', 'status' => 'released'],
                                    'market' => 'worldwide',
                                    'sources' => ['market' => 'magic-mom-release', 'channel' => 'magic-mom-release', 'released_on' => 'magic-mom-release', 'status' => 'magic-mom-release'],
                                ],
                            ],
                            'sources' => ['name' => 'magic-mom-es', 'language' => 'magic-mom-print', 'status' => 'magic-mom-print', 'official_code' => 'magic-mom-release'],
                        ],
                    ],
                    'sources' => ['name' => 'magic-mom-print', 'display_name.en' => 'magic-mom-print', 'display_name.fr' => 'magic-mom-fr', 'display_name.de' => 'magic-mom-de', 'display_name.it' => 'magic-mom-it', 'display_name.ja' => 'magic-mom-ja', 'display_name.zh-Hans' => 'magic-mom-zh-hans', 'display_name.pt' => 'magic-mom-pt', 'display_name.es' => 'magic-mom-es'],
                ],
                [
                    'attributes' => [
                        'key' => 'wilds-of-eldraine',
                        'name' => 'Wilds of Eldraine',
                        'type' => 'main',
                        'sort_order' => 8,
                        'is_visible' => true,
                        'display_name' => ['en' => 'Wilds of Eldraine', 'fr' => 'Les friches d\'Eldraine', 'de' => 'Wildnis von Eldraine', 'it' => 'Terre Selvagge di Eldraine', 'ja' => 'エルドレインの森', 'zh-Hans' => '艾卓仙踪', 'pt' => 'Terras Selvagens de Eldraine', 'es' => 'Las tierras salvajes de Eldraine'],
                    ],
                    'series' => null,
                    'editions' => [
                        [
                            'attributes' => ['name' => 'Wilds of Eldraine', 'status' => 'released', 'is_visible' => true, 'official_code' => 'WOE'],
                            'language' => 'en',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2023-09-08', 'status' => 'released'],
                                    'market' => 'worldwide',
                                    'sources' => ['market' => 'magic-woe-release', 'channel' => 'magic-woe-release', 'released_on' => 'magic-woe-release', 'status' => 'magic-woe-release'],
                                ],
                            ],
                            'sources' => ['name' => 'magic-woe-print', 'language' => 'magic-woe-print', 'status' => 'magic-woe-print', 'official_code' => 'magic-woe-release'],
                        ],
                        [
                            'attributes' => ['name' => 'Les friches d\'Eldraine', 'status' => 'released', 'is_visible' => true, 'official_code' => 'WOE'],
                            'language' => 'fr',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2023-09-08', 'status' => 'released'],
                                    'market' => 'worldwide',
                                    'sources' => ['market' => 'magic-woe-release', 'channel' => 'magic-woe-release', 'released_on' => 'magic-woe-release', 'status' => 'magic-woe-release'],
                                ],
                            ],
                            'sources' => ['name' => 'magic-woe-fr', 'language' => 'magic-woe-print', 'status' => 'magic-woe-print', 'official_code' => 'magic-woe-release'],
                        ],
                        [
                            'attributes' => ['name' => 'Wildnis von Eldraine', 'status' => 'released', 'is_visible' => true, 'official_code' => 'WOE'],
                            'language' => 'de',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2023-09-08', 'status' => 'released'],
                                    'market' => 'worldwide',
                                    'sources' => ['market' => 'magic-woe-release', 'channel' => 'magic-woe-release', 'released_on' => 'magic-woe-release', 'status' => 'magic-woe-release'],
                                ],
                            ],
                            'sources' => ['name' => 'magic-woe-de', 'language' => 'magic-woe-print', 'status' => 'magic-woe-print', 'official_code' => 'magic-woe-release'],
                        ],
                        [
                            'attributes' => ['name' => 'Terre Selvagge di Eldraine', 'status' => 'released', 'is_visible' => true, 'official_code' => 'WOE'],
                            'language' => 'it',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2023-09-08', 'status' => 'released'],
                                    'market' => 'worldwide',
                                    'sources' => ['market' => 'magic-woe-release', 'channel' => 'magic-woe-release', 'released_on' => 'magic-woe-release', 'status' => 'magic-woe-release'],
                                ],
                            ],
                            'sources' => ['name' => 'magic-woe-it', 'language' => 'magic-woe-print', 'status' => 'magic-woe-print', 'official_code' => 'magic-woe-release'],
                        ],
                        [
                            'attributes' => ['name' => 'エルドレインの森', 'status' => 'released', 'is_visible' => true, 'official_code' => 'WOE'],
                            'language' => 'ja',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2023-09-08', 'status' => 'released'],
                                    'market' => 'worldwide',
                                    'sources' => ['market' => 'magic-woe-release', 'channel' => 'magic-woe-release', 'released_on' => 'magic-woe-release', 'status' => 'magic-woe-release'],
                                ],
                            ],
                            'sources' => ['name' => 'magic-woe-ja', 'language' => 'magic-woe-print', 'status' => 'magic-woe-print', 'official_code' => 'magic-woe-release'],
                        ],
                        [
                            'attributes' => ['name' => '艾卓仙踪', 'status' => 'released', 'is_visible' => true, 'official_code' => 'WOE'],
                            'language' => 'zh-Hans',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2023-09-08', 'status' => 'released'],
                                    'market' => 'worldwide',
                                    'sources' => ['market' => 'magic-woe-release', 'channel' => 'magic-woe-release', 'released_on' => 'magic-woe-release', 'status' => 'magic-woe-release'],
                                ],
                            ],
                            'sources' => ['name' => 'magic-woe-zh-hans', 'language' => 'magic-woe-print', 'status' => 'magic-woe-print', 'official_code' => 'magic-woe-release'],
                        ],
                        [
                            'attributes' => ['name' => 'Terras Selvagens de Eldraine', 'status' => 'released', 'is_visible' => true, 'official_code' => 'WOE'],
                            'language' => 'pt',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2023-09-08', 'status' => 'released'],
                                    'market' => 'worldwide',
                                    'sources' => ['market' => 'magic-woe-release', 'channel' => 'magic-woe-release', 'released_on' => 'magic-woe-release', 'status' => 'magic-woe-release'],
                                ],
                            ],
                            'sources' => ['name' => 'magic-woe-pt', 'language' => 'magic-woe-print', 'status' => 'magic-woe-print', 'official_code' => 'magic-woe-release'],
                        ],
                        [
                            'attributes' => ['name' => 'Las tierras salvajes de Eldraine', 'status' => 'released', 'is_visible' => true, 'official_code' => 'WOE'],
                            'language' => 'es',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2023-09-08', 'status' => 'released'],
                                    'market' => 'worldwide',
                                    'sources' => ['market' => 'magic-woe-release', 'channel' => 'magic-woe-release', 'released_on' => 'magic-woe-release', 'status' => 'magic-woe-release'],
                                ],
                            ],
                            'sources' => ['name' => 'magic-woe-es', 'language' => 'magic-woe-print', 'status' => 'magic-woe-print', 'official_code' => 'magic-woe-release'],
                        ],
                    ],
                    'sources' => ['name' => 'magic-woe-print', 'display_name.en' => 'magic-woe-print', 'display_name.fr' => 'magic-woe-fr', 'display_name.de' => 'magic-woe-de', 'display_name.it' => 'magic-woe-it', 'display_name.ja' => 'magic-woe-ja', 'display_name.zh-Hans' => 'magic-woe-zh-hans', 'display_name.pt' => 'magic-woe-pt', 'display_name.es' => 'magic-woe-es'],
                ],
                [
                    'attributes' => [
                        'key' => 'the-lost-caverns-of-ixalan',
                        'name' => 'The Lost Caverns of Ixalan',
                        'type' => 'main',
                        'sort_order' => 9,
                        'is_visible' => true,
                        'display_name' => ['en' => 'The Lost Caverns of Ixalan', 'fr' => 'Les cavernes oubliées d\'Ixalan', 'de' => 'Die verlorenen Höhlen von Ixalan', 'it' => 'Le Caverne Perdute di Ixalan', 'ja' => 'イクサラン：失われし洞窟', 'zh-Hans' => '依夏兰迷窟', 'pt' => 'As Cavernas Perdidas de Ixalan', 'es' => 'Las cavernas perdidas de Ixalan'],
                    ],
                    'series' => null,
                    'editions' => [
                        [
                            'attributes' => ['name' => 'The Lost Caverns of Ixalan', 'status' => 'released', 'is_visible' => true, 'official_code' => 'LCI'],
                            'language' => 'en',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2023-11-17', 'status' => 'released'],
                                    'market' => 'worldwide',
                                    'sources' => ['market' => 'magic-lci-release', 'channel' => 'magic-lci-release', 'released_on' => 'magic-lci-release', 'status' => 'magic-lci-release'],
                                ],
                            ],
                            'sources' => ['name' => 'magic-lci-print', 'language' => 'magic-lci-print', 'status' => 'magic-lci-print', 'official_code' => 'magic-lci-release'],
                        ],
                        [
                            'attributes' => ['name' => 'Les cavernes oubliées d\'Ixalan', 'status' => 'released', 'is_visible' => true, 'official_code' => 'LCI'],
                            'language' => 'fr',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2023-11-17', 'status' => 'released'],
                                    'market' => 'worldwide',
                                    'sources' => ['market' => 'magic-lci-release', 'channel' => 'magic-lci-release', 'released_on' => 'magic-lci-release', 'status' => 'magic-lci-release'],
                                ],
                            ],
                            'sources' => ['name' => 'magic-lci-fr', 'language' => 'magic-lci-print', 'status' => 'magic-lci-print', 'official_code' => 'magic-lci-release'],
                        ],
                        [
                            'attributes' => ['name' => 'Die verlorenen Höhlen von Ixalan', 'status' => 'released', 'is_visible' => true, 'official_code' => 'LCI'],
                            'language' => 'de',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2023-11-17', 'status' => 'released'],
                                    'market' => 'worldwide',
                                    'sources' => ['market' => 'magic-lci-release', 'channel' => 'magic-lci-release', 'released_on' => 'magic-lci-release', 'status' => 'magic-lci-release'],
                                ],
                            ],
                            'sources' => ['name' => 'magic-lci-de', 'language' => 'magic-lci-print', 'status' => 'magic-lci-print', 'official_code' => 'magic-lci-release'],
                        ],
                        [
                            'attributes' => ['name' => 'Le Caverne Perdute di Ixalan', 'status' => 'released', 'is_visible' => true, 'official_code' => 'LCI'],
                            'language' => 'it',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2023-11-17', 'status' => 'released'],
                                    'market' => 'worldwide',
                                    'sources' => ['market' => 'magic-lci-release', 'channel' => 'magic-lci-release', 'released_on' => 'magic-lci-release', 'status' => 'magic-lci-release'],
                                ],
                            ],
                            'sources' => ['name' => 'magic-lci-it', 'language' => 'magic-lci-print', 'status' => 'magic-lci-print', 'official_code' => 'magic-lci-release'],
                        ],
                        [
                            'attributes' => ['name' => 'イクサラン：失われし洞窟', 'status' => 'released', 'is_visible' => true, 'official_code' => 'LCI'],
                            'language' => 'ja',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2023-11-17', 'status' => 'released'],
                                    'market' => 'worldwide',
                                    'sources' => ['market' => 'magic-lci-release', 'channel' => 'magic-lci-release', 'released_on' => 'magic-lci-release', 'status' => 'magic-lci-release'],
                                ],
                            ],
                            'sources' => ['name' => 'magic-lci-ja', 'language' => 'magic-lci-print', 'status' => 'magic-lci-print', 'official_code' => 'magic-lci-release'],
                        ],
                        [
                            'attributes' => ['name' => '依夏兰迷窟', 'status' => 'released', 'is_visible' => true, 'official_code' => 'LCI'],
                            'language' => 'zh-Hans',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2023-11-17', 'status' => 'released'],
                                    'market' => 'worldwide',
                                    'sources' => ['market' => 'magic-lci-release', 'channel' => 'magic-lci-release', 'released_on' => 'magic-lci-release', 'status' => 'magic-lci-release'],
                                ],
                            ],
                            'sources' => ['name' => 'magic-lci-zh-hans', 'language' => 'magic-lci-print', 'status' => 'magic-lci-print', 'official_code' => 'magic-lci-release'],
                        ],
                        [
                            'attributes' => ['name' => 'As Cavernas Perdidas de Ixalan', 'status' => 'released', 'is_visible' => true, 'official_code' => 'LCI'],
                            'language' => 'pt',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2023-11-17', 'status' => 'released'],
                                    'market' => 'worldwide',
                                    'sources' => ['market' => 'magic-lci-release', 'channel' => 'magic-lci-release', 'released_on' => 'magic-lci-release', 'status' => 'magic-lci-release'],
                                ],
                            ],
                            'sources' => ['name' => 'magic-lci-pt', 'language' => 'magic-lci-print', 'status' => 'magic-lci-print', 'official_code' => 'magic-lci-release'],
                        ],
                        [
                            'attributes' => ['name' => 'Las cavernas perdidas de Ixalan', 'status' => 'released', 'is_visible' => true, 'official_code' => 'LCI'],
                            'language' => 'es',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2023-11-17', 'status' => 'released'],
                                    'market' => 'worldwide',
                                    'sources' => ['market' => 'magic-lci-release', 'channel' => 'magic-lci-release', 'released_on' => 'magic-lci-release', 'status' => 'magic-lci-release'],
                                ],
                            ],
                            'sources' => ['name' => 'magic-lci-es', 'language' => 'magic-lci-print', 'status' => 'magic-lci-print', 'official_code' => 'magic-lci-release'],
                        ],
                    ],
                    'sources' => ['name' => 'magic-lci-print', 'display_name.en' => 'magic-lci-print', 'display_name.fr' => 'magic-lci-fr', 'display_name.de' => 'magic-lci-de', 'display_name.it' => 'magic-lci-it', 'display_name.ja' => 'magic-lci-ja', 'display_name.zh-Hans' => 'magic-lci-zh-hans', 'display_name.pt' => 'magic-lci-pt', 'display_name.es' => 'magic-lci-es'],
                ],
            ],
        ],
    ],
];
