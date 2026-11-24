<?php

return [
    'game' => ['slug' => 'flesh-and-blood', 'name' => 'Flesh and Blood', 'is_visible' => true],
    'sources' => ['name' => 'fab-wtr'],
    'catalogs' => [
        [
            'attributes' => ['key' => 'international', 'name' => 'International', 'sort_order' => 0, 'is_visible' => true],
            'default_language' => 'en',
            'series' => [],
            'expansions' => [
                [
                    'attributes' => ['key' => 'welcome-to-rathe', 'name' => 'Welcome to Rathe', 'type' => 'main', 'sort_order' => 0, 'is_visible' => true],
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
                                    'sources' => ['market' => 'fab-wtr', 'channel' => 'fab-wtr-date', 'released_on' => 'fab-wtr-date', 'status' => 'fab-wtr-date'],
                                ],
                            ],
                            'sources' => ['name' => 'fab-wtr', 'language' => 'fab-wtr-print', 'status' => 'fab-wtr-date', 'official_code' => 'fab-wtr'],
                        ],
                    ],
                    'sources' => ['name' => 'fab-wtr'],
                ],
                [
                    'attributes' => ['key' => 'history-pack-1', 'name' => 'History Pack 1', 'type' => 'reprint', 'sort_order' => 1, 'is_visible' => true],
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
                    ],
                    'sources' => ['name' => 'fab-history', 'type' => 'fab-history-live'],
                ],
            ],
        ],
    ],
];
