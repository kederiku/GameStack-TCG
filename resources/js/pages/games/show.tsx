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
    const { t, localizedUrl, languageName } = useLocale();
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
            <Head title={`${currentGame.name} — ${t('Catalogue', 'Catalog')}`}>
                <meta
                    name="description"
                    content={t(
                        `Explorez les séries et extensions ${currentGame.name}, leurs éditions et leurs sorties.`,
                        `Explore ${currentGame.name} series, expansions, editions and releases.`,
                    )}
                />
            </Head>
            <Breadcrumbs
                breadcrumbs={[
                    { title: t('Jeux', 'Games'), href: localizedUrl('/') },
                    {
                        title: currentGame.name,
                        href: localizedUrl(currentGame.url),
                    },
                    { title: t('Catalogue', 'Catalog'), href: '' },
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
                        {pagination.total}{' '}
                        {t(
                            pagination.total === 1 ? 'extension' : 'extensions',
                            pagination.total === 1 ? 'expansion' : 'expansions',
                        )}
                    </span>{' '}
                    ·{' '}
                    {catalogData.filters.language === '*'
                        ? t('Toutes les langues', 'All languages')
                        : languageName(catalogData.filters.language)}
                </p>
                <span className="flex items-center gap-2">
                    <Layers2 className="size-4" aria-hidden="true" />
                    {t('Séries & extensions', 'Series & expansions')}
                </span>
            </div>
            {catalogData.filters.series && (
                <div className="mb-5">
                    <Button asChild variant="secondary" size="sm">
                        <Link
                            href={localizedUrl(
                                `${currentGame.url}?catalog=${encodeURIComponent(catalogData.filters.catalog)}`,
                            )}
                        >
                            <X className="size-3" />
                            {t('Toutes les séries', 'All series')}
                        </Link>
                    </Button>
                </div>
            )}
            <div className="space-y-9 pb-8">
                {[...groups.entries()].map(([key, expansions]) => {
                    const first = expansions[0];
                    const series = first.series;
                    const name =
                        series?.name ??
                        t('Hors série', 'Standalone expansions');
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
                                                href={localizedUrl(series.url)}
                                            >
                                                {name}
                                            </Link>
                                        ) : (
                                            name
                                        )}
                                        <span
                                            className="ml-3 align-middle font-sans text-xs font-normal text-muted-foreground"
                                            title={t(
                                                'Extensions sur cette page',
                                                'Expansions on this page',
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
                                        aria-label={t(
                                            `Afficher ou masquer ${name}`,
                                            `Show or hide ${name}`,
                                        )}
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
                                ? t(
                                      'Aucune extension trouvée',
                                      'No expansions found',
                                  )
                                : t(
                                      'Le catalogue se prépare',
                                      'The catalog is coming soon',
                                  )
                        }
                        description={
                            filtered
                                ? t(
                                      'Essayez une autre recherche ou une autre langue.',
                                      'Try a different search or language.',
                                  )
                                : t(
                                      'Les séries et extensions publiées rejoindront bientôt cet espace.',
                                      'Published series and expansions will appear here soon.',
                                  )
                        }
                    >
                        {filtered && (
                            <Button asChild variant="secondary">
                                <Link href={localizedUrl(currentGame.url)}>
                                    {t(
                                        'Réinitialiser les filtres',
                                        'Reset filters',
                                    )}
                                </Link>
                            </Button>
                        )}
                    </EmptyState>
                )}
                {pagination.last_page > 1 && (
                    <nav
                        aria-label={t(
                            'Pagination des extensions',
                            'Expansion pages',
                        )}
                        className="flex flex-wrap items-center justify-center gap-4 border-t border-border pt-7"
                    >
                        {pagination.prev_page_url ? (
                            <Button asChild variant="secondary">
                                <Link href={pagination.prev_page_url}>
                                    {t('Précédent', 'Previous')}
                                </Link>
                            </Button>
                        ) : (
                            <Button variant="secondary" disabled>
                                {t('Précédent', 'Previous')}
                            </Button>
                        )}
                        <span className="text-xs text-muted-foreground">
                            {t('Page', 'Page')} {pagination.current_page} /{' '}
                            {pagination.last_page}
                        </span>
                        {pagination.next_page_url ? (
                            <Button asChild variant="secondary">
                                <Link href={pagination.next_page_url}>
                                    {t('Suivant', 'Next')}
                                </Link>
                            </Button>
                        ) : (
                            <Button variant="secondary" disabled>
                                {t('Suivant', 'Next')}
                            </Button>
                        )}
                    </nav>
                )}
            </div>
        </>
    );
}
