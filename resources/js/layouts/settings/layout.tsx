import { Link } from '@inertiajs/react';
import { Palette, ShieldCheck, UserRound } from 'lucide-react';
import type { PropsWithChildren } from 'react';
import { useCurrentUrl } from '@/hooks/use-current-url';
import { cn, toUrl } from '@/lib/utils';
import { edit as editAppearance } from '@/routes/appearance';
import { edit } from '@/routes/profile';
import { edit as editSecurity } from '@/routes/security';

const settingsPages = [
    {
        label: 'Profil',
        title: 'Mon profil',
        description: 'Les informations qui font de cet espace le vôtre.',
        href: edit(),
        icon: UserRound,
    },
    {
        label: 'Sécurité',
        title: 'Sécurité',
        description: 'Gardez le contrôle sur les accès à votre compte.',
        href: editSecurity(),
        icon: ShieldCheck,
    },
    {
        label: 'Apparence',
        title: 'Apparence',
        description: 'Votre collection, dans l’ambiance qui vous ressemble.',
        href: editAppearance(),
        icon: Palette,
    },
];

export default function SettingsLayout({ children }: PropsWithChildren) {
    const { isCurrentOrParentUrl } = useCurrentUrl();
    const currentPage =
        settingsPages.find((page) => isCurrentOrParentUrl(page.href)) ??
        settingsPages[0];

    return (
        <div className="mx-auto w-full max-w-7xl px-4 py-7 sm:px-8 sm:py-9">
            <header className="mb-7">
                <p className="mb-3 text-[0.65rem] font-semibold tracking-[0.18em] text-brand-highlight uppercase">
                    Votre compte GameStackr
                </p>
                <h1 className="font-display text-4xl leading-none font-black uppercase sm:text-5xl">
                    {currentPage.title}
                    <span className="text-brand-highlight">.</span>
                </h1>
                <p className="mt-3 text-sm leading-6 text-muted-foreground">
                    {currentPage.description}
                </p>
            </header>
            <nav
                aria-label="Réglages du compte"
                className="mb-7 border-b border-border pb-5"
            >
                <div className="flex w-fit max-w-full flex-wrap gap-1 rounded-2xl border border-border bg-card p-1.5">
                    {settingsPages.map(({ label, href, icon: Icon }) => {
                        const active = isCurrentOrParentUrl(href);
                        return (
                            <Link
                                key={toUrl(href)}
                                href={href}
                                aria-current={active ? 'page' : undefined}
                                className={cn(
                                    'flex min-h-11 items-center gap-2 rounded-xl px-3 text-sm font-medium transition-colors focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-ring sm:px-5',
                                    active
                                        ? 'bg-primary/10 text-brand-highlight'
                                        : 'text-muted-foreground hover:bg-accent hover:text-foreground',
                                )}
                            >
                                <Icon
                                    className="hidden size-4 sm:block"
                                    aria-hidden="true"
                                />
                                {label}
                            </Link>
                        );
                    })}
                </div>
            </nav>
            <div className="max-w-5xl space-y-6">{children}</div>
            <p className="mt-8 text-xs leading-6 text-muted-foreground">
                GameStackr TCG <span aria-hidden="true">/</span> Un espace à
                votre image.
            </p>
        </div>
    );
}
