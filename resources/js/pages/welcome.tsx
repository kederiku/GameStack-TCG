import { Head } from '@inertiajs/react';
import { ArrowRight, Layers2, Sparkles } from 'lucide-react';
import { CardFan } from '@/components/card-fan';
import { GameGrid } from '@/components/games/game-grid';
import { Button } from '@/components/ui/button';
import type { PublicGamePageProps } from '@/types/game';
import { useLocale } from '@/hooks/use-locale';

export default function Welcome({ games }: PublicGamePageProps) {
    const { t } = useLocale();
    return (
        <>
            <Head title={t('common.welcome.title')}>
                <meta
                    name="description"
                    content={t('common.welcome.meta-description')}
                />
            </Head>
            <section className="brand-surface relative isolate overflow-hidden rounded-3xl border border-border px-5 pt-10 text-center sm:px-10 sm:pt-14 lg:pt-16">
                <p className="inline-flex items-center justify-center gap-2 text-[0.65rem] font-semibold tracking-[0.18em] text-brand-highlight uppercase sm:text-xs">
                    <Layers2 className="size-4 shrink-0" aria-hidden="true" />
                    {t('common.welcome.eyebrow')}
                </p>
                <h1 className="mx-auto mt-6 max-w-4xl font-display text-[clamp(2.75rem,6.5vw,5.5rem)] leading-none font-black tracking-tight uppercase">
                    {t('common.welcome.headline')}
                    <span className="mt-1 block text-brand-highlight">
                        {t('common.welcome.headline-emphasis')}
                    </span>
                </h1>
                <p className="mx-auto mt-6 max-w-md text-sm leading-relaxed text-muted-foreground sm:text-base">
                    {t('common.welcome.introduction')}
                </p>
                <div className="mt-7 flex flex-wrap justify-center gap-3">
                    <Button asChild size="lg" className="h-12 px-6">
                        <a href="#jeux">
                            {t('common.welcome.choose-game')}
                            <ArrowRight className="size-4" />
                        </a>
                    </Button>
                    <Button
                        asChild
                        variant="secondary"
                        size="lg"
                        className="h-12 border border-border px-6"
                    >
                        <a href="#a-propos">{t('common.welcome.discover')}</a>
                    </Button>
                </div>
                <CardFan />
            </section>

            <section id="jeux" className="scroll-mt-8 py-10 sm:py-12">
                <div className="mb-6 flex flex-col justify-between gap-3 sm:flex-row sm:items-end">
                    <h2 className="font-display text-3xl font-extrabold uppercase sm:text-4xl">
                        {t('common.welcome.games-title')}
                    </h2>
                    <p className="text-sm text-muted-foreground">
                        {t('common.welcome.games-description')}
                    </p>
                </div>
                <GameGrid games={games} />
            </section>

            <section
                id="a-propos"
                className="grid scroll-mt-8 gap-5 border-t border-border/70 py-9 sm:grid-cols-2 sm:gap-12 sm:py-12"
            >
                <div>
                    <p className="mb-3 inline-flex items-center gap-2 text-xs font-medium tracking-widest text-brand-highlight uppercase">
                        <Sparkles className="size-4" aria-hidden="true" />
                        {t('common.welcome.collectors')}
                    </p>
                    <h2 className="max-w-sm font-display text-3xl leading-tight font-extrabold uppercase sm:text-4xl">
                        {t('common.welcome.project-title')}
                    </h2>
                </div>
                <div className="max-w-lg self-center text-sm leading-7 text-muted-foreground sm:text-base">
                    <p>{t('common.welcome.project-description')}</p>
                    <p className="mt-4 text-sm">
                        {t('common.welcome.project-status')}
                    </p>
                </div>
            </section>
        </>
    );
}
