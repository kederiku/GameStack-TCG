<?php

namespace App\Actions\Catalog;

use App\Actions\Games\SaveGame;
use App\Models\Catalog;
use App\Models\CatalogExampleBinding;
use App\Models\CatalogRecord;
use App\Models\Expansion;
use App\Models\ExpansionEdition;
use App\Models\ExpansionLocalization;
use App\Models\ExpansionRelation;
use App\Models\ExpansionRelease;
use App\Models\Game;
use App\Models\Language;
use App\Models\Market;
use App\Models\Series;
use App\Models\SeriesLocalization;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use RuntimeException;

/**
 * @phpstan-type ExampleRow array{type: string, attributes: array<string, string|int|bool|null>, references: array<string, string>, asset: ?string, facts: array<string, string>}
 */
class SeedCatalogExamples
{
    public function __construct(
        private readonly SaveCatalogRecord $saveCatalog,
        private readonly SaveGame $saveGame,
    ) {}

    /** @return array{created: int, preserved: int, recreated: int} */
    public function handle(): array
    {
        /** @var array{records: array<string, ExampleRow>} $manifest */
        $manifest = require database_path('seeders/data/tcg-catalog-examples.php');
        /** @var array<string, array{url: string, consulted_on: string, note: string}> $sources */
        $sources = require database_path('seeders/data/tcg-catalog-sources.php');

        return DB::transaction(function () use ($manifest, $sources): array {
            // Prevent two explicit seed runs from resolving the same absent binding concurrently.
            DB::select('select pg_advisory_xact_lock(20261115, 150000)');
            $counts = ['created' => 0, 'preserved' => 0, 'recreated' => 0];
            $resolved = [];

            foreach ($manifest['records'] as $key => $row) {
                $attributes = $row['attributes'];
                foreach ($row['references'] as $column => $reference) {
                    if (! isset($resolved[$reference])) {
                        throw $this->conflict($key, 'dépendance manquante : '.$reference);
                    }

                    $attributes[$column] = (int) $resolved[$reference]->getKey();
                }

                foreach ($row['facts'] as $field => $source) {
                    if (! isset($sources[$source])) {
                        throw $this->conflict($key, 'source inconnue pour '.$field.' : '.$source);
                    }
                }

                $sourceField = match ($row['type']) {
                    'release' => 'released_on',
                    'relation' => 'source_expansion',
                    default => null,
                };
                if ($sourceField !== null) {
                    $source = $row['facts'][$sourceField] ?? throw $this->conflict($key, 'source manquante pour '.$sourceField);
                    $attributes['source_url'] = $sources[$source]['url'];
                }

                try {
                    [$record, $outcome] = $this->resolve($key, $row['type'], $attributes, $row['asset']);
                } catch (UniqueConstraintViolationException|ValidationException $exception) {
                    throw new RuntimeException('Exemple '.$key.' : '.$exception->getMessage(), previous: $exception);
                }

                $resolved[$key] = $record;
                $counts[$outcome]++;
            }

            return $counts;
        });
    }

