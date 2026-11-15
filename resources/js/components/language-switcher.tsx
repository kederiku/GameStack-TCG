import { router, usePage } from '@inertiajs/react';
import { Globe2 } from 'lucide-react';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { useLocale } from '@/hooks/use-locale';

export function LanguageSwitcher() {
    const { url } = usePage();
    const { locale, t } = useLocale();

    return (
        <Select
            value={locale}
            onValueChange={(language) => {
                const next = new URL(url, window.location.origin);
                next.searchParams.set('lang', language);
                router.get(
                    `${next.pathname}${next.search}${next.hash}`,
                    {},
                    { preserveScroll: true, preserveState: true },
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
                <SelectItem value="fr" textValue="FR">
                    Français
                </SelectItem>
                <SelectItem value="en" textValue="EN">
                    English
                </SelectItem>
            </SelectContent>
        </Select>
    );
}
