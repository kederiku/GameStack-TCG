import { useState } from 'react';
import { cn } from '@/lib/utils';
import type { PublicGame } from '@/types/game';

export function GameImage({
    game,
    className,
}: {
    game: PublicGame;
    className?: string;
}) {
    const [failedSource, setFailedSource] = useState<string | null>(null);
    const initials = Array.from(game.name.trim())
        .slice(0, 2)
        .join('')
        .toLocaleUpperCase('fr');

    return (
        <span
            aria-hidden="true"
            className={cn(
                'flex size-10 shrink-0 items-center justify-center rounded-lg border border-border/70 bg-secondary/60 p-1.5 font-display text-lg font-extrabold text-brand-highlight',
                className,
            )}
        >
            {game.imageUrl && game.imageUrl !== failedSource ? (
                <img
                    src={game.imageUrl}
                    alt=""
                    className="max-h-full max-w-full object-contain"
                    onError={() => setFailedSource(game.imageUrl)}
                />
            ) : (
                initials
            )}
        </span>
    );
}
