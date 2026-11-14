import { Head, Link } from '@inertiajs/react';
import { ArrowLeft, Layers2 } from 'lucide-react';
import { EmptyState } from '@/components/empty-state';
import { GameImage } from '@/components/games/game-image';
import { Button } from '@/components/ui/button';
import { home } from '@/routes';
import type { PublicGame } from '@/types/game';

export default function GameShow({ currentGame }: { currentGame: PublicGame }) {
    return (
        <>
            <Head title={currentGame.name}>
                <meta
                    name="description"
                    content={`Retrouvez l’univers ${currentGame.name} sur GameStackr TCG.`}
                />
            </Head>
            <div className="py-3">
                <Button asChild variant="ghost" className="-ml-3">
                    <Link href={home()}>
                        <ArrowLeft className="size-4" />
                        Tous les jeux
                    </Link>
                </Button>
            </div>
            <section className="mx-auto flex max-w-3xl flex-col items-center py-12 text-center sm:py-16">
                <GameImage
                    key={currentGame.id}
                    game={currentGame}
                    className="mb-7 h-28 w-36 rounded-2xl p-5 text-4xl"
                />
                <p className="mb-3 text-xs font-semibold tracking-[0.2em] text-brand-highlight uppercase">
                    Votre univers
                </p>
                <h1 className="max-w-full font-display text-5xl leading-tight font-black [overflow-wrap:anywhere] uppercase sm:text-7xl">
                    {currentGame.name}
                </h1>
                <p className="mt-4 max-w-lg [overflow-wrap:anywhere] text-muted-foreground">
                    Bienvenue dans l’espace {currentGame.name} de GameStackr.
                </p>
                <EmptyState
                    icon={Layers2}
                    title="Le catalogue se prépare"
                    titleAs="h2"
                    description="Les cartes et extensions arriveront ici prochainement."
                    className="mt-10 w-full max-w-lg"
                />
            </section>
        </>
    );
}
