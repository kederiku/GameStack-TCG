<?php

namespace Tests\Feature;

use Inertia\Testing\AssertableInertia as Assert;
use Tests\Concerns\ForwardOnlyRefreshDatabase;
use Tests\TestCase;

class SiteLocaleTest extends TestCase
{
    use ForwardOnlyRefreshDatabase;

    public function test_public_locale_defaults_to_french_and_accepts_english(): void
    {
        $this->get('/')->assertRedirect('/fr');
        $this->get('/?lang=en')->assertCookieMissing('site_locale')->assertRedirect('/en');
        $this->get('/')->assertRedirect('/fr');
        $this->get('/en')->assertSee('<html lang="en" dir="ltr" class="dark">', false)
            ->assertInertia(fn (Assert $page) => $page->where('locale', 'en'));
    }

    public function test_locale_persists_but_invalid_query_values_do_not_replace_it(): void
    {
        $this->withCookie('site_locale', 'en')->get('/')->assertRedirect('/en');
        foreach (['?lang=invalid', '?lang[]=fr'] as $query) {
            $this->withCookie('site_locale', 'en')->get('/'.$query)->assertRedirect('/en');
        }
        $this->withCookie('site_locale', 'en')->get('/?lang=fr')->assertCookieMissing('site_locale')->assertRedirect('/fr');
        $this->get('/')->assertRedirect('/en')->assertCookieMissing('site_locale');
    }

    public function test_old_appearance_cookies_cannot_render_the_site_in_light_mode(): void
    {
        $this->withCookie('appearance', 'light')->get('/fr')->assertSee('class="dark"', false);
    }
}
