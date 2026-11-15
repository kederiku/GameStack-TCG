<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class SiteLocaleTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_locale_defaults_to_french_and_accepts_english(): void
    {
        $this->get('/')->assertInertia(fn (Assert $page) => $page->where('locale', 'fr'));
        $this->get('/?lang=en')->assertCookie('site_locale', 'en')
            ->assertSee('<html lang="en" class="dark">', false)
            ->assertInertia(fn (Assert $page) => $page->where('locale', 'en'));
    }

    public function test_locale_persists_but_invalid_query_values_do_not_replace_it(): void
    {
        $this->withCookie('site_locale', 'en')->get('/')->assertInertia(fn (Assert $page) => $page->where('locale', 'en'));
        foreach (['?lang=invalid', '?lang[]=fr'] as $query) {
            $this->withCookie('site_locale', 'en')->get('/'.$query)->assertOk()
                ->assertInertia(fn (Assert $page) => $page->where('locale', 'en'));
        }
        $this->withCookie('site_locale', 'en')->get('/?lang=fr')->assertCookie('site_locale', 'fr')
            ->assertInertia(fn (Assert $page) => $page->where('locale', 'fr'));
    }

    public function test_old_appearance_cookies_cannot_render_the_site_in_light_mode(): void
    {
        $this->withCookie('appearance', 'light')->get('/')->assertSee('class="dark"', false);
    }
}
