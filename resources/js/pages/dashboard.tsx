import { Head, Link, usePage } from '@inertiajs/react';
import {
    ArrowRight,
    ArrowUpRight,
    BookOpen,
    Compass,
    Heart,
    Layers2,
    Palette,
    ShieldCheck,
    Sparkles,
    UserRound,
} from 'lucide-react';
import { CardFan } from '@/components/card-fan';
import { EmptyState } from '@/components/empty-state';
import { GameGrid } from '@/components/games/game-grid';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import { dashboard } from '@/routes';
import { edit as appearance } from '@/routes/appearance';
import { edit as profile } from '@/routes/profile';
import { edit as security } from '@/routes/security';
import type { PublicGame } from '@/types/game';

const collectionFeatures = [
    {
        icon: Layers2,
        title: 'Vos cartes',
        description: 'Chaque pièce à sa place.',
    },
    {
        icon: BookOpen,
        title: 'Vos séries',
        description: 'La prochaine carte manquante.',
    },
    {
        icon: Heart,
        title: 'Vos envies',
        description: 'Les trouvailles à venir.',
    },
];
const accountLinks = [
    {
        icon: UserRound,
        title: 'Mon profil',
        description: 'Vos informations personnelles',
        href: profile(),
    },
    {
        icon: ShieldCheck,
        title: 'Sécurité',
        description: 'Protéger votre compte',
        href: security(),
    },
    {
        icon: Palette,
        title: 'Apparence',
        description: 'Un espace à votre image',
        href: appearance(),
    },
];

