<?php

namespace Tests\Feature\Filament;

use App\Filament\Pages\DevelopmentServices;
use App\Models\User;
use App\Support\Localization\AdminPanels;
use Filament\Facades\Filament;
use Illuminate\Http\Client\Factory;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\Concerns\ForwardOnlyRefreshDatabase;
use Tests\TestCase;

class DevelopmentServicesTest extends TestCase
{
    use ForwardOnlyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
        Http::fake(['*' => Http::response('OK')]);
        config(['development.snapshot_path' => '/missing/docker-report.json']);
        Filament::setCurrentPanel(Filament::getPanel(AdminPanels::id('fr')));
        $this->actingAs(User::factory()->create(['is_admin' => true]));
    }

    public function test_local_admins_see_the_page_and_external_navigation_links(): void
    {
        $this->app['env'] = 'local';
        config(['development.mailpit_port' => 18025, 'development.garage_ui_port' => 13909]);

        $this->get('/fr/admin/services-developpement')->assertOk()
            ->assertSee('État des services')->assertSee('PostgreSQL')
            ->assertSee('http://localhost:18025', false)->assertSee('http://localhost:13909', false)
            ->assertSee('target="_blank"', false)->assertSee('wire:poll.15s.visible', false);
    }

    public function test_other_environments_hide_navigation_and_deny_direct_access_even_with_debug_enabled(): void
    {
        config(['app.debug' => true]);

        foreach (['production', 'staging', 'testing'] as $environment) {
            $this->app['env'] = $environment;
            $this->get('/fr/admin')->assertOk()
                ->assertDontSee('État des services')->assertDontSee('Mailpit')->assertDontSee('Garage Web UI');
            $this->get('/fr/admin/services-developpement')->assertForbidden();
            $this->get('/en/admin/development-services')->assertForbidden();
        }

        Http::assertNothingSent();
    }

    public function test_guests_regular_users_and_unverified_admins_cannot_access_the_page(): void
    {
        $this->app['env'] = 'local';
        auth()->logout();
        $this->get('/fr/admin/services-developpement')->assertRedirect('/fr/admin/connexion');
        $this->actingAs(User::factory()->create())->get('/fr/admin/services-developpement')->assertForbidden();
        $this->actingAs(User::factory()->unverified()->create(['is_admin' => true]))
            ->get('/fr/admin/services-developpement')->assertForbidden();
        Http::assertNothingSent();
    }

    public function test_refresh_displays_a_service_failure_independently_of_the_docker_report(): void
    {
        $this->app['env'] = 'local';
        $page = Livewire::test(DevelopmentServices::class)
            ->assertSee('Répond')->assertSee('Indisponible');

        Http::swap(new Factory);
        Http::preventStrayRequests();
        Http::fake(['http://mailpit:8025/readyz' => Http::response('internal-secret', 503), '*' => Http::response('OK')]);
        $page->call('refreshStatus')->assertSet('health.mailpit.responding', false)
            ->assertSet('health.garage.responding', true)
            ->assertSee('Ne répond pas')->assertDontSee('internal-secret');
    }

    public function test_livewire_refresh_is_forbidden_after_leaving_local(): void
    {
        $this->app['env'] = 'local';
        $page = Livewire::test(DevelopmentServices::class);
        $this->app['env'] = 'production';
        Http::fake();

        $page->call('refreshStatus')->assertForbidden();
        Http::assertNothingSent();
    }

    public function test_stopped_absent_and_unhealthy_containers_have_distinct_states(): void
    {
        $this->app['env'] = 'local';
        $path = tempnam(sys_get_temp_dir(), 'development-report-');
        config(['development.snapshot_path' => $path]);
        file_put_contents($path, json_encode(['generated_at' => time(), 'services' => [
            'postgres' => [['state' => 'running', 'health' => 'healthy']],
            'mailpit' => [['state' => 'exited', 'health' => 'none']],
            'garage' => [['state' => 'running', 'health' => 'unhealthy']],
        ]]));

        try {
            Livewire::test(DevelopmentServices::class)
                ->assertSee('Arrêté')->assertSee('Dégradé')->assertSee('Absent');
        } finally {
            unlink($path);
        }
    }
}
