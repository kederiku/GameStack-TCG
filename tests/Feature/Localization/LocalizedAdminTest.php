<?php

namespace Tests\Feature\Localization;

use App\Filament\Resources\Games\GameResource;
use App\Models\Game;
use App\Models\User;
use App\Support\Localization\AdminPanels;
use Filament\Facades\Filament;
use Filament\Panel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\RouteCollection;
use Illuminate\Support\Facades\Route;
use Livewire\Mechanisms\HandleRequests\HandleRequests;
use Tests\Concerns\BuildsCatalog;
use Tests\TestCase;

class LocalizedAdminTest extends TestCase
{
    use BuildsCatalog, RefreshDatabase;

    public function test_panel_locale_is_explicit(): void
    {
        $this->assertSame(['admin-fr', 'admin-en'], array_keys(Filament::getPanels()));
        $this->assertSame(['admin-fr' => 'fr', 'admin-en' => 'en'], AdminPanels::all());
        $this->assertSame('en', AdminPanels::locale('admin-en'));
        $this->assertSame('admin-fr', Filament::getDefaultPanel()->getId());
        $user = User::factory()->create(['is_admin' => true]);
        $this->assertFalse($user->canAccessPanel(Panel::make()->id('admin-fake')));
    }

    public function test_both_panels_use_the_same_records_and_restore_the_locale(): void
    {
        $user = User::factory()->create(['is_admin' => true, 'preferred_locale' => 'en']);
        $game = Game::factory()->create();
        app()->setLocale('en');
        $this->actingAs($user)->get('/en/admin/games')->assertOk()->assertSee('lang="en"', false)->assertSee('Games');
        $this->get('/fr/admin/jeux')->assertOk()->assertSee('lang="fr"', false)->assertSee('Jeux')->assertSee('Rechercher');
        $this->assertSame('en', app()->getLocale());
        $this->get('/fr/admin/jeux/'.$game->id.'/modifier')->assertOk()->assertSee($game->name)
            ->assertSee('/en/admin/games/'.$game->id.'/edit', false)->assertSee('method="POST"', false)
            ->assertSee('setUpFilamentUnsavedDataChangesAlert', false);
        $this->assertSame('en', $user->fresh()->preferred_locale);
        foreach (['/fr/admin/jeux', '/en/admin/games'] as $url) {
            $this->actingAs(User::factory()->create())->get($url)->assertForbidden();
            $this->actingAs(User::factory()->unverified()->create(['is_admin' => true]))->get($url)->assertForbidden();
        }
    }

    public function test_registration_is_independent_of_ambient_locale_and_panel_order(): void
    {
        $original = Route::getRoutes();
        try {
            $inventories = [];
            foreach (['en', 'fr'] as $ambient) {
                app()->setLocale($ambient);
                Route::setRoutes(new RouteCollection);
                require base_path('vendor/filament/filament/routes/web.php');
                $inventories[] = array_map(fn ($route) => [$route->getName(), $route->uri()], Route::getRoutes()->getRoutes());
            }
            $this->assertSame($inventories[0], $inventories[1]);
            $this->assertContains(['filament.admin-fr.resources.jeux.create', 'fr/admin/jeux/creer'], $inventories[0]);
            $this->assertContains(['filament.admin-en.resources.games.edit', 'en/admin/games/{record}/edit'], $inventories[0]);
        } finally {
            Route::setRoutes($original);
        }
        foreach (['en', 'fr', 'en'] as $locale) {
            $panel = Filament::getPanel(AdminPanels::id($locale));
            $this->assertSame($locale === 'fr' ? 'jeux' : 'games', GameResource::getSlug($panel));
            $this->assertStringContainsString('/'.$locale.'/admin/', AdminPanels::url('games.edit', ['record' => 42], $locale));
        }
    }