export default function Dashboard({ games }: { games: PublicGame[] }) {
    const { auth } = usePage().props;
    const firstName =
        auth.user?.name.trim().split(/\s+/)[0] || 'collectionneur';

    return (
        <>
            <Head title="Mon espace" />
            <div className="mx-auto flex w-full max-w-7xl flex-1 flex-col gap-8 px-4 py-7 sm:px-8 sm:py-9">
                <div className="flex flex-wrap items-end justify-between gap-5">
                    <div className="min-w-0">
                        <p className="mb-2 text-sm [overflow-wrap:anywhere] text-muted-foreground">
                            Bonjour {firstName}, bienvenue chez vous.
                        </p>
                        <h1 className="font-display text-4xl leading-none font-black uppercase sm:text-5xl">
                            Mon espace
                            <span className="text-brand-highlight">.</span>
                        </h1>
                    </div>
                    <Button
                        asChild
                        variant="outline"
                        className="h-11 rounded-xl bg-card"
                    >
                        <a href="#jeux">
                            <Compass className="size-4" aria-hidden="true" />
                            Explorer les jeux
                        </a>
                    </Button>
                </div>

                <section
                    aria-labelledby="welcome-title"
                    className="brand-surface relative isolate overflow-hidden rounded-2xl border border-border px-6 py-8 sm:px-8 lg:py-10"
                >
                    <div className="relative z-10 max-w-md xl:max-w-[60%]">
                        <p className="mb-4 flex items-center gap-2 text-[0.65rem] font-semibold tracking-[0.18em] text-brand-highlight uppercase">
                            <Sparkles className="size-3.5" aria-hidden="true" />
                            Chaque collection a son histoire
                        </p>
                        <h2
                            id="welcome-title"
                            className="font-display text-3xl leading-tight font-extrabold uppercase sm:text-4xl"
                        >
                            La vôtre commence ici.
                        </h2>
                        <p className="mt-3 max-w-md text-sm leading-7 text-muted-foreground">
                            Vos univers favoris, vos prochaines trouvailles,
                            votre passion. Retrouvez tout ce qui vous anime dans
                            un même espace.
                        </p>
                        <Button asChild className="mt-6 h-11 rounded-xl px-5">
                            <a href="#jeux">
                                Trouver mon univers
                                <ArrowRight
                                    className="size-4"
                                    aria-hidden="true"
                                />
                            </a>
                        </Button>
                    </div>
                    <CardFan className="dashboard-card-fan" />
                </section>

                <div className="grid min-w-0 items-start gap-6 xl:grid-cols-[minmax(0,1fr)_19rem]">
                    <section
                        aria-labelledby="collection-title"
                        className="min-w-0"
                    >
                        <Card className="gap-0 rounded-2xl py-0 shadow-none">
                            <CardHeader className="flex-row flex-wrap items-center justify-between gap-3 border-b border-border px-6 py-5">
                                <h2
                                    id="collection-title"
                                    className="flex items-center gap-3 font-display text-2xl font-extrabold uppercase"
                                >
                                    <Layers2
                                        className="size-5 text-brand-highlight"
                                        aria-hidden="true"
                                    />
                                    Ma collection
                                </h2>
                                <Badge
                                    variant="secondary"
                                    className="rounded-full px-3 py-1 text-[0.65rem] font-medium"
                                >
                                    Bientôt disponible
                                </Badge>
                            </CardHeader>
                            <CardContent className="px-5 py-5 sm:px-6">
                                <EmptyState
                                    icon={BookOpen}
                                    title="Une place pour chaque carte"
                                    description="Votre espace de collection se prépare. Vous pourrez bientôt y réunir vos cartes et suivre les séries qui vous tiennent à cœur."
                                    className="border-0 bg-background/60 py-9"
                                />
                            </CardContent>
                            <div className="grid divide-y divide-border border-t border-border sm:grid-cols-3 sm:divide-x sm:divide-y-0">
                                {collectionFeatures.map(
                                    ({ icon: Icon, title, description }) => (
                                        <div key={title} className="p-5">
                                            <Icon
                                                className="mb-3 size-4 text-muted-foreground"
                                                aria-hidden="true"
                                            />
                                            <p className="text-sm font-medium">
                                                {title}
                                            </p>
                                            <p className="mt-1.5 text-xs leading-5 text-muted-foreground">
                                                {description}
                                            </p>
                                        </div>
                                    ),
                                )}
                            </div>
                        </Card>
                    </section>

                    <aside aria-labelledby="account-title" className="min-w-0">
                        <Card className="gap-3 rounded-2xl shadow-none">
                            <CardHeader>
                                <h2
                                    id="account-title"
                                    className="font-display text-2xl font-extrabold uppercase"
                                >
                                    À votre image
                                </h2>
                                <p className="text-xs leading-5 text-muted-foreground">
                                    Les essentiels de votre compte.
                                </p>
                            </CardHeader>
                            <CardContent className="px-3">
                                <ul className="space-y-1">
                                    {accountLinks.map(
                                        ({
                                            icon: Icon,
                                            title,
                                            description,
                                            href,
                                        }) => (
                                            <li key={title}>
                                                <Link
                                                    href={href}
                                                    className="group flex items-center gap-3 rounded-xl px-3 py-3.5 transition-colors hover:bg-accent/60 focus-visible:outline-2 focus-visible:outline-ring"
                                                >
                                                    <span className="flex size-9 shrink-0 items-center justify-center rounded-xl bg-secondary/60 text-muted-foreground group-hover:text-brand-highlight">
                                                        <Icon
                                                            className="size-4"
                                                            aria-hidden="true"
                                                        />
                                                    </span>
                                                    <span className="min-w-0 flex-1">
                                                        <span className="block text-sm font-medium">
                                                            {title}
                                                        </span>
                                                        <span className="mt-1 block text-xs leading-5 text-muted-foreground">
                                                            {description}
                                                        </span>
                                                    </span>
                                                    <ArrowUpRight
                                                        className="size-3.5 shrink-0 text-muted-foreground"
                                                        aria-hidden="true"
                                                    />
                                                </Link>
                                            </li>
                                        ),
                                    )}
                                </ul>
                            </CardContent>
                        </Card>
                        <p className="px-5 pt-5 text-xs leading-6 text-muted-foreground">
                            Un univers ne suffit jamais.
                            <br />
                            <span className="text-foreground">
                                Une même passion les rassemble.
                            </span>
                        </p>
                    </aside>
                </div>

                <section
                    id="jeux"
                    aria-labelledby="games-title"
                    className="scroll-mt-6 pb-3"
                >
                    <div className="mb-5 flex flex-wrap items-end justify-between gap-3">
                        <div>
                            <p className="mb-2 text-[0.65rem] font-semibold tracking-[0.18em] text-brand-highlight uppercase">
                                D’un univers à l’autre
                            </p>
                            <h2
                                id="games-title"
                                className="font-display text-3xl font-extrabold uppercase"
                            >
                                Les jeux à explorer
                            </h2>
                        </div>
                        <p className="text-sm text-muted-foreground">
                            Retrouvez les TCG qui vous passionnent.
                        </p>
                    </div>
                    <GameGrid
                        games={games}
                        className="lg:grid-cols-2 2xl:grid-cols-3"
                    />
                </section>
                <footer className="border-t border-border pt-5 text-xs text-muted-foreground">
                    GameStackr TCG <span aria-hidden="true">/</span> Vos cartes,
                    vos collections, votre espace.
                </footer>
            </div>
        </>
    );
}

Dashboard.layout = {
    breadcrumbs: [{ title: 'Mon espace', href: dashboard() }],
};
