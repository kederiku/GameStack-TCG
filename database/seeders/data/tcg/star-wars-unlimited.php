<?php

return [
    'game' => ['slug' => 'star-wars-unlimited', 'name' => 'Star Wars: Unlimited', 'is_visible' => true],
    'sources' => ['name' => 'swu-global-release'],
    'catalogs' => [
        [
            'attributes' => ['key' => 'international', 'name' => 'International', 'sort_order' => 0, 'is_visible' => true],
            'default_language' => 'fr',
            'series' => [],
            'expansions' => [
                [
                    'attributes' => [
                        'key' => 'spark-of-rebellion',
                        'name' => 'Étincelle de Rébellion',
                        'type' => 'main',
                        'sort_order' => 0,
                        'is_visible' => true,
                        'display_name' => ['fr' => 'Étincelle de Rébellion', 'en' => 'Spark of Rebellion'],
                    ],
                    'series' => null,
                    'editions' => [
                        [
                            'attributes' => ['name' => 'Étincelle de Rébellion', 'status' => 'released', 'is_visible' => true],
                            'language' => 'fr',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2024-03-08', 'status' => 'released'],
                                    'market' => 'worldwide',
                                    'sources' => ['market' => 'swu-product', 'channel' => 'swu-product', 'released_on' => 'swu-product', 'status' => 'swu-product'],
                                ],
                            ],
                            'sources' => ['name' => 'swu-global-release', 'language' => 'swu-global-release', 'status' => 'swu-global-release'],
                        ],
                        [
                            'attributes' => ['name' => 'Spark of Rebellion', 'status' => 'released', 'is_visible' => true],
                            'language' => 'en',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2024-03-08', 'status' => 'released'],
                                    'market' => 'worldwide',
                                    'sources' => ['market' => 'swu-product', 'channel' => 'swu-product', 'released_on' => 'swu-product', 'status' => 'swu-product'],
                                ],
                            ],
                            'sources' => ['name' => 'swu-global-release', 'language' => 'swu-global-release', 'status' => 'swu-global-release'],
                        ],
                    ],
                    'sources' => ['name' => 'swu-global-release', 'display_name.fr' => 'swu-global-release', 'display_name.en' => 'swu-global-release'],
                ],
            ],
        ],
    ],
];
