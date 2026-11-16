import { Badge } from '@/components/ui/badge';
import { useLocale } from '@/hooks/use-locale';
import type { EditionRelease } from '@/types/catalog';
import { useCatalogLabels } from './use-catalog-labels';

export function EditionReleases({ releases }: { releases: EditionRelease[] }) {
    const { t, date } = useLocale();
    const { channels, statuses } = useCatalogLabels();
    return (
        <section aria-labelledby="edition-releases" className="mt-9">
            <h2
                id="edition-releases"
                className="mb-4 font-display text-2xl font-bold sm:text-3xl"
            >
                {t('catalog.edition-releases.releases-for-this-edition')}
            </h2>
            {releases.length === 0 ? (
                <p className="rounded-xl border border-border bg-card px-5 py-6 text-sm text-muted-foreground">
                    {t('catalog.edition-releases.no-releases-listed-yet')}
                </p>
            ) : (
                <ul className="divide-y divide-border overflow-hidden rounded-xl border border-border bg-card">
                    {releases.map((release) => (
                        <li
                            key={release.id}
                            className="flex flex-wrap items-center justify-between gap-4 p-5"
                        >
                            <div className="min-w-0">
                                <h3 className="text-sm font-medium [overflow-wrap:anywhere]">
                                    {release.market.name}
                                </h3>
                                <p className="mt-1 text-xs text-muted-foreground">
                                    {channels[release.channel]}
                                </p>
                            </div>
                            <div className="flex flex-wrap items-center justify-end gap-4">
                                <span className="text-sm">
                                    {release.releasedOn
                                        ? date(release.releasedOn)
                                        : (release.announcedPeriod ??
                                          t(
                                              'catalog.edition-releases.date-not-listed',
                                          ))}
                                </span>
                                <Badge
                                    variant="secondary"
                                    className="font-normal"
                                >
                                    {statuses[release.status]}
                                </Badge>
                            </div>
                        </li>
                    ))}
                </ul>
            )}
        </section>
    );
}
