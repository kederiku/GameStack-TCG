<?php

namespace Database\Seeders;

/**
 * Composes the documentary reference with previously verified product records.
 *
 * @phpstan-type ReleaseRow array{market: string, period?: string, date?: string, status?: string, source?: string}
 * @phpstan-type ReferenceRow array{key: string, series: string, type: string, names: array<string, string>, editions: array<string, ?string>, source: string, row: int, period?: string, codes?: array<string, string>, releases?: array<string, list<ReleaseRow>>, status?: string, context_source?: string}
 * @phpstan-type ReferenceCatalog array{key: string, names: array<string, string>, language: string, series: array<string, array<string, string>>, expansions: list<ReferenceRow>}
 */
final class PokemonCatalogData
{
    /** @return array<string, mixed> */
    public static function load(): array
    {
        $directory = __DIR__.'/data/tcg/pokemon';
        $curated = require $directory.'/curated.php';
        $catalogs = [];

        foreach (['international', 'japanese', 'korean', 'traditional-chinese', 'simplified-chinese', 'thai', 'indonesian'] as $key) {
            /** @var ReferenceCatalog $reference */
            $reference = require $directory.'/'.$key.'.php';
            $catalog = [
                'attributes' => self::attributes($key, $reference['names'], count($catalogs)),
                'default_language' => $reference['language'],
                'series' => [],
                'expansions' => [],
            ];

            foreach ($reference['series'] as $seriesKey => $names) {
                $catalog['series'][] = [
                    'attributes' => [...self::attributes($seriesKey, $names, count($catalog['series'])), 'type' => 'series'],
                    'sources' => ['name' => 'pokemon-doc-encyclopedia'],
                ];
            }

            foreach ($reference['expansions'] as $row) {
                $catalog['expansions'][] = self::expansion($row, count($catalog['expansions']));
            }

            $catalogs[$key] = $catalog;
        }

        foreach ($curated['catalogs'] as $catalog) {
            $key = $catalog['attributes']['key'];
            $reference = $catalogs[$key];
            $catalogs[$key] = [
                ...$reference,
                'attributes' => array_replace_recursive($reference['attributes'], $catalog['attributes']),
                'series' => self::mergeRecords($reference['series'], $catalog['series']),
                'expansions' => self::mergeRecords($reference['expansions'], $catalog['expansions']),
            ];
        }

        return [...$curated, 'catalogs' => array_values($catalogs)];
    }

    /**
     * @param  array<string, string>  $names
     * @return array<string, mixed>
     */
    private static function attributes(string $key, array $names, int $order): array
    {
        return ['key' => $key, 'name' => $names['fr'] ?? $names['en'], 'display_name' => $names, 'sort_order' => $order, 'is_visible' => true];
    }

    /**
     * @param  ReferenceRow  $row
     * @return array<string, mixed>
     */
    private static function expansion(array $row, int $order): array
    {
        $source = $row['source'];
        $attributes = [...self::attributes($row['key'], $row['names'], $order), 'type' => $row['type']];
        // An English-language date without a market cannot become a regional release.
        if (isset($row['period'])) {
            $attributes['description'] = [
                'fr' => 'Période de sortie anglophone indiquée dans la référence : '.$row['period'].'.',
                'en' => 'English-language release period recorded in the reference: '.$row['period'].'.',
            ];
        }
        $expansion = [
            'attributes' => $attributes,
            'series' => $row['series'],
            'editions' => [],
            'sources' => array_fill_keys(['name', 'series', ...array_map(fn (string $locale): string => 'display_name.'.$locale, array_keys($row['names']))], $source),
            'source_row' => $row['row'],
        ];
        if (isset($row['context_source'])) {
            $expansion['sources']['context'] = $row['context_source'];
        }

        foreach ($row['editions'] as $language => $name) {
            $edition = [
                // The model requires a name. A neutral unknown value must never
                // present an English reference title as a native official name.
                'attributes' => ['name' => $name ?? '—', 'status' => $row['status'] ?? 'released', 'is_visible' => true],
                'language' => $language,
                'asset' => null,
                'releases' => [],
                'sources' => array_fill_keys(['name', 'language', 'status'], $source),
            ];
            if (isset($row['codes'][$language])) {
                $edition['attributes']['official_code'] = $row['codes'][$language];
                $edition['sources']['official_code'] = $source;
            }
            foreach ($row['releases'][$language] ?? [] as $release) {
                $edition['releases'][] = [
                    'attributes' => [
                        'channel' => 'general',
                        'released_on' => $release['date'] ?? null,
                        'reference_announced_period' => $release['period'] ?? null,
                        'status' => $release['status'] ?? $row['status'] ?? 'released',
                    ],
                    'market' => $release['market'],
                    'sources' => array_fill_keys(['market', 'channel', 'released_on', 'status'], $release['source'] ?? $source),
                ];
            }
            $expansion['editions'][] = $edition;
        }

        return $expansion;
    }

    /**
     * Match lists by business identifiers, never by numeric array offsets.
     * Curated records retain their names, assets and exact release dates.
     *
     * @param  list<array<string, mixed>>  $reference
     * @param  list<array<string, mixed>>  $curated
     * @return list<array<string, mixed>>
     */
    private static function mergeRecords(array $reference, array $curated, string $identity = 'key'): array
    {
        $records = [];
        $keyOf = static fn (array $record): string => match ($identity) {
            'language' => $record['language'],
            'release' => $record['market'].'/'.$record['attributes']['channel'],
            default => $record['attributes']['key'],
        };
        foreach ($reference as $record) {
            $records[$keyOf($record)] = $record;
        }
        foreach ($curated as $record) {
            $key = $keyOf($record);
            $previous = $records[$key] ?? [];
            $merged = array_replace_recursive($previous, $record);
            if (isset($previous['attributes']['sort_order'])) {
                $merged['attributes']['sort_order'] = $previous['attributes']['sort_order'];
            }
            foreach (['editions' => 'language', 'releases' => 'release'] as $field => $childIdentity) {
                if (isset($record[$field])) {
                    $merged[$field] = self::mergeRecords($previous[$field] ?? [], $record[$field], $childIdentity);
                }
            }
            // An exact verified release supersedes a documentary month for this market/channel.
            if ($identity === 'release' && isset($record['attributes']['released_on'])) {
                $merged['attributes']['reference_announced_period'] = $record['attributes']['reference_announced_period'] ?? null;
            }
            $records[$key] = $merged;
        }

        return array_values($records);
    }
}
