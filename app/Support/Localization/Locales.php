<?php

namespace App\Support\Localization;

use Illuminate\Support\Facades\Config;
use InvalidArgumentException;

/**
 * @phpstan-type LocaleDefinition array{code: string, laravelLocale: string, htmlLang: string, label: string, direction: 'ltr'|'rtl'}
 */
final class Locales
{
    /** @return array<string, LocaleDefinition> */
    public static function all(): array
    {
        /** @var array<string, array{laravelLocale: string, htmlLang: string, label: string, direction: 'ltr'|'rtl'}> $configured */
        $configured = Config::array('localization.locales');
        $locales = [];

        foreach ($configured as $code => $definition) {
            $locales[$code] = ['code' => $code, ...$definition];
        }

        return $locales;
    }

    public static function supports(string $locale): bool
    {
        return array_key_exists($locale, self::all());
    }

    public static function default(): string
    {
        return Config::string('localization.default');
    }

    /** @return LocaleDefinition */
    public static function definition(string $locale): array
    {
        return self::all()[$locale] ?? throw new InvalidArgumentException("Unsupported interface locale [{$locale}].");
    }
}
