import { Link } from '@inertiajs/react';
import { ArrowRight } from 'lucide-react';
import { Badge } from '@/components/ui/badge';
import { useLocale } from '@/hooks/use-locale';
import type { PublicExpansion } from '@/types/catalog';
import { EditionImage } from './edition-image';
import { useCatalogLabels } from './use-catalog-labels';

export function ExpansionCard({ expansion }: { expansion: PublicExpansion }) {
    const { t, date, languageName } = useLocale();
    const { types, statuses } = useCatalogLabels();
    const edition = expansion.edition;
    const url = `${expansion.url}${edition ? `?edition=${encodeURIComponent(edition.language.code)}` : ''}`;
    return (
        <Link
            href={url}
            className="group flex h-full min-w-0 overflow-hidden rounded-xl border border-border bg-card transition-colors hover:border-brand-highlight/70 hover:bg-accent/30 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-ring"
        >
            <EditionImage
                imageUrl={edition?.imageUrl ?? null}
                className="w-24 border-r border-border sm:w-32"
            />
            <div className="flex min-w-0 flex-1 flex-col p-4 sm:p-5">
                <span className="text-[11px] text-muted-foreground">
                    {types[expansion.type]}
                </span>
                <h3
                    lang={expansion.nameLanguage ?? undefined}
                    className="mt-1 font-display text-2xl leading-tight font-bold [overflow-wrap:anywhere] sm:text-3xl"
                >
                    {expansion.name}
                </h3>
                <p className="mt-4 text-xs leading-5 text-muted-foreground">
                    {expansion.firstRelease ? (
                        <>
                            {date(expansion.firstRelease.releasedOn)} ·{' '}
                            {expansion.firstRelease.market.name}
                            {expansion.firstRelease.language !==
                                edition?.language.code &&
                                ` · ${languageName(expansion.firstRelease.language)}`}
                        </>
                    ) : edition ? (
                        statuses[edition.status]
                    ) : (
                        t('Éditions à venir', 'Editions coming soon')
                    )}
                </p>
                <div className="mt-auto flex items-end justify-between gap-3 pt-4">
                    <span
                        className="flex flex-wrap gap-1.5"
                        aria-label={t(
                            'Langues disponibles',
                            'Available languages',
                        )}
                    >
                        {expansion.languages.map((language) => (
                            <Badge
                                variant="secondary"
                                className="rounded px-1.5 py-0.5 text-[10px] font-normal text-muted-foreground"
                                key={language.code}
                                title={languageName(
                                    language.code,
                                    language.name,
                                )}
                            >
                                {language.code.toUpperCase()}
                            </Badge>
                        ))}
                    </span>
                    <ArrowRight
                        aria-hidden="true"
                        className="size-4 shrink-0 text-brand-highlight transition-transform group-hover:translate-x-1 motion-reduce:transform-none"
                    />
                </div>
            </div>
        </Link>
    );
}
