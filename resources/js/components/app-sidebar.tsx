import { Link } from '@inertiajs/react';
import {
    ArrowUpRight,
    Compass,
    House,
    LayoutGrid,
    Palette,
    ShieldCheck,
    UserRound,
} from 'lucide-react';
import { BrandLogo } from '@/components/brand-logo';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    useSidebar,
} from '@/components/ui/sidebar';
import { dashboard, home } from '@/routes';
import { edit as appearance } from '@/routes/appearance';
import { edit as profile } from '@/routes/profile';
import { edit as security } from '@/routes/security';
import type { NavItem } from '@/types';

const mainNavItems: NavItem[] = [
    { title: 'Mon espace', href: dashboard(), icon: LayoutGrid },
    { title: 'Explorer les jeux', href: `${home.url()}#jeux`, icon: Compass },
];
const accountNavItems: NavItem[] = [
    { title: 'Mon profil', href: profile(), icon: UserRound },
    { title: 'Sécurité', href: security(), icon: ShieldCheck },
    { title: 'Apparence', href: appearance(), icon: Palette },
];

export function AppSidebar() {
    const { setOpenMobile } = useSidebar();

    return (
        <Sidebar collapsible="icon" className="border-sidebar-border">
            <SidebarHeader className="h-20 justify-center border-b border-sidebar-border px-4 group-data-[collapsible=icon]:px-2">
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton
                            asChild
                            className="h-auto py-2 hover:bg-transparent"
                        >
                            <Link
                                href={home()}
                                aria-label="GameStackr TCG — accueil"
                                onClick={() => setOpenMobile(false)}
                            >
                                <BrandLogo className="brand-logo-small group-data-[collapsible=icon]:[&>.brand-logo-name]:hidden" />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>
            <SidebarContent className="gap-7 py-6">
                <NavMain label="Votre espace" items={mainNavItems} />
                <NavMain label="Compte" items={accountNavItems} />
            </SidebarContent>
            <SidebarFooter className="gap-3 border-t border-sidebar-border p-3 group-data-[collapsible=icon]:p-2">
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton
                            asChild
                            tooltip="Retour à l’accueil"
                            className="h-11"
                        >
                            <Link
                                href={home()}
                                onClick={() => setOpenMobile(false)}
                            >
                                <House aria-hidden="true" />
                                <span>Retour à l’accueil</span>
                                <ArrowUpRight
                                    className="ml-auto"
                                    aria-hidden="true"
                                />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
