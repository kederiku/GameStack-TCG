<?php

use Illuminate\Contracts\Console\Kernel;

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

if (empty(config('app.key'))) {
    $status = $kernel->call('key:generate', ['--no-interaction' => true]);
    echo $kernel->output();
    exit($status);
}
