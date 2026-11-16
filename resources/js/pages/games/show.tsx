import { useLocalizedRoutes } from '@/hooks/use-localized-routes';
import { Head, Link } from '@inertiajs/react';
import { ChevronDown, Layers2, Search, X } from 'lucide-react';
import { Breadcrumbs } from '@/components/breadcrumbs';
import { CatalogFilters } from '@/components/catalog/catalog-filters';
import { ExpansionCard } from '@/components/catalog/expansion-card';
import { GameCatalogHeading } from '@/components/catalog/game-catalog-heading';
import { EmptyState } from '@/components/empty-state';
import { Button } from '@/components/ui/button';
import {
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
} from '@/components/ui/collapsible';
import { useLocale } from '@/hooks/use-locale';
import type {
    CatalogData,
    GameDetails,
    PublicExpansion,
} from '@/types/catalog';
import type { PublicGame } from '@/types/game';

export default function GameShow({
    currentGame,
    gameDetails,
    catalogData,
}: {
    currentGame: PublicGame;
    gameDetails: GameDetails;
    catalogData: CatalogData;
}) {
    const routes = useLocalizedRoutes();
    const { t, tChoice, languageName } = useLocale();
    const groups = new Map<string, PublicExpansion[]>();
    for (const expansion of catalogData.expansions.data) {
        const key = `${expansion.catalog.id}-${expansion.series?.id ?? 'none'}`;
        groups.set(key, [...(groups.get(key) ?? []), expansion]);
    }
    const filtered =
        catalogData.filters.q ||
        catalogData.filters.language !== '*' ||
        catalogData.filters.type !== 'all' ||
        catalogData.filters.series;
    const pagination = catalogData.expansions;
    return (
        <>
            <Head title={`${currentGame.name} — ${t('catalog.show.catalog')}`}>
                <meta
                    name="description"
                    content={t('catalog.game-description', {
                        name: currentGame.name,
                    })}
                />
            </Head>
            <Breadcrumbs
                breadcrumbs={[
                    { title: t('catalog.show.games'), href: routes.home.url() },
                    {
                        title: currentGame.name,
                        href: currentGame.url,
                    },
                    { title: t('catalog.show.catalog'), href: '' },
                ]}
            />
            <GameCatalogHeading
                game={currentGame}
                details={gameDetails}
                total={catalogData.total}
            />
            <CatalogFilters
                key={currentGame.id}
                data={catalogData}
                url={currentGame.url}
            />
            <div
                className="my-6 flex flex-wrap items-center justify-between gap-3 text-xs text-muted-foreground"
                aria-live="polite"
            >
                <p>
                    <span className="text-foreground">
                        {tChoice('catalog.expansions.count', pagination.total)}
                    </span>{' '}
                    ·{' '}
                    {catalogData.filters.language === '*'
                        ? t('catalog.show.all-languages')
                        : languageName(catalogData.filters.language)}
                </p>
                <span className="flex items-center gap-2">
                    <Layers2 className="size-4" aria-hidden="true" />
                    {t('catalog.show.series-expansions')}
                </span>
            </div>
            {catalogData.filters.series && (
                <div className="mb-5">
                    <Button asChild variant="secondary" size="sm">
                        <Link
                            href={`${currentGame.url}?catalog=${encodeURIComponent(catalogData.filters.catalog)}`}
                        >
                            <X className="size-3" />
                            {t('catalog.show.all-series')}
                        </Link>
                    </Button>
                </div>
            )}
            <div className="space-y-9 pb-8">
                {[...groups.entries()].map(([key, expansions]) => {
                    const first = expansions[0];
                    const series = first.series;
                    const name =
                        series?.name ?? t('catalog.show.standalone-expansions');
                    return (
                        <Collapsible
                            key={`${key}-${JSON.stringify(catalogData.filters)}`}
                            defaultOpen
                        >
                            <div className="mb-4 flex items-center justify-between gap-3">
                                <div className="min-w-0">
                                    <p className="mb-1 text-[11px] text-muted-foreground">
                                        {first.catalog.name}
                                    </p>
                                    <h2
                                        className="font-display text-3xl leading-tight font-bold [overflow-wrap:anywhere]"
                                        lang={series?.nameLanguage ?? undefined}
                                    >
                                        {series ? (
                                            <Link
                                                className="rounded-sm hover:text-brand-highlight focus-visible:outline-2 focus-visible:outline-ring"
                                                href={series.url}
                                            >
                                                {name}
                                            </Link>
                                        ) : (
                                            name
                                        )}
                                        <span
                                            className="ml-3 align-middle font-sans text-xs font-normal text-muted-foreground"
                                            title={t(
                                                'catalog.show.expansions-on-this-page',
                                            )}
                                        >
                                            {expansions.length}
                                        </span>
                                    </h2>
                                </div>
                                <CollapsibleTrigger asChild>
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        aria-label={t('catalog.toggle-series', {
                                            name,
                                        })}
                                        className="shrink-0 data-[state=closed]:[&>svg]:-rotate-90"
                                    >
                                        <ChevronDown className="size-4 transition-transform" />
                                    </Button>
                                </CollapsibleTrigger>
                            </div>
                            <CollapsibleContent>
                                <div className="grid gap-4 md:grid-cols-2">
                                    {expansions.map((expansion) => (
                                        <ExpansionCard
                                            key={expansion.id}
                                            expansion={expansion}
                                        />
                                    ))}
                                </div>
                            </CollapsibleContent>
                        </Collapsible>
                    );
                })}
                {pagination.total === 0 && (
                    <EmptyState
                        icon={filtered ? Search : Layers2}
                        titleAs="h2"
                        title={
                            filtered
                                ? t('catalog.show.no-expansions-found')
                                : t('catalog.show.empty-title')
                        }
                        description={
                            filtered
                                ? t('catalog.show.filter-help')
                                : t('catalog.show.empty-description')
                        }
                    >
                        {filtered && (
                            <Button asChild variant="secondary">
                                <Link href={currentGame.url}>
                                    {t('catalog.show.reset-filters')}
                                </Link>
                            </Button>
                        )}
                    </EmptyState>
                )}
                {pagination.last_page > 1 && (
                    <nav
                        aria-label={t('catalog.show.expansion-pages')}
                        className="flex flex-wrap items-center justify-center gap-4 border-t border-border pt-7"
                    >
                        {pagination.prev_page_url ? (
                            <Button asChild variant="secondary">
                                <Link href={pagination.prev_page_url}>
                                    {t('catalog.show.previous')}
                                </Link>
                            </Button>
                        ) : (
                            <Button variant="secondary" disabled>
                                {t('catalog.show.previous')}
                            </Button>
                        )}
                        <span className="text-xs text-muted-foreground">
                            {t('catalog.show.page')} {pagination.current_page} /{' '}
                            {pagination.last_page}
                        </span>
                        {pagination.next_page_url ? (
                            <Button asChild variant="secondary">
                                <Link href={pagination.next_page_url}>
                                    {t('catalog.show.next')}
                                </Link>
                            </Button>
                        ) : (
                            <Button variant="secondary" disabled>
                                {t('catalog.show.next')}
                            </Button>
                        )}
                    </nav>
                )}
            </div>
        </>
    );
}
