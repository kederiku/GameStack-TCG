<?php

use App\Models\Game;
use App\Models\Language;
use App\Models\Series;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

// Separate PHP process: explicitly guarded against any application database/schema.
require __DIR__.'/../../../vendor/autoload.php';
$app = require __DIR__.'/../../../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
$connection = DB::connection();
[$script, $schema, $operation, $class, $recordId, $languageId] = $argv;
if (! $app->environment('testing') || $connection->getDatabaseName() !== 'gamestackr_testing'
    || ! preg_match('/\Alocalized_runtime_[a-f0-9]{12}\z/', $schema)
    || ! in_array($class, [Game::class, Series::class], true)) {
    throw new LogicException('Concurrent localization tests require their owned testing schema.');
}
$connection->statement('SET search_path TO '.$schema);
$connection->statement("SET statement_timeout TO '10s'");
fwrite(STDOUT, json_encode(['pid' => $connection->selectOne('SELECT pg_backend_pid() AS pid')->pid])."\n");
fflush(STDOUT);
try {
    if ($operation === 'delete') {
        Language::findOrFail((int) $languageId)->delete();
    } else {
        $class::findOrFail((int) $recordId)->setTranslation('description', 'ja', 'Concurrent')->save();
    }
    fwrite(STDOUT, "saved\n");
} catch (ValidationException $exception) {
    fwrite(STDOUT, 'rejected:'.implode(',', array_keys($exception->errors()))."\n");
}
