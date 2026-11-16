import { Link } from '@inertiajs/react';
import { ArrowUpRight } from 'lucide-react';
import { GameImage } from '@/components/games/game-image';
import type { PublicGame } from '@/types/game';
import { useLocale } from '@/hooks/use-locale';

export function GameCard({ game }: { game: PublicGame }) {
    const { t } = useLocale();
    return (
        <Link
            href={game.url}
            className="group flex h-full min-w-0 items-center gap-4 rounded-2xl border border-border bg-card p-5 transition-colors hover:border-primary/60 hover:bg-accent/40 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-ring"
        >
            <GameImage game={game} className="size-16 rounded-xl p-2.5" />
            <span className="min-w-0 flex-1">
                <span className="block font-semibold [overflow-wrap:anywhere]">
                    {game.name}
                </span>
                <span className="mt-1.5 block text-xs text-muted-foreground">
                    {t('catalog.game-card.explore-this-tcg')}
                </span>
            </span>
            <ArrowUpRight
                className="size-4 shrink-0 text-muted-foreground transition-colors group-hover:text-brand-highlight"
                aria-hidden="true"
            />
        </Link>
    );
}
