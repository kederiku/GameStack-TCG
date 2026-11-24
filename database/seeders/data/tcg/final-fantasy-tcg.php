<?php

return [
    'game' => ['slug' => 'final-fantasy-tcg', 'name' => 'FINAL FANTASY TRADING CARD GAME', 'is_visible' => true],
    'sources' => ['name' => 'ff-chapter'],
    'catalogs' => [
        [
            'attributes' => ['key' => 'chapter', 'name' => 'Chapter', 'sort_order' => 0, 'is_visible' => true],
            'default_language' => 'ja',
            'series' => [],
            'expansions' => [
                [
                    'attributes' => ['key' => 'chapter-i', 'name' => 'Chapter I', 'type' => 'main', 'sort_order' => 0, 'is_visible' => true],
                    'series' => null,
                    'editions' => [
                        [
                            'attributes' => ['name' => 'Chapter I', 'status' => 'released', 'is_visible' => true],
                            'language' => 'ja',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2011-02-25', 'status' => 'released'],
                                    'market' => 'japan',
                                    'sources' => ['market' => 'ff-chapter', 'channel' => 'ff-chapter-i', 'released_on' => 'ff-chapter-i', 'status' => 'ff-chapter-i'],
                                ],
                            ],
                            'sources' => ['name' => 'ff-chapter-i', 'language' => 'ff-chapter', 'status' => 'ff-chapter-i'],
                        ],
                    ],
                    'sources' => ['name' => 'ff-chapter-i'],
                ],
            ],
            'sources' => ['name' => 'ff-chapter'],
        ],
        [
            'attributes' => ['key' => 'opus', 'name' => 'Opus', 'sort_order' => 1, 'is_visible' => true],
            'default_language' => 'en',
            'series' => [],
            'expansions' => [
                [
                    'attributes' => ['key' => 'dreamlike-oceans', 'name' => 'Dreamlike Oceans', 'type' => 'main', 'sort_order' => 0, 'is_visible' => true],
                    'series' => null,
                    'editions' => [
                        [
                            'attributes' => ['name' => 'Dreamlike Oceans', 'status' => 'released', 'is_visible' => true],
                            'language' => 'en',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2026-03-27', 'status' => 'released'],
                                    'market' => 'north-america',
                                    'sources' => ['market' => 'ff-dreamlike', 'channel' => 'ff-dreamlike', 'released_on' => 'ff-dreamlike', 'status' => 'ff-dreamlike'],
                                ],
                            ],
                            'sources' => ['name' => 'ff-dreamlike', 'language' => 'ff-dreamlike-card', 'status' => 'ff-dreamlike'],
                        ],
                    ],
                    'sources' => ['name' => 'ff-dreamlike'],
                ],
            ],
            'sources' => ['name' => 'ff-chapter'],
        ],
    ],
];
