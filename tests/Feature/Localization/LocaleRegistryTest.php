<?php

namespace Tests\Feature\Localization;

use App\Models\Language;
use App\Support\Localization\Locales;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\ForwardOnlyRefreshDatabase;
use Tests\TestCase;

class LocaleRegistryTest extends TestCase
{
    use ForwardOnlyRefreshDatabase;

    public function test_active_locales_are_independent_from_content_languages(): void
    {
        Language::query()->create(['code' => 'ja', 'name' => '日本語']);

        $this->assertTrue(class_exists(Locales::class));
        $this->assertSame(['fr', 'en'], array_keys(Locales::all()));
        $this->assertSame('fr', Locales::default());
        $this->assertSame('fr', config('app.locale'));
        $this->assertSame('fr', config('app.fallback_locale'));
        $this->assertFalse(Locales::supports('ja'));
        $this->get('/ja')->assertNotFound();
    }

    public function test_registry_accepts_additional_locales_with_explicit_laravel_identifiers(): void
    {
        config([
            'localization.locales.de' => ['laravelLocale' => 'de', 'htmlLang' => 'de', 'label' => 'Deutsch', 'direction' => 'ltr'],
            'localization.locales.pt-BR' => ['laravelLocale' => 'pt_BR', 'htmlLang' => 'pt-BR', 'label' => 'Português do Brasil', 'direction' => 'ltr'],
        ]);

        $this->assertTrue(class_exists(Locales::class));
        $this->assertTrue(Locales::supports('de'));
        $this->assertTrue(Locales::supports('pt-BR'));
        $this->assertFalse(Locales::supports('pt_BR'));
        $this->assertSame([
            'code' => 'pt-BR',
            'laravelLocale' => 'pt_BR',
            'htmlLang' => 'pt-BR',
            'label' => 'Português do Brasil',
            'direction' => 'ltr',
        ], Locales::definition('pt-BR'));
    }

    public function test_sync_works_without_example_seeder(): void
    {
        $this->assertArrayHasKey('localization:sync-languages', Artisan::all());
        $this->artisan('localization:sync-languages')->assertSuccessful();
        $this->assertSame([
            'fr' => 'Français',
            'en' => 'English',
        ], Language::query()->orderBy('id')->pluck('name', 'code')->all());

        // The unique index is case-insensitive, including rows predating normalization.
        DB::table('languages')->where('code', 'fr')->update(['code' => 'FR', 'name' => 'Nom administré']);

        $this->artisan('localization:sync-languages')->assertSuccessful();

        $this->assertSame([
            'FR' => 'Nom administré',
            'en' => 'English',
        ], Language::query()->orderBy('id')->pluck('name', 'code')->all());
    }
}