    public function test_switch_preserves_record_and_table_state_and_validates_real_admin_pages(): void
    {
        $user = User::factory()->create(['is_admin' => true]);
        $game = Game::factory()->create();
        $target = '/en/admin/games/'.$game->id.'/edit?tableFilters[catalog][value]=42&tableSearch=alpha&tableSort=name%3Adesc&page=3#details';
        $this->actingAs($user)->post('/fr/preferences/langue', ['locale' => 'en', 'route' => 'admin.games.edit', 'target' => $target])
            ->assertStatus(303)->assertRedirect('/en/admin/games/'.$game->id.'/edit?tableFilters%5Bcatalog%5D%5Bvalue%5D=42&tableSearch=alpha&tableSort=name%3Adesc&page=3#details')
            ->assertCookie('site_locale', 'en');
        $this->assertSame('en', $user->fresh()->preferred_locale);
        foreach (['/en/admin/games/'.$game->id.'/edit?signature=invalid', '/en/admin/fake', '/fr/admin/jeux/'.$game->id.'/modifier', '/en/admin/games', '//evil.test/en/admin/games/'.$game->id.'/edit'] as $invalid) {
            $this->postJson('/fr/preferences/langue', ['locale' => 'en', 'route' => 'admin.games.edit', 'target' => $invalid])->assertUnprocessable();
        }
    }

    public function test_switch_and_legacy_alias_preserve_the_actual_filament_table_query(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        $first = $this->catalog();
        $first->update(['name' => 'Pick Alpha']);
        $last = $this->catalog($first->game, key: 'last');
        $last->update(['name' => 'Pick Zulu']);
        $otherGame = $this->catalog();
        $otherGame->update(['name' => 'Pick Excluded']);
        $otherSearch = $this->catalog($first->game, key: 'other');
        $otherSearch->update(['name' => 'Unrelated']);
        $query = http_build_query([
            'filters' => ['game' => ['value' => (string) $first->game_id]],
            'search' => 'Pick', 'sort' => 'name:desc', 'page' => '1',
        ]);
        $source = $this->get('/fr/admin/catalogues?'.$query)->assertOk();
        $source->assertSeeInOrder(['Pick Zulu', 'Pick Alpha'])
            ->assertDontSee('.table.records.'.$otherGame->id.'"', false)
            ->assertDontSee('.table.records.'.$otherSearch->id.'"', false);
        // A real table interaction updates the list component while the topbar stays mounted.
        preg_match_all('/wire:snapshot="([^"]+)"/', $this->get('/fr/admin/catalogues')->getContent(), $matches);
        $snapshot = collect($matches[1])->map(fn (string $value) => html_entity_decode($value, ENT_QUOTES))
            ->first(fn (string $value) => array_key_exists('tableSearch', json_decode($value, true)['data']));
        $this->assertNotNull($snapshot);
        $interaction = $this->postJson(app(HandleRequests::class)->getUpdateUri(), ['components' => [[
            'snapshot' => $snapshot,
            'updates' => ['tableSearch' => 'Pick', 'tableFilters.game.value' => (string) $first->game_id],
            'calls' => [['method' => 'sortTable', 'params' => ['name', 'desc'], 'path' => '']],
        ]]], ['X-Livewire' => 'true'])->assertOk();
        $html = $interaction->json('components.0.effects.html');
        $this->assertStringContainsString('Pick Zulu', $html);
        $this->assertStringContainsString('Pick Alpha', $html);
        $this->assertStringNotContainsString('.table.records.'.$otherGame->id.'"', $html);
        $this->assertStringNotContainsString('.table.records.'.$otherSearch->id.'"', $html);

        $expected = '/en/admin/catalogs?'.$query;
        $source->assertSee($expected);
        $this->post('/fr/preferences/langue', [
            'locale' => 'en', 'route' => 'admin.catalogs.index',
            'target' => $expected.'&redirect=https://evil.test#table',
        ])->assertRedirect($expected.'#table');
        $this->get('/admin/catalogs?lang=en&'.$query)->assertRedirect($expected);
        $this->get($expected)->assertOk()->assertSeeInOrder(['Pick Zulu', 'Pick Alpha'])
            ->assertDontSee('.table.records.'.$otherGame->id.'"', false)
            ->assertDontSee('.table.records.'.$otherSearch->id.'"', false);
    }

    public function test_legacy_gets_redirect_privately_preserving_admin_state(): void
    {
        $query = 'tableFilters[catalog][value]=42&tableSearch=alpha&tableSort=name%3Adesc&page=2';
        $response = $this->get('/admin/expansion-editions/42/edit?lang=en&'.$query)
            ->assertRedirect('/en/admin/expansion-editions/42/edit?tableFilters%5Bcatalog%5D%5Bvalue%5D=42&tableSearch=alpha&tableSort=name%3Adesc&page=2');
        $this->assertTrue($response->headers->hasCacheControlDirective('private'));
        $response->assertCookieMissing('site_locale');
        $this->get('/admin/missing')->assertNotFound();
        $this->post('/admin/games')->assertStatus(405);
    }

