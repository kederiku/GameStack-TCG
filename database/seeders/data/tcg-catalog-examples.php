<?php

// Clés, rattachements, types, ordres, noms administratifs des catalogues et choix
// de visibilité sont éditoriaux. Les champs factuels indiqués dans "facts" pointent
// vers tcg-catalog-sources.php. Les clés du manifeste survivent aux renommages.
$records = [];
$add = function (string $type, string $key, array $attributes, array $references = [], array $facts = [], ?string $asset = null) use (&$records): string {
    $manifestKey = $type.':'.$key;
    if (isset($records[$manifestKey])) {
        throw new LogicException('Clé d’exemple dupliquée : '.$manifestKey);
    }

    $records[$manifestKey] = compact('type', 'attributes', 'references', 'facts', 'asset');

    return $manifestKey;
};

foreach (['en' => 'Anglais', 'fr' => 'Français', 'ja' => 'Japonais', 'ko' => 'Coréen', 'zh-Hant' => 'Chinois traditionnel', 'zh-Hans' => 'Chinois simplifié'] as $code => $name) {
    $add('language', $code, ['code' => $code, 'name' => $name], facts: ['code' => 'language-tags']);
}

foreach ([
    'france' => 'France',
    'united-states' => 'États-Unis',
    'japan' => 'Japon',
    'south-korea' => 'Corée du Sud',
    'taiwan' => 'Taïwan',
    'mainland-china' => 'Chine continentale',
    'worldwide' => 'Monde',
] as $key => $name) {
    // Marchés choisis pour les sorties documentées, sans déduire de pays d’une langue.
    $add('market', $key, ['key' => $key, 'name' => $name]);
}

$catalog = function (string $game, string $key, string $name, string $language) use ($add): void {
    $add('catalog', "$game/$key", ['key' => $key, 'name' => $name, 'is_visible' => false], ['game_id' => 'game:'.$game, 'default_language_id' => 'language:'.$language]);
};

$series = function (string $catalog, string $key, string $name, string $type, array $names) use ($add): void {
    $first = reset($names);
    $id = $add(
        'series', "$catalog/$key", ['key' => $key, 'name' => $name, 'type' => $type, 'is_visible' => false],
        ['catalog_id' => 'catalog:'.$catalog], ['name' => $first[1]],
    );
    foreach ($names as $language => [$localizedName, $source, $asset, $assetSource]) {
        $facts = ['name' => $source];
        if ($assetSource !== null) {
            $facts['image'] = $assetSource;
        }
        $add('series-localization', "$catalog/$key/$language", ['name' => $localizedName], ['series_id' => $id, 'language_id' => 'language:'.$language], $facts, $asset);
    }
};

$edition = fn (string $name, string $source, array $releases = [], ?string $code = null, ?string $asset = null, ?string $assetSource = null, array $facts = []): array => [
    'name' => $name, 'code' => $code, 'asset' => $asset, 'releases' => $releases,
    'facts' => array_replace(['name' => $source, 'print_language' => $source, 'status' => $source], $code === null ? [] : ['official_code' => $source], $assetSource === null ? [] : ['image' => $assetSource], $facts),
];

$release = fn (string $market, string $date, string $source, string $channel = 'general', ?string $marketSource = null): array => [
    'market' => $market, 'date' => $date, 'channel' => $channel, 'source' => $source, 'market_source' => $marketSource ?? $source,
];

