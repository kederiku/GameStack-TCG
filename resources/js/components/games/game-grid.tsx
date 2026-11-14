import { Layers2 } from 'lucide-react';
import { EmptyState } from '@/components/empty-state';
import { GameCard } from '@/components/games/game-card';
import { cn } from '@/lib/utils';
import type { PublicGame } from '@/types/game';

export function GameGrid({
    games,
    className,
}: {
    games: PublicGame[];
    className?: string;
}) {
    if (games.length === 0) {
        return (
            <EmptyState
                icon={Layers2}
                title="Les premiers TCG arrivent bientôt"
                description="Les jeux à collectionner rejoindront bientôt cet espace. Votre prochaine collection commence ici."
            />
        );
    }

    return (
        <ul
            className={cn(
                'grid gap-4 sm:grid-cols-2 lg:grid-cols-4',
                className,
            )}
        >
            {games.map((game) => (
                <li key={game.id} className="min-w-0">
                    <GameCard game={game} />
                </li>
            ))}
        </ul>
    );
}
