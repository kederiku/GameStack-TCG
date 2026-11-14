import { Link } from '@inertiajs/react';
import { ArrowLeft, Layers2 } from 'lucide-react';
import { BrandLogo } from '@/components/public/brand-logo';
import { CardFan } from '@/components/public/card-fan';
import { Button } from '@/components/ui/button';
import { home } from '@/routes';
import type { AuthLayoutProps } from '@/types';

export default function AuthImmersiveLayout({
    children,
    title,
    description,
}: AuthLayoutProps) {
    return (
        <div
            lang="fr"
            className="public-theme flex min-h-svh flex-col bg-background text-foreground"
        >
            <a
                href="#auth-content"
                className="sr-only z-50 rounded-md bg-primary px-4 py-3 text-primary-foreground focus:not-sr-only focus:absolute focus:top-2 focus:left-2"
            >
                Aller au formulaire
            </a>
            <header className="mx-auto flex w-full max-w-7xl flex-wrap items-center justify-between gap-3 px-4 py-5 sm:px-8">
                <Link
                    href={home()}
                    aria-label="GameStackr TCG — accueil"
                    className="rounded-sm focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-ring"
                >
                    <BrandLogo />
                </Link>
                <Button asChild variant="ghost" size="sm">
                    <Link href={home()}>
                        <ArrowLeft className="size-4" />
                        <span className="hidden sm:inline">
                            Retour à l’accueil
                        </span>
                        <span className="sm:hidden">Accueil</span>
                    </Link>
                </Button>
            </header>

            <main
                id="auth-content"
                className="mx-auto grid w-full max-w-7xl flex-1 items-center gap-10 px-4 py-6 sm:px-8 sm:py-10 lg:grid-cols-[1.05fr_1fr] lg:gap-16"
            >
                <aside className="public-hero relative hidden min-h-[38rem] self-stretch overflow-hidden rounded-3xl border border-border p-10 lg:flex lg:flex-col lg:justify-between xl:p-12">
                    <div className="relative z-10">
                        <p className="mb-8 flex items-center gap-2.5 text-xs font-semibold tracking-[0.18em] text-brand-highlight uppercase">
                            <Layers2 className="size-4" aria-hidden="true" />
                            Votre espace GameStackr
                        </p>
                        <p className="font-display text-6xl leading-[1.05] font-black uppercase xl:text-7xl">
                            Plusieurs univers.
                            <span className="mt-1 block text-brand-highlight">
                                Une même passion.
                            </span>
                        </p>
                        <p className="mt-6 max-w-xs text-base leading-relaxed text-muted-foreground">
                            Un jeu de cœur, ou plusieurs à la fois. Retrouvez
                            votre passion pour les cartes au même endroit.
                        </p>
                    </div>
                    <div className="relative -mx-6 mt-8 pb-14">
                        <CardFan />
                    </div>
                </aside>

                <section
                    aria-labelledby="auth-title"
                    className="auth-form mx-auto w-full max-w-md min-w-0 rounded-3xl border border-border bg-card p-5 shadow-lg shadow-black/5 sm:p-8"
                >
                    <div className="mb-8 space-y-3">
                        <p className="text-xs font-semibold tracking-[0.18em] text-brand-highlight uppercase">
                            GameStackr TCG
                        </p>
                        <h1
                            id="auth-title"
                            className="font-display text-4xl leading-tight font-extrabold text-balance uppercase sm:text-[2.75rem]"
                        >
                            {title}
                        </h1>
                        <p className="text-sm leading-relaxed text-muted-foreground">
                            {description}
                        </p>
                    </div>
                    {children}
                </section>
            </main>

            <footer className="px-4 py-6 text-center text-xs text-muted-foreground">
                Tous vos univers, un seul endroit.
            </footer>
        </div>
    );
}
