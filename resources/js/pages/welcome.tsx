import { Head, Link } from '@inertiajs/react';
import { ArrowRight, Layers2, Sparkles } from 'lucide-react';
import { CardFan } from '@/components/public/card-fan';
import { GameImage } from '@/components/public/game-image';
import { Button } from '@/components/ui/button';
import type { PublicGamePageProps } from '@/types/game';

export default function Welcome({ games }: PublicGamePageProps) {
    return (
        <>
            <Head title="Vos collections de cartes TCG">
                <meta
                    name="description"
                    content="GameStackr prépare votre espace de gestion de collections multi-TCG : organiser vos cartes, suivre vos séries et repérer les cartes manquantes."
                />
            </Head>
            <section className="public-hero relative isolate overflow-hidden rounded-3xl border border-border px-5 pt-10 text-center sm:px-10 sm:pt-14 lg:pt-16">
                <p className="inline-flex items-center justify-center gap-2 text-[0.65rem] font-semibold tracking-[0.18em] text-brand-highlight uppercase sm:text-xs">
                    <Layers2 className="size-4 shrink-0" aria-hidden="true" />
                    La collection, au cœur du jeu
                </p>
                <h1 className="mx-auto mt-6 max-w-4xl font-display text-[clamp(2.75rem,6.5vw,5.5rem)] leading-none font-black tracking-tight uppercase">
                    Vos cartes.
                    <span className="mt-1 block text-brand-highlight">
                        Toutes vos collections.
                    </span>
                </h1>
                <p className="mx-auto mt-6 max-w-md text-sm leading-relaxed text-muted-foreground sm:text-base">
                    L’espace multi-TCG pensé pour organiser vos cartes, suivre
                    vos collections et préparer vos prochaines trouvailles.
                </p>
                <div className="mt-7 flex flex-wrap justify-center gap-3">
                    <Button asChild size="lg" className="h-12 px-6">
                        <a href="#jeux">
                            Choisir mon TCG
                            <ArrowRight className="size-4" />
                        </a>
                    </Button>
                    <Button
                        asChild
                        variant="secondary"
                        size="lg"
                        className="h-12 border border-border px-6"
                    >
                        <a href="#a-propos">Découvrir le projet</a>
                    </Button>
                </div>
                <CardFan />
            </section>

            <section id="jeux" className="scroll-mt-8 py-10 sm:py-12">
                <div className="mb-6 flex flex-col justify-between gap-3 sm:flex-row sm:items-end">
                    <h2 className="font-display text-3xl font-extrabold uppercase sm:text-4xl">
                        Une collection, plusieurs TCG
                    </h2>
                    <p className="text-sm text-muted-foreground">
                        Retrouvez les jeux que vous collectionnez.
                    </p>
                </div>
                {games.length > 0 ? (
                    <ul className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        {games.map((game) => (
                            <li key={game.id} className="min-w-0">
                                <Link
                                    href={game.url}
                                    className="group flex h-full min-w-0 items-center gap-4 rounded-xl border border-border bg-card px-5 py-6 transition-colors hover:border-primary/70 hover:bg-accent/40 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-ring"
                                >
                                    <GameImage
                                        game={game}
                                        className="size-14 rounded-xl p-2"
                                    />
                                    <span className="min-w-0 flex-1">
                                        <span className="block font-medium [overflow-wrap:anywhere]">
                                            {game.name}
                                        </span>
                                        <span className="mt-1 block text-xs text-muted-foreground">
                                            Découvrir ce TCG
                                        </span>
                                    </span>
                                    <ArrowRight
                                        className="size-4 shrink-0 text-muted-foreground transition-colors group-hover:text-brand-highlight"
                                        aria-hidden="true"
                                    />
                                </Link>
                            </li>
                        ))}
                    </ul>
                ) : (
                    <div className="rounded-xl border border-dashed border-border bg-card/50 px-6 py-10 text-center">
                        <Layers2
                            className="mx-auto mb-4 size-6 text-muted-foreground"
                            aria-hidden="true"
                        />
                        <h3 className="font-medium">
                            Les premiers TCG arrivent bientôt
                        </h3>
                        <p className="mt-2 text-sm text-muted-foreground">
                            Les jeux à collectionner rejoindront bientôt cet
                            espace. Votre prochaine collection commence ici.
                        </p>
                    </div>
                )}
            </section>

            <section
                id="a-propos"
                className="grid scroll-mt-8 gap-5 border-t border-border/70 py-9 sm:grid-cols-2 sm:gap-12 sm:py-12"
            >
                <div>
                    <p className="mb-3 inline-flex items-center gap-2 text-xs font-medium tracking-widest text-brand-highlight uppercase">
                        <Sparkles className="size-4" aria-hidden="true" />
                        Pensé pour les collectionneurs
                    </p>
                    <h2 className="max-w-sm font-display text-3xl leading-tight font-extrabold uppercase sm:text-4xl">
                        Chaque carte compte.
                    </h2>
                </div>
                <div className="max-w-lg self-center text-sm leading-7 text-muted-foreground sm:text-base">
                    <p>
                        Cartes possédées, doubles, envies : GameStackr se
                        construit autour de votre façon de collectionner. Notre
                        objectif : vous aider à organiser vos cartes, suivre vos
                        séries et repérer les pièces manquantes, d’un TCG à
                        l’autre.
                    </p>
                    <p className="mt-4 text-sm">
                        Les outils de gestion de collection sont en préparation.
                        Découvrez dès maintenant les espaces de vos jeux
                        favoris.
                    </p>
                </div>
            </section>
        </>
    );
}