$set = function (string $catalog, string $key, array $editions, ?string $series = null, string $type = 'main') use ($add): void {
    $first = reset($editions);
    $path = "$catalog/$key";
    $references = ['catalog_id' => 'catalog:'.$catalog];
    if ($series !== null) {
        $references['series_id'] = "series:$catalog/$series";
    }
    $id = $add('expansion', $path, ['key' => $key, 'name' => $first['name'], 'type' => $type, 'is_visible' => false], $references, ['name' => $first['facts']['name']]);

    foreach ($editions as $language => $edition) {
        $name = $edition['name'];
        $references = ['expansion_id' => $id, 'language_id' => 'language:'.$language];
        if (count($editions) > 1) {
            $add('expansion-localization', "$path/$language", ['name' => $name], $references, ['name' => $edition['facts']['name']]);
        }

        $editionId = $add(
            'edition', "$path/$language",
            ['name' => $name, 'official_code' => $edition['code'], 'status' => 'released', 'is_visible' => false],
            $references, $edition['facts'], $edition['asset'],
        );

        foreach ($edition['releases'] as $release) {
            $market = $release['market'];
            $channel = $release['channel'];
            $add(
                'release', "$path/$language/$market/$channel",
                ['channel' => $channel, 'released_on' => $release['date'], 'status' => 'released'],
                ['edition_id' => $editionId, 'market_id' => 'market:'.$market],
                ['market' => $release['market_source'], 'channel' => $release['market_source'], 'released_on' => $release['source'], 'status' => $release['source']],
            );
        }
    }
};

// Pokémon : un catalogue par marché éditorial quand les ensembles sont distincts.
$add('game', 'pokemon', ['slug' => 'pokemon', 'name' => 'Pokémon', 'is_visible' => false], facts: ['name' => 'P1']);
$catalog('pokemon', 'international', 'International', 'fr');
$catalog('pokemon', 'japanese', 'Japonais', 'ja');
$catalog('pokemon', 'korean', 'Coréen', 'ko');
$catalog('pokemon', 'traditional-chinese', 'Chinois traditionnel', 'zh-Hant');
$catalog('pokemon', 'simplified-chinese', 'Chinois simplifié', 'zh-Hans');
$series('pokemon/international', 'scarlet-violet', 'Écarlate et Violet', 'series', [
    'fr' => ['Écarlate et Violet', 'P1', 'scarlet-violet-fr.avif', 'pokemon-logo-fr'],
    'en' => ['Scarlet & Violet', 'P2', 'scarlet-violet-en.avif', 'pokemon-logo-en'],
]);
$series('pokemon/japanese', 'scarlet-violet', 'スカーレット＆バイオレット', 'series', ['ja' => ['スカーレット＆バイオレット', 'P5', null, null]]);
$series('pokemon/korean', 'scarlet-violet', '스칼렛&바이올렛', 'series', ['ko' => ['스칼렛&바이올렛', 'P7', null, null]]);
$series('pokemon/traditional-chinese', 'scarlet-violet', '朱＆紫', 'series', ['zh-Hant' => ['朱＆紫', 'P8', null, null]]);
$series('pokemon/traditional-chinese', 'sun-moon', '太陽 & 月亮', 'series', ['zh-Hant' => ['太陽 & 月亮', 'P9', null, null]]);
$series('pokemon/simplified-chinese', 'scarlet-violet', '朱&紫', 'series', ['zh-Hans' => ['朱&紫', 'P10', null, null]]);

