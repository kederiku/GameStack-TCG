import { useLocalizedRoutes } from '@/hooks/use-localized-routes';
import { Link } from '@inertiajs/react';
import type { PropsWithChildren } from 'react';
import { useLocale } from '@/hooks/use-locale';
import { localeDefinitions } from '@/i18n/generated/types';
import { BrandLogo } from '@/components/brand-logo';
import { PublicHeader } from '@/components/public/public-header';

export default function PublicLayout({ children }: PropsWithChildren) {
    const routes = useLocalizedRoutes();
    const { locale, t } = useLocale();
    return (
        <div
            lang={localeDefinitions[locale].htmlLang}
            className="flex min-h-screen flex-col bg-background text-foreground"
        >
            <a
                href="#main-content"
                className="sr-only z-50 rounded-md bg-primary px-4 py-3 text-primary-foreground focus:not-sr-only focus:absolute focus:top-2 focus:left-2"
            >
                {t('Aller au contenu', 'Skip to content')}
            </a>
            <PublicHeader />
            <main
                id="main-content"
                className="mx-auto w-full max-w-7xl flex-1 px-4 py-6 sm:px-6 lg:px-8 lg:py-9"
            >
                {children}
            </main>
            <footer className="border-t border-border/70">
                <div className="mx-auto flex max-w-7xl flex-col items-start justify-between gap-5 px-4 py-7 sm:flex-row sm:items-center sm:px-6 lg:px-8">
                    <Link
                        href={routes.home.url()}
                        aria-label={t(
                            'GameStackr TCG — accueil',
                            'GameStackr TCG — home',
                        )}
                    >
                        <BrandLogo className="brand-logo-small" />
                    </Link>
                    <p className="text-xs text-muted-foreground">
                        {t(
                            'Vos cartes, vos collections, votre espace.',
                            'Your cards, your collections, your space.',
                        )}
                    </p>
                </div>
            </footer>
        </div>
    );
}
