import { Link } from '@inertiajs/react';
import { Check, ChevronDown, House, Layers2 } from 'lucide-react';
import { GameImage } from '@/components/games/game-image';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { home } from '@/routes';
import type { PublicGame } from '@/types/game';

export function GameSelector({
    games,
    currentGame,
}: {
    games: PublicGame[];
    currentGame: PublicGame | null;
}) {
    return (
        <DropdownMenu>
            <DropdownMenuTrigger asChild>
                <Button
                    variant="outline"
                    disabled={games.length === 0}
                    aria-label={
                        games.length === 0
                            ? 'Aucun jeu disponible'
                            : currentGame
                              ? `Changer de jeu : ${currentGame.name}`
                              : 'Choisir un jeu'
                    }
                    className="h-11 w-full min-w-0 justify-between bg-card px-3 font-normal"
                >
                    <span className="flex min-w-0 items-center gap-2.5">
                        {currentGame ? (
                            <GameImage
                                key={currentGame.id}
                                game={currentGame}
                                className="size-7 rounded border-0 p-0.5 text-xs"
                            />
                        ) : (
                            <Layers2 className="size-4 text-muted-foreground" />
                        )}
                        <span className="truncate">
                            {games.length === 0
                                ? 'Aucun jeu disponible'
                                : (currentGame?.name ?? 'Choisir un jeu')}
                        </span>
                    </span>
                    <ChevronDown className="size-4 shrink-0 text-muted-foreground" />
                </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent
                lang="fr"
                align="start"
                sideOffset={8}
                className="max-h-[min(26rem,var(--radix-dropdown-menu-content-available-height))] w-72 max-w-[calc(100vw-2rem)] overflow-y-auto rounded-xl p-1.5"
            >
                <DropdownMenuLabel className="px-2.5 py-2 text-xs font-medium tracking-widest text-muted-foreground uppercase">
                    Vos univers
                </DropdownMenuLabel>
                {games.map((game) => (
                    <DropdownMenuItem key={game.id} asChild>
                        <Link
                            href={game.url}
                            aria-current={
                                currentGame?.id === game.id ? 'page' : undefined
                            }
                            className="min-w-0 gap-3 rounded-lg px-2.5 py-2.5"
                        >
                            <GameImage game={game} className="size-9" />
                            <span className="min-w-0 flex-1 [overflow-wrap:anywhere]">
                                {game.name}
                            </span>
                            {currentGame?.id === game.id && (
                                <Check className="size-4 text-brand-highlight" />
                            )}
                        </Link>
                    </DropdownMenuItem>
                ))}
                <DropdownMenuSeparator />
                <DropdownMenuItem asChild>
                    <Link href={home()} className="gap-3 px-2.5 py-3">
                        <House className="size-4" />
                        Tous les jeux
                    </Link>
                </DropdownMenuItem>
            </DropdownMenuContent>
        </DropdownMenu>
    );
}