$set('pokemon/international', 'scarlet-violet', [
    'fr' => $edition('Écarlate et Violet', 'P1', [$release('france', '2023-03-31', 'P1')], asset: 'scarlet-violet-fr.avif', assetSource: 'pokemon-logo-fr'),
    'en' => $edition('Scarlet & Violet', 'P2', [$release('united-states', '2023-03-31', 'P2')], asset: 'scarlet-violet-en.avif', assetSource: 'pokemon-logo-en'),
], 'scarlet-violet');
$set('pokemon/international', '151', [
    'fr' => $edition('Écarlate et Violet – 151', 'P3', [$release('france', '2023-09-22', 'P3')]),
    'en' => $edition('Scarlet & Violet—151', 'P4', [$release('united-states', '2023-09-22', 'P4')]),
], 'scarlet-violet', 'special');
$set('pokemon/japanese', 'scarlet-ex', ['ja' => $edition('スカーレットex', 'P5', [$release('japan', '2023-01-20', 'P5')])], 'scarlet-violet');
$set('pokemon/japanese', 'violet-ex', ['ja' => $edition('バイオレットex', 'P5', [$release('japan', '2023-01-20', 'P5')])], 'scarlet-violet');
$set('pokemon/japanese', '151', ['ja' => $edition('ポケモンカード151', 'P6', [$release('japan', '2023-06-16', 'P6')])], 'scarlet-violet', 'special');
$set('pokemon/korean', '151', ['ko' => $edition('포켓몬 카드 151', 'P7', [$release('south-korea', '2023-07-28', 'P7')])], 'scarlet-violet', 'special');
$set('pokemon/traditional-chinese', '151', ['zh-Hant' => $edition('寶可夢卡牌151', 'P8', [$release('taiwan', '2023-06-30', 'P8')])], 'scarlet-violet', 'special');
$set('pokemon/traditional-chinese', 'set-b', ['zh-Hant' => $edition('眾星雲集組合篇 SET B', 'P9')], 'sun-moon', 'special');
$set('pokemon/simplified-chinese', '151-jing', ['zh-Hans' => $edition('收集啦151 惊', 'P10', [$release('mainland-china', '2025-07-18', 'P10')])], 'scarlet-violet', 'special');
$set('pokemon/simplified-chinese', '151-ju', ['zh-Hans' => $edition('收集啦151 聚', 'P10', [$release('mainland-china', '2025-10-17', 'P10')])], 'scarlet-violet', 'special');
$add('expansion-localization', 'pokemon/japanese/151/fr', [
    'name' => 'Pokémon 151 (Japon)',
    'description' => 'Présentation française rédigée pour le site. Cet ensemble japonais ne désigne pas une édition imprimée en français.',
], ['expansion_id' => 'expansion:pokemon/japanese/151', 'language_id' => 'language:fr'], ['ensemble décrit' => 'P6']);

$add('game', 'magic', ['slug' => 'magic', 'name' => 'Magic: The Gathering', 'is_visible' => false], facts: ['name' => 'M2']);
$catalog('magic', 'international', 'International', 'en');
$series('magic/international', 'khans-of-tarkir', 'Khans of Tarkir', 'block', ['en' => ['Khans of Tarkir', 'M1', null, null]]);
$set('magic/international', 'khans-of-tarkir', ['en' => $edition('Khans of Tarkir', 'M1', facts: ['status' => 'magic-tarkir-releases'])], 'khans-of-tarkir');
$set('magic/international', 'fate-reforged', ['en' => $edition('Fate Reforged', 'M1', facts: ['status' => 'magic-tarkir-releases'])], 'khans-of-tarkir');
$set('magic/international', 'dragons-of-tarkir', ['en' => $edition('Dragons of Tarkir', 'M1', facts: ['status' => 'magic-tarkir-releases'])], 'khans-of-tarkir');
$set('magic/international', 'foundations', ['en' => $edition('Foundations', 'M2', [$release('united-states', '2024-11-15', 'magic-foundations-release', marketSource: 'M2')], facts: ['status' => 'magic-foundations-release'])]);

$add('game', 'one-piece', ['slug' => 'one-piece', 'name' => 'One Piece Card Game', 'is_visible' => false], facts: ['name' => 'O2']);
$catalog('one-piece', 'japanese', 'Japonais', 'ja');
$catalog('one-piece', 'international', 'International', 'en');
$set('one-piece/japanese', 'romance-dawn', ['ja' => $edition('ROMANCE DAWN', 'O1', [$release('japan', '2022-07-22', 'O1')], 'OP-01')]);
$set('one-piece/international', 'romance-dawn', ['en' => $edition('ROMANCE DAWN', 'O2', [$release('united-states', '2022-12-02', 'O2')], 'OP01')]);

