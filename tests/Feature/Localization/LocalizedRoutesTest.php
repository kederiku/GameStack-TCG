<?php

namespace Tests\Feature\Localization;

use App\Http\Controllers\PublicGameController;
use App\Http\Middleware\HandleInertiaRequests;
use App\Models\Game;
use App\Models\User;
use App\Support\GameSlug;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class LocalizedRoutesTest extends TestCase
{
    use RefreshDatabase;

    public function test_url_locale_wins_and_is_restored_after_response(): void
    {
        app()->setLocale('en');
        $this->withCookie('site_locale', 'en')->get('/fr?lang=en')->assertOk()
            ->assertInertia(fn (Assert $page) => $page->where('locale', 'fr')->where('routeKey', 'home')
                ->where('localizedUrls.en', '/en')->where('direction', 'ltr')->has('availableLocales', 2));
        $this->assertSame('en', app()->getLocale());
        $this->get('/en')->assertOk()->assertInertia(fn (Assert $page) => $page->where('locale', 'en'));
        $this->get('/de/connexion')->assertNotFound();
    }

    public function test_fixed_settings_routes_and_model_binding_use_translated_paths(): void
    {
        $this->assertSame('fr/parametres/profil', Route::getRoutes()->getByName('site.fr.profile.edit')?->uri());
        $this->actingAs(User::factory()->create())->get('/fr/parametres/profil')->assertOk();
        $game = Game::factory()->create(['slug' => 'pokemon']);
        $this->get('/en/pokemon?catalog=international&language=ja&q=base&page=2&edition=ja&lang=fr')
            ->assertOk()->assertInertia(fn (Assert $page) => $page
            ->where('currentGame.id', $game->id)->where('currentGame.url', '/en/pokemon')
            ->where('localizedUrls.fr', '/fr/pokemon?catalog=international&language=ja&q=base&page=2&edition=ja'));
    }

    public function test_legacy_game_url_keeps_selection(): void
    {
        Game::factory()->create(['slug' => 'pokemon']);
        $response = $this->get('/pokemon?lang=en&edition=ja&page=2');
        $response->assertStatus(302)->assertRedirect('/en/pokemon?edition=ja&page=2');
        $this->assertTrue($response->headers->hasCacheControlDirective('private'));
        $this->get('/en/pokemon?edition=ja&page=2')->assertOk();
        $this->head('/pokemon?lang=en')->assertRedirect('/en/pokemon');
    }

    public function test_legacy_redirects_validate_queries_and_visibility(): void
    {
        Game::factory()->create(['slug' => 'hidden', 'is_visible' => false]);
        $this->get('/hidden?lang=en')->assertNotFound();
        $this->get('/missing')->assertNotFound();
        $this->get('/unknown/path')->assertNotFound();
        $this->get('/?lang[]=en&page=-2&edition[]=ja&redirect=https://example.com')->assertRedirect('/fr');
        $this->post('/pokemon')->assertStatus(405);
        $this->withCookie('site_locale', 'en')->get('/settings/profile')->assertRedirect('/en/settings/profile');
    }

    public function test_translated_segments_and_active_codes_are_reserved(): void
    {
        foreach (['fr', 'en', 'connexion', 'parametres', 'profil', 'securite', 'mon-espace'] as $slug) {
            $this->assertContains($slug, GameSlug::reserved());
        }
    }

    public function test_mutation_aliases_process_the_request_before_localized_redirect(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->patch('/settings/profile?lang=en', ['name' => 'Updated', 'email' => $user->email])
            ->assertRedirect('/en/settings/profile');
        $this->assertSame('Updated', $user->fresh()->name);
        $this->patch('/fr/parametres/profil', ['name' => 'Modifié', 'email' => $user->email])->assertRedirect('/fr/parametres/profil');
        $this->assertSame('Modifié', $user->fresh()->name);
    }

    public function test_inertia_uses_url_code_when_laravel_locale_differs(): void
    {
        config(['localization.locales.pt-BR' => ['laravelLocale' => 'pt_BR', 'htmlLang' => 'pt-BR', 'label' => 'Português', 'direction' => 'ltr']]);
        $root = sys_get_temp_dir().'/gamestackr-routes-'.bin2hex(random_bytes(8));
        File::copyDirectory(lang_path(), $root);
        File::copyDirectory($root.'/en', $root.'/pt_BR');
        $this->app->useLangPath($root);
        $this->beforeApplicationDestroyed(fn () => File::deleteDirectory($root));
        Route::middleware('web')->get('pt-BR', [PublicGameController::class, 'index'])
            ->defaults('_locale', 'pt-BR')->name('site.pt-BR.home');
        Route::getRoutes()->refreshNameLookups();
        $this->get('/pt-BR')->assertOk()->assertInertia(fn (Assert $page) => $page->where('locale', 'pt-BR'));
    }

    public function test_language_switch_response_preserves_fragment_despite_query_normalization(): void
    {
        Game::factory()->create(['slug' => 'pokemon']);
        $this->get('/en/pokemon?language=fr&catalog=international', [
            'X-Inertia' => 'true', 'X-Locale-Switch' => 'true',
            'X-Inertia-Version' => app(HandleInertiaRequests::class)->version(request()),
        ])->assertOk()->assertJsonPath('preserveFragment', true);
        $this->get('/en', [
            'X-Inertia' => 'true',
            'X-Inertia-Version' => app(HandleInertiaRequests::class)->version(request()),
        ])->assertOk()->assertJsonMissingPath('preserveFragment');
    }
}
