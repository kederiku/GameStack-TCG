<?php

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Bootstrap\LoadConfiguration;
use Laravel\Fortify\Features;

require getcwd().'/vendor/autoload.php';
$app = require getcwd().'/bootstrap/app.php';
$app->afterBootstrapping(LoadConfiguration::class, function (): void {
    config(['fortify.features' => array_diff(config('fortify.features'), [Features::passkeys()])]);
});
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();
if (app('router')->has('site.en.passkey.login') || ! app('router')->has('site.en.login.store')) {
    throw new RuntimeException('Incorrect passkey-disabled route registry.');
}
$root = $argv[1];
exit($kernel->call('wayfinder:generate', ['--path' => $root, '--with-form' => true, '--skip-database' => true])
    ?: $kernel->call('localization:export', ['--path' => $root.'/i18n/generated']));
