import { localizedRoutes } from '@/i18n/generated/routes';
import { useLocale } from '@/hooks/use-locale';

type PasskeyAction =
    | 'passkey.login-options'
    | 'passkey.login'
    | 'passkey.confirm-options'
    | 'passkey.confirm'
    | 'passkey.registration-options'
    | 'passkey.store';

type PasskeyRoutes = Partial<Record<PasskeyAction, { url(): string }>> & {
    'passkey.destroy'?: { url(id: number): string };
};

// Fortify removes its Wayfinder functions entirely when passkeys are disabled.
type Routes = (typeof localizedRoutes)[keyof typeof localizedRoutes];
export function useLocalizedRoutes(): Omit<Routes, keyof PasskeyRoutes> &
    PasskeyRoutes {
    const { locale } = useLocale();
    return localizedRoutes[locale];
}
