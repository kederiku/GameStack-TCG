<?php

namespace App\Support\Localization;

use Illuminate\Contracts\Translation\Loader;

/** Bridges vendor literal translation keys to PHP-only application dictionaries. */
final class VendorTranslationLoader implements Loader
{
    public const PACKAGES = ['fortify', 'passkeys', 'laravel'];

    public function __construct(private readonly Loader $loader) {}

    /** @return array<string, mixed> */
    public function load($locale, $group, $namespace = null): array
    {
        $messages = $this->loader->load($locale, $group, $namespace);

        if ($group === '*' && $namespace === '*') {
            foreach (self::PACKAGES as $package) {
                $path = lang_path('vendor/'.$package.'/'.$locale.'/messages.php');
                if (is_file($path)) {
                    $messages = array_replace($messages, require $path);
                }
            }
        }

        return $messages;
    }

    public function addNamespace($namespace, $hint): void
    {
        $this->loader->addNamespace($namespace, $hint);
    }

    public function addJsonPath($path): void
    {
        $this->loader->addJsonPath($path);
    }

    public function namespaces(): array
    {
        return $this->loader->namespaces();
    }
}