$add('game', 'yu-gi-oh', ['slug' => 'yu-gi-oh', 'name' => 'Yu-Gi-Oh!', 'is_visible' => false], facts: ['name' => 'Y3']);
$catalog('yu-gi-oh', 'ocg', 'OCG japonais', 'ja');
$catalog('yu-gi-oh', 'tcg', 'TCG', 'fr');
$set('yu-gi-oh/ocg', 'darkwing-blast', ['ja' => $edition('DARKWING BLAST', 'Y1', [$release('japan', '2022-07-16', 'Y1')])]);
$set('yu-gi-oh/tcg', 'darkwing-blast', [
    'fr' => $edition('Explosion de l’Aile des Ténèbres', 'Y2', [$release('france', '2022-10-20', 'Y2')]),
    'en' => $edition('Darkwing Blast', 'Y3', [
        $release('united-states', '2022-10-21', 'Y3'),
        $release('united-states', '2022-10-19', 'Y3', 'specialist'),
    ]),
]);

$add('game', 'lorcana', ['slug' => 'lorcana', 'name' => 'Disney Lorcana', 'is_visible' => false], facts: ['name' => 'L1']);
$catalog('lorcana', 'international', 'International', 'fr');
$set('lorcana/international', 'first-chapter', [
    'fr' => $edition('Premier Chapitre', 'L1', [$release('france', '2023-09-01', 'L1')]),
    'en' => $edition('The First Chapter', 'L2', [$release('united-states', '2023-09-01', 'L2')]),
]);

$add('game', 'cyberpunk', ['slug' => 'cyberpunk', 'name' => 'Cyberpunk TCG', 'is_visible' => false], facts: ['name' => 'cyberpunk-release']);
$catalog('cyberpunk', 'international', 'International', 'en');
$set('cyberpunk/international', 'welcome-to-night-city', [
    'en' => $edition('Welcome to Night City', 'cyberpunk-print', [$release('worldwide', '2026-11-06', 'cyberpunk-release')], facts: ['status' => 'cyberpunk-release']),
]);

$add('game', 'flesh-and-blood', ['slug' => 'flesh-and-blood', 'name' => 'Flesh and Blood', 'is_visible' => false], facts: ['name' => 'fab-wtr']);
$catalog('flesh-and-blood', 'international', 'International', 'en');
$set('flesh-and-blood/international', 'welcome-to-rathe', [
    'en' => $edition('Welcome to Rathe', 'fab-wtr', [$release('united-states', '2019-10-11', 'fab-wtr-date', 'specialist', 'fab-wtr')]),
]);
$set('flesh-and-blood/international', 'history-pack-1', [
    'en' => $edition('History Pack 1', 'fab-history', [$release('united-states', '2022-05-06', 'fab-history', 'specialist')]),
], type: 'reprint');

$add('game', 'riftbound', ['slug' => 'riftbound', 'name' => 'Riftbound', 'is_visible' => false], facts: ['name' => 'riftbound-sets']);
$catalog('riftbound', 'international', 'International', 'en');
$set('riftbound/international', 'origins', [
    'en' => $edition('Origins', 'riftbound-sets', code: 'OGN', facts: ['print_language' => 'riftbound-release', 'status' => 'riftbound-launch']),
]);
$set('riftbound/international', 'proving-grounds', [
    'en' => $edition('Proving Grounds', 'riftbound-sets', code: 'OGS', facts: ['print_language' => 'riftbound-release', 'status' => 'riftbound-launch']),
], type: 'deck');

// Le slug historique du jeu existant reste inchangé.
$add('game', 'naturo-mythos', ['slug' => 'naturo-mythos', 'name' => 'Naruto Mythos TCG', 'is_visible' => false], facts: ['name' => 'mythos-set']);
$catalog('naturo-mythos', 'international', 'International', 'en');
$set('naturo-mythos/international', 'konoha-shido', [
    'en' => $edition('Konoha Shidō', 'mythos-set', facts: ['print_language' => 'mythos-language', 'status' => 'mythos-release']),
]);

