<?php

return [
    'game' => ['slug' => 'riftbound', 'name' => 'Riftbound', 'is_visible' => true],
    'sources' => ['name' => 'riftbound-sets'],
    'catalogs' => [
        [
            'attributes' => ['key' => 'international', 'name' => 'International', 'sort_order' => 0, 'is_visible' => true],
            'default_language' => 'en',
            'series' => [],
            'expansions' => [
                [
                    'attributes' => ['key' => 'origins', 'name' => 'Origins', 'type' => 'main', 'sort_order' => 0, 'is_visible' => true],
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
                    ],
                    'sources' => ['name' => 'riftbound-sets'],
                ],
                [
                    'attributes' => ['key' => 'proving-grounds', 'name' => 'Proving Grounds', 'type' => 'deck', 'sort_order' => 1, 'is_visible' => true],
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
                    ],
                    'sources' => ['name' => 'riftbound-sets', 'type' => 'riftbound-sets'],
                ],
            ],
        ],
    ],
];
