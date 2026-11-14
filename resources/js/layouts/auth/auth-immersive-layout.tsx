import { Link } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';
import { BrandLogo } from '@/components/public/brand-logo';
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
            className="public-theme flex min-h-svh flex-col justify-center bg-background p-4 text-foreground sm:p-6 lg:p-8"
        >
            <a
                href="#auth-content"
                className="sr-only z-50 rounded-md bg-primary px-4 py-3 text-primary-foreground focus:not-sr-only focus:absolute focus:top-2 focus:left-2"
            >
                Aller au formulaire
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
                                href={home()}
                                aria-label="GameStackr TCG — accueil"
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
                        {children}
                        <footer className="mt-8 border-t border-border pt-6 text-center">
                            <Link
                                href={home()}
                                className="inline-flex min-h-11 items-center justify-center gap-2 rounded-sm text-sm text-muted-foreground focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-ring"
                            >
                                <ArrowLeft className="size-4" />
                                Retour à l’accueil
                            </Link>
                            <p className="mt-2 text-xs leading-relaxed text-muted-foreground">
                                Vos cartes, vos collections, votre espace.
                            </p>
                        </footer>
                    </div>
                </section>

                <aside
                    aria-hidden="true"
                    className="auth-artwork sticky top-8 hidden h-[calc(100svh-4rem)] max-h-[56rem] min-h-[34rem] overflow-hidden rounded-3xl bg-[#07112a] lg:block"
                >
                    <div className="absolute inset-x-0 bottom-0 bg-linear-to-t from-[#07112a] via-[#07112a]/80 to-transparent px-8 pt-24 pb-10 text-center text-white">
                        <p className="font-display text-4xl font-extrabold uppercase xl:text-5xl">
                            Chaque carte a sa place.
                        </p>
                        <p className="mt-3 text-sm text-blue-100/80">
                            Vos collections, d’un TCG à l’autre.
                        </p>
                    </div>
                </aside>
            </main>
        </div>
    );
}
