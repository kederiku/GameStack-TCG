import { useLocalizedRoutes } from '@/hooks/use-localized-routes';
import { Head, Link, router } from '@inertiajs/react';
import { ArrowLeft, Layers2 } from 'lucide-react';
import { Breadcrumbs } from '@/components/breadcrumbs';
import { EditionImage } from '@/components/catalog/edition-image';
import { EditionReleases } from '@/components/catalog/edition-releases';
import { ExpansionCard } from '@/components/catalog/expansion-card';
import { useCatalogLabels } from '@/components/catalog/use-catalog-labels';
import { EmptyState } from '@/components/empty-state';
import { SegmentedPicker } from '@/components/segmented-picker';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { useLocale } from '@/hooks/use-locale';
import type { ExpansionDetail } from '@/types/catalog';
import type { PublicGame } from '@/types/game';

export default function ExpansionShow({
    currentGame,
    expansion,
}: {
    currentGame: PublicGame;
    expansion: ExpansionDetail;
}) {
    const routes = useLocalizedRoutes();
    const { t, languageName } = useLocale();
    const { types, statuses } = useCatalogLabels();
    const edition = expansion.selectedEdition;
    const catalogUrl = `${currentGame.url}?catalog=${encodeURIComponent(expansion.catalog.key)}`;
    return (
        <>
            <Head title={`${expansion.name} — ${currentGame.name}`}>
                <meta
                    name="description"
                    content={
                        expansion.description ??
                        t(
                            `Découvrez les éditions et sorties de ${expansion.name}.`,
                            `Explore editions and releases for ${expansion.name}.`,
                        )
                    }
                />
            </Head>
            <Breadcrumbs
                breadcrumbs={[
                    { title: t('Jeux', 'Games'), href: routes.home.url() },
                    {
                        title: currentGame.name,
                        href: currentGame.url,
                    },
                    { title: expansion.catalog.name, href: catalogUrl },
                    { title: expansion.name, href: '' },
                ]}
            />
            <Button asChild variant="ghost" className="mt-5 -ml-3">
                <Link href={catalogUrl}>
                    <ArrowLeft className="size-4" />
                    {t('Toutes les extensions', 'All expansions')}
                </Link>
            </Button>
            <article className="pt-5 pb-10">
                {expansion.editionFallback && !edition && (
                    <p
                        role="status"
                        className="mb-5 rounded-lg border border-border bg-card p-4 text-sm text-muted-foreground"
                    >
                        {t(
                            'L’édition demandée n’est plus disponible. Aucune autre édition n’est disponible pour le moment.',
                            'The requested edition is no longer available. No other edition is available yet.',
                        )}
                    </p>
                )}
                <div className="grid items-start gap-7 md:grid-cols-[260px_minmax(0,1fr)] lg:grid-cols-[300px_minmax(0,1fr)]">
                    <div className="relative mx-auto w-full max-w-xs overflow-hidden rounded-2xl border border-border bg-card md:max-w-none">
                        {edition && (
                            <Badge
                                variant="secondary"
                                className="absolute top-4 left-4 font-normal"
                            >
                                {languageName(
                                    edition.language.code,
                                    edition.language.name,
                                )}
                            </Badge>
                        )}
                        <EditionImage
                            imageUrl={edition?.imageUrl ?? null}
                            className="h-56 bg-transparent p-10 md:h-64 lg:h-72"
                        />
                    </div>
                    <div className="min-w-0">
                        <p className="text-xs text-muted-foreground">
                            {types[expansion.type]} · {expansion.catalog.name}
                        </p>
                        <h1
                            lang={expansion.nameLanguage ?? undefined}
                            className="mt-3 font-display text-4xl leading-tight font-black [overflow-wrap:anywhere] uppercase sm:text-5xl"
                        >
                            {expansion.name}
                        </h1>
                        {expansion.nameFallback && expansion.nameLanguage && (
                            <p className="mt-2 text-xs text-muted-foreground">
                                {t('Nom affiché en', 'Name shown in')}{' '}
                                {languageName(expansion.nameLanguage)}
                            </p>
                        )}
                        <p className="mt-4 text-sm leading-7 whitespace-pre-line text-muted-foreground">
                            {expansion.description ??
                                t(
                                    'Explorez les éditions de cette extension et leurs sorties.',
                                    'Explore this expansion’s editions and releases.',
                                )}
                        </p>
                        {edition && (
                            <div className="mt-6">
                                <p className="mb-3 text-xs text-muted-foreground">
                                    {t(
                                        'Langue de l’édition',
                                        'Edition language',
                                    )}
                                </p>
                                <div className="[&>[data-slot=toggle-group]]:mx-0 [&>[data-slot=toggle-group]]:justify-start">
                                    <SegmentedPicker
                                        label={t(
                                            'Langue de l’édition',
                                            'Edition language',
                                        )}
                                        value={edition.language.code}
                                        onValueChange={(language) =>
                                            router.get(
                                                expansion.url,
                                                {
                                                    edition: language,
                                                },
                                                {
                                                    preserveScroll: true,
                                                    preserveState: true,
                                                },
                                            )
                                        }
                                        options={expansion.editions.map(
                                            (item) => ({
                                                value: item.language.code,
                                                label: languageName(
                                                    item.language.code,
                                                    item.language.name,
                                                ),
                                            }),
                                        )}
                                    />
                                </div>
                                {expansion.editionFallback && (
                                    <p
                                        role="status"
                                        className="mt-3 text-xs text-brand-highlight"
                                    >
                                        {t(
                                            'L’édition demandée n’est pas disponible. Voici une autre édition disponible.',
                                            'The requested edition is unavailable. Showing an available catalog edition.',
                                        )}
                                    </p>
                                )}
                                <dl className="mt-5">
                                    <dt className="text-xs text-muted-foreground">
                                        {t('Nom officiel', 'Official name')}
                                    </dt>
                                    <dd
                                        lang={edition.language.code}
                                        className="mt-1 font-medium [overflow-wrap:anywhere]"
                                    >
                                        {edition.name}
                                    </dd>
                                </dl>
                            </div>
                        )}
                    </div>
                </div>
                <dl className="mt-7 grid gap-5 rounded-xl border border-border bg-card p-5 sm:grid-cols-3 sm:gap-0 sm:divide-x sm:divide-border sm:py-6">
                    <div className="min-w-0 sm:px-5">
                        <dt className="text-xs text-muted-foreground">
                            {t('Série', 'Series')}
                        </dt>
                        <dd className="mt-2 text-sm font-medium [overflow-wrap:anywhere]">
                            {expansion.series ? (
                                <Link
                                    href={expansion.series.url}
                                    className="text-brand-highlight hover:underline"
                                >
                                    {expansion.series.name}
                                </Link>
                            ) : (
                                t('Hors série', 'Standalone expansion')
                            )}
                        </dd>
                    </div>
                    <div className="min-w-0 sm:px-5">
                        <dt className="text-xs text-muted-foreground">
                            {t('Édition', 'Edition')}
                        </dt>
                        <dd className="mt-2 text-sm font-medium">
                            {edition
                                ? languageName(
                                      edition.language.code,
                                      edition.language.name,
                                  )
                                : t('À venir', 'Coming soon')}
                            {edition && (
                                <span className="mt-1 block text-xs font-normal text-muted-foreground">
                                    {statuses[edition.status]}
                                </span>
                            )}
                        </dd>
                    </div>
                    <div className="min-w-0 sm:px-5">
                        <dt className="text-xs text-muted-foreground">
                            {t('Code officiel', 'Official code')}
                        </dt>
                        <dd className="mt-2 text-sm font-medium [overflow-wrap:anywhere]">
                            {edition?.officialCode ??
                                t('Non renseigné', 'Not listed')}
                        </dd>
                    </div>
                </dl>
                {edition && <EditionReleases releases={edition.releases} />}
                <EmptyState
                    icon={Layers2}
                    titleAs="h2"
                    className="mt-9"
                    title={t(
                        'Les cartes arrivent bientôt',
                        'Cards are coming soon',
                    )}
                    description={
                        edition
                            ? t(
                                  `Les cartes de cette édition (${languageName(edition.language.code)}) seront disponibles ici.`,
                                  `Cards for this edition (${languageName(edition.language.code)}) will be available here.`,
                              )
                            : t(
                                  'Les éditions linguistiques et leurs cartes seront disponibles ici.',
                                  'Language editions and their cards will be available here.',
                              )
                    }
                />
                {expansion.related.length > 0 && (
                    <section className="mt-10">
                        <h2 className="font-display text-3xl font-bold">
                            {t('Extensions apparentées', 'Related expansions')}
                        </h2>
                        <p className="mt-2 mb-5 text-sm text-muted-foreground">
                            {t(
                                'Le contenu peut différer d’un catalogue à l’autre.',
                                'Contents may differ between catalogs.',
                            )}
                        </p>
                        <div className="grid gap-4 md:grid-cols-2">
                            {expansion.related.map((item) => (
                                <ExpansionCard key={item.id} expansion={item} />
                            ))}
                        </div>
                    </section>
                )}
            </article>
        </>
    );
}
