import AppLayoutTemplate from '@/layouts/app/app-sidebar-layout';
import { useLocale } from '@/hooks/use-locale';
import { useLocalizedRoutes } from '@/hooks/use-localized-routes';
import type { TranslationKey } from '@/i18n/types';

export default function AppLayout({
    breadcrumbs = [],
    children,
}: {
    breadcrumbs?: {
        title: TranslationKey;
        route:
            | 'dashboard'
            | 'profile.edit'
            | 'security.edit'
            | 'appearance.edit';
    }[];
    children: React.ReactNode;
}) {
    const { t } = useLocale();
    const routes = useLocalizedRoutes();
    const resolved = breadcrumbs.map(({ title, route }) => ({
        title: t(title),
        href: routes[route](),
    }));
    return (
        <AppLayoutTemplate breadcrumbs={resolved}>{children}</AppLayoutTemplate>
    );
}
