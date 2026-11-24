export type {
    LocaleCode,
    LocaleDefinition,
    TranslationKey,
} from './generated/types';

export type Replacements = Readonly<Record<string, string | number>>;

export type Translate<Key extends string> = (
    key: Key,
    replacements?: Replacements,
) => string;

export type TranslateChoice<Key extends string> = (
    key: Key,
    count: number,
    replacements?: Replacements,
) => string;