    public function test_every_historical_admin_page_has_a_localized_alias(): void
    {
        foreach (AdminPanels::all() as $locale) {
            foreach (AdminPanels::resources() as $key => $resource) {
                foreach (array_keys($resource::getPages()) as $page) {
                    $suffix = match ($page) {
                        'create' => '/create', 'edit' => '/42/edit', default => ''
                    };
                    $parameters = $page === 'edit' ? ['record' => 42] : [];
                    $this->get('/admin/'.$key.$suffix.'?lang='.$locale)->assertRedirect(AdminPanels::url($key.'.'.$page, $parameters, $locale));
                }
            }
            foreach (['' => 'dashboard', 'login' => 'login', 'development-services' => 'development-services'] as $path => $page) {
                $this->get('/admin/'.$path.'?lang='.$locale)->assertRedirect(AdminPanels::url($page, [], $locale));
            }
        }
    }

    public function test_real_livewire_transport_restores_each_panel_locale_and_rechecks_access(): void
    {
        $user = User::factory()->create(['is_admin' => true]);
        $this->actingAs($user);
        $snapshots = [];
        foreach (['en' => '/en/admin/games', 'fr' => '/fr/admin/jeux'] as $locale => $url) {
            $html = $this->get($url)->assertOk()->getContent();
            preg_match_all('/wire:snapshot="([^"]+)"/', $html, $matches);
            foreach ($matches[1] as $encoded) {
                $snapshot = html_entity_decode($encoded, ENT_QUOTES);
                $decoded = json_decode($snapshot, true, flags: JSON_THROW_ON_ERROR);
                if (array_key_exists('tableSearch', $decoded['data'])) {
                    $snapshots[$locale] = $snapshot;
                }
            }
        }
        $this->assertCount(2, $snapshots);
        $transport = app(HandleRequests::class)->getUpdateUri();
        $this->assertStringNotContainsString('/admin/', $transport);
        app()->setLocale('en');
        foreach (['en', 'fr', 'en', 'fr'] as $locale) {
            $response = $this->postJson($transport, ['components' => [[
                'snapshot' => $snapshots[$locale], 'updates' => [],
                'calls' => [['method' => '$refresh', 'params' => [], 'path' => '']],
            ]]], ['X-Livewire' => 'true'])->assertOk();
            $snapshot = json_decode($response->json('components.0.snapshot'), true, flags: JSON_THROW_ON_ERROR);
            $this->assertSame($locale, $snapshot['memo']['locale']);
            $this->assertStringContainsString($locale === 'fr' ? 'Rechercher' : 'Search', $response->json('components.0.effects.html'));
            $this->assertSame('en', app()->getLocale());
        }
        $user->forceFill(['is_admin' => false])->save();
        $this->postJson($transport, ['components' => [[
            'snapshot' => $snapshots['fr'], 'updates' => [],
            'calls' => [['method' => '$refresh', 'params' => [], 'path' => '']],
        ]]], ['X-Livewire' => 'true'])->assertForbidden();
    }

    public function test_english_admin_intended_edit_survives_login_and_two_factor(): void
    {
        $user = User::factory()->withTwoFactor()->create(['is_admin' => true]);
        $game = Game::factory()->create();
        $target = '/en/admin/games/'.$game->id.'/edit';
        $this->get($target)->assertRedirect('/en/admin/login');
        $this->get('/en/admin/login')->assertRedirect('/en/login');
        $this->post('/en/login', ['email' => $user->email, 'password' => 'password'], ['X-Inertia' => 'true'])
            ->assertRedirect('/en/two-factor-challenge');
        $this->post('/en/two-factor-challenge', ['recovery_code' => $user->recoveryCodes()[0]], ['X-Inertia' => 'true'])
            ->assertConflict()->assertHeader('X-Inertia-Location', url($target))->assertSessionMissing('url.intended');
        $this->get($target)->assertOk();
    }
}
