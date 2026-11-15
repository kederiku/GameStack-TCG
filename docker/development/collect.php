<?php

use App\Support\Development\DockerCollector;

require __DIR__.'/DockerCollector.php';

// The Compose stack is for development; never query Docker in other environments.
if (getenv('APP_ENV') !== 'local') {
    @unlink('/status/services.json');
    exit(0);
}

$collector = new DockerCollector(
    getenv('COMPOSE_PROJECT_NAME') ?: 'gamestackr-tcg',
    '/status',
    static function (string $path): array {
        $process = proc_open([
            'curl', '--silent', '--fail', '--max-time', '3',
            '--unix-socket', '/var/run/docker.sock', 'http://localhost'.$path,
        ], [1 => ['pipe', 'w'], 2 => ['file', '/dev/null', 'w']], $pipes);

        if (! is_resource($process)) {
            throw new RuntimeException('Docker unavailable.');
        }

        $body = stream_get_contents($pipes[1]);
        fclose($pipes[1]);

        if (proc_close($process) !== 0) {
            throw new RuntimeException('Docker unavailable.');
        }

        $containers = json_decode($body, true, flags: JSON_THROW_ON_ERROR);

        if (! is_array($containers) || ! array_is_list($containers)) {
            throw new RuntimeException('Invalid Docker response.');
        }

        return $containers;
    },
);

while (true) {
    try {
        $collector->collect();
    } catch (Throwable) {
        // Keep the last complete report; the consumer expires it after 30 seconds.
        fwrite(STDERR, "Docker status collection unavailable.\n");
    }

    sleep(5);
}
