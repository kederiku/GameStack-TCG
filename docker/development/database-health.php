<?php

use Illuminate\Database\Connectors\PostgresConnector;

require dirname(__DIR__, 2).'/vendor/autoload.php';

try {
    // The parent passes its resolved connection (including gamestackr_testing
    // during tests) through stdin, never command arguments or a generated file.
    $config = json_decode(stream_get_contents(STDIN), true, flags: JSON_THROW_ON_ERROR);

    if (($config['driver'] ?? null) !== 'pgsql') {
        exit(1);
    }

    $config['server_options']['statement_timeout'] = 2000;
    $config['options'][PDO::ATTR_TIMEOUT] = 2;
    $pdo = (new PostgresConnector)->connect($config);
    exit((int) $pdo->query('SELECT 1')->fetchColumn() === 1 ? 0 : 1);
} catch (Throwable) {
    // No credentials, DSN, SQL errors or response bodies leave this process.
    exit(1);
}
