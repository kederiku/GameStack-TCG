import { LanguageSwitcher } from '@/components/language-switcher';
import { useLocalizedRoutes } from '@/hooks/use-localized-routes';
import { useLocale } from '@/hooks/use-locale';
import { Link, usePage } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';
import { AuthGameMarquee } from '@/components/auth-game-marquee';
import { BrandLogo } from '@/components/brand-logo';
import type { AuthLayoutProps } from '@/types';
import type { PublicGame } from '@/types/game';

export default function AuthImmersiveLayout({
    children,
    title,
    description,
}: AuthLayoutProps) {
    const { t } = useLocale();
    const routes = useLocalizedRoutes();
    const { games } = usePage<{ games: PublicGame[] }>().props;

    return (
        <div className="flex min-h-svh flex-col justify-center bg-background p-4 text-foreground sm:p-6 lg:p-8">
            <a
                href="#auth-content"
                className="sr-only z-50 rounded-md bg-primary px-4 py-3 text-primary-foreground focus:not-sr-only focus:absolute focus:top-2 focus:left-2"
            >
                {t('auth.auth-immersive-layout.skip-to-form')}
            </a>
            <main
                id="auth-content"
                className="mx-auto grid w-full max-w-7xl items-start gap-6 lg:grid-cols-2 lg:gap-10"
            >
                <section
                    aria-labelledby="auth-title"
                    className="auth-form mx-auto flex w-full max-w-xl min-w-0 justify-center rounded-3xl border border-border bg-card px-5 py-8 sm:px-10 sm:py-10 lg:max-w-none"
                >
                    <div className="w-full max-w-sm min-w-0">
                        <div className="mb-8 flex justify-center sm:mb-10">
                            <Link
                                href={routes.home()}
                                aria-label={t(
                                    'auth.auth-immersive-layout.gamestackr-tcg-home',
                                )}
                                className="auth-brand rounded-sm focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-ring"
                            >
                                <BrandLogo />
                            </Link>
                        </div>
                        <div className="mb-7 space-y-3 text-center">
                            <h1
                                id="auth-title"
                                className="font-display text-4xl leading-tight font-extrabold text-balance uppercase"
                            >
                                {title}
                            </h1>
                            <p className="text-sm leading-relaxed text-muted-foreground">
                                {description}
                            </p>
                        </div>
                        <LanguageSwitcher />
                        {children}
                        <footer className="mt-8 border-t border-border pt-6 text-center">
                            <Link
                                href={routes.home()}
                                className="inline-flex min-h-11 items-center justify-center gap-2 rounded-sm text-sm text-muted-foreground focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-ring"
                            >
                                <ArrowLeft className="size-4" />
                                {t('auth.auth-immersive-layout.back-to-home')}
                            </Link>
                            <p className="mt-2 text-xs leading-relaxed text-muted-foreground">
                                {t('auth.auth-immersive-layout.tagline')}
                            </p>
                        </footer>
                    </div>
                </section>

                <AuthGameMarquee games={games} />
            </main>
        </div>
    );
}
