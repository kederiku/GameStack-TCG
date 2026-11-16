import { useLocalizedRoutes } from '@/hooks/use-localized-routes';
import { Link, usePage } from '@inertiajs/react';
import { ArrowUpRight, UserRound } from 'lucide-react';
import { BrandLogo } from '@/components/brand-logo';
import { GameSelector } from '@/components/games/game-selector';
import { LanguageSwitcher } from '@/components/language-switcher';
import { useLocale } from '@/hooks/use-locale';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import type { PublicGamePageProps } from '@/types/game';

export function PublicHeader() {
    const routes = useLocalizedRoutes();
    const { t, locale } = useLocale();
    const { games, currentGame, canRegister, auth } =
        usePage<PublicGamePageProps>().props;

    return (
        <header className="border-b border-border/80">
            <div className="mx-auto flex max-w-7xl flex-wrap items-center gap-x-5 gap-y-4 px-4 py-5 sm:px-6 lg:gap-x-7 lg:px-8">
                <Link
                    href={routes.home.url()}
                    aria-label={t('common.public-header.gamestackr-tcg-home')}
                    className="shrink-0 rounded-sm focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-ring"
                >
                    <BrandLogo />
                </Link>
                <div className="order-3 w-full min-w-0 sm:order-none sm:w-52 lg:w-60 lg:border-l lg:border-border lg:pl-7">
                    <GameSelector games={games} currentGame={currentGame} />
                </div>
                <nav
                    aria-label={t('common.public-header.account')}
                    className="ml-auto flex shrink-0 items-center gap-2"
                >
                    <LanguageSwitcher />
                    {auth.user ? (
                        <Button variant="secondary" asChild>
                            <Link href={routes.dashboard()}>
                                <UserRound className="size-4" />
                                <span className="hidden md:inline">
                                    {t('common.public-header.my-space')}
                                </span>
                                <span className="sr-only md:hidden">
                                    {t('common.public-header.my-space')}
                                </span>
                            </Link>
                        </Button>
                    ) : (
                        <>
                            <div className="hidden items-center gap-2 md:flex">
                                <Button variant="ghost" asChild>
                                    <Link href={routes.login()}>
                                        {t('common.public-header.log-in')}
                                    </Link>
                                </Button>
                                {canRegister && (
                                    <Button asChild>
                                        <Link href={routes.register()}>
                                            {t(
                                                'common.public-header.create-account',
                                            )}
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
                                            aria-label={t(
                                                'common.public-header.open-account-menu',
                                            )}
                                        >
                                            <UserRound className="size-4" />
                                        </Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent
                                        lang={locale}
                                        align="end"
                                        className="w-48"
                                    >
                                        <DropdownMenuItem asChild>
                                            <Link href={routes.login()}>
                                                {t(
                                                    'common.public-header.log-in',
                                                )}
                                            </Link>
                                        </DropdownMenuItem>
                                        {canRegister && (
                                            <DropdownMenuItem asChild>
                                                <Link href={routes.register()}>
                                                    {t(
                                                        'common.public-header.create-account',
                                                    )}
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
