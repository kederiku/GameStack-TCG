import type { PropsWithChildren } from 'react';
import { createInertiaApp } from '@inertiajs/react';
import { Toaster } from '@/components/ui/sonner';
import { TooltipProvider } from '@/components/ui/tooltip';
import { initializeTheme } from '@/hooks/use-appearance';
import { LocaleProvider } from '@/i18n/provider';
import AppLayout from '@/layouts/app-layout';
import AuthLayout from '@/layouts/auth-layout';
import PublicLayout from '@/layouts/public-layout';
import SettingsLayout from '@/layouts/settings/layout';

function LocalizedApp({ children }: PropsWithChildren) {
    return (
        <LocaleProvider>
            {children}
            <Toaster />
        </LocaleProvider>
    );
}

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

void createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    layout: (name) => {
        switch (true) {
            case name === 'welcome':
            case name.startsWith('games/'):
                return [LocalizedApp, PublicLayout];
            case name.startsWith('auth/'):
                return [LocalizedApp, AuthLayout];
            case name.startsWith('settings/'):
                return [LocalizedApp, AppLayout, SettingsLayout];
            default:
                return [LocalizedApp, AppLayout];
        }
    },
    strictMode: true,
    withApp(app) {
        return <TooltipProvider delayDuration={0}>{app}</TooltipProvider>;
    },
    progress: {
        color: '#3767ef',
    },
});

// Keep the first render and client navigation in the same dark theme.
initializeTheme();
