<?php

namespace Database\Seeders;

use App\Actions\Catalog\SaveCatalogRecord;
use App\Actions\Games\SaveGame;
use App\Models\ExpansionEdition;
use App\Models\Game;
use App\Models\Language;
use App\Models\Market;
use App\Models\SeriesImage;
use App\Services\CatalogImages;
use App\Support\Localization\ContentLanguageUsage;
use Illuminate\Database\Connection;
use Illuminate\Database\Seeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

/**
 * @phpstan-type Attributes array<string, mixed>
 * @phpstan-type KeyedAttributes array{key: string, ...<string, mixed>}
 * @phpstan-type NamedSource array{url: ?string, consulted_on: string, note: string}
 * @phpstan-type ReleaseData array{attributes: array{channel: string, ...<string, mixed>}, market: string, sources: array{released_on: string, ...<string, string>}}
 * @phpstan-type EditionData array{attributes: Attributes, language: string, asset: ?string, releases: list<ReleaseData>}
 * @phpstan-type ExpansionData array{attributes: KeyedAttributes, series: ?string, editions: list<EditionData>}
 * @phpstan-type SeriesData array{attributes: KeyedAttributes, images?: array<string, string>}
 * @phpstan-type CatalogData array{attributes: KeyedAttributes, default_language: string, series: list<SeriesData>, expansions: list<ExpansionData>}
 * @phpstan-type GameData array{game: array{slug: string, ...<string, mixed>}, catalogs: list<CatalogData>}
 * @phpstan-type References array{languages: array<string, array{attributes: array{code: string, name: string}}>, markets: array<string, array{attributes: KeyedAttributes}>, games: list<string>}
 */
class TcgCatalogSeeder extends Seeder
{
    public function __construct(
        private readonly SaveGame $saveGame,
        private readonly SaveCatalogRecord $saveCatalog,
        private readonly CatalogImages $images,
    ) {}

    public function run(): void
    {
        /** @var References $data */
        $data = require __DIR__.'/data/tcg.php';
        /** @var array<string, NamedSource> $sources */
        $sources = require __DIR__.'/data/tcg-sources.php';

        DB::transaction(function (Connection $connection) use ($data, $sources): void {
            $connection->statement('SELECT pg_advisory_xact_lock(hashtext(?))', ['gamestackr:tcg-catalog-seed']);
            $this->call(ContentLanguagesSeeder::class);

            $languageKeys = array_keys($data['languages']);
            $keysByCode = array_combine(array_map(strtolower(...), $languageKeys), $languageKeys);
            $languages = [];
            foreach (ContentLanguageUsage::orderedCodes($languageKeys) as $code) {
                $key = $keysByCode[$code];
                $record = Language::query()->whereRaw('lower(code) = ?', [$code])->first() ?? new Language;
                $languages[$key] = $this->saveCatalog->handle($record, $data['languages'][$key]['attributes'])->id;
            }

            $markets = [];
            foreach ($data['markets'] as $key => $market) {
                $record = Market::query()->firstOrNew(['key' => $key]);
                $markets[$key] = $this->saveCatalog->handle($record, $market['attributes'])->id;
            }

            foreach ($data['games'] as $file) {
                /** @var GameData $game */
                $game = require __DIR__.'/data/'.$file;
                $this->seedGame($game, $languages, $markets, $sources);
            }
        });
    }

    /**
     * @param  GameData  $data
     * @param  array<string, int>  $languages
     * @param  array<string, int>  $markets
     * @param  array<string, NamedSource>  $sources
     */
    private function seedGame(array $data, array $languages, array $markets, array $sources): void
    {
        $game = Game::query()->firstOrNew(['slug' => $data['game']['slug']]);
        $game = $this->saveGame->handle($game, $data['game']);

        foreach ($data['catalogs'] as $catalogData) {
            $attributes = $catalogData['attributes'];
            $catalog = $this->saveCatalog->handle($game->catalogs()->firstOrNew(['key' => $attributes['key']]), [
                ...$attributes,
                'default_language_id' => $languages[$catalogData['default_language']],
            ]);

            $series = [];
            foreach ($catalogData['series'] as $seriesData) {
                $attributes = $seriesData['attributes'];
                $key = $attributes['key'];
                $record = $this->saveCatalog->handle($catalog->series()->firstOrNew(['key' => $key]), $attributes);
                $series[$key] = $record->id;

                foreach ($seriesData['images'] ?? [] as $language => $asset) {
                    $image = $record->images()->firstOrNew(['language_id' => $languages[$language]]);
                    $attributes = $this->imageAttributes($image, $asset);
                    if ($attributes !== []) {
                        $this->saveCatalog->handle($image, $attributes);
                    }
                }
            }

            foreach ($catalogData['expansions'] as $expansionData) {
                $attributes = $expansionData['attributes'];
                $expansion = $this->saveCatalog->handle($catalog->expansions()->firstOrNew(['key' => $attributes['key']]), [
                    ...$attributes,
                    'series_id' => $expansionData['series'] === null ? null : $series[$expansionData['series']],
                ]);

                foreach ($expansionData['editions'] as $editionData) {
                    $edition = $expansion->editions()->firstOrNew(['language_id' => $languages[$editionData['language']]]);
                    $edition = $this->saveCatalog->handle($edition, [
                        // Only a new record needs a fallback for a name absent from the reference.
                        ...($edition->exists ? [] : ['name' => '—']),
                        ...$editionData['attributes'],
                        ...$this->imageAttributes($edition, $editionData['asset']),
                    ]);

                    foreach ($editionData['releases'] as $releaseData) {
                        $attributes = $releaseData['attributes'];
                        $release = $edition->releases()->firstOrNew([
                            'market_id' => $markets[$releaseData['market']],
                            'channel' => $attributes['channel'],
                        ]);
                        $this->saveCatalog->handle($release, [
                            ...$attributes,
                            'source_url' => $sources[$releaseData['sources']['released_on']]['url'],
                        ]);
                    }
                }
            }
        }
    }

    /** @return array{image?: UploadedFile} */
    private function imageAttributes(ExpansionEdition|SeriesImage $record, ?string $asset): array
    {
        if ($asset === null) {
            return [];
        }

        $upload = new UploadedFile(__DIR__.'/assets/tcg-catalog/'.$asset, $asset, test: true);

        return $this->images->matchesStoredUpload($record->image, $upload) ? [] : ['image' => $upload];
    }
}
