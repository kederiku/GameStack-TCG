<?php

return [
    'game' => [
        'slug' => 'cyberpunk',
        'name' => 'Cyberpunk TCG',
        'is_visible' => true,
        'display_name' => ['fr' => 'Cyberpunk TCG', 'en' => 'Cyberpunk TCG'],
    ],
    'sources' => ['name' => 'cyberpunk-release', 'display_name.fr' => 'cyberpunk-release', 'display_name.en' => 'cyberpunk-release'],
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
                        'key' => 'welcome-to-night-city',
                        'name' => 'Welcome to Night City',
                        'type' => 'main',
                        'sort_order' => 0,
                        'is_visible' => true,
                        'display_name' => ['en' => 'Welcome to Night City', 'fr' => 'Welcome to Night City'],
                    ],
                    'series' => null,
                    'editions' => [
                        [
                            'attributes' => ['name' => 'Welcome to Night City', 'status' => 'released', 'is_visible' => true],
                            'language' => 'en',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2026-11-06', 'status' => 'released'],
                                    'market' => 'worldwide',
                                    'sources' => [
                                        'market' => 'cyberpunk-release',
                                        'channel' => 'cyberpunk-release',
                                        'released_on' => 'cyberpunk-release',
                                        'status' => 'cyberpunk-release',
                                    ],
                                ],
                            ],
                            'sources' => ['name' => 'cyberpunk-print', 'language' => 'cyberpunk-print', 'status' => 'cyberpunk-retail'],
                        ],
                        [
                            'attributes' => ['name' => 'Welcome to Night City', 'status' => 'released', 'is_visible' => true],
                            'language' => 'fr',
                            'asset' => null,
                            'releases' => [],
                            'sources' => ['name' => 'cyberpunk-retail-fr-print', 'language' => 'cyberpunk-retail-fr-print', 'status' => 'cyberpunk-retail-fr-print'],
                        ],
                    ],
                    'sources' => ['name' => 'cyberpunk-print', 'display_name.en' => 'cyberpunk-print', 'display_name.fr' => 'cyberpunk-retail-fr-print'],
                ],
            ],
        ],
    ],
];
