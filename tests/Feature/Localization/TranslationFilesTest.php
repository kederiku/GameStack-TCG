<?php

namespace Tests\Feature\Localization;

use App\Support\Localization\TranslationFiles;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class TranslationFilesTest extends TestCase
{
    public function test_checked_in_dictionaries_pass_the_console_check(): void
    {
        $this->assertArrayHasKey('localization:check', Artisan::all());
        $this->artisan('localization:check')->assertSuccessful();
    }

    public function test_route_keys_and_placeholders_are_complete(): void
    {
        $root = $this->useTemporaryDictionaries();
        $this->writeDictionary($root, 'en', 'routes', [
            'auth' => ['reset-password' => 'reset-password/{id}'],
        ]);
        $this->writeDictionary($root, 'en', 'common', [
            'welcome' => 'Welcome!',
            'results' => ':count result|:count results',
        ]);

        $this->assertTrue(class_exists(TranslationFiles::class));
        $files = app(TranslationFiles::class);
        $errors = $files->validate();

        $this->assertContains('en/routes.php: missing key auth.login.', $errors);
        $this->assertContains('en/routes.php: auth.reset-password expects parameters {token}, found {id}.', $errors);
        $this->assertContains('en/common.php: welcome expects placeholders :name, found none.', $errors);
        $this->assertArrayNotHasKey('auth.login', $files->routes('en'));
        $this->assertSame('connexion', $files->routes('fr')['auth.login']);
        $this->assertArrayHasKey('localization:check', Artisan::all());
        $this->artisan('localization:check')
            ->expectsOutputToContain('en/routes.php: missing key auth.login.')
            ->assertFailed();
    }

    #[DataProvider('duplicateRouteParameters')]
    public function test_route_parameter_names_cannot_repeat(string $path): void
    {
        $root = $this->useTemporaryDictionaries();
        $this->writeDictionary($root, 'en', 'routes', [
            'home' => '',
            'auth' => ['login' => 'login', 'reset-password' => $path],
        ]);

        $this->assertContains(
            'en/routes.php: auth.reset-password repeats parameter {token}.',
            app(TranslationFiles::class)->validate(),
        );
    }

    /** @return array<string, array{string}> */
    public static function duplicateRouteParameters(): array
    {
        return [
            'required twice' => ['reset-password/{token}/{token}'],
            'required and optional' => ['reset-password/{token}/{token?}'],
        ];
    }

    public function test_plural_forms_follow_the_locale_instead_of_matching_the_fallback_form_count(): void
    {
        $root = $this->useTemporaryDictionaries();
        $this->writeDictionary($root, 'en', 'common', [
            'welcome' => 'Welcome, :name!',
            'results' => '{0} No results|{1} :count result|[2,*] :count results',
        ]);
        config(['localization.locales.ja' => ['laravelLocale' => 'ja', 'htmlLang' => 'ja', 'label' => '日本語', 'direction' => 'ltr']]);
        File::copyDirectory($root.'/en', $root.'/ja');
        foreach (['fortify', 'passkeys', 'laravel'] as $package) {
            File::copyDirectory($root.'/vendor/'.$package.'/en', $root.'/vendor/'.$package.'/ja');
        }
        $this->writeDictionary($root, 'ja', 'common', [
            'welcome' => 'ようこそ、:name！',
            'results' => ':count 件の検索結果',
        ]);
        $this->writeDictionary($root, 'ja', 'routes', [
            'home' => '',
            'auth' => ['login' => 'ログイン', 'reset-password' => 'パスワード再設定/{token}'],
        ]);

        $this->assertTrue(class_exists(TranslationFiles::class));
        $files = app(TranslationFiles::class);
        $this->assertSame([], $files->validate());

        $this->writeDictionary($root, 'en', 'common', [
            'welcome' => 'Welcome, :name!',
            'results' => ':count result',
        ]);

        $this->assertContains('en/common.php: results needs 2 plural forms or explicit ranges covering all counts.', $files->validate());

        $this->writeDictionary($root, 'en', 'common', [
            'welcome' => 'Welcome, :name!',
            'results' => '{1} :count result|[2,*] :count results',
        ]);

        $this->assertContains('en/common.php: results needs 2 plural forms or explicit ranges covering all counts.', $files->validate());
    }

    public function test_regional_locale_uses_its_own_dictionary_and_exports_only_frontend_keys(): void
    {
        $root = $this->useTemporaryDictionaries();
        config([
            'localization.locales.de' => ['laravelLocale' => 'de', 'htmlLang' => 'de', 'label' => 'Deutsch', 'direction' => 'ltr'],
            'localization.locales.pt-BR' => ['laravelLocale' => 'pt_BR', 'htmlLang' => 'pt-BR', 'label' => 'Português do Brasil', 'direction' => 'ltr'],
        ]);
        File::copyDirectory($root.'/en', $root.'/de');
        foreach (['fortify', 'passkeys', 'laravel'] as $package) {
            File::copyDirectory($root.'/vendor/'.$package.'/en', $root.'/vendor/'.$package.'/de');
        }
        File::copyDirectory($root.'/en', $root.'/pt_BR');
        foreach (['fortify', 'passkeys', 'laravel'] as $package) {
            File::copyDirectory($root.'/vendor/'.$package.'/en', $root.'/vendor/'.$package.'/pt_BR');
        }
        $this->writeDictionary($root, 'pt_BR', 'common', [
            'welcome' => 'Olá, :name!',
            'results' => ':count resultado|:count resultados',
        ]);
        $this->writeDictionary($root, 'pt_BR', 'auth', [
            'failed' => 'Credenciais inválidas.',
            'login' => ['title' => 'Entrar'],
        ]);

        $this->assertTrue(class_exists(TranslationFiles::class));
        $files = app(TranslationFiles::class);
        $this->assertSame([], $files->validate());
        $this->assertSame([
            'account.title' => 'Account',
            'auth.login.title' => 'Entrar',
            'catalog.title' => 'Catalog',
            'common.results' => ':count resultado|:count resultados',
            'common.welcome' => 'Olá, :name!',
        ], $files->frontend('pt-BR'));
    }

    public function test_backend_auth_keys_require_parity_but_stay_out_of_frontend(): void
    {
        $root = $this->useTemporaryDictionaries();
        $keys = ['failed' => 'Failed', 'password' => 'Invalid password', 'throttle' => 'Retry in :seconds'];
        $this->writeDictionary($root, 'fr', 'auth', $keys);
        $this->writeDictionary($root, 'en', 'auth', []);
        $files = app(TranslationFiles::class);
        foreach (array_keys($keys) as $key) {
            $this->assertContains("en/auth.php: missing key {$key}.", $files->validate());
            $this->assertArrayNotHasKey('auth.'.$key, $files->frontend('fr'));
        }
    }

    public function test_vendor_aliases_are_required_checked_and_never_exported(): void
    {
        $root = sys_get_temp_dir().'/gamestackr-vendor-copy-'.bin2hex(random_bytes(8));
        File::copyDirectory(lang_path(), $root);
        $this->beforeApplicationDestroyed(fn () => File::deleteDirectory($root));
        $this->app->useLangPath($root);
        $path = $root.'/vendor/passkeys/en/messages.php';
        $lines = require $path;
        unset($lines['Unable to register this passkey.']);
        File::put($path, '<?php return '.var_export($lines, true).';');
        $errors = app(TranslationFiles::class)->validate();
        $this->assertContains('vendor/passkeys/en/messages.php: missing key Unable to register this passkey..', $errors);
        $this->artisan('localization:check')->assertFailed();
        $this->assertArrayNotHasKey('Unable to register this passkey.', app(TranslationFiles::class)->frontend('en'));
    }

    private function useTemporaryDictionaries(): string
    {
        $root = sys_get_temp_dir().'/gamestackr-translations-'.bin2hex(random_bytes(8));
        $this->beforeApplicationDestroyed(fn () => File::deleteDirectory($root));
        $this->app->useLangPath($root);

        foreach (['fr', 'en'] as $locale) {
            $this->writeDictionary($root, $locale, 'routes', [
                'home' => '',
                'auth' => [
                    'login' => $locale === 'fr' ? 'connexion' : 'login',
                    'reset-password' => $locale === 'fr' ? 'reinitialiser/{token}' : 'reset-password/{token}',
                ],
            ]);
            $this->writeDictionary($root, $locale, 'common', [
                'welcome' => $locale === 'fr' ? 'Bonjour, :name !' : 'Welcome, :name!',
                'results' => $locale === 'fr' ? ':count résultat|:count résultats' : ':count result|:count results',
            ]);
            $this->writeDictionary($root, $locale, 'auth', ['failed' => 'Invalid credentials', 'login' => ['title' => 'Sign in']]);

            foreach (['account' => 'Account', 'catalog' => 'Catalog', 'admin' => 'Admin', 'notifications' => 'Notification'] as $domain => $title) {
                $this->writeDictionary($root, $locale, $domain, ['title' => $title]);
            }
            $this->writeDictionary($root, $locale, 'validation', [
                'required' => 'The :attribute field is required.',
                'attributes' => [],
            ]);
            $this->writeDictionary($root, $locale, 'passwords', ['reset' => 'Password reset.']);
            foreach (['fortify', 'passkeys', 'laravel'] as $package) {
                File::ensureDirectoryExists($root.'/vendor/'.$package.'/'.$locale);
                File::put($root.'/vendor/'.$package.'/'.$locale.'/messages.php', "<?php return ['Vendor message' => 'Vendor message'];");
            }
        }

        return $root;
    }

    /** @param array<string, mixed> $translations */
    private function writeDictionary(string $root, string $locale, string $domain, array $translations): void
    {
        File::ensureDirectoryExists($root.'/'.$locale);
        File::put($root.'/'.$locale.'/'.$domain.'.php', '<?php return '.var_export($translations, true).';');
    }
}
