<?php

namespace Tests\Feature\Localization;

use App\Http\Middleware\HandleInertiaRequests;
use App\Models\User;
use App\Support\Localization\LocalizedRoutes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Concerns\ForwardOnlyRefreshDatabase;
use Tests\TestCase;

class LocalizedPreferencesTest extends TestCase
{
    use ForwardOnlyRefreshDatabase;

    public function test_url_does_not_overwrite_saved_preference(): void
    {
        $user = User::factory()->create(['preferred_locale' => 'fr']);
        $this->actingAs($user)->get('/en')->assertOk()->assertInertia(fn (Assert $page) => $page->where('locale', 'en'));
        $this->assertSame('fr', $user->fresh()->preferred_locale);
        $this->post('/en/preferences/locale', ['locale' => 'en', 'route' => 'home', 'target' => '/en'])
            ->assertRedirect('/en')->assertCookie('site_locale', 'en');
        $this->assertSame('en', $user->fresh()->preferred_locale);
    }

    public function test_invalid_preference_and_target_are_rejected_without_writes(): void
    {
        $user = User::factory()->create(['preferred_locale' => 'fr']);
        foreach ([
            ['locale' => ['en']], ['locale' => 'de'], ['target' => 'https://example.com/en'],
            ['target' => '//example.com/en'], ['target' => '/fr'], ['route' => 'login'],
            ['target' => '/en?signature=anything'],
        ] as $invalid) {
            $this->actingAs($user)->postJson('/en/preferences/locale', array_replace([
                'locale' => 'en', 'route' => 'home', 'target' => '/en',
            ], $invalid))->assertUnprocessable()->assertCookieMissing('site_locale');
            $this->assertSame('fr', $user->fresh()->preferred_locale);
        }
    }

    public function test_guest_switch_preserves_query_fragment_and_does_not_replace_intended(): void
    {
        $this->withSession(['url.intended' => '/en/settings/security'])
            ->post('/fr/preferences/langue', ['locale' => 'en', 'route' => 'home', 'target' => '/en?page=2&edition=ja#releases'])
            ->assertRedirect('/en?page=2&edition=ja#releases')->assertCookie('site_locale', 'en')
            ->assertSessionHas('url.intended', '/en/settings/security');
    }

    public function test_legacy_resolution_prefers_valid_query_then_user_then_cookie(): void
    {
        $user = User::factory()->create(['preferred_locale' => 'en']);
        $this->actingAs($user)->withCookie('site_locale', 'fr')->get('/')->assertRedirect('/en');
        $this->get('/?lang=fr')->assertRedirect('/fr')->assertCookieMissing('site_locale');
        $this->get('/?lang[]=fr')->assertRedirect('/en');
        $this->assertSame('en', $user->fresh()->preferred_locale);
    }

    public function test_legacy_get_and_head_choose_locale_without_persisting_browser_preference(): void
    {
        $this->get('/?lang=en')->assertRedirect('/en')->assertCookieMissing('site_locale');
        $this->get('/')->assertRedirect('/fr')->assertCookieMissing('site_locale');
        $this->withCookie('site_locale', 'fr')->get('/login?lang=en')
            ->assertRedirect('/en/login')->assertCookieMissing('site_locale');
        $this->head('/?lang=en')->assertRedirect('/en')->assertCookieMissing('site_locale');
        $this->get('/')->assertRedirect('/fr')->assertCookieMissing('site_locale');
        $this->post('/fr/preferences/langue', ['locale' => 'en', 'route' => 'home', 'target' => '/en'])
            ->assertRedirect('/en')->assertCookie('site_locale', 'en');
    }

    public function test_switch_rejects_signed_action_and_retains_reset_email_context(): void
    {
        $user = User::factory()->unverified()->create(['preferred_locale' => 'fr']);
        $target = URL::temporarySignedRoute('site.en.verification.verify', now()->addHour(), ['id' => $user->id, 'hash' => sha1($user->email)], false);
        $this->actingAs($user)->postJson('/fr/preferences/langue', ['locale' => 'en', 'route' => 'verification.verify', 'target' => $target])->assertUnprocessable();
        $this->assertSame('fr', $user->fresh()->preferred_locale);
        $request = Request::create($target);
        $route = Route::getRoutes()->match($request);
        $request->setRouteResolver(fn () => $route);
        $request->attributes->set('site_locale', 'en');
        $this->assertSame([], LocalizedRoutes::alternates($request));
        $this->post('/fr/preferences/langue', ['locale' => 'en', 'route' => 'password.reset', 'target' => '/en/reset-password/token?email=user%40example.com'])
            ->assertRedirect('/en/reset-password/token?email=user%40example.com');
    }

    public function test_disabled_saved_locale_falls_back_to_default_without_rewriting_account(): void
    {
        $user = User::factory()->create(['preferred_locale' => 'de']);
        $this->actingAs($user)->withCookie('site_locale', 'en')->get('/')->assertRedirect('/fr');
        $this->assertSame('de', $user->fresh()->preferred_locale);
        $this->assertSame('fr', $user->preferredLocale());
    }

    public function test_inertia_post_switch_preserves_fragment_flag_on_its_get_response_only(): void
    {
        $this->withHeaders([
            'X-Inertia' => 'true', 'X-Locale-Switch' => 'true',
            'X-Inertia-Version' => app(HandleInertiaRequests::class)->version(request()),
        ])->post('/fr/preferences/langue', ['locale' => 'en', 'route' => 'home', 'target' => '/en?page=2&edition=ja#releases'])
            ->assertStatus(409)->assertHeader('X-Inertia-Redirect', url('/en?page=2&edition=ja#releases'))
            ->assertCookie('site_locale', 'en');
        // Inertia follows X-Inertia-Redirect using a GET with the visit options and headers.
        $this->get('/en?page=2&edition=ja')->assertOk()->assertJsonPath('preserveFragment', true)->assertJsonPath('props.locale', 'en');
        $this->flushHeaders()->withHeaders([
            'X-Inertia' => 'true', 'X-Inertia-Version' => app(HandleInertiaRequests::class)->version(request()),
        ])->get('/en')->assertOk()->assertJsonMissingPath('preserveFragment');
    }
}
