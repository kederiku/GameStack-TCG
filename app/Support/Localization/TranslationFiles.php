<?php

namespace App\Support\Localization;

use Illuminate\Support\Arr;
use Illuminate\Translation\MessageSelector;
use RuntimeException;
use Throwable;

final class TranslationFiles
{
    private const FRONTEND_DOMAINS = ['common', 'auth', 'account', 'catalog'];

    private const DOMAINS = [...self::FRONTEND_DOMAINS, 'admin', 'validation', 'passwords', 'notifications', 'routes'];

    private const FRAMEWORK_AUTH_KEYS = ['failed', 'password', 'throttle'];

    private const ROUTE_SEGMENT = '[\pL\pN\pM-]+|\{[a-zA-Z_][a-zA-Z0-9_]*\??\}';

    /** @return list<string> */
    public function validate(): array
    {
        $errors = [];
        $default = Locales::default();
        $locales = array_column(Locales::all(), 'laravelLocale', 'code');

        if (! isset($locales[$default])) {
            return ["Default interface locale [{$default}] is not active."];
        }

        $pluralForms = array_map($this->pluralForms(...), $locales);

        foreach (self::DOMAINS as $domain) {
            $dictionaries = [];
            $pluralKeys = [];

            foreach ($locales as $code => $locale) {
                try {
                    $dictionaries[$code] = $this->read($locale, $domain);
                    if ($domain !== 'routes') {
                        foreach ($dictionaries[$code] as $key => $line) {
                            if (str_contains($line, '|') || preg_match('/\A[\[{][-\d*,.]+[\]}]/', $line)) {
                                $pluralKeys[$key] = true;
                            }
                        }
                    }
                } catch (RuntimeException $exception) {
                    $errors[] = $exception->getMessage();
                }
            }

            // Use the default dictionary as the reference for shared keys and include
            // keys introduced in other locales so the default cannot silently omit them.
            $reference = ($dictionaries[$default] ?? []) + array_merge([], ...array_values($dictionaries));
            foreach ($reference as $key => $source) {
                foreach ($dictionaries as $code => $lines) {
                    $file = $locales[$code].'/'.$domain.'.php';
                    $target = $lines[$key] ?? null;

                    if ($target === null) {
                        if ($domain !== 'auth' || ! in_array($key, self::FRAMEWORK_AUTH_KEYS, true)) {
                            $errors[] = "{$file}: missing key {$key}.";
                        }

                        continue;
                    }

                    $forms = isset($pluralKeys[$key]) ? $pluralForms[$code] : null;
                    array_push($errors, ...$this->validateEntry($file, $domain === 'routes', $key, $source, $target, $forms));
                }
            }
        }

        return $errors;
    }

    /** @return array<string, string> */
    public function frontend(string $locale): array
    {
        $laravelLocale = Locales::definition($locale)['laravelLocale'];
        $messages = [];

        foreach (self::FRONTEND_DOMAINS as $domain) {
            foreach ($this->read($laravelLocale, $domain) as $key => $value) {
                if ($domain !== 'auth' || ! in_array($key, self::FRAMEWORK_AUTH_KEYS, true)) {
                    $messages[$domain.'.'.$key] = $value;
                }
            }
        }

        ksort($messages, SORT_STRING);

        return $messages;
    }

    /** @return array<string, string> */
    public function routes(string $locale): array
    {
        return $this->read(Locales::definition($locale)['laravelLocale'], 'routes');
    }

    /** @return array<string, string> */
    private function read(string $locale, string $domain): array
    {
        $file = $locale.'/'.$domain.'.php';
        $path = lang_path($file);

        if (! is_file($path)) {
            throw new RuntimeException("{$file}: missing dictionary.");
        }

        try {
            $dictionary = require $path;
        } catch (Throwable $exception) {
            throw new RuntimeException("{$file}: ".$exception->getMessage(), previous: $exception);
        }

        if (! is_array($dictionary)) {
            throw new RuntimeException("{$file}: expected an array of translations.");
        }

        $lines = [];
        foreach (Arr::dot($dictionary) as $key => $line) {
            if ($line === []) {
                continue;
            }

            if (! is_string($key) || $key === '' || ! is_string($line) || ($domain !== 'routes' && trim($line) === '')) {
                throw new RuntimeException("{$file}: {$key} must be a translation string.");
            }

            $lines[$key] = $line;
        }

        return $lines;
    }

    /** @return list<string> */
    private function validateEntry(string $file, bool $route, string $key, string $source, string $target, ?int $forms): array
    {
        $errors = [];

        if ($route && (($target === '') !== ($source === '') || ! $this->validPath($target))) {
            $errors[] = "{$file}: {$key} must be a relative path with literal segments or named parameters.";
        }

        $expected = $this->placeholders($source, $route);
        $actual = $this->placeholders($target, $route);

        if ($actual !== $expected) {
            $kind = $route ? 'parameters' : 'placeholders';
            $found = implode(', ', $actual) ?: 'none';
            $wanted = implode(', ', $expected) ?: 'none';
            $errors[] = "{$file}: {$key} expects {$kind} {$wanted}, found {$found}.";
        }

        if ($forms !== null && ! $this->hasPluralForms($target, $forms)) {
            $errors[] = "{$file}: {$key} needs {$forms} plural forms or explicit ranges covering all counts.";
        }

        return $errors;
    }

    private function validPath(string $path): bool
    {
        $segment = self::ROUTE_SEGMENT;

        return $path === '' || preg_match("~\A(?:{$segment})(?:/(?:{$segment}))*\z~u", $path) === 1;
    }

    /** @return list<string> */
    private function placeholders(string $value, bool $route): array
    {
        $pattern = $route ? '/\{[a-zA-Z_][a-zA-Z0-9_]*\??\}/' : '/(?<!:):[a-zA-Z_][a-zA-Z0-9_]*/';
        preg_match_all($pattern, $route ? $value : strtolower($value), $matches);
        $parameters = array_values(array_unique($matches[0]));
        sort($parameters, SORT_STRING);

        return $parameters;
    }

    private function pluralForms(string $locale): int
    {
        $selector = new MessageSelector;

        // Laravel's cardinal rules use explicit small counts or modulo 10/100.
        return 1 + max(array_map(fn (int $count): int => $selector->getPluralIndex($locale, $count), range(0, 199)));
    }

    private function hasPluralForms(string $value, int $expected): bool
    {
        $segments = explode('|', $value);
        $ranges = [];

        foreach ($segments as $segment) {
            if (trim($segment) === '') {
                return false;
            }

            if (preg_match('/\A(?:\{(-?\d+)\}|\[(-?\d+|\*),(-?\d+|\*)\])\s*\S/s', $segment, $match)) {
                $from = $match[2] ?? $match[1];
                $to = $match[3] ?? $match[1];
                $ranges[] = [$from === '*' ? -INF : (int) $from, $to === '*' ? INF : (int) $to];
            } elseif (preg_match('/\A[\[{][-\d*,.]+[\]}]/', $segment)) {
                return false;
            }
        }

        if ($ranges === []) {
            return count($segments) === $expected;
        }

        if (count($ranges) !== count($segments)) {
            return false;
        }

        sort($ranges);
        $next = 0;

        foreach ($ranges as [$from, $to]) {
            if ($from > $next || $from > $to) {
                return false;
            }

            $next = max($next, $to + 1);
        }

        return $next === INF;
    }
}
