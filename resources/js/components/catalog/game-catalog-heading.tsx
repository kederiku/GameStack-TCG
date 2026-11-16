import { GameImage } from '@/components/games/game-image';
import { Badge } from '@/components/ui/badge';
import { useLocale } from '@/hooks/use-locale';
import type { GameDetails } from '@/types/catalog';
import type { PublicGame } from '@/types/game';

export function GameCatalogHeading({
    game,
    details,
    total,
}: {
    game: PublicGame;
    details: GameDetails;
    total: number;
}) {
    const { t, tChoice } = useLocale();
    return (
        <section className="flex flex-col justify-between gap-8 py-10 sm:py-12 lg:flex-row lg:items-center">
            <div className="min-w-0 lg:max-w-[60%]">
                <p className="mb-3 text-[11px] font-semibold tracking-[0.16em] text-brand-highlight uppercase">
                    {t('catalog.game-catalog-heading.the-catalog')}
                </p>
                <h1 className="font-display text-6xl leading-none font-black tracking-tight [overflow-wrap:anywhere] uppercase sm:text-7xl lg:text-8xl">
                    {game.name}
                    <span className="text-primary">.</span>
                </h1>
                <p className="mt-5 text-sm leading-6 text-muted-foreground">
                    {t('catalog.game-catalog-heading.tagline')}
                </p>
                {details.description && (
                    <p className="mt-3 max-w-xl text-sm leading-6 whitespace-pre-line text-muted-foreground">
                        {details.description}
                    </p>
                )}
            </div>
            <div className="ml-auto max-w-full shrink-0">
                <div className="flex items-center justify-end gap-5 sm:gap-7">
                    <GameImage
                        game={game}
                        className="h-24 w-36 bg-transparent p-2 sm:h-25 sm:w-40"
                    />
                    <div className="border-l border-border pl-5 sm:pl-7">
                        <strong className="font-display text-[42px] leading-none font-bold">
                            {total}
                        </strong>
                        <span className="mt-2 block text-[11px] text-muted-foreground">
                            {tChoice('catalog.explore-label', total)}
                        </span>
                    </div>
                </div>
                <dl className="mt-5 flex flex-wrap justify-end gap-x-7 gap-y-4 text-right">
                    {details.publisher && (
                        <div className="max-w-72">
                            <dt className="mb-1 text-[10px] text-muted-foreground">
                                {t('catalog.game-catalog-heading.publisher')}
                            </dt>
                            <dd className="text-xs font-medium [overflow-wrap:anywhere]">
                                {details.publisher}
                            </dd>
                        </div>
                    )}
                    {(details.startYear || details.endYear) && (
                        <div>
                            <dt className="mb-1 text-[10px] text-muted-foreground">
                                {t('catalog.game-catalog-heading.publication')}
                            </dt>
                            <dd className="flex flex-wrap justify-end gap-2 text-xs font-medium">
                                {details.startYear &&
                                    (details.endYear
                                        ? `${details.startYear} – ${details.endYear}`
                                        : t('catalog.since', {
                                              year: details.startYear,
                                          }))}
                                {details.endYear && (
                                    <Badge
                                        variant="secondary"
                                        className="text-[10px] font-normal"
                                    >
                                        {t('catalog.ended', {
                                            year: details.endYear,
                                        })}
                                    </Badge>
                                )}
                            </dd>
                        </div>
                    )}
                </dl>
            </div>
        </section>
    );
}
