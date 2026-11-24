<?php

namespace Tests\Feature\Localization;

use App\Support\Localization\RegisterLocalizedRoutes;
use Illuminate\Routing\RouteCollection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Symfony\Component\Process\Process;
use Tests\TestCase;

class LocalizedRouteCacheTest extends TestCase
{
    public function test_registration_is_independent_of_ambient_locale(): void
    {
        $original = Route::getRoutes();
        $previous = app()->getLocale();
        try {
            $inventories = [];
            foreach (['fr', 'en'] as $locale) {
                app()->setLocale($locale);
                Route::setRoutes(new RouteCollection);
                RegisterLocalizedRoutes::register();
                $inventories[] = array_map(fn ($route) => [$route->getName(), $route->uri(), $route->methods(), $route->getActionName(), $route->gatherMiddleware(), $route->defaults, $route->bindingFields()], Route::getRoutes()->getRoutes());
            }
            $this->assertNotEmpty($inventories[0]);
            $this->assertSame($inventories[0], $inventories[1]);
            $names = array_column($inventories[0], 0);
            $this->assertSame($names, array_values(array_unique($names)));
        } finally {
            Route::setRoutes($original);
            app()->setLocale($previous);
        }
    }

    public function test_route_and_config_caches_preserve_the_inventory_in_isolated_processes(): void
    {
        $root = sys_get_temp_dir().'/gamestackr-route-cache-'.bin2hex(random_bytes(8));
        File::makeDirectory($root);
        $environment = [
            'APP_ENV' => 'testing', 'DB_CONNECTION' => 'pgsql', 'DB_DATABASE' => 'gamestackr_testing', 'DB_URL' => '',
            'APP_ROUTES_CACHE' => $root.'/routes.php', 'APP_CONFIG_CACHE' => $root.'/config.php',
            'APP_EVENTS_CACHE' => $root.'/events.php', 'APP_SERVICES_CACHE' => $root.'/services.php', 'APP_PACKAGES_CACHE' => $root.'/packages.php',
        ];
        $run = function (array $arguments) use ($environment): string {
            $process = new Process([PHP_BINARY, base_path('artisan'), ...$arguments], base_path(), $environment);
            $process->mustRun();

            return $process->getOutput();
        };
        $contract = function (string $ambient = 'fr') use ($environment): string {
            $script = <<<'PHP'
                require 'vendor/autoload.php';
                $app = require 'bootstrap/app.php';
                $app->booting(fn () => $app->setLocale(getenv('TEST_AMBIENT_LOCALE')));
                $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
                $routes = [];
                foreach ($app['router']->getRoutes() as $route) {
                    if (str_starts_with($route->getName() ?? '', 'site.') || str_starts_with($route->getName() ?? '', 'filament.admin-')) {
                        $routes[$route->getName()] = [$route->uri(), $route->methods(), $route->getActionName(), $route->gatherMiddleware(), $route->defaults, $route->bindingFields(), $route->wheres];
                    }
                }
                ksort($routes);
                echo json_encode($routes, JSON_THROW_ON_ERROR);
                PHP;
            $process = new Process([PHP_BINARY, '-r', $script], base_path(), [...$environment, 'TEST_AMBIENT_LOCALE' => $ambient]);
            $process->mustRun();

            return $process->getOutput();
        };
        try {
            $contractBefore = $contract();
            $this->assertSame($contractBefore, $contract('en'));
            $before = json_decode($run(['route:list', '--json']), true, flags: JSON_THROW_ON_ERROR);
            $run(['config:cache']);
            $run(['route:cache']);
            $this->assertFileExists($root.'/routes.php');
            $this->assertJson($contractBefore);
            $this->assertSame($contractBefore, $contract());
            $this->assertSame($contractBefore, $contract('en'));
            $after = json_decode($run(['route:list', '--json']), true, flags: JSON_THROW_ON_ERROR);
            // Serialized vendor closures lose source locations and receive generated names.
            // Compare the route contract while excluding this cache-only debug metadata.
            $normalize = fn (array $inventory): array => array_map(function (array $route): array {
                unset($route['path']);
                if (str_starts_with($route['name'] ?? '', 'generated::')) {
                    $route['name'] = null;
                }

                return $route;
            }, $inventory);
            $this->assertSame($normalize($before), $normalize($after));
            $this->assertContains('site.fr.profile.edit', array_column($after, 'name'));
            $this->assertContains('filament.admin-fr.resources.jeux.edit', array_column($after, 'name'));
            $this->assertContains('filament.admin-en.resources.games.create', array_column($after, 'name'));
        } finally {
            File::deleteDirectory($root);
        }
    }
}
