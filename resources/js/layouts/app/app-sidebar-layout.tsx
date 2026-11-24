import { useLocale } from '@/hooks/use-locale';
import { AppContent } from '@/components/app-content';
import { AppShell } from '@/components/app-shell';
import { AppSidebar } from '@/components/app-sidebar';
import { AppSidebarHeader } from '@/components/app-sidebar-header';
import type { AppLayoutProps } from '@/types';

export default function AppSidebarLayout({
    children,
    breadcrumbs = [],
}: AppLayoutProps) {
    const { t } = useLocale();
    return (
        <AppShell variant="sidebar">
            <a
                href="#main-content"
                className="sr-only fixed top-2 left-2 z-50 rounded-lg bg-primary px-4 py-3 text-primary-foreground focus:not-sr-only"
            >
                {t('common.app-sidebar-layout.skip-to-content')}
            </a>
            <AppSidebar />
            <AppContent variant="sidebar" id="main-content" className="min-w-0">
                <AppSidebarHeader breadcrumbs={breadcrumbs} />
                {children}
            </AppContent>
        </AppShell>
    );
}
