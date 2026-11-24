<?php

namespace Tests\Feature\Localization;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class FrontendTranslationsTest extends TestCase
{
    public function test_frontend_export_excludes_backend_domains(): void
    {
        $path = sys_get_temp_dir().'/gamestackr-frontend-translations-'.bin2hex(random_bytes(8));
        $this->beforeApplicationDestroyed(fn () => File::deleteDirectory($path));

        $this->assertArrayHasKey('localization:export', Artisan::all());
        $this->artisan('localization:export', ['--path' => $path])->assertSuccessful();

        $dictionaries = [];

        foreach (['fr' => 'Connexion', 'en' => 'Log in'] as $locale => $login) {
            $json = File::get($path.'/php_'.$locale.'.json');
            $dictionaries[$locale] = $json;
            $messages = json_decode($json, true, flags: JSON_THROW_ON_ERROR);

            $this->assertSame($login, $messages['auth.login.title']);
            $this->assertSame([], array_filter(array_keys($messages), fn (string $key): bool => ! preg_match('/^(common|auth|account|catalog)\./', $key)));
            $this->assertArrayNotHasKey('auth.failed', $messages);

            $keys = array_keys($messages);
            sort($keys, SORT_STRING);
            $this->assertSame($keys, array_keys($messages));
        }

        $types = File::get($path.'/types.ts');
        $this->assertStringContainsString('export type TranslationKey', $types);
        $this->assertStringContainsString('"auth.login.title"', $types);
        $this->assertStringContainsString('export type LocaleCode', $types);

        $this->artisan('localization:export', ['--path' => $path])->assertSuccessful();
        $this->assertSame($types, File::get($path.'/types.ts'));

        foreach ($dictionaries as $locale => $json) {
            $this->assertSame($json, File::get($path.'/php_'.$locale.'.json'));
        }
    }
}
