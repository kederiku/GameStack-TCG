<?php

namespace App\Support\Catalog;

use App\Models\Catalog;
use App\Models\Expansion;
use App\Models\ExpansionEdition;
use App\Models\ExpansionRelation;
use App\Models\ExpansionRelease;
use App\Models\Game;
use App\Models\Language;
use App\Support\GameSlug;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class PublicCatalog
{
    public function __construct(private PublicCatalogPresentation $presentation) {}

    /** @return Builder<Expansion> */
    private function visible(): Builder
    {
        return Expansion::query()->where('expansions.is_visible', true)
            ->whereHas('catalog', fn (Builder $query) => $query->where('is_visible', true)
                ->whereHas('game', fn (Builder $games) => $games->where('is_visible', true)->whereNotIn('slug', GameSlug::reserved())))
            ->where(fn (Builder $query) => $query->whereNull('series_id')->orWhereHas('series', fn (Builder $series) => $series->where('is_visible', true)));
    }

    /** @return array<string, mixed> */
    public function index(Game $game, Request $request): array
    {
        $base = $this->visible()->whereHas('catalog', fn (Builder $query) => $query->where('game_id', $game->id));
        $catalogs = Catalog::query()->where('game_id', $game->id)->where('is_visible', true)
            ->orderBy('sort_order')->orderBy('id')->get();
        $counts = (clone $base)->selectRaw('catalog_id, count(*) as total')->groupBy('catalog_id')->pluck('total', 'catalog_id');
        $catalog = self::input($request, 'catalog', '*');
        $catalog = $catalogs->contains('key', $catalog) ? $catalog : '*';
        $language = CatalogRules::languageCode(mb_substr(self::input($request, 'language', '*'), 0, 255));
        $type = self::input($request, 'type', 'all');
        $sort = self::input($request, 'sort', 'recent');
        $filters = [
            'catalog' => $catalog,
            'language' => $language,
            'type' => in_array($type, Expansion::TYPES, true) ? $type : 'all',
            'sort' => in_array($sort, ['recent', 'oldest', 'name'], true) ? $sort : 'recent',
            'series' => $catalog !== '*' ? self::input($request, 'series') : '',
            'q' => mb_substr(trim(self::input($request, 'q')), 0, 150),
        ];

        $query = (clone $base)->when($catalog !== '*', fn (Builder $query) => $query->whereHas('catalog', fn (Builder $catalogs) => $catalogs->where('key', $catalog)));
        $languageExpansionIds = (clone $query)->select('expansions.id');
        $languages = Language::query()->whereIn('id', ExpansionEdition::query()->select('language_id')
            ->where('is_visible', true)->whereIn('expansion_id', $languageExpansionIds))->orderBy('code')->get(['code', 'name']);

        $query->when($filters['language'] !== '*', fn (Builder $query) => $query->whereHas('editions', fn (Builder $editions) => $editions
            ->where('is_visible', true)->whereHas('language', fn (Builder $languages) => $languages->where('code', $filters['language']))))
            ->when($filters['type'] !== 'all', fn (Builder $query) => $query->where('type', $filters['type']))
            ->when($filters['series'] !== '', fn (Builder $query) => $query->whereHas('series', fn (Builder $series) => $series->where('key', $filters['series'])));
        if ($filters['q'] !== '') {
            $this->search($query, $filters['q']);
        }

        $firstRelease = ExpansionRelease::query()->selectRaw('min(released_on)')
            ->where('expansion_releases.status', '!=', 'cancelled')
            ->join('expansion_editions', 'expansion_editions.id', '=', 'expansion_releases.edition_id')
            ->where('expansion_editions.is_visible', true)
            ->whereColumn('expansion_editions.expansion_id', 'expansions.id')
            ->when($filters['language'] !== '*', fn (Builder $query) => $query
                ->whereIn('expansion_editions.language_id', Language::query()->select('id')->where('code', $filters['language'])));
        $query->select('expansions.*')->selectSub($firstRelease, 'first_release_on');
        if ($filters['sort'] === 'name') {
            $query->orderByRaw('coalesce(
                (select el.name from expansion_localizations el join languages l on l.id = el.language_id
                    where el.expansion_id = expansions.id and l.code = ? limit 1),
                (select el.name from expansion_localizations el join catalogs c on c.default_language_id = el.language_id
                    where el.expansion_id = expansions.id and c.id = expansions.catalog_id limit 1),
                expansions.name)', [app()->getLocale()]);
        } else {
            $query->orderByRaw('first_release_on '.($filters['sort'] === 'oldest' ? 'ASC' : 'DESC').' NULLS LAST');
        }
        $total = (clone $query)->toBase()->getCountForPagination();
        $lastPage = max(1, (int) ceil($total / 36));
        $page = min($lastPage, max(1, (int) self::input($request, 'page', '1')));
        $expansions = $query->orderBy('sort_order')->orderBy('expansions.id')->with($this->relations())->paginate(36, page: $page, total: $total)
            ->appends($filters)
            ->through(fn (Expansion $expansion) => $this->presentation->card($expansion, $filters['language']));

        return [
            'catalogs' => $catalogs->map(fn (Catalog $item) => [
                'id' => $item->id, 'key' => $item->key, 'name' => $item->name, 'count' => (int) ($counts[$item->id] ?? 0),
            ]),
            'languages' => $languages,
            'filters' => $filters,
            'expansions' => $expansions,
            'total' => (int) $counts->sum(),
        ];
    }

    /** @return array<string, mixed> */
    public function show(Game $game, string $catalogKey, string $expansionKey, Request $request): array
    {
        $expansion = $this->visible()->where('key', $expansionKey)
            ->whereHas('catalog', fn (Builder $query) => $query->where('game_id', $game->id)->where('key', $catalogKey))
            ->with($this->relations())->firstOrFail();
        $requested = self::input($request, 'edition');
        $edition = $this->presentation->selectEdition($expansion, $requested);
        $relations = ExpansionRelation::query()->where('source_expansion_id', $expansion->id)->orWhere('target_expansion_id', $expansion->id)->get();
        $ids = $relations->map(fn (ExpansionRelation $relation) => $relation->source_expansion_id === $expansion->id ? $relation->target_expansion_id : $relation->source_expansion_id);
        $related = $this->visible()->whereIn('id', $ids)->with($this->relations())->orderBy('id')->get();

        return [
            ...$this->presentation->card($expansion),
            'editions' => $expansion->editions->map(fn (ExpansionEdition $item) => $this->presentation->edition($item))->values(),
            'selectedEdition' => $edition ? $this->presentation->edition($edition) : null,
            'editionFallback' => $requested !== '' && $edition?->language->code !== $requested,
            'related' => $related->map(fn (Expansion $item) => $this->presentation->card($item))->values(),
        ];
    }

    /** @return array<int|string, mixed> */
    private function relations(): array
    {
        return [
            'catalog.game', 'catalog.defaultLanguage', 'series.localizations.language', 'localizations.language',
            'editions' => fn ($query) => $query->where('is_visible', true)->orderBy('language_id')->orderBy('id'),
            'editions.language', 'editions.releases.market',
        ];
    }

    public static function input(Request $request, string $key, string $default = ''): string
    {
        $value = $request->query($key);

        return is_string($value) ? $value : $default;
    }

    /** @param Builder<Expansion> $query */
    private function search(Builder $query, string $term): void
    {
        // Accent-insensitive Latin search without a PostgreSQL extension; preserve non-Latin scripts.
        $accents = 'àáâäãåèéêëìíîïòóôöõùúûüýÿçñ';
        $plain = 'aaaaaaeeeeiiiiooooouuuuyycn';
        $term = strtr(mb_strtolower($term), array_combine(mb_str_split($accents), mb_str_split($plain)));
        $term = '%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $term).'%';
        $match = fn (Builder $query, string $column) => $query->whereRaw(match ($column) {
            'expansions.name' => 'translate(lower(expansions.name), ?, ?) like ?',
            'official_code' => 'translate(lower(official_code), ?, ?) like ?',
            default => 'translate(lower(name), ?, ?) like ?',
        }, [$accents, $plain, $term]);
        $query->where(function (Builder $query) use ($match): void {
            $match($query, 'expansions.name');
            $query->orWhereHas('localizations', fn (Builder $localizations) => $match($localizations, 'name'))
                ->orWhereHas('editions', function (Builder $editions) use ($match): void {
                    $editions->where('is_visible', true)->where(function (Builder $editions) use ($match): void {
                        $match($editions, 'name');
                        $editions->orWhere(fn (Builder $codes) => $match($codes, 'official_code'));
                    });
                });
        });
    }
}
