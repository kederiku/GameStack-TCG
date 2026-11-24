import { localeDefinitions } from '@/i18n/generated/types';
import { useTranslations } from '@/i18n/provider';
import type { LocaleCode } from '@/i18n/types';

export type Locale = LocaleCode;

export function useLocale() {
    const { locale, t, tChoice } = useTranslations();
    const { htmlLang } = localeDefinitions[locale];

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
                new Intl.DisplayNames([htmlLang], {
                    type: 'language',
                    fallback: 'none',
                }).of(code) ??
                fallback ??
                code
            );
        } catch {
            return fallback ?? code;
        }
    };

    return { locale, t, tChoice, date, languageName };
}
