<?php

return [
    'game' => [
        'slug' => 'altered',
        'name' => 'Altered',
        'is_visible' => true,
        'display_name' => ['fr' => 'Altered', 'en' => 'Altered'],
    ],
    'sources' => ['name' => 'altered-physical', 'display_name.fr' => 'altered-physical', 'display_name.en' => 'altered-physical'],
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
                        'key' => 'beyond-the-gates',
                        'name' => 'Beyond the Gates',
                        'type' => 'main',
                        'sort_order' => 0,
                        'is_visible' => true,
                        'display_name' => ['en' => 'Beyond the Gates', 'fr' => 'Au-delà des portes'],
                    ],
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
                        [
                            'attributes' => ['name' => 'Au-delà des portes', 'status' => 'released', 'is_visible' => true],
                            'language' => 'fr',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2024-09-13', 'status' => 'released'],
                                    'market' => 'worldwide',
                                    'sources' => ['market' => 'altered-physical', 'channel' => 'altered-physical', 'released_on' => 'altered-physical', 'status' => 'altered-physical'],
                                ],
                            ],
                            'sources' => ['name' => 'altered-btg-fr-name', 'language' => 'altered-btg-fr-print', 'status' => 'altered-btg-fr-print'],
                        ],
                    ],
                    'sources' => ['name' => 'altered-physical', 'display_name.en' => 'altered-physical', 'display_name.fr' => 'altered-btg-fr-name'],
                ],
                [
                    'attributes' => [
                        'key' => 'trial-by-frost',
                        'name' => 'Trial by Frost',
                        'type' => 'main',
                        'sort_order' => 1,
                        'is_visible' => true,
                        'display_name' => ['en' => 'Trial by Frost', 'fr' => 'L’Épreuve du Froid'],
                    ],
                    'series' => null,
                    'editions' => [
                        [
                            'attributes' => ['name' => 'Trial by Frost', 'status' => 'released', 'is_visible' => true],
                            'language' => 'en',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2025-01-31', 'status' => 'released'],
                                    'market' => 'united-states',
                                    'sources' => ['market' => 'altered-usa-booster-catalog', 'channel' => 'altered-usa-booster-catalog', 'released_on' => 'altered-usa-booster-catalog', 'status' => 'altered-usa-booster-catalog'],
                                ],
                            ],
                            'sources' => ['name' => 'altered-trial-by-frost-en', 'language' => 'altered-usa-booster-catalog', 'status' => 'altered-usa-booster-catalog'],
                        ],
                        [
                            'attributes' => ['name' => 'L’Épreuve du Froid', 'status' => 'released', 'is_visible' => true],
                            'language' => 'fr',
                            'asset' => null,
                            'releases' => [],
                            'sources' => ['name' => 'altered-trial-by-frost-fr', 'language' => 'altered-trial-by-frost-fr', 'status' => 'altered-trial-by-frost-fr'],
                        ],
                    ],
                    'sources' => ['name' => 'altered-trial-by-frost-en', 'display_name.en' => 'altered-trial-by-frost-en', 'display_name.fr' => 'altered-trial-by-frost-fr'],
                ],
                [
                    'attributes' => [
                        'key' => 'whispers-from-the-maze',
                        'name' => 'Whispers from the Maze',
                        'type' => 'main',
                        'sort_order' => 2,
                        'is_visible' => true,
                        'display_name' => ['en' => 'Whispers from the Maze', 'fr' => 'Murmures du Labyrinthe'],
                    ],
                    'series' => null,
                    'editions' => [
                        [
                            'attributes' => ['name' => 'Whispers from the Maze', 'status' => 'released', 'is_visible' => true],
                            'language' => 'en',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2025-06-06', 'status' => 'released'],
                                    'market' => 'united-states',
                                    'sources' => ['market' => 'altered-usa-booster-catalog', 'channel' => 'altered-usa-booster-catalog', 'released_on' => 'altered-usa-booster-catalog', 'status' => 'altered-usa-booster-catalog'],
                                ],
                            ],
                            'sources' => ['name' => 'altered-whispers-from-the-maze-en', 'language' => 'altered-usa-booster-catalog', 'status' => 'altered-usa-booster-catalog'],
                        ],
                        [
                            'attributes' => ['name' => 'Murmures du Labyrinthe', 'status' => 'released', 'is_visible' => true],
                            'language' => 'fr',
                            'asset' => null,
                            'releases' => [],
                            'sources' => ['name' => 'altered-whispers-from-the-maze-fr', 'language' => 'altered-whispers-from-the-maze-fr', 'status' => 'altered-whispers-from-the-maze-fr'],
                        ],
                    ],
                    'sources' => ['name' => 'altered-whispers-from-the-maze-en', 'display_name.en' => 'altered-whispers-from-the-maze-en', 'display_name.fr' => 'altered-whispers-from-the-maze-fr'],
                ],
                [
                    'attributes' => [
                        'key' => 'skybound-odyssey',
                        'name' => 'Skybound Odyssey',
                        'type' => 'main',
                        'sort_order' => 3,
                        'is_visible' => true,
                        'display_name' => ['en' => 'Skybound Odyssey', 'fr' => 'L’Odyssée des Cieux'],
                    ],
                    'series' => null,
                    'editions' => [
                        [
                            'attributes' => ['name' => 'Skybound Odyssey', 'status' => 'released', 'is_visible' => true],
                            'language' => 'en',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2025-10-03', 'status' => 'released'],
                                    'market' => 'united-states',
                                    'sources' => ['market' => 'altered-usa-booster-catalog', 'channel' => 'altered-usa-booster-catalog', 'released_on' => 'altered-usa-booster-catalog', 'status' => 'altered-usa-booster-catalog'],
                                ],
                            ],
                            'sources' => ['name' => 'altered-skybound-odyssey-en', 'language' => 'altered-usa-booster-catalog', 'status' => 'altered-usa-booster-catalog'],
                        ],
                        [
                            'attributes' => ['name' => 'L’Odyssée des Cieux', 'status' => 'released', 'is_visible' => true],
                            'language' => 'fr',
                            'asset' => null,
                            'releases' => [],
                            'sources' => ['name' => 'altered-skybound-odyssey-fr', 'language' => 'altered-skybound-odyssey-fr', 'status' => 'altered-skybound-odyssey-fr'],
                        ],
                    ],
                    'sources' => ['name' => 'altered-skybound-odyssey-en', 'display_name.en' => 'altered-skybound-odyssey-en', 'display_name.fr' => 'altered-skybound-odyssey-fr'],
                ],
                [
                    'attributes' => [
                        'key' => 'seeds-of-unity',
                        'name' => 'Seeds of Unity',
                        'type' => 'main',
                        'sort_order' => 4,
                        'is_visible' => true,
                        'display_name' => ['en' => 'Seeds of Unity', 'fr' => 'Les Graines de l’Unité'],
                    ],
                    'series' => null,
                    'editions' => [
                        [
                            'attributes' => ['name' => 'Seeds of Unity', 'status' => 'released', 'is_visible' => true],
                            'language' => 'en',
                            'asset' => null,
                            'releases' => [],
                            'sources' => ['name' => 'altered-seeds-of-unity-en', 'language' => 'altered-pod-legacy', 'status' => 'altered-seeds-delivered'],
                        ],
                        [
                            'attributes' => ['name' => 'Les Graines de l’Unité', 'status' => 'released', 'is_visible' => true],
                            'language' => 'fr',
                            'asset' => null,
                            'releases' => [],
                            'sources' => ['name' => 'altered-seeds-of-unity-fr', 'language' => 'altered-pod-legacy', 'status' => 'altered-seeds-delivered'],
                        ],
                    ],
                    'sources' => ['name' => 'altered-seeds-of-unity-en', 'display_name.en' => 'altered-seeds-of-unity-en', 'display_name.fr' => 'altered-seeds-of-unity-fr'],
                ],
            ],
        ],
    ],
];
