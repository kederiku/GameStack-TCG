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
                        'display_name' => ['en' => 'Khans of Tarkir'],
                    ],
                    'sources' => ['name' => 'M1', 'type' => 'M1', 'display_name.en' => 'M1'],
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
                        'display_name' => ['en' => 'Khans of Tarkir'],
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
                    ],
                    'sources' => ['name' => 'M1', 'series' => 'M1', 'display_name.en' => 'M1'],
                ],
                [
                    'attributes' => [
                        'key' => 'fate-reforged',
                        'name' => 'Fate Reforged',
                        'type' => 'main',
                        'sort_order' => 1,
                        'is_visible' => true,
                        'display_name' => ['en' => 'Fate Reforged'],
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
                    ],
                    'sources' => ['name' => 'M1', 'series' => 'M1', 'display_name.en' => 'M1'],
                ],
                [
                    'attributes' => [
                        'key' => 'dragons-of-tarkir',
                        'name' => 'Dragons of Tarkir',
                        'type' => 'main',
                        'sort_order' => 2,
                        'is_visible' => true,
                        'display_name' => ['en' => 'Dragons of Tarkir'],
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
                    ],
                    'sources' => ['name' => 'M1', 'series' => 'M1', 'display_name.en' => 'M1'],
                ],
                [
                    'attributes' => [
                        'key' => 'foundations',
                        'name' => 'Foundations',
                        'type' => 'main',
                        'sort_order' => 3,
                        'is_visible' => true,
                        'display_name' => ['en' => 'Foundations'],
                    ],
                    'series' => null,
                    'editions' => [
                        [
                            'attributes' => ['name' => 'Magic: The Gathering Foundations', 'status' => 'released', 'is_visible' => true, 'official_code' => 'FDN'],
                            'language' => 'en',
                            'asset' => null,
                            'releases' => [],
                            'sources' => ['name' => 'M2', 'language' => 'magic-foundations-print', 'status' => 'magic-foundations-release', 'official_code' => 'M2'],
                        ],
                    ],
                    'sources' => ['name' => 'M2', 'display_name.en' => 'M2'],
                ],
            ],
        ],
    ],
];
