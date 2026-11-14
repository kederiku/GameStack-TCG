import { Link, usePage } from '@inertiajs/react';
import { ArrowUpRight, UserRound } from 'lucide-react';
import { BrandLogo } from '@/components/brand-logo';
import { GameSelector } from '@/components/games/game-selector';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { dashboard, home, login, register } from '@/routes';
import type { PublicGamePageProps } from '@/types/game';

export function PublicHeader() {
    const { games, currentGame, canRegister, auth } =
        usePage<PublicGamePageProps>().props;

    return (
        <header className="border-b border-border/80">
            <div className="mx-auto flex max-w-7xl flex-wrap items-center gap-x-5 gap-y-4 px-4 py-5 sm:px-6 lg:gap-x-7 lg:px-8">
                <Link
                    href={home()}
                    aria-label="GameStackr TCG — accueil"
                    className="shrink-0 rounded-sm focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-ring"
                >
                    <BrandLogo />
                </Link>
                <div className="order-3 w-full min-w-0 sm:order-none sm:w-52 lg:w-60 lg:border-l lg:border-border lg:pl-7">
                    <GameSelector games={games} currentGame={currentGame} />
                </div>
                <nav
                    aria-label="Compte"
                    className="ml-auto flex shrink-0 items-center gap-2"
                >
                    {auth.user ? (
                        <Button variant="secondary" asChild>
                            <Link href={dashboard()}>
                                <UserRound className="size-4" />
                                <span className="hidden md:inline">
                                    Mon espace
                                </span>
                                <span className="sr-only md:hidden">
                                    Mon espace
                                </span>
                            </Link>
                        </Button>
                    ) : (
                        <>
                            <div className="hidden items-center gap-2 md:flex">
                                <Button variant="ghost" asChild>
                                    <Link href={login()}>Connexion</Link>
                                </Button>
                                {canRegister && (
                                    <Button asChild>
                                        <Link href={register()}>
                                            Créer un compte
                                            <ArrowUpRight className="size-4" />
                                        </Link>
                                    </Button>
                                )}
                            </div>
                            <div className="md:hidden">
                                <DropdownMenu>
                                    <DropdownMenuTrigger asChild>
                                        <Button
                                            variant="outline"
                                            size="icon"
                                            className="size-11 bg-card"
                                            aria-label="Accéder à mon compte"
                                        >
                                            <UserRound className="size-4" />
                                        </Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent
                                        lang="fr"
                                        align="end"
                                        className="w-48"
                                    >
                                        <DropdownMenuItem asChild>
                                            <Link href={login()}>
                                                Connexion
                                            </Link>
                                        </DropdownMenuItem>
                                        {canRegister && (
                                            <DropdownMenuItem asChild>
                                                <Link href={register()}>
                                                    Créer un compte
                                                </Link>
                                            </DropdownMenuItem>
                                        )}
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </div>
                        </>
                    )}
                </nav>
            </div>
        </header>
    );
}
