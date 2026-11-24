<?php

// Synthetic records only. Shared paths exercise preservation without filesystem access.
return [
    'languages' => [
        ['id' => 1, 'code' => 'FR', 'name' => 'Français'],
        ['id' => 2, 'code' => 'en', 'name' => 'English'],
        ['id' => 3, 'code' => 'ja', 'name' => '日本語'],
        ['id' => 4, 'code' => 'zh-hant', 'name' => 'Chinese'],
        ['id' => 5, 'code' => 'pt-br', 'name' => 'Portuguese'],
    ],
    'games' => [['id' => 10, 'name' => 'Jeu renommé', 'slug' => 'renamed-game', 'description' => 'Unknown language', 'image' => 'games/shared.avif']],
    'markets' => [['id' => 11, 'key' => 'custom', 'name' => '独自地域']],
    'catalogs' => [['id' => 20, 'game_id' => 10, 'default_language_id' => 1, 'key' => 'international', 'name' => 'カタログ', 'description' => null]],
    'series' => [['id' => 30, 'catalog_id' => 20, 'key' => 'original', 'name' => '参照シリーズ', 'type' => 'main']],
    'expansions' => [['id' => 42, 'catalog_id' => 20, 'series_id' => 30, 'key' => 'set', 'name' => '参考名', 'type' => 'main']],
    'series_localizations' => [
        ['id' => 50, 'series_id' => 30, 'language_id' => 1, 'name' => 'Série FR', 'description' => null, 'image' => 'catalog/shared.avif'],
        ['id' => 51, 'series_id' => 30, 'language_id' => 2, 'name' => 'Series EN', 'description' => 'Series description', 'image' => 'catalog/shared.avif'],
    ],
    'expansion_localizations' => [
        ['id' => 60, 'expansion_id' => 42, 'language_id' => 1, 'name' => 'Nom FR', 'description' => null],
        ['id' => 61, 'expansion_id' => 42, 'language_id' => 2, 'name' => 'English name', 'description' => 'English description'],
        ['id' => 62, 'expansion_id' => 42, 'language_id' => 4, 'name' => '繁體', 'description' => null],
        ['id' => 63, 'expansion_id' => 42, 'language_id' => 5, 'name' => 'Português', 'description' => null],
    ],
    'expansion_editions' => [['id' => 70, 'expansion_id' => 42, 'language_id' => 3, 'name' => '日本語版', 'official_code' => 'JP-1', 'image' => 'catalog/shared.avif', 'status' => 'released']],
    'expansion_releases' => [['id' => 80, 'edition_id' => 70, 'market_id' => 11, 'channel' => 'general', 'released_on' => '2020-01-02', 'announced_period' => 'Printemps', 'status' => 'released', 'source_url' => 'https://example.test/release']],
    'expansion_relations' => [],
    'catalog_example_bindings' => [
        ['manifest_key' => 'game:old-game', 'record_type' => 'game', 'record_id' => 10],
        ['manifest_key' => 'series:old-game/international/original', 'record_type' => 'series', 'record_id' => 30],
        ['manifest_key' => 'expansion:old-game/international/set', 'record_type' => 'expansion', 'record_id' => 42],
        ['manifest_key' => 'series-localization:old-game/international/original/fr', 'record_type' => 'series-localization', 'record_id' => 50],
        ['manifest_key' => 'series-localization:old-game/international/original/en', 'record_type' => 'series-localization', 'record_id' => 51],
        ['manifest_key' => 'expansion-localization:old-game/international/set/fr', 'record_type' => 'expansion-localization', 'record_id' => 60],
        ['manifest_key' => 'expansion-localization:old-game/international/set/en', 'record_type' => 'expansion-localization', 'record_id' => 61],
        ['manifest_key' => 'expansion-localization:old-game/international/set/ja', 'record_type' => 'expansion-localization', 'record_id' => 999],
    ],
];
