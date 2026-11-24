<?php

return [
    'game' => ['slug' => 'altered', 'name' => 'Altered', 'is_visible' => true],
    'sources' => ['name' => 'altered-physical'],
    'catalogs' => [
        [
            'attributes' => ['key' => 'international', 'name' => 'International', 'sort_order' => 0, 'is_visible' => true],
            'default_language' => 'en',
            'series' => [],
            'expansions' => [
                [
                    'attributes' => ['key' => 'beyond-the-gates', 'name' => 'Beyond the Gates', 'type' => 'main', 'sort_order' => 0, 'is_visible' => true],
                    'series' => null,
                    'editions' => [
                        [
                            'attributes' => ['name' => 'Beyond the Gates', 'status' => 'released', 'is_visible' => true],
                            'language' => 'en',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2024-09-13', 'status' => 'released'],
                                    'market' => 'worldwide',
                                    'sources' => [
                                        'market' => 'altered-physical',
                                        'channel' => 'altered-physical',
                                        'released_on' => 'altered-physical',
                                        'status' => 'altered-physical',
                                    ],
                                ],
                            ],
                            'sources' => ['name' => 'altered-physical', 'language' => 'altered-distributor', 'status' => 'altered-physical'],
                        ],
                    ],
                    'sources' => ['name' => 'altered-physical'],
                ],
            ],
        ],
    ],
];
