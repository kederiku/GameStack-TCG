import AppearanceTabs from '@/components/appearance-tabs';
import { Breadcrumbs } from '@/components/breadcrumbs';
import { SidebarTrigger } from '@/components/ui/sidebar';
import type { BreadcrumbItem } from '@/types';

export function AppSidebarHeader({
    breadcrumbs = [],
}: {
    breadcrumbs?: BreadcrumbItem[];
}) {
    return (
        <header className="flex h-20 shrink-0 items-center justify-between gap-3 border-b border-border px-4 sm:px-8">
            <div className="flex min-w-0 items-center gap-3">
                <SidebarTrigger className="size-10 shrink-0 text-muted-foreground" />
                <div className="min-w-0">
                    <Breadcrumbs breadcrumbs={breadcrumbs} />
                </div>
            </div>
            <AppearanceTabs compact className="shrink-0" />
        </header>
    );
}
