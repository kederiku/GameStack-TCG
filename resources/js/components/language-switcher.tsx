import { router, usePage } from '@inertiajs/react';
import { Globe2 } from 'lucide-react';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { switchLocale } from '@/lib/localized-navigation';
import { useLocalizedRoutes } from '@/hooks/use-localized-routes';
import { useLocale } from '@/hooks/use-locale';

export function LanguageSwitcher() {
    const { localizedUrls, availableLocales, routeKey } = usePage().props;
    const routes = useLocalizedRoutes();
    const { locale, t } = useLocale();

    if (Object.keys(localizedUrls).length === 0) return null;

    return (
        <Select
            value={locale}
            onValueChange={(language) => {
                switchLocale(
                    localizedUrls,
                    language,
                    window.location.hash,
                    (url) => {
                        router.post(
                            routes['locale.update'].url(),
                            { locale: language, route: routeKey, target: url },
                            {
                                preserveScroll: true,
                                preserveState: true,
                                headers: { 'X-Locale-Switch': 'true' },
                            },
                        );
                    },
                );
            }}
        >
            <SelectTrigger
                aria-label={t('Langue du site', 'Site language')}
                className="h-11 w-auto gap-2 border-transparent bg-transparent px-2 shadow-none hover:bg-accent"
            >
                <Globe2
                    className="size-4 shrink-0 text-muted-foreground"
                    aria-hidden="true"
                />
                <SelectValue>{locale.toUpperCase()}</SelectValue>
            </SelectTrigger>
            <SelectContent align="end" lang={locale}>
                {availableLocales
                    .filter(
                        ({ code }) =>
                            localizedUrls[code as keyof typeof localizedUrls],
                    )
                    .map(({ code, label }) => (
                        <SelectItem
                            key={code}
                            value={code}
                            textValue={code.toUpperCase()}
                        >
                            {label}
                        </SelectItem>
                    ))}
            </SelectContent>
        </Select>
    );
}
