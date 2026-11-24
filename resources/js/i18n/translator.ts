import { choose } from 'laravel-translator/pluralizer';
import type { Replacements, Translate, TranslateChoice } from './types';

type Dictionary = Readonly<Record<string, string>>;
type LocaleDefinition = { readonly laravelLocale: string };

export function createTranslator<Key extends string, Locale extends string>(
    dictionaries: Readonly<Record<Locale, Dictionary>>,
    definitions: Readonly<Record<Locale, LocaleDefinition>>,
    locale: Locale,
    fallback: Locale,
): { t: Translate<Key>; tChoice: TranslateChoice<Key> } {
    const lookup = (key: Key) => {
        const messageLocale = Object.hasOwn(dictionaries[locale], key)
            ? locale
            : fallback;
        const dictionary = dictionaries[messageLocale];

        return {
            line: Object.hasOwn(dictionary, key) ? dictionary[key] : key,
            laravelLocale: definitions[messageLocale].laravelLocale,
        };
    };

    return {
        t(key, replacements) {
            return interpolate(lookup(key).line, replacements);
        },
        tChoice(key, count, replacements) {
            const { line, laravelLocale } = lookup(key);

            return interpolate(choose(line, count, laravelLocale), {
                ...replacements,
                count,
            });
        },
    };
}

function upperFirst(value: string): string {
    return value.slice(0, 1).toUpperCase() + value.slice(1);
}

function interpolate(line: string, replacements: Replacements = {}): string {
    const values = new Map<string, string>();

    for (const [name, replacement] of Object.entries(replacements)) {
        const value = String(replacement);
        values.set(upperFirst(name), upperFirst(value));
        values.set(name.toUpperCase(), value.toUpperCase());
        values.set(name, value);
    }

    // The callback preserves dollar signs and replaces all occurrences in one pass.
    return line.replace(
        /:([a-zA-Z_][a-zA-Z_0-9]*)/g,
        (token, name: string) => values.get(name) ?? token,
    );
}
