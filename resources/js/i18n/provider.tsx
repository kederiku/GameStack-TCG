import { usePage } from '@inertiajs/react';
import { createContext, useContext, useEffect, useMemo } from 'react';
import type { PropsWithChildren } from 'react';
import { dictionaries } from './generated/files';
import {
    defaultLocale,
    fallbackLocale,
    localeDefinitions,
} from './generated/types';
import { createTranslator } from './translator';
import type { LocaleCode, TranslationKey } from './types';

const LocaleContext = createContext<ReturnType<typeof buildLocale> | null>(
    null,
);

function buildLocale(locale: LocaleCode) {
    return {
        locale,
        ...createTranslator<TranslationKey, LocaleCode>(
            dictionaries,
            localeDefinitions,
            locale,
            fallbackLocale,
        ),
    };
}

export function LocaleProvider({ children }: PropsWithChildren) {
    const { locale: pageLocale } = usePage().props;
    const locale = Object.hasOwn(localeDefinitions, pageLocale)
        ? pageLocale
        : defaultLocale;
    const value = useMemo(() => buildLocale(locale), [locale]);
    const { htmlLang, direction } = localeDefinitions[locale];

    useEffect(() => {
        document.documentElement.lang = htmlLang;
        document.documentElement.dir = direction;
    }, [htmlLang, direction]);

    return <LocaleContext value={value}>{children}</LocaleContext>;
}

export function useTranslations() {
    const value = useContext(LocaleContext);

    if (!value) {
        throw new Error('useTranslations requires LocaleProvider');
    }

    return value;
}
