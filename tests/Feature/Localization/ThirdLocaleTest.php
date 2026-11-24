<?php

namespace Tests\Feature\Localization;

use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;
use Tests\TestCase;

class ThirdLocaleTest extends TestCase
{
    public function test_complete_german_fixture_survives_route_cache_and_missing_route_rejects_activation(): void
    {
        $root = sys_get_temp_dir().'/gamestackr-de-'.bin2hex(random_bytes(8));
        File::ensureDirectoryExists($root.'/bootstrap/cache');
        $this->beforeApplicationDestroyed(fn () => File::deleteDirectory($root));
        File::copyDirectory(lang_path(), $root.'/lang');
        File::copyDirectory(lang_path('en'), $root.'/lang/de');
        foreach (['fortify', 'passkeys', 'laravel'] as $vendor) {
            File::copyDirectory(lang_path("vendor/$vendor/en"), "$root/lang/vendor/$vendor/de");
        }
        $routes = require lang_path('en/routes.php');
        $routes['auth']['login'] = $routes['auth']['login.store'] = 'anmelden';
        $routes['admin']['resources']['games'] = 'spiele';
        $routes['admin']['pages']['edit'] = '{record}/bearbeiten';
        $routeFile = $root.'/lang/de/routes.php';
        File::put($routeFile, '<?php return '.var_export($routes, true).';');
        $auth = require lang_path('en/auth.php');
        $auth['login']['title'] = 'Anmelden';
        File::put($root.'/lang/de/auth.php', '<?php return '.var_export($auth, true).';');

        // route:cache boots a second application. Both boots use the isolated
        // language directory, active registry and cache directory.
        File::copy(app()->bootstrapPath('providers.php'), $root.'/bootstrap/providers.php');
        File::put($root.'/bootstrap/app.php', <<<'BOOT'
            <?php
            $app = require getcwd().'/bootstrap/app.php';
            $app->useBootstrapPath(__DIR__);
            $app->useLangPath(dirname(__DIR__).'/lang');
            $app->afterBootstrapping(Illuminate\Foundation\Bootstrap\LoadConfiguration::class, function () {
                config(['localization.locales.de' => ['laravelLocale' => 'de', 'htmlLang' => 'de', 'label' => 'Deutsch', 'direction' => 'ltr']]);
            });
            return $app;
            BOOT);
        $run = function (string $code, string ...$args) use ($root): Process {
            $script = 'require "vendor/autoload.php"; $app = require getenv("FIXTURE_ROOT")."/bootstrap/app.php"; '.$code;
            $process = new Process([PHP_BINARY, '-r', $script, '--', ...$args], base_path(), [
                'FIXTURE_ROOT' => $root, 'APP_ENV' => 'testing', 'DB_CONNECTION' => 'pgsql',
                'DB_DATABASE' => 'gamestackr_testing', 'DB_URL' => '', 'CACHE_STORE' => 'array', 'SESSION_DRIVER' => 'array',
                'APP_ROUTES_CACHE' => $root.'/bootstrap/cache/routes.php', 'APP_CONFIG_CACHE' => $root.'/bootstrap/cache/config.php',
                'APP_SERVICES_CACHE' => $root.'/bootstrap/cache/services.php', 'APP_PACKAGES_CACHE' => $root.'/bootstrap/cache/packages.php',
                'APP_EVENTS_CACHE' => $root.'/bootstrap/cache/events.php',
            ]);
            $process->run();

            return $process;
        };
        $command = 'exit($app->handleCommand(new Symfony\Component\Console\Input\ArgvInput));';
        foreach ([['localization:check'], ['route:cache'], ['localization:export', '--path='.$root.'/export']] as $args) {
            $process = $run($command, ...$args);
            $this->assertTrue($process->isSuccessful(), $process->getOutput().$process->getErrorOutput());
        }
        $this->assertFileExists($root.'/bootstrap/cache/routes.php');
        $this->assertSame('Anmelden', json_decode(File::get($root.'/export/php_de.json'), true)['auth.login.title']);
        $this->assertStringContainsString('@/routes/site/de', File::get($root.'/export/routes.ts'));
        $process = $run(<<<'PHP_CODE'
            $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
            $request = Illuminate\Http\Request::create('/de/anmelden');
            $route = app('router')->getRoutes()->match($request);
            $request->setRouteResolver(fn () => $route);
            $app->instance('request', $request);
            $middleware = new App\Http\Middleware\SetSiteLocale;
            $response = $middleware->handle($request, function ($request) use ($app) {
                $game = new App\Models\Game(['display_name' => ['fr' => 'Nom français']]);
                return response()->json([
                    'cached' => $app->routesAreCached(),
                    'login' => __('auth.login.title'),
                    'home' => App\Support\Localization\LocalizedRoutes::url('home'),
                    'admin' => App\Support\Localization\AdminPanels::url('games.edit', ['record' => 7], 'de'),
                    'switcher' => App\Support\Localization\LocalizedRoutes::alternates($request),
                    'fallback' => App\Support\Localization\TranslatedContent::field($game, 'display_name', 'de'),
                ]);
            });
            $switch = $app->make(Illuminate\Contracts\Http\Kernel::class)->handle(
                Illuminate\Http\Request::create('/en/preferences/locale', 'POST', ['locale' => 'de', 'route' => 'login', 'target' => '/de/anmelden'])
            );
            echo json_encode([...$response->getData(true), 'visit' => [$switch->getStatusCode(), parse_url($switch->headers->get('Location'), PHP_URL_PATH)]], JSON_THROW_ON_ERROR);
            PHP_CODE);
        $this->assertTrue($process->isSuccessful(), $process->getErrorOutput());
        $this->assertSame([
            'cached' => true, 'login' => 'Anmelden', 'home' => '/de', 'admin' => '/de/admin/spiele/7/bearbeiten',
            'switcher' => ['fr' => '/fr/connexion', 'en' => '/en/login', 'de' => '/de/anmelden'],
            'fallback' => ['value' => 'Nom français', 'locale' => 'fr', 'fallback' => true],
            'visit' => [303, '/de/anmelden'],
        ], json_decode($process->getOutput(), true));
        $incomplete = $routes;
        unset($incomplete['auth']['login']);
        File::put($routeFile, '<?php return '.var_export($incomplete, true).';');
        $process = $run($command, 'localization:check');
        $this->assertFalse($process->isSuccessful());
        $this->assertStringContainsString('missing key auth.login', $process->getOutput());
        $this->assertFalse($run($command, 'route:cache')->isSuccessful());
        foreach ([
            ['dashboard', 'anmelden'],
            ['auth.password.reset', 'admin/spiele/{token}/bearbeiten'],
            ['auth.logout', 'admin/logout'],
            ['admin.resources.catalogs', 'spiele'],
        ] as [$key, $path]) {
            $collision = $routes;
            if (str_starts_with($key, 'auth.')) {
                $collision['auth'][substr($key, 5)] = $path;
            } else {
                data_set($collision, $key, $path);
            }
            File::put($routeFile, '<?php return '.var_export($collision, true).';');
            $check = $run($command, 'localization:check');
            $this->assertFalse($check->isSuccessful(), $key);
            $this->assertStringContainsString('collision', $check->getOutput().$check->getErrorOutput());
            $this->assertFalse($run($command, 'route:cache')->isSuccessful(), $key);
        }
    }
}
