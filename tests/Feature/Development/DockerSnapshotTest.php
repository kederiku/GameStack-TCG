<?php

namespace Tests\Feature\Development;

use App\Support\Development\DockerSnapshot;
use Tests\TestCase;

class DockerSnapshotTest extends TestCase
{
    public function test_only_fresh_valid_reports_are_available(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'snapshot-');
        config(['development.snapshot_path' => $path]);
        $this->freezeTime();
        $valid = ['generated_at' => now()->timestamp, 'services' => ['postgres' => [['state' => 'running', 'health' => 'healthy']]]];

        try {
            file_put_contents($path, json_encode($valid));
            $this->assertSame($valid, app(DockerSnapshot::class)->read());

            foreach ([
                'invalid-json',
                json_encode(['generated_at' => now()->subSeconds(31)->timestamp, 'services' => $valid['services']]),
                json_encode(['generated_at' => now()->addMinute()->timestamp, 'services' => $valid['services']]),
                json_encode(['generated_at' => now()->timestamp, 'services' => ['postgres' => 'invalid']]),
            ] as $invalid) {
                file_put_contents($path, $invalid);
                $this->assertSame([], app(DockerSnapshot::class)->read());
            }

            unlink($path);
            $this->assertSame([], app(DockerSnapshot::class)->read());
        } finally {
            @unlink($path);
        }
    }
}
