<?php

return [
    'game' => ['slug' => 'lorcana', 'name' => 'Disney Lorcana', 'is_visible' => true],
    'sources' => ['name' => 'L2'],
    'catalogs' => [
        [
            'attributes' => ['key' => 'international', 'name' => 'International', 'sort_order' => 0, 'is_visible' => true],
            'default_language' => 'fr',
            'series' => [],
            'expansions' => [
                [
                    'attributes' => [
                        'key' => 'first-chapter',
                        'name' => 'Premier Chapitre',
                        'type' => 'main',
                        'sort_order' => 0,
                        'is_visible' => true,
                        'display_name' => ['fr' => 'Premier Chapitre', 'en' => 'The First Chapter'],
                    ],
                    'series' => null,
                    'editions' => [
                        [
                            'attributes' => ['name' => 'Premier Chapitre', 'status' => 'released', 'is_visible' => true],
                            'language' => 'fr',
                            'asset' => null,
                            'releases' => [],
                            'sources' => ['name' => 'lorcana-fr-notes', 'language' => 'lorcana-fr-notes', 'status' => 'lorcana-first-chapter-faq'],
                        ],
                        [
                            'attributes' => ['name' => 'The First Chapter', 'status' => 'released', 'is_visible' => true],
                            'language' => 'en',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2023-09-01', 'status' => 'released'],
                                    'market' => 'united-states',
                                    'sources' => [
                                        'market' => 'lorcana-us-launch',
                                        'channel' => 'lorcana-us-launch',
                                        'released_on' => 'lorcana-us-launch',
                                        'status' => 'lorcana-us-launch',
                                    ],
                                ],
                            ],
                            'sources' => ['name' => 'L2', 'language' => 'lorcana-en-checklist', 'status' => 'L2'],
                        ],
                    ],
                    'sources' => ['name' => 'lorcana-fr-notes', 'display_name.fr' => 'lorcana-fr-notes', 'display_name.en' => 'L2'],
                ],
            ],
        ],
    ],
];
