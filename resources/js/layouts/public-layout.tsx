import { Link } from '@inertiajs/react';
import type { PropsWithChildren } from 'react';
import { BrandLogo } from '@/components/public/brand-logo';
import { PublicHeader } from '@/components/public/public-header';
import { home } from '@/routes';

export default function PublicLayout({ children }: PropsWithChildren) {
    return (
        <div className="public-theme flex min-h-screen flex-col bg-background text-foreground">
            <a
                href="#main-content"
                className="sr-only z-50 rounded-md bg-primary px-4 py-3 text-primary-foreground focus:not-sr-only focus:absolute focus:top-2 focus:left-2"
            >
                Aller au contenu
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
                    <Link href={home()} aria-label="GameStackr TCG — accueil">
                        <BrandLogo className="brand-logo-small" />
                    </Link>
                    <p className="text-xs text-muted-foreground">
                        Tous vos univers, un seul endroit.
                    </p>
                </div>
            </footer>
        </div>
    );
}
