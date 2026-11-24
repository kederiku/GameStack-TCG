import { useLocalizedRoutes } from '@/hooks/use-localized-routes';
import { useLocale } from '@/hooks/use-locale';
import { Link, router } from '@inertiajs/react';
import { LogOut, Settings } from 'lucide-react';
import {
    DropdownMenuGroup,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
} from '@/components/ui/dropdown-menu';
import { UserInfo } from '@/components/user-info';
import { useMobileNavigation } from '@/hooks/use-mobile-navigation';
import type { User } from '@/types';

type Props = {
    user: User;
    onNavigate?: () => void;
};

export function UserMenuContent({ user, onNavigate }: Props) {
    const { t } = useLocale();
    const routes = useLocalizedRoutes();
    const cleanup = useMobileNavigation();

    const handleNavigate = () => {
        cleanup();
        onNavigate?.();
    };

    const handleLogout = () => {
        handleNavigate();
        router.flushAll();
    };

    return (
        <>
            <DropdownMenuLabel className="p-0 font-normal">
                <div className="flex items-center gap-2 px-1 py-1.5 text-left text-sm">
                    <UserInfo user={user} showEmail={true} />
                </div>
            </DropdownMenuLabel>
            <DropdownMenuSeparator />
            <DropdownMenuGroup>
                <DropdownMenuItem asChild>
                    <Link
                        className="block w-full cursor-pointer"
                        href={routes['profile.edit']()}
                        prefetch
                        onClick={handleNavigate}
                    >
                        <Settings className="mr-2" />
                        {t('common.user-menu-content.settings')}
                    </Link>
                </DropdownMenuItem>
            </DropdownMenuGroup>
            <DropdownMenuSeparator />
            <DropdownMenuItem asChild>
                <Link
                    className="block w-full cursor-pointer"
                    href={routes.logout()}
                    as="button"
                    onClick={handleLogout}
                    data-test="logout-button"
                >
                    <LogOut className="mr-2" />
                    {t('common.user-menu-content.log-out')}
                </Link>
            </DropdownMenuItem>
        </>
    );
}
