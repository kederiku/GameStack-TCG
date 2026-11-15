<?php

namespace App\Support\Development;

class DockerSnapshot
{
    /** @return array{generated_at: int, services: array<string, list<array{state: string, health: string}>>}|array{} */
    public function read(): array
    {
        $path = config('development.snapshot_path');

        if (! is_readable($path)) {
            return [];
        }

        $report = json_decode((string) @file_get_contents($path), true);

        if (! is_array($report) || ! is_int($report['generated_at'] ?? null)
            || now()->timestamp - $report['generated_at'] > 30
            || $report['generated_at'] > now()->timestamp
            || ! is_array($report['services'] ?? null)) {
            return [];
        }

        foreach ($report['services'] as $service => $containers) {
            if (! in_array($service, DockerCollector::SERVICES, true) || ! is_array($containers) || ! array_is_list($containers)) {
                return [];
            }

            foreach ($containers as $container) {
                if (! is_array($container)
                    || ! in_array($container['state'] ?? null, DockerCollector::STATES, true)
                    || ! in_array($container['health'] ?? null, DockerCollector::HEALTH, true)
                    || count($container) !== 2) {
                    return [];
                }
            }
        }

        return ['generated_at' => $report['generated_at'], 'services' => $report['services']];
    }
}
