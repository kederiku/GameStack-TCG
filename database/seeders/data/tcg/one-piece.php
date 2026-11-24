<?php

return [
    'game' => ['slug' => 'one-piece', 'name' => 'ONE PIECE CARD GAME', 'is_visible' => true],
    'sources' => ['name' => 'O2'],
    'catalogs' => [
        [
            'attributes' => ['key' => 'international', 'name' => 'International', 'sort_order' => 0, 'is_visible' => true],
            'default_language' => 'en',
            'series' => [],
            'expansions' => [
                [
                    'attributes' => ['key' => 'romance-dawn', 'name' => 'ROMANCE DAWN', 'type' => 'main', 'sort_order' => 0, 'is_visible' => true],
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
                    'sources' => ['name' => 'O2'],
                ],
            ],
        ],
        [
            'attributes' => [
                'key' => 'japanese',
                'name' => 'Japonais',
                'sort_order' => 1,
                'is_visible' => true,
                'display_name' => ['en' => 'Japanese'],
            ],
            'default_language' => 'ja',
            'series' => [],
            'expansions' => [
                [
                    'attributes' => ['key' => 'romance-dawn', 'name' => 'ROMANCE DAWN', 'type' => 'main', 'sort_order' => 0, 'is_visible' => true],
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
                    'sources' => ['name' => 'O1'],
                ],
            ],
        ],
    ],
];
