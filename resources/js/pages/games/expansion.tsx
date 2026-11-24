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
                        t('catalog.expansion-description', {
                            name: expansion.name,
                        })
                    }
                />
            </Head>
            <Breadcrumbs
                breadcrumbs={[
                    {
                        title: t('catalog.expansion.games'),
                        href: routes.home.url(),
                    },
                    {
                        title: currentGame.name,
                        lang: currentGame.nameLanguage ?? '',
                        href: currentGame.url,
                    },
                    {
                        title: expansion.catalog.name,
                        lang: expansion.catalog.nameLanguage ?? '',
                        href: catalogUrl,
                    },
                    {
                        title: expansion.name,
                        lang: expansion.nameLanguage ?? '',
                        href: '',
                    },
                ]}
            />
            <Button asChild variant="ghost" className="mt-5 -ml-3">
                <Link href={catalogUrl}>
                    <ArrowLeft className="size-4" />
                    {t('catalog.expansion.all-expansions')}
                </Link>
            </Button>
            <article className="pt-5 pb-10">
                {expansion.editionFallback && !edition && (
                    <p
                        role="status"
                        className="mb-5 rounded-lg border border-border bg-card p-4 text-sm text-muted-foreground"
                    >
                        {t('catalog.expansion.no-edition')}
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
                            {types[expansion.type]} ·{' '}
                            <span lang={expansion.catalog.nameLanguage ?? ''}>
                                {expansion.catalog.name}
                            </span>
                        </p>
                        <h1
                            lang={expansion.nameLanguage ?? ''}
                            className="mt-3 font-display text-4xl leading-tight font-black [overflow-wrap:anywhere] uppercase sm:text-5xl"
                        >
                            {expansion.name}
                        </h1>
                        {expansion.nameFallback && expansion.nameLanguage && (
                            <p className="mt-2 text-xs text-muted-foreground">
                                {t('catalog.expansion.name-shown-in')}{' '}
                                {languageName(expansion.nameLanguage)}
                            </p>
                        )}
                        {expansion.description !== null && (
                            <p
                                lang={expansion.descriptionLanguage ?? ''}
                                className="mt-4 text-sm leading-7 whitespace-pre-line text-muted-foreground"
                            >
                                {expansion.description}
                            </p>
                        )}
                        {edition && (
                            <div className="mt-6">
                                <p className="mb-3 text-xs text-muted-foreground">
                                    {t('catalog.expansion.edition-language')}
                                </p>
                                <div className="[&>[data-slot=toggle-group]]:mx-0 [&>[data-slot=toggle-group]]:justify-start">
                                    <SegmentedPicker
                                        label={t(
                                            'catalog.expansion.edition-language',
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
                                            'catalog.expansion.edition-fallback',
                                        )}
                                    </p>
                                )}
                                <dl className="mt-5">
                                    <dt className="text-xs text-muted-foreground">
                                        {t('catalog.expansion.official-name')}
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
                            {t('catalog.expansion.series')}
                        </dt>
                        <dd className="mt-2 text-sm font-medium [overflow-wrap:anywhere]">
                            {expansion.series ? (
                                <Link
                                    href={expansion.series.url}
                                    lang={expansion.series.nameLanguage ?? ''}
                                    className="text-brand-highlight hover:underline"
                                >
                                    {expansion.series.name}
                                </Link>
                            ) : (
                                t('catalog.expansion.standalone-expansion')
                            )}
                        </dd>
                    </div>
                    <div className="min-w-0 sm:px-5">
                        <dt className="text-xs text-muted-foreground">
                            {t('catalog.expansion.edition')}
                        </dt>
                        <dd className="mt-2 text-sm font-medium">
                            {edition
                                ? languageName(
                                      edition.language.code,
                                      edition.language.name,
                                  )
                                : t('catalog.expansion.coming-soon')}
                            {edition && (
                                <span className="mt-1 block text-xs font-normal text-muted-foreground">
                                    {statuses[edition.status]}
                                </span>
                            )}
                        </dd>
                    </div>
                    <div className="min-w-0 sm:px-5">
                        <dt className="text-xs text-muted-foreground">
                            {t('catalog.expansion.official-code')}
                        </dt>
                        <dd className="mt-2 text-sm font-medium [overflow-wrap:anywhere]">
                            {edition?.officialCode ??
                                t('catalog.expansion.not-listed')}
                        </dd>
                    </div>
                </dl>
                {edition && <EditionReleases releases={edition.releases} />}
                <EmptyState
                    icon={Layers2}
                    titleAs="h2"
                    className="mt-9"
                    title={t('catalog.expansion.cards-are-coming-soon')}
                    description={
                        edition
                            ? t('catalog.edition-coming', {
                                  language: languageName(edition.language.code),
                              })
                            : t('catalog.expansion.cards-description')
                    }
                />
                {expansion.related.length > 0 && (
                    <section className="mt-10">
                        <h2 className="font-display text-3xl font-bold">
                            {t('catalog.expansion.related-expansions')}
                        </h2>
                        <p className="mt-2 mb-5 text-sm text-muted-foreground">
                            {t('catalog.expansion.related-help')}
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
