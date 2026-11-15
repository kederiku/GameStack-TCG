<?php

namespace Tests\Feature\Development;

use App\Support\Development\ServiceCatalog;
use App\Support\Development\ServiceHealth;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Process;
use Tests\TestCase;

class ServiceHealthTest extends TestCase
{
    public function test_read_only_checks_report_a_responding_database_and_http_services(): void
    {
        Http::preventStrayRequests();
        Http::fake([
            'http://mailpit:8025/readyz' => Http::response('OK'),
            'http://garage:3903/health' => Http::response('Garage is fully operational'),
        ]);

        $results = app(ServiceHealth::class)->check();

        foreach (['postgres', 'mailpit', 'garage'] as $service) {
            $this->assertTrue($results[$service]['responding']);
            $this->assertGreaterThanOrEqual(0, $results[$service]['milliseconds']);
        }
    }

    public function test_failures_do_not_leak_details_or_hide_other_service_results(): void
    {
        Http::preventStrayRequests();
        Http::fake([
            'http://mailpit:8025/readyz' => fn () => throw new ConnectionException('sensitive-host'),
            'http://garage:3903/health' => Http::response('OK'),
        ]);
        Process::fake(['*' => Process::result(errorOutput: 'sensitive-password', exitCode: 1)]);

        $results = app(ServiceHealth::class)->check();

        $this->assertFalse($results['postgres']['responding']);
        $this->assertFalse($results['mailpit']['responding']);
        $this->assertTrue($results['garage']['responding']);
        $this->assertStringNotContainsString('sensitive', json_encode($results));
    }

    public function test_an_http_error_is_not_a_successful_health_check(): void
    {
        Http::fake(['*' => Http::response('unavailable', 503)]);
        $results = app(ServiceHealth::class)->check();
        $this->assertFalse($results['mailpit']['responding']);
        $this->assertFalse($results['garage']['responding']);
    }

    public function test_database_failure_is_bounded_and_uses_the_test_database(): void
    {
        Http::fake(['*' => Http::response('OK')]);
        $this->assertSame('gamestackr_testing', config('database.connections.pgsql.database'));
        // An unreachable port exercises the actual isolated connection, not the app's connection.
        config(['database.connections.pgsql.port' => 1]);
        $started = microtime(true);
        $results = app(ServiceHealth::class)->check();

        $this->assertFalse($results['postgres']['responding']);
        $this->assertLessThan(4, microtime(true) - $started);
    }

    public function test_browser_links_use_configured_published_ports(): void
    {
        config(['development.mailpit_port' => 18025, 'development.garage_ui_port' => 13909]);
        $links = app(ServiceCatalog::class)->links();

        $this->assertSame('http://localhost:18025', $links['mailpit']['url']);
        $this->assertSame('http://localhost:13909', $links['garage-webui']['url']);
    }
}
