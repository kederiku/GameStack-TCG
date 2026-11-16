import type { LocaleDefinition } from '@/i18n/generated/types';
import type { LocaleCode } from '@/i18n/types';
import type { Auth } from '@/types/auth';

declare module 'react' {
    interface InputHTMLAttributes<T> {
        passwordrules?: string;
    }
}

declare module '@inertiajs/core' {
    export interface InertiaConfig {
        sharedPageProps: {
            name: string;
            locale: LocaleCode;
            availableLocales: LocaleDefinition[];
            localizedUrls: Partial<Record<LocaleCode, string>>;
            direction: 'ltr' | 'rtl';
            routeKey: string | null;
            auth: Auth;
            sidebarOpen: boolean;
            [key: string]: unknown;
        };
    }
}
