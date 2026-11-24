import { useLocalizedRoutes } from '@/hooks/use-localized-routes';
import { useLocale } from '@/hooks/use-locale';
import { Link } from '@inertiajs/react';
import {
    ArrowUpRight,
    Compass,
    House,
    LayoutGrid,
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
import type { NavItem } from '@/types';

export function AppSidebar() {
    const { t } = useLocale();
    const routes = useLocalizedRoutes();
    const mainNavItems: NavItem[] = [
        {
            title: t('common.app-sidebar.my-space'),
            href: routes.dashboard(),
            icon: LayoutGrid,
        },
        {
            title: t('common.app-sidebar.explore-games'),
            href: `${routes.home.url()}#jeux`,
            icon: Compass,
        },
    ];
    const accountNavItems: NavItem[] = [
        {
            title: t('common.app-sidebar.my-profile'),
            href: routes['profile.edit'](),
            icon: UserRound,
        },
        {
            title: t('common.app-sidebar.security'),
            href: routes['security.edit'](),
            icon: ShieldCheck,
        },
    ];

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
                                href={routes.home()}
                                aria-label={t(
                                    'common.app-sidebar.gamestackr-tcg-home',
                                )}
                                onClick={() => setOpenMobile(false)}
                            >
                                <BrandLogo className="brand-logo-small group-data-[collapsible=icon]:[&>.brand-logo-name]:hidden" />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>
            <SidebarContent className="gap-7 py-6">
                <NavMain
                    label={t('common.app-sidebar.your-space')}
                    items={mainNavItems}
                />
                <NavMain
                    label={t('common.app-sidebar.account')}
                    items={accountNavItems}
                />
            </SidebarContent>
            <SidebarFooter className="gap-3 border-t border-sidebar-border p-3 group-data-[collapsible=icon]:p-2">
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton
                            asChild
                            tooltip={t('common.app-sidebar.back-to-home')}
                            className="h-11"
                        >
                            <Link
                                href={routes.home()}
                                onClick={() => setOpenMobile(false)}
                            >
                                <House aria-hidden="true" />
                                <span>
                                    {t('common.app-sidebar.back-to-home')}
                                </span>
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
