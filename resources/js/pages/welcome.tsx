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
            <Head
                title={t(
                    'Vos collections de cartes TCG',
                    'Your TCG card collections',
                )}
            >
                <meta
                    name="description"
                    content={t(
                        'GameStackr prépare votre espace de gestion de collections multi-TCG : organiser vos cartes, suivre vos séries et repérer les cartes manquantes.',
                        'GameStackr is building your multi-TCG collection space: organize cards, follow series and discover missing cards.',
                    )}
                />
            </Head>
            <section className="brand-surface relative isolate overflow-hidden rounded-3xl border border-border px-5 pt-10 text-center sm:px-10 sm:pt-14 lg:pt-16">
                <p className="inline-flex items-center justify-center gap-2 text-[0.65rem] font-semibold tracking-[0.18em] text-brand-highlight uppercase sm:text-xs">
                    <Layers2 className="size-4 shrink-0" aria-hidden="true" />
                    {t(
                        'La collection, au cœur du jeu',
                        'Collecting, at the heart of the game',
                    )}
                </p>
                <h1 className="mx-auto mt-6 max-w-4xl font-display text-[clamp(2.75rem,6.5vw,5.5rem)] leading-none font-black tracking-tight uppercase">
                    {t('Vos cartes.', 'Your cards.')}
                    <span className="mt-1 block text-brand-highlight">
                        {t('Toutes vos collections.', 'All your collections.')}
                    </span>
                </h1>
                <p className="mx-auto mt-6 max-w-md text-sm leading-relaxed text-muted-foreground sm:text-base">
                    {t(
                        'L’espace multi-TCG pensé pour organiser vos cartes, suivre vos collections et préparer vos prochaines trouvailles.',
                        'A multi-TCG space to organize your cards, follow your collections and plan your next discoveries.',
                    )}
                </p>
                <div className="mt-7 flex flex-wrap justify-center gap-3">
                    <Button asChild size="lg" className="h-12 px-6">
                        <a href="#jeux">
                            {t('Choisir mon TCG', 'Choose my TCG')}
                            <ArrowRight className="size-4" />
                        </a>
                    </Button>
                    <Button
                        asChild
                        variant="secondary"
                        size="lg"
                        className="h-12 border border-border px-6"
                    >
                        <a href="#a-propos">
                            {t('Découvrir le projet', 'Discover the project')}
                        </a>
                    </Button>
                </div>
                <CardFan />
            </section>

            <section id="jeux" className="scroll-mt-8 py-10 sm:py-12">
                <div className="mb-6 flex flex-col justify-between gap-3 sm:flex-row sm:items-end">
                    <h2 className="font-display text-3xl font-extrabold uppercase sm:text-4xl">
                        {t(
                            'Une collection, plusieurs TCG',
                            'One collection, many TCGs',
                        )}
                    </h2>
                    <p className="text-sm text-muted-foreground">
                        {t(
                            'Retrouvez les jeux que vous collectionnez.',
                            'Find the games you collect.',
                        )}
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
                        {t(
                            'Pensé pour les collectionneurs',
                            'Made for collectors',
                        )}
                    </p>
                    <h2 className="max-w-sm font-display text-3xl leading-tight font-extrabold uppercase sm:text-4xl">
                        {t('Chaque carte compte.', 'Every card counts.')}
                    </h2>
                </div>
                <div className="max-w-lg self-center text-sm leading-7 text-muted-foreground sm:text-base">
                    <p>
                        {t(
                            'Cartes possédées, doubles, envies : GameStackr se construit autour de votre façon de collectionner. Notre objectif : vous aider à organiser vos cartes, suivre vos séries et repérer les pièces manquantes, d’un TCG à l’autre.',
                            'Owned cards, duplicates, wishlists: GameStackr is built around the way you collect. Our goal is to help you organize cards, follow series and find missing pieces across TCGs.',
                        )}
                    </p>
                    <p className="mt-4 text-sm">
                        {t(
                            'Les outils de gestion de collection sont en préparation. Découvrez dès maintenant les espaces de vos jeux favoris.',
                            'Collection tools are on the way. Explore your favorite games right now.',
                        )}
                    </p>
                </div>
            </section>
        </>
    );
}