$add('game', 'final-fantasy-tcg', ['slug' => 'final-fantasy-tcg', 'name' => 'Final Fantasy TCG', 'is_visible' => false], facts: ['name' => 'ff-chapter']);
$catalog('final-fantasy-tcg', 'chapter', 'Chapter', 'ja');
$catalog('final-fantasy-tcg', 'opus', 'Opus', 'en');
$set('final-fantasy-tcg/chapter', 'chapter-i', [
    'ja' => $edition('Chapter I', 'ff-chapter-i', [$release('japan', '2011-02-25', 'ff-chapter-i')], facts: ['print_language' => 'ff-chapter']),
]);
$set('final-fantasy-tcg/opus', 'dreamlike-oceans', ['en' => $edition('Dreamlike Oceans', 'ff-dreamlike')]);

$add('game', 'star-wars-unlimited', ['slug' => 'star-wars-unlimited', 'name' => 'Star Wars: Unlimited', 'is_visible' => false], facts: ['name' => 'swu-en']);
$catalog('star-wars-unlimited', 'international', 'International', 'fr');
$set('star-wars-unlimited/international', 'spark-of-rebellion', [
    'fr' => $edition('Étincelle de Rébellion', 'swu-fr', [$release('worldwide', '2024-03-08', 'swu-global-release')], facts: ['print_language' => 'swu-global-release']),
    'en' => $edition('Spark of Rebellion', 'swu-en', [$release('worldwide', '2024-03-08', 'swu-global-release')], facts: ['print_language' => 'swu-global-release']),
]);

$add('game', 'altered', ['slug' => 'altered', 'name' => 'Altered', 'is_visible' => false], facts: ['name' => 'altered-physical']);
$catalog('altered', 'international', 'International', 'en');
$set('altered/international', 'beyond-the-gates', [
    'en' => $edition('Beyond the Gates', 'altered-physical', [$release('worldwide', '2024-09-13', 'altered-physical')], facts: ['print_language' => 'altered-language']),
]);

// Rapprochements éditoriaux : deux sources attestent les ensembles comparés,
// sans affirmer l’équivalence ou la reprise de leur contenu.
$relate = function (string $key, string $left, string $right, string $sourceLeft, string $sourceRight) use ($add): void {
    $add('relation', $key, [
        'type' => 'related_to',
        'notes' => 'Rapprochement de catalogage entre ces ensembles. Les listes de cartes ne sont pas déclarées identiques.',
    ], ['source_expansion_id' => 'expansion:'.$left, 'target_expansion_id' => 'expansion:'.$right],
        ['source_expansion' => $sourceLeft, 'target_expansion' => $sourceRight]);
};

foreach ([
    ['international/151', 'P4'],
    ['korean/151', 'P7'],
    ['traditional-chinese/151', 'P8'],
    ['simplified-chinese/151-jing', 'P10'],
    ['simplified-chinese/151-ju', 'P10'],
] as [$path, $source]) {
    $relate('pokemon/151-japan/'.$path, 'pokemon/japanese/151', 'pokemon/'.$path, 'P6', $source);
}
$relate('one-piece/romance-dawn', 'one-piece/japanese/romance-dawn', 'one-piece/international/romance-dawn', 'O1', 'O2');
$relate('yu-gi-oh/darkwing-blast', 'yu-gi-oh/ocg/darkwing-blast', 'yu-gi-oh/tcg/darkwing-blast', 'Y1', 'Y3');

return [
    'records' => $records,
    'notes' => [
        'naruto-card-game' => [
            'announcement' => 'Jeu de cartes Naruto de Bandai annoncé pour juin 2027.',
            'source' => 'bandai-naruto',
            'limite' => 'La langue d’impression n’est pas confirmée dans la source retenue. Cette note ne génère aucune fiche et ne change pas le jeu existant.',
        ],
    ],
];