    /**
     * @param  array<string, string|int|bool|null>  $attributes
     * @return array{Game|CatalogRecord, 'created'|'preserved'|'recreated'}
     */
    private function resolve(string $key, string $type, array $attributes, ?string $asset): array
    {
        [$model, $identityFields] = match ($type) {
            'game' => [new Game, ['slug']],
            'language' => [new Language, ['code']],
            'market' => [new Market, ['key']],
            'catalog' => [new Catalog, ['game_id', 'key']],
            'series' => [new Series, ['catalog_id', 'key']],
            'series-localization' => [new SeriesLocalization, ['series_id', 'language_id']],
            'expansion' => [new Expansion, ['catalog_id', 'key']],
            'expansion-localization' => [new ExpansionLocalization, ['expansion_id', 'language_id']],
            'edition' => [new ExpansionEdition, ['expansion_id', 'language_id']],
            'release' => [new ExpansionRelease, ['edition_id', 'market_id', 'channel']],
            'relation' => [new ExpansionRelation, ['source_expansion_id', 'target_expansion_id', 'type']],
            default => throw $this->conflict($key, 'type de fiche inconnu'),
        };

        if ($model instanceof ExpansionRelation && $attributes['type'] === 'related_to'
            && $attributes['source_expansion_id'] > $attributes['target_expansion_id']) {
            [$attributes['source_expansion_id'], $attributes['target_expansion_id']] = [$attributes['target_expansion_id'], $attributes['source_expansion_id']];
        }

        $identity = array_intersect_key($attributes, array_flip($identityFields));
        $binding = CatalogExampleBinding::query()->lockForUpdate()->find($key);
        if ($binding !== null && $binding->record_type !== $type) {
            throw $this->conflict($key, 'la liaison enregistrée désigne un autre type de fiche');
        }

        $record = $binding === null ? null : $model->newQuery()->lockForUpdate()->find($binding->record_id);
        if ($record !== null) {
            // Game slugs can change. All other identity fields are immutable in the domain.
            if (! $record instanceof Game) {
                foreach ($identity as $field => $value) {
                    $matches = $record instanceof Language
                        ? strcasecmp($record->code, (string) $value) === 0
                        : $record->getAttribute($field) === $value;
                    if (! $matches) {
                        throw $this->conflict($key, 'la liaison enregistrée ne correspond pas à '.$field);
                    }
                }
            }

            return [$record, 'preserved'];
        }

        $query = $model->newQuery()->lockForUpdate();
        if ($model instanceof Language) {
            $query->whereRaw('lower(code) = lower(?)', [$attributes['code']]);
        } else {
            $query->where($identity);
        }

        $candidates = $query->limit(2)->get();
        if ($candidates->count() > 1) {
            throw $this->conflict($key, 'plusieurs fiches correspondent à la clé dans son parent');
        }

        $record = $candidates->first();
        if ($model instanceof Game && Game::query()->where('slug', '!=', $attributes['slug'])
            ->whereRaw('lower(name) = lower(?)', [$attributes['name']])->exists()) {
            throw $this->conflict($key, 'un jeu du même nom existe sous une autre URL ; vérifiez son identité');
        }

        $outcome = 'preserved';
        if ($record === null) {
            $record = $this->create($key, $model, $attributes, $asset);
            $outcome = $binding === null ? 'created' : 'recreated';
        }

        $id = (int) $record->getKey();
        if (CatalogExampleBinding::query()->where('record_type', $type)->where('record_id', $id)->exists()) {
            throw $this->conflict($key, 'la fiche correspond déjà à une autre clé du manifeste');
        }

        $binding ??= new CatalogExampleBinding(['manifest_key' => $key, 'record_type' => $type]);
        $binding->record_id = $id;
        if (! $binding->saveOrFail()) {
            throw $this->conflict($key, 'la liaison n’a pas pu être enregistrée');
        }

        return [$record, $outcome];
    }

    /** @param array<string, string|int|bool|null> $attributes */
    private function create(string $key, Game|CatalogRecord $model, array $attributes, ?string $asset): Game|CatalogRecord
    {
        if ($model instanceof ExpansionRelease && $attributes['status'] === 'released') {
            $edition = ExpansionEdition::query()->lockForUpdate()->findOrFail($attributes['edition_id']);
            if ($edition->status !== 'released') {
                throw $this->conflict($key, 'cette sortie publiée changerait le statut de l’édition « '.$edition->name.' » ('.$edition->status.'). Vérifiez l’édition et le lancement avant de relancer.');
            }
        }

        $data = $attributes;
        if ($asset !== null) {
            $path = database_path('seeders/assets/tcg-catalog/'.$asset);
            if (! preg_match('/\A[a-z0-9-]+\.avif\z/', $asset) || ! is_file($path)) {
                throw $this->conflict($key, 'visuel local introuvable ou invalide');
            }

            $data['image'] = new UploadedFile($path, $asset, 'image/avif', test: true);
        }

        return $model instanceof Game ? $this->saveGame->handle($model, $data) : $this->saveCatalog->handle($model, $data);
    }

    private function conflict(string $key, string $message): RuntimeException
    {
        return new RuntimeException('Exemple '.$key.' : '.$message.'.');
    }
}
