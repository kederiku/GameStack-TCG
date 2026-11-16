import { localeDefinitions } from '@/i18n/generated/types';
import { useTranslations } from '@/i18n/provider';
import type { LocaleCode, Replacements, TranslationKey } from '@/i18n/types';

export type Locale = LocaleCode;

export function useLocale() {
    const { locale, t: translate, tChoice } = useTranslations();
    const { htmlLang } = localeDefinitions[locale];

    function t(key: TranslationKey, replacements?: Replacements): string;
    /** @deprecated Use a semantic translation key from the Laravel dictionaries. */
    function t(fr: string, en: string): string;
    function t(key: string, replacements?: Replacements | string): string {
        return typeof replacements === 'string'
            ? locale === 'en'
                ? replacements
                : key
            : translate(key as TranslationKey, replacements);
    }

    const localizedUrl = (path: string) => {
        const url = new URL(path, 'http://local');
        url.searchParams.set('lang', locale);
        return `${url.pathname}${url.search}${url.hash}`;
    };
    const date = (value: string | null) =>
        value
            ? new Intl.DateTimeFormat(htmlLang, {
                  day: 'numeric',
                  month: 'short',
                  year: 'numeric',
                  timeZone: 'UTC',
              }).format(new Date(`${value}T12:00:00Z`))
            : t('common.date-unavailable');
    const languageName = (code: string, fallback?: string) => {
        try {
            return (
                new Intl.DisplayNames([htmlLang], { type: 'language' }).of(
                    code,
                ) ??
                fallback ??
                code
            );
        } catch {
            return fallback ?? code;
        }
    };

    return { locale, t, tChoice, localizedUrl, date, languageName };
}
