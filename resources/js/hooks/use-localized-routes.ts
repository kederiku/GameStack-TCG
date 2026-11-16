import { localizedRoutes } from '@/i18n/generated/routes';
import { useLocale } from '@/hooks/use-locale';

export function useLocalizedRoutes() {
    const { locale } = useLocale();
    return localizedRoutes[locale];
}
