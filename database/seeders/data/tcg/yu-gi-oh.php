<?php

return [
    'game' => [
        'slug' => 'yu-gi-oh',
        'name' => 'Yu-Gi-Oh!',
        'is_visible' => true,
        'display_name' => ['fr' => 'Yu-Gi-Oh!', 'en' => 'Yu-Gi-Oh!'],
    ],
    'sources' => ['name' => 'Y3', 'display_name.fr' => 'Y3', 'display_name.en' => 'Y3'],
    'catalogs' => [
        [
            'attributes' => [
                'key' => 'tcg',
                'name' => 'TCG',
                'sort_order' => 0,
                'is_visible' => true,
                'display_name' => ['fr' => 'TCG', 'en' => 'TCG'],
            ],
            'default_language' => 'fr',
            'series' => [],
            'expansions' => [
                [
                    'attributes' => [
                        'key' => 'darkwing-blast',
                        'name' => 'Explosion de l’Aile des Ténèbres',
                        'type' => 'main',
                        'sort_order' => 0,
                        'is_visible' => true,
                        'display_name' => ['fr' => 'Explosion de l’Aile des Ténèbres', 'en' => 'Darkwing Blast'],
                    ],
                    'series' => null,
                    'editions' => [
                        [
                            'attributes' => ['name' => 'Explosion de l’Aile des Ténèbres', 'status' => 'released', 'is_visible' => true],
                            'language' => 'fr',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2022-10-20', 'status' => 'released'],
                                    'market' => 'europe',
                                    'sources' => ['market' => 'Y2', 'channel' => 'Y2', 'released_on' => 'Y2', 'status' => 'Y2'],
                                ],
                            ],
                            'sources' => ['name' => 'Y2', 'language' => 'yugioh-fr-card', 'status' => 'Y2'],
                        ],
                        [
                            'attributes' => ['name' => 'Darkwing Blast', 'status' => 'released', 'is_visible' => true],
                            'language' => 'en',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2022-10-21', 'status' => 'released'],
                                    'market' => 'north-america',
                                    'sources' => ['market' => 'Y3', 'channel' => 'Y3', 'released_on' => 'Y3', 'status' => 'Y3'],
                                ],
                                [
                                    'attributes' => ['channel' => 'specialist', 'released_on' => '2022-10-19', 'status' => 'released'],
                                    'market' => 'north-america',
                                    'sources' => ['market' => 'Y3', 'channel' => 'Y3', 'released_on' => 'Y3', 'status' => 'Y3'],
                                ],
                            ],
                            'sources' => ['name' => 'Y3', 'language' => 'yugioh-en-card', 'status' => 'Y3'],
                        ],
                        [
                            'attributes' => ['name' => 'Darkwing Blast', 'status' => 'released', 'is_visible' => true],
                            'language' => 'de',
                            'asset' => null,
                            'releases' => [],
                            'sources' => ['name' => 'yugioh-dabl042-de', 'language' => 'yugioh-dabl042-de', 'status' => 'yugioh-dabl042-de'],
                        ],
                        [
                            'attributes' => ['name' => 'Explosión Alaoscura', 'status' => 'released', 'is_visible' => true],
                            'language' => 'es',
                            'asset' => null,
                            'releases' => [],
                            'sources' => ['name' => 'yugioh-dabl042-es', 'language' => 'yugioh-dabl042-es', 'status' => 'yugioh-dabl042-es'],
                        ],
                        [
                            'attributes' => ['name' => 'Esplosione Alaoscura', 'status' => 'released', 'is_visible' => true],
                            'language' => 'it',
                            'asset' => null,
                            'releases' => [],
                            'sources' => ['name' => 'yugioh-dabl042-it', 'language' => 'yugioh-dabl042-it', 'status' => 'yugioh-dabl042-it'],
                        ],
                        [
                            'attributes' => ['name' => 'Explosão da Asaescura', 'status' => 'released', 'is_visible' => true],
                            'language' => 'pt',
                            'asset' => null,
                            'releases' => [],
                            'sources' => ['name' => 'yugioh-dabl042-pt', 'language' => 'yugioh-dabl042-pt', 'status' => 'yugioh-dabl042-pt'],
                        ],
                    ],
                    'sources' => ['name' => 'Y2', 'display_name.fr' => 'Y2', 'display_name.en' => 'Y3'],
                ],
                [
                    'attributes' => [
                        'key' => 'photon-hypernova',
                        'name' => 'Hypernova Photon',
                        'type' => 'main',
                        'sort_order' => 1,
                        'is_visible' => true,
                        'display_name' => ['fr' => 'Hypernova Photon', 'en' => 'Photon Hypernova'],
                    ],
                    'series' => null,
                    'editions' => [
                        [
                            'attributes' => ['name' => 'Hypernova Photon', 'status' => 'released', 'is_visible' => true],
                            'language' => 'fr',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2023-02-09', 'status' => 'released'],
                                    'market' => 'europe',
                                    'sources' => ['market' => 'yugioh-phhy-fr', 'channel' => 'yugioh-phhy-fr', 'released_on' => 'yugioh-phhy-fr', 'status' => 'yugioh-phhy-fr'],
                                ],
                            ],
                            'sources' => ['name' => 'yugioh-phhy-fr', 'language' => 'yugioh-tcg-fr-card-list', 'status' => 'yugioh-tcg-fr-card-list'],
                        ],
                        [
                            'attributes' => ['name' => 'Photon Hypernova', 'status' => 'released', 'is_visible' => true],
                            'language' => 'en',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2023-02-10', 'status' => 'released'],
                                    'market' => 'north-america',
                                    'sources' => ['market' => 'yugioh-phhy-en', 'channel' => 'yugioh-phhy-en', 'released_on' => 'yugioh-phhy-en', 'status' => 'yugioh-phhy-en'],
                                ],
                                [
                                    'attributes' => ['channel' => 'specialist', 'released_on' => '2023-02-08', 'status' => 'released'],
                                    'market' => 'north-america',
                                    'sources' => ['market' => 'yugioh-phhy-en', 'channel' => 'yugioh-phhy-en', 'released_on' => 'yugioh-phhy-en', 'status' => 'yugioh-phhy-en'],
                                ],
                            ],
                            'sources' => ['name' => 'yugioh-phhy-en', 'language' => 'yugioh-tcg-en-card-list', 'status' => 'yugioh-tcg-en-card-list'],
                        ],
                    ],
                    'sources' => ['name' => 'yugioh-phhy-fr', 'display_name.fr' => 'yugioh-phhy-fr', 'display_name.en' => 'yugioh-phhy-en'],
                ],
                [
                    'attributes' => [
                        'key' => 'cyberstorm-access',
                        'name' => 'Accès à la Cyber-Tempête',
                        'type' => 'main',
                        'sort_order' => 2,
                        'is_visible' => true,
                        'display_name' => ['fr' => 'Accès à la Cyber-Tempête', 'en' => 'Cyberstorm Access'],
                    ],
                    'series' => null,
                    'editions' => [
                        [
                            'attributes' => ['name' => 'Accès à la Cyber-Tempête', 'status' => 'released', 'is_visible' => true],
                            'language' => 'fr',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2023-05-04', 'status' => 'released'],
                                    'market' => 'europe',
                                    'sources' => ['market' => 'yugioh-cyac-fr', 'channel' => 'yugioh-cyac-fr', 'released_on' => 'yugioh-cyac-fr', 'status' => 'yugioh-cyac-fr'],
                                ],
                            ],
                            'sources' => ['name' => 'yugioh-cyac-fr', 'language' => 'yugioh-tcg-fr-card-list', 'status' => 'yugioh-tcg-fr-card-list'],
                        ],
                        [
                            'attributes' => ['name' => 'Cyberstorm Access', 'status' => 'released', 'is_visible' => true],
                            'language' => 'en',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2023-05-05', 'status' => 'released'],
                                    'market' => 'north-america',
                                    'sources' => ['market' => 'yugioh-cyac-en', 'channel' => 'yugioh-cyac-en', 'released_on' => 'yugioh-cyac-en', 'status' => 'yugioh-cyac-en'],
                                ],
                                [
                                    'attributes' => ['channel' => 'specialist', 'released_on' => '2023-05-03', 'status' => 'released'],
                                    'market' => 'north-america',
                                    'sources' => ['market' => 'yugioh-cyac-en', 'channel' => 'yugioh-cyac-en', 'released_on' => 'yugioh-cyac-en', 'status' => 'yugioh-cyac-en'],
                                ],
                            ],
                            'sources' => ['name' => 'yugioh-cyac-en', 'language' => 'yugioh-tcg-en-card-list', 'status' => 'yugioh-tcg-en-card-list'],
                        ],
                    ],
                    'sources' => ['name' => 'yugioh-cyac-fr', 'display_name.fr' => 'yugioh-cyac-fr', 'display_name.en' => 'yugioh-cyac-en'],
                ],
                [
                    'attributes' => [
                        'key' => 'duelist-nexus',
                        'name' => 'Nexus du Duelliste',
                        'type' => 'main',
                        'sort_order' => 3,
                        'is_visible' => true,
                        'display_name' => ['fr' => 'Nexus du Duelliste', 'en' => 'Duelist Nexus'],
                    ],
                    'series' => null,
                    'editions' => [
                        [
                            'attributes' => ['name' => 'Nexus du Duelliste', 'status' => 'released', 'is_visible' => true],
                            'language' => 'fr',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2023-07-27', 'status' => 'released'],
                                    'market' => 'europe',
                                    'sources' => ['market' => 'yugioh-dune-fr', 'channel' => 'yugioh-dune-fr', 'released_on' => 'yugioh-dune-fr', 'status' => 'yugioh-dune-fr'],
                                ],
                            ],
                            'sources' => ['name' => 'yugioh-dune-fr', 'language' => 'yugioh-tcg-fr-card-list', 'status' => 'yugioh-tcg-fr-card-list'],
                        ],
                        [
                            'attributes' => ['name' => 'Duelist Nexus', 'status' => 'released', 'is_visible' => true],
                            'language' => 'en',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2023-07-28', 'status' => 'released'],
                                    'market' => 'north-america',
                                    'sources' => ['market' => 'yugioh-dune-en', 'channel' => 'yugioh-dune-en', 'released_on' => 'yugioh-dune-en', 'status' => 'yugioh-dune-en'],
                                ],
                                [
                                    'attributes' => ['channel' => 'specialist', 'released_on' => '2023-07-26', 'status' => 'released'],
                                    'market' => 'north-america',
                                    'sources' => ['market' => 'yugioh-dune-en', 'channel' => 'yugioh-dune-en', 'released_on' => 'yugioh-dune-en', 'status' => 'yugioh-dune-en'],
                                ],
                            ],
                            'sources' => ['name' => 'yugioh-dune-en', 'language' => 'yugioh-tcg-en-card-list', 'status' => 'yugioh-tcg-en-card-list'],
                        ],
                    ],
                    'sources' => ['name' => 'yugioh-dune-fr', 'display_name.fr' => 'yugioh-dune-fr', 'display_name.en' => 'yugioh-dune-en'],
                ],
                [
                    'attributes' => [
                        'key' => 'age-of-overlord',
                        'name' => 'L’Ère du Seigneur Suprême',
                        'type' => 'main',
                        'sort_order' => 4,
                        'is_visible' => true,
                        'display_name' => ['fr' => 'L’Ère du Seigneur Suprême', 'en' => 'Age of Overlord'],
                    ],
                    'series' => null,
                    'editions' => [
                        [
                            'attributes' => ['name' => 'L’Ère du Seigneur Suprême', 'status' => 'released', 'is_visible' => true],
                            'language' => 'fr',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2023-10-19', 'status' => 'released'],
                                    'market' => 'europe',
                                    'sources' => ['market' => 'yugioh-agov-fr', 'channel' => 'yugioh-agov-fr', 'released_on' => 'yugioh-agov-fr', 'status' => 'yugioh-agov-fr'],
                                ],
                            ],
                            'sources' => ['name' => 'yugioh-agov-fr', 'language' => 'yugioh-tcg-fr-card-list', 'status' => 'yugioh-tcg-fr-card-list'],
                        ],
                        [
                            'attributes' => ['name' => 'Age of Overlord', 'status' => 'released', 'is_visible' => true],
                            'language' => 'en',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2023-10-20', 'status' => 'released'],
                                    'market' => 'north-america',
                                    'sources' => ['market' => 'yugioh-agov-en', 'channel' => 'yugioh-agov-en', 'released_on' => 'yugioh-agov-en', 'status' => 'yugioh-agov-en'],
                                ],
                                [
                                    'attributes' => ['channel' => 'specialist', 'released_on' => '2023-10-18', 'status' => 'released'],
                                    'market' => 'north-america',
                                    'sources' => ['market' => 'yugioh-agov-en', 'channel' => 'yugioh-agov-en', 'released_on' => 'yugioh-agov-en', 'status' => 'yugioh-agov-en'],
                                ],
                            ],
                            'sources' => ['name' => 'yugioh-agov-en', 'language' => 'yugioh-tcg-en-card-list', 'status' => 'yugioh-tcg-en-card-list'],
                        ],
                    ],
                    'sources' => ['name' => 'yugioh-agov-fr', 'display_name.fr' => 'yugioh-agov-fr', 'display_name.en' => 'yugioh-agov-en'],
                ],
                [
                    'attributes' => [
                        'key' => 'phantom-nightmare',
                        'name' => 'Cauchemar Fantôme',
                        'type' => 'main',
                        'sort_order' => 5,
                        'is_visible' => true,
                        'display_name' => ['fr' => 'Cauchemar Fantôme', 'en' => 'Phantom Nightmare'],
                    ],
                    'series' => null,
                    'editions' => [
                        [
                            'attributes' => ['name' => 'Cauchemar Fantôme', 'status' => 'released', 'is_visible' => true],
                            'language' => 'fr',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2024-02-08', 'status' => 'released'],
                                    'market' => 'europe',
                                    'sources' => ['market' => 'yugioh-phni-fr', 'channel' => 'yugioh-phni-fr', 'released_on' => 'yugioh-phni-fr', 'status' => 'yugioh-phni-fr'],
                                ],
                            ],
                            'sources' => ['name' => 'yugioh-phni-fr', 'language' => 'yugioh-tcg-fr-card-list', 'status' => 'yugioh-tcg-fr-card-list'],
                        ],
                        [
                            'attributes' => ['name' => 'Phantom Nightmare', 'status' => 'released', 'is_visible' => true],
                            'language' => 'en',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2024-02-09', 'status' => 'released'],
                                    'market' => 'north-america',
                                    'sources' => ['market' => 'yugioh-phni-en', 'channel' => 'yugioh-phni-en', 'released_on' => 'yugioh-phni-en', 'status' => 'yugioh-phni-en'],
                                ],
                                [
                                    'attributes' => ['channel' => 'specialist', 'released_on' => '2024-02-07', 'status' => 'released'],
                                    'market' => 'north-america',
                                    'sources' => ['market' => 'yugioh-phni-en', 'channel' => 'yugioh-phni-en', 'released_on' => 'yugioh-phni-en', 'status' => 'yugioh-phni-en'],
                                ],
                            ],
                            'sources' => ['name' => 'yugioh-phni-en', 'language' => 'yugioh-tcg-en-card-list', 'status' => 'yugioh-tcg-en-card-list'],
                        ],
                        [
                            'attributes' => ['name' => 'Phantom Nightmare', 'status' => 'released', 'is_visible' => true],
                            'language' => 'de',
                            'asset' => null,
                            'releases' => [],
                            'sources' => ['name' => 'yugioh-phni054-de', 'language' => 'yugioh-phni054-de', 'status' => 'yugioh-phni054-de'],
                        ],
                        [
                            'attributes' => ['name' => 'Pesadilla Fantasma', 'status' => 'released', 'is_visible' => true],
                            'language' => 'es',
                            'asset' => null,
                            'releases' => [],
                            'sources' => ['name' => 'yugioh-phni054-es', 'language' => 'yugioh-phni054-es', 'status' => 'yugioh-phni054-es'],
                        ],
                        [
                            'attributes' => ['name' => 'Incubo Fantasma', 'status' => 'released', 'is_visible' => true],
                            'language' => 'it',
                            'asset' => null,
                            'releases' => [],
                            'sources' => ['name' => 'yugioh-phni054-it', 'language' => 'yugioh-phni054-it', 'status' => 'yugioh-phni054-it'],
                        ],
                        [
                            'attributes' => ['name' => 'Pesadelo Fantasma', 'status' => 'released', 'is_visible' => true],
                            'language' => 'pt',
                            'asset' => null,
                            'releases' => [],
                            'sources' => ['name' => 'yugioh-phni054-pt', 'language' => 'yugioh-phni054-pt', 'status' => 'yugioh-phni054-pt'],
                        ],
                    ],
                    'sources' => ['name' => 'yugioh-phni-fr', 'display_name.fr' => 'yugioh-phni-fr', 'display_name.en' => 'yugioh-phni-en'],
                ],
                [
                    'attributes' => [
                        'key' => 'legacy-of-destruction',
                        'name' => 'Héritage de la Destruction',
                        'type' => 'main',
                        'sort_order' => 6,
                        'is_visible' => true,
                        'display_name' => ['fr' => 'Héritage de la Destruction', 'en' => 'Legacy of Destruction'],
                    ],
                    'series' => null,
                    'editions' => [
                        [
                            'attributes' => ['name' => 'Héritage de la Destruction', 'status' => 'released', 'is_visible' => true],
                            'language' => 'fr',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2024-04-25', 'status' => 'released'],
                                    'market' => 'europe',
                                    'sources' => ['market' => 'yugioh-lede-fr', 'channel' => 'yugioh-lede-fr', 'released_on' => 'yugioh-lede-fr', 'status' => 'yugioh-lede-fr'],
                                ],
                            ],
                            'sources' => ['name' => 'yugioh-lede-fr', 'language' => 'yugioh-tcg-fr-card-list', 'status' => 'yugioh-tcg-fr-card-list'],
                        ],
                        [
                            'attributes' => ['name' => 'Legacy of Destruction', 'status' => 'released', 'is_visible' => true],
                            'language' => 'en',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2024-04-26', 'status' => 'released'],
                                    'market' => 'north-america',
                                    'sources' => ['market' => 'yugioh-lede-en', 'channel' => 'yugioh-lede-en', 'released_on' => 'yugioh-lede-en', 'status' => 'yugioh-lede-en'],
                                ],
                                [
                                    'attributes' => ['channel' => 'specialist', 'released_on' => '2024-04-24', 'status' => 'released'],
                                    'market' => 'north-america',
                                    'sources' => ['market' => 'yugioh-lede-en', 'channel' => 'yugioh-lede-en', 'released_on' => 'yugioh-lede-en', 'status' => 'yugioh-lede-en'],
                                ],
                            ],
                            'sources' => ['name' => 'yugioh-lede-en', 'language' => 'yugioh-tcg-en-card-list', 'status' => 'yugioh-tcg-en-card-list'],
                        ],
                    ],
                    'sources' => ['name' => 'yugioh-lede-fr', 'display_name.fr' => 'yugioh-lede-fr', 'display_name.en' => 'yugioh-lede-en'],
                ],
                [
                    'attributes' => [
                        'key' => 'the-infinite-forbidden',
                        'name' => 'L’Infini Interdit',
                        'type' => 'main',
                        'sort_order' => 7,
                        'is_visible' => true,
                        'display_name' => ['fr' => 'L’Infini Interdit', 'en' => 'The Infinite Forbidden'],
                    ],
                    'series' => null,
                    'editions' => [
                        [
                            'attributes' => ['name' => 'L’Infini Interdit', 'status' => 'released', 'is_visible' => true],
                            'language' => 'fr',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2024-07-18', 'status' => 'released'],
                                    'market' => 'europe',
                                    'sources' => ['market' => 'yugioh-info-fr', 'channel' => 'yugioh-info-fr', 'released_on' => 'yugioh-info-fr', 'status' => 'yugioh-info-fr'],
                                ],
                            ],
                            'sources' => ['name' => 'yugioh-info-fr', 'language' => 'yugioh-tcg-fr-card-list', 'status' => 'yugioh-tcg-fr-card-list'],
                        ],
                        [
                            'attributes' => ['name' => 'The Infinite Forbidden', 'status' => 'released', 'is_visible' => true],
                            'language' => 'en',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2024-07-19', 'status' => 'released'],
                                    'market' => 'north-america',
                                    'sources' => ['market' => 'yugioh-info-en', 'channel' => 'yugioh-info-en', 'released_on' => 'yugioh-info-en', 'status' => 'yugioh-info-en'],
                                ],
                                [
                                    'attributes' => ['channel' => 'specialist', 'released_on' => '2024-07-17', 'status' => 'released'],
                                    'market' => 'north-america',
                                    'sources' => ['market' => 'yugioh-info-en', 'channel' => 'yugioh-info-en', 'released_on' => 'yugioh-info-en', 'status' => 'yugioh-info-en'],
                                ],
                            ],
                            'sources' => ['name' => 'yugioh-info-en', 'language' => 'yugioh-tcg-en-card-list', 'status' => 'yugioh-tcg-en-card-list'],
                        ],
                    ],
                    'sources' => ['name' => 'yugioh-info-fr', 'display_name.fr' => 'yugioh-info-fr', 'display_name.en' => 'yugioh-info-en'],
                ],
                [
                    'attributes' => [
                        'key' => 'rage-of-the-abyss',
                        'name' => 'La Rage des Abysses',
                        'type' => 'main',
                        'sort_order' => 8,
                        'is_visible' => true,
                        'display_name' => ['fr' => 'La Rage des Abysses', 'en' => 'Rage of the Abyss'],
                    ],
                    'series' => null,
                    'editions' => [
                        [
                            'attributes' => ['name' => 'La Rage des Abysses', 'status' => 'released', 'is_visible' => true],
                            'language' => 'fr',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2024-10-10', 'status' => 'released'],
                                    'market' => 'europe',
                                    'sources' => ['market' => 'yugioh-rota-fr', 'channel' => 'yugioh-rota-fr', 'released_on' => 'yugioh-rota-fr', 'status' => 'yugioh-rota-fr'],
                                ],
                            ],
                            'sources' => ['name' => 'yugioh-rota-fr', 'language' => 'yugioh-tcg-fr-card-list', 'status' => 'yugioh-tcg-fr-card-list'],
                        ],
                        [
                            'attributes' => ['name' => 'Rage of the Abyss', 'status' => 'released', 'is_visible' => true],
                            'language' => 'en',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2024-10-11', 'status' => 'released'],
                                    'market' => 'north-america',
                                    'sources' => ['market' => 'yugioh-rota-en', 'channel' => 'yugioh-rota-en', 'released_on' => 'yugioh-rota-en', 'status' => 'yugioh-rota-en'],
                                ],
                                [
                                    'attributes' => ['channel' => 'specialist', 'released_on' => '2024-10-09', 'status' => 'released'],
                                    'market' => 'north-america',
                                    'sources' => ['market' => 'yugioh-rota-en', 'channel' => 'yugioh-rota-en', 'released_on' => 'yugioh-rota-en', 'status' => 'yugioh-rota-en'],
                                ],
                            ],
                            'sources' => ['name' => 'yugioh-rota-en', 'language' => 'yugioh-tcg-en-card-list', 'status' => 'yugioh-tcg-en-card-list'],
                        ],
                    ],
                    'sources' => ['name' => 'yugioh-rota-fr', 'display_name.fr' => 'yugioh-rota-fr', 'display_name.en' => 'yugioh-rota-en'],
                ],
                [
                    'attributes' => [
                        'key' => 'supreme-darkness',
                        'name' => 'Les Ténèbres Suprêmes',
                        'type' => 'main',
                        'sort_order' => 9,
                        'is_visible' => true,
                        'display_name' => ['fr' => 'Les Ténèbres Suprêmes', 'en' => 'Supreme Darkness'],
                    ],
                    'series' => null,
                    'editions' => [
                        [
                            'attributes' => ['name' => 'Les Ténèbres Suprêmes', 'status' => 'released', 'is_visible' => true],
                            'language' => 'fr',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2025-01-23', 'status' => 'released'],
                                    'market' => 'europe',
                                    'sources' => ['market' => 'yugioh-suda-fr', 'channel' => 'yugioh-suda-fr', 'released_on' => 'yugioh-suda-fr', 'status' => 'yugioh-suda-fr'],
                                ],
                            ],
                            'sources' => ['name' => 'yugioh-suda-fr', 'language' => 'yugioh-tcg-fr-card-list', 'status' => 'yugioh-tcg-fr-card-list'],
                        ],
                        [
                            'attributes' => ['name' => 'Supreme Darkness', 'status' => 'released', 'is_visible' => true],
                            'language' => 'en',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2025-01-24', 'status' => 'released'],
                                    'market' => 'north-america',
                                    'sources' => ['market' => 'yugioh-suda-en', 'channel' => 'yugioh-suda-en', 'released_on' => 'yugioh-suda-en', 'status' => 'yugioh-suda-en'],
                                ],
                                [
                                    'attributes' => ['channel' => 'specialist', 'released_on' => '2025-01-22', 'status' => 'released'],
                                    'market' => 'north-america',
                                    'sources' => ['market' => 'yugioh-suda-en', 'channel' => 'yugioh-suda-en', 'released_on' => 'yugioh-suda-en', 'status' => 'yugioh-suda-en'],
                                ],
                            ],
                            'sources' => ['name' => 'yugioh-suda-en', 'language' => 'yugioh-tcg-en-card-list', 'status' => 'yugioh-tcg-en-card-list'],
                        ],
                    ],
                    'sources' => ['name' => 'yugioh-suda-fr', 'display_name.fr' => 'yugioh-suda-fr', 'display_name.en' => 'yugioh-suda-en'],
                ],
            ],
        ],
        [
            'attributes' => [
                'key' => 'ocg',
                'name' => 'OCG japonais',
                'sort_order' => 1,
                'is_visible' => true,
                'display_name' => ['fr' => 'OCG japonais', 'en' => 'Japanese OCG'],
            ],
            'default_language' => 'ja',
            'series' => [],
            'expansions' => [
                [
                    'attributes' => [
                        'key' => 'darkwing-blast',
                        'name' => 'DARKWING BLAST',
                        'type' => 'main',
                        'sort_order' => 0,
                        'is_visible' => true,
                        'display_name' => ['ja' => 'DARKWING BLAST', 'en' => 'Darkwing Blast'],
                    ],
                    'series' => null,
                    'editions' => [
                        [
                            'attributes' => ['name' => 'DARKWING BLAST', 'status' => 'released', 'is_visible' => true],
                            'language' => 'ja',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2022-07-16', 'status' => 'released'],
                                    'market' => 'japan',
                                    'sources' => ['market' => 'Y1', 'channel' => 'Y1', 'released_on' => 'Y1', 'status' => 'Y1'],
                                ],
                            ],
                            'sources' => ['name' => 'Y1', 'language' => 'yugioh-ja-card', 'status' => 'Y1'],
                        ],
                    ],
                    'sources' => ['name' => 'Y1', 'display_name.ja' => 'Y1', 'display_name.en' => 'Y3'],
                ],
                [
                    'attributes' => [
                        'key' => 'photon-hypernova',
                        'name' => 'PHOTON HYPERNOVA',
                        'type' => 'main',
                        'sort_order' => 1,
                        'is_visible' => true,
                        'display_name' => ['ja' => 'PHOTON HYPERNOVA', 'en' => 'Photon Hypernova'],
                    ],
                    'series' => null,
                    'editions' => [
                        [
                            'attributes' => ['name' => 'PHOTON HYPERNOVA', 'status' => 'released', 'is_visible' => true],
                            'language' => 'ja',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2022-10-15', 'status' => 'released'],
                                    'market' => 'japan',
                                    'sources' => ['market' => 'yugioh-phhy-ja', 'channel' => 'yugioh-phhy-ja', 'released_on' => 'yugioh-phhy-ja', 'status' => 'yugioh-phhy-ja'],
                                ],
                            ],
                            'sources' => ['name' => 'yugioh-phhy-ja', 'language' => 'yugioh-ocg-ja-card-list', 'status' => 'yugioh-ocg-ja-card-list'],
                        ],
                    ],
                    'sources' => ['name' => 'yugioh-phhy-ja', 'display_name.ja' => 'yugioh-phhy-ja', 'display_name.en' => 'yugioh-phhy-en'],
                ],
                [
                    'attributes' => [
                        'key' => 'cyberstorm-access',
                        'name' => 'CYBERSTORM ACCESS',
                        'type' => 'main',
                        'sort_order' => 2,
                        'is_visible' => true,
                        'display_name' => ['ja' => 'CYBERSTORM ACCESS', 'en' => 'Cyberstorm Access'],
                    ],
                    'series' => null,
                    'editions' => [
                        [
                            'attributes' => ['name' => 'CYBERSTORM ACCESS', 'status' => 'released', 'is_visible' => true],
                            'language' => 'ja',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2023-01-14', 'status' => 'released'],
                                    'market' => 'japan',
                                    'sources' => ['market' => 'yugioh-cyac-ja', 'channel' => 'yugioh-cyac-ja', 'released_on' => 'yugioh-cyac-ja', 'status' => 'yugioh-cyac-ja'],
                                ],
                            ],
                            'sources' => ['name' => 'yugioh-cyac-ja', 'language' => 'yugioh-ocg-ja-card-list', 'status' => 'yugioh-ocg-ja-card-list'],
                        ],
                    ],
                    'sources' => ['name' => 'yugioh-cyac-ja', 'display_name.ja' => 'yugioh-cyac-ja', 'display_name.en' => 'yugioh-cyac-en'],
                ],
                [
                    'attributes' => [
                        'key' => 'duelist-nexus',
                        'name' => 'DUELIST NEXUS',
                        'type' => 'main',
                        'sort_order' => 3,
                        'is_visible' => true,
                        'display_name' => ['ja' => 'DUELIST NEXUS', 'en' => 'Duelist Nexus'],
                    ],
                    'series' => null,
                    'editions' => [
                        [
                            'attributes' => ['name' => 'DUELIST NEXUS', 'status' => 'released', 'is_visible' => true],
                            'language' => 'ja',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2023-04-22', 'status' => 'released'],
                                    'market' => 'japan',
                                    'sources' => ['market' => 'yugioh-dune-ja', 'channel' => 'yugioh-dune-ja', 'released_on' => 'yugioh-dune-ja', 'status' => 'yugioh-dune-ja'],
                                ],
                            ],
                            'sources' => ['name' => 'yugioh-dune-ja', 'language' => 'yugioh-ocg-ja-card-list', 'status' => 'yugioh-ocg-ja-card-list'],
                        ],
                    ],
                    'sources' => ['name' => 'yugioh-dune-ja', 'display_name.ja' => 'yugioh-dune-ja', 'display_name.en' => 'yugioh-dune-en'],
                ],
                [
                    'attributes' => [
                        'key' => 'age-of-overlord',
                        'name' => 'AGE OF OVERLORD',
                        'type' => 'main',
                        'sort_order' => 4,
                        'is_visible' => true,
                        'display_name' => ['ja' => 'AGE OF OVERLORD', 'en' => 'Age of Overlord'],
                    ],
                    'series' => null,
                    'editions' => [
                        [
                            'attributes' => ['name' => 'AGE OF OVERLORD', 'status' => 'released', 'is_visible' => true],
                            'language' => 'ja',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2023-07-22', 'status' => 'released'],
                                    'market' => 'japan',
                                    'sources' => ['market' => 'yugioh-agov-ja', 'channel' => 'yugioh-agov-ja', 'released_on' => 'yugioh-agov-ja', 'status' => 'yugioh-agov-ja'],
                                ],
                            ],
                            'sources' => ['name' => 'yugioh-agov-ja', 'language' => 'yugioh-ocg-ja-card-list', 'status' => 'yugioh-ocg-ja-card-list'],
                        ],
                    ],
                    'sources' => ['name' => 'yugioh-agov-ja', 'display_name.ja' => 'yugioh-agov-ja', 'display_name.en' => 'yugioh-agov-en'],
                ],
                [
                    'attributes' => [
                        'key' => 'phantom-nightmare',
                        'name' => 'PHANTOM NIGHTMARE',
                        'type' => 'main',
                        'sort_order' => 5,
                        'is_visible' => true,
                        'display_name' => ['ja' => 'PHANTOM NIGHTMARE', 'en' => 'Phantom Nightmare'],
                    ],
                    'series' => null,
                    'editions' => [
                        [
                            'attributes' => ['name' => 'PHANTOM NIGHTMARE', 'status' => 'released', 'is_visible' => true],
                            'language' => 'ja',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2023-10-28', 'status' => 'released'],
                                    'market' => 'japan',
                                    'sources' => ['market' => 'yugioh-phni-ja', 'channel' => 'yugioh-phni-ja', 'released_on' => 'yugioh-phni-ja', 'status' => 'yugioh-phni-ja'],
                                ],
                            ],
                            'sources' => ['name' => 'yugioh-phni-ja', 'language' => 'yugioh-ocg-ja-card-list', 'status' => 'yugioh-ocg-ja-card-list'],
                        ],
                    ],
                    'sources' => ['name' => 'yugioh-phni-ja', 'display_name.ja' => 'yugioh-phni-ja', 'display_name.en' => 'yugioh-phni-en'],
                ],
                [
                    'attributes' => [
                        'key' => 'legacy-of-destruction',
                        'name' => 'LEGACY OF DESTRUCTION',
                        'type' => 'main',
                        'sort_order' => 6,
                        'is_visible' => true,
                        'display_name' => ['ja' => 'LEGACY OF DESTRUCTION', 'en' => 'Legacy of Destruction'],
                    ],
                    'series' => null,
                    'editions' => [
                        [
                            'attributes' => ['name' => 'LEGACY OF DESTRUCTION', 'status' => 'released', 'is_visible' => true],
                            'language' => 'ja',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2024-01-27', 'status' => 'released'],
                                    'market' => 'japan',
                                    'sources' => ['market' => 'yugioh-lede-ja', 'channel' => 'yugioh-lede-ja', 'released_on' => 'yugioh-lede-ja', 'status' => 'yugioh-lede-ja'],
                                ],
                            ],
                            'sources' => ['name' => 'yugioh-lede-ja', 'language' => 'yugioh-ocg-ja-card-list', 'status' => 'yugioh-ocg-ja-card-list'],
                        ],
                    ],
                    'sources' => ['name' => 'yugioh-lede-ja', 'display_name.ja' => 'yugioh-lede-ja', 'display_name.en' => 'yugioh-lede-en'],
                ],
                [
                    'attributes' => [
                        'key' => 'the-infinite-forbidden',
                        'name' => 'INFINITE FORBIDDEN',
                        'type' => 'main',
                        'sort_order' => 7,
                        'is_visible' => true,
                        'display_name' => ['ja' => 'INFINITE FORBIDDEN', 'en' => 'The Infinite Forbidden'],
                    ],
                    'series' => null,
                    'editions' => [
                        [
                            'attributes' => ['name' => 'INFINITE FORBIDDEN', 'status' => 'released', 'is_visible' => true],
                            'language' => 'ja',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2024-04-27', 'status' => 'released'],
                                    'market' => 'japan',
                                    'sources' => ['market' => 'yugioh-info-ja', 'channel' => 'yugioh-info-ja', 'released_on' => 'yugioh-info-ja', 'status' => 'yugioh-info-ja'],
                                ],
                            ],
                            'sources' => ['name' => 'yugioh-info-ja', 'language' => 'yugioh-ocg-ja-card-list', 'status' => 'yugioh-ocg-ja-card-list'],
                        ],
                    ],
                    'sources' => ['name' => 'yugioh-info-ja', 'display_name.ja' => 'yugioh-info-ja', 'display_name.en' => 'yugioh-info-en'],
                ],
                [
                    'attributes' => [
                        'key' => 'rage-of-the-abyss',
                        'name' => 'RAGE OF THE ABYSS',
                        'type' => 'main',
                        'sort_order' => 8,
                        'is_visible' => true,
                        'display_name' => ['ja' => 'RAGE OF THE ABYSS', 'en' => 'Rage of the Abyss'],
                    ],
                    'series' => null,
                    'editions' => [
                        [
                            'attributes' => ['name' => 'RAGE OF THE ABYSS', 'status' => 'released', 'is_visible' => true],
                            'language' => 'ja',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2024-07-27', 'status' => 'released'],
                                    'market' => 'japan',
                                    'sources' => ['market' => 'yugioh-rota-ja', 'channel' => 'yugioh-rota-ja', 'released_on' => 'yugioh-rota-ja', 'status' => 'yugioh-rota-ja'],
                                ],
                            ],
                            'sources' => ['name' => 'yugioh-rota-ja', 'language' => 'yugioh-ocg-ja-card-list', 'status' => 'yugioh-ocg-ja-card-list'],
                        ],
                    ],
                    'sources' => ['name' => 'yugioh-rota-ja', 'display_name.ja' => 'yugioh-rota-ja', 'display_name.en' => 'yugioh-rota-en'],
                ],
                [
                    'attributes' => [
                        'key' => 'supreme-darkness',
                        'name' => 'SUPREME DARKNESS',
                        'type' => 'main',
                        'sort_order' => 9,
                        'is_visible' => true,
                        'display_name' => ['ja' => 'SUPREME DARKNESS', 'en' => 'Supreme Darkness'],
                    ],
                    'series' => null,
                    'editions' => [
                        [
                            'attributes' => ['name' => 'SUPREME DARKNESS', 'status' => 'released', 'is_visible' => true],
                            'language' => 'ja',
                            'asset' => null,
                            'releases' => [
                                [
                                    'attributes' => ['channel' => 'general', 'released_on' => '2024-10-26', 'status' => 'released'],
                                    'market' => 'japan',
                                    'sources' => ['market' => 'yugioh-suda-ja', 'channel' => 'yugioh-suda-ja', 'released_on' => 'yugioh-suda-ja', 'status' => 'yugioh-suda-ja'],
                                ],
                            ],
                            'sources' => ['name' => 'yugioh-suda-ja', 'language' => 'yugioh-ocg-ja-card-list', 'status' => 'yugioh-ocg-ja-card-list'],
                        ],
                    ],
                    'sources' => ['name' => 'yugioh-suda-ja', 'display_name.ja' => 'yugioh-suda-ja', 'display_name.en' => 'yugioh-suda-en'],
                ],
            ],
        ],
    ],
];
