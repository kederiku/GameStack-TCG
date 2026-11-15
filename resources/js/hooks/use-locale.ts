import { usePage } from '@inertiajs/react';

export type Locale = 'fr' | 'en';

export function useLocale() {
    const { locale } = usePage().props;
    const t = (fr: string, en: string) => (locale === 'en' ? en : fr);
    const localizedUrl = (path: string) => {
        const url = new URL(path, 'http://local');
        url.searchParams.set('lang', locale);
        return `${url.pathname}${url.search}${url.hash}`;
    };
    const date = (value: string | null) =>
        value
            ? new Intl.DateTimeFormat(locale, {
                  day: 'numeric',
                  month: 'short',
                  year: 'numeric',
                  timeZone: 'UTC',
              }).format(new Date(`${value}T12:00:00Z`))
            : t('Date non renseignée', 'Date not listed');
    const languageName = (code: string, fallback?: string) => {
        try {
            return (
                new Intl.DisplayNames([locale], { type: 'language' }).of(
                    code,
                ) ??
                fallback ??
                code
            );
        } catch {
            return fallback ?? code;
        }
    };

    return { locale, t, localizedUrl, date, languageName };
}
