<?php

require dirname(__DIR__, 2).'/vendor/autoload.php';
require __DIR__.'/files.php';

use Dotenv\Dotenv;

$root = isset($argv[1]) && ! str_starts_with($argv[1], '--') ? $argv[1] : dirname(__DIR__, 2);
$directory = $root.'/storage/app/private/garage';

try {
    umask(0077);
    if (! is_dir($directory) && ! mkdir($directory, 0700, true)) {
        throw new RuntimeException('Impossible de créer le répertoire privé Garage.');
    }
    $lock = fopen($directory.'/setup.lock', 'c');
    if ($lock === false || ! flock($lock, LOCK_EX)) {
        throw new RuntimeException('Impossible de verrouiller la configuration Garage.');
    }

    $environment = is_file($root.'/.env') ? Dotenv::parse(file_get_contents($root.'/.env')) : [];
    $value = fn (string $name, string $fallback): string => (string) (getenv($name) ?: ($environment[$name] ?? $fallback));
    $existing = is_file($directory.'/state.json');
    $state = $existing ? json_decode(file_get_contents($directory.'/state.json'), true, flags: JSON_THROW_ON_ERROR) : [
        'rpc_secret' => bin2hex(random_bytes(32)),
        'admin_token' => bin2hex(random_bytes(32)),
    ];
    foreach (['rpc_secret', 'admin_token'] as $name) {
        if (! is_string($state[$name] ?? null) || ! preg_match('/\A[a-f0-9]{64}\z/', $state[$name])) {
            throw new RuntimeException('Configuration Garage existante invalide ; aucun secret n’a été remplacé.');
        }
    }

    $state['bucket'] = $value('GARAGE_BUCKET', 'gamestackr-images');
    $state['region'] = $value('AWS_DEFAULT_REGION', 'garage');
    $state['origin'] = rtrim($value('APP_URL', 'http://localhost:8000'), '/');
    $port = $value('GARAGE_WEB_PORT', '3902');
    if (! preg_match('/\A[a-z0-9][a-z0-9-]{1,61}[a-z0-9]\z/', $state['bucket']) || ! ctype_digit($port) || (int) $port < 1 || (int) $port > 65535) {
        throw new RuntimeException('Bucket ou port web Garage invalide.');
    }
    $state['url'] = 'http://'.$state['bucket'].'.localhost:'.$port;

    if (! $existing || in_array('--password', $argv, true) || ! is_file($directory.'/login.txt')) {
        $password = bin2hex(random_bytes(16));
        $state['password_hash'] = password_hash($password, PASSWORD_BCRYPT);
        writeGarageFile($directory.'/login.txt', "Username: admin\nPassword: {$password}\n");
    }
    writeGarageFile($directory.'/state.json', json_encode($state, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR)."\n");
    $quote = fn (string $value): string => json_encode($value, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
    $configuration = 'metadata_dir = "/var/lib/garage/meta"'."\n"
        .'data_dir = "/var/lib/garage/data"'."\n"
        .'db_engine = "sqlite"'."\n"
        .'replication_factor = 1'."\n"
        .'rpc_bind_addr = "0.0.0.0:3901"'."\n"
        .'rpc_public_addr = "garage:3901"'."\n"
        .'rpc_secret = '.$quote($state['rpc_secret'])."\n"
        ."\n[s3_api]\n".'api_bind_addr = "0.0.0.0:3900"'."\n"
        .'s3_region = '.$quote($state['region'])."\n"
        ."\n[s3_web]\n".'bind_addr = "0.0.0.0:3902"'."\n"
        .'root_domain = ".localhost"'."\n".'index = "index.html"'."\n"
        ."\n[admin]\n".'api_bind_addr = "0.0.0.0:3903"'."\n"
        .'admin_token = '.$quote($state['admin_token'])."\n";
    writeGarageFile($directory.'/garage.toml', $configuration);
    // Compose reads this file in raw mode so bcrypt's dollar signs stay literal.
    writeGarageFile($directory.'/webui.env', 'AUTH_USER_PASS=admin:'.$state['password_hash']."\n");
    echo "Configuration locale prête. Accès Web UI : storage/app/private/garage/login.txt\n";
} catch (Throwable $exception) {
    fwrite(STDERR, $exception->getMessage()."\n");
    exit(1);
}
