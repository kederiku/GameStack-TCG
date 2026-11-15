<?php

namespace Tests\Unit;

use App\Support\Development\DockerCollector;
use PHPUnit\Framework\TestCase;

class DockerCollectorTest extends TestCase
{
    public function test_only_project_services_are_published_without_docker_metadata(): void
    {
        $directory = sys_get_temp_dir().'/docker-status-'.bin2hex(random_bytes(8));
        mkdir($directory);

        try {
            $container = [
                'State' => 'running', 'Status' => 'Up 2 hours (healthy)',
                'Labels' => ['com.docker.compose.project' => 'example', 'com.docker.compose.service' => 'postgres', 'com.docker.compose.oneoff' => 'False'],
                'Command' => 'secret-command', 'Mounts' => [['Source' => '/private/secrets']],
            ];
            $containers = [
                $container,
                array_replace_recursive($container, ['Labels' => ['com.docker.compose.project' => 'other']]),
                array_replace_recursive($container, ['Labels' => ['com.docker.compose.oneoff' => 'True']]),
                array_replace_recursive($container, ['Labels' => ['com.docker.compose.service' => 'unknown']]),
                array_replace_recursive($container, ['State' => 'exited', 'Status' => 'Exited (1) 2 minutes ago', 'Labels' => ['com.docker.compose.service' => 'mailpit']]),
            ];

            $collector = new DockerCollector('example', $directory, function (string $path) use ($containers): array {
                parse_str((string) parse_url($path, PHP_URL_QUERY), $query);
                $this->assertSame('1', $query['all']);
                $this->assertSame(['label' => ['com.docker.compose.project=example']], json_decode($query['filters'], true));

                return $containers;
            });
            $collector->collect();
            $report = json_decode(file_get_contents($directory.'/services.json'), true);

            $this->assertEqualsWithDelta(time(), $report['generated_at'], 2);
            $this->assertSame([
                'postgres' => [['state' => 'running', 'health' => 'healthy']],
                'mailpit' => [['state' => 'exited', 'health' => 'none']],
            ], $report['services']);
            $this->assertStringNotContainsString('secret', file_get_contents($directory.'/services.json'));
            $this->assertSame(['services.json'], array_values(array_diff(scandir($directory), ['.', '..'])));

            // A failed collection must not overwrite the last complete report.
            $failing = new DockerCollector('example', $directory, fn () => throw new \RuntimeException('offline'));
            try {
                $failing->collect();
                $this->fail('Collection should fail.');
            } catch (\RuntimeException) {
                $this->assertSame($report, json_decode(file_get_contents($directory.'/services.json'), true));
            }
        } finally {
            @unlink($directory.'/services.json');
            rmdir($directory);
        }
    }
}
