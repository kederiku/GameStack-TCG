<?php

namespace App\Support\Development;

use Closure;
use RuntimeException;

/** Standalone collector: this class must not bootstrap Laravel or load its secrets. */
class DockerCollector
{
    public const SERVICES = ['app', 'postgres', 'mailpit', 'garage', 'garage-webui', 'queue', 'vite', 'service-status'];

    public const STATES = ['created', 'restarting', 'running', 'removing', 'paused', 'exited', 'dead', 'unknown'];

    public const HEALTH = ['healthy', 'unhealthy', 'starting', 'none'];

    /** @param Closure(string): array<int, array<string, mixed>> $request */
    public function __construct(
        private string $project,
        private string $directory,
        private Closure $request,
    ) {}

    public function collect(): void
    {
        $containers = ($this->request)('/containers/json?'.http_build_query([
            'all' => 1,
            'filters' => json_encode(['label' => ['com.docker.compose.project='.$this->project]], JSON_THROW_ON_ERROR),
        ]));
        $services = [];

        foreach ($containers as $container) {
            $labels = $container['Labels'] ?? [];
            $service = $labels['com.docker.compose.service'] ?? '';

            if (($labels['com.docker.compose.project'] ?? null) !== $this->project
                || ! in_array($service, self::SERVICES, true)
                || strtolower($labels['com.docker.compose.oneoff'] ?? 'false') !== 'false') {
                continue;
            }

            $state = $container['State'] ?? 'unknown';
            preg_match('/\((healthy|unhealthy|health: starting)\)/', $container['Status'] ?? '', $health);

            $services[$service][] = [
                'state' => in_array($state, self::STATES, true) ? $state : 'unknown',
                'health' => str_replace('health: ', '', $health[1] ?? 'none'),
            ];
        }

        $json = json_encode(['generated_at' => time(), 'services' => $services], JSON_THROW_ON_ERROR);
        $temporary = $this->directory.'/services.json.tmp';

        if (file_put_contents($temporary, $json, LOCK_EX) === false
            || ! rename($temporary, $this->directory.'/services.json')) {
            throw new RuntimeException('Cannot publish Docker status.');
        }
    }
}
