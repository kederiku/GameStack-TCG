<?php

namespace App\Support\Development;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Process;
use Throwable;

class ServiceHealth
{
    /** @return array<string, array{responding: bool, milliseconds: int}> */
    public function check(): array
    {
        $probes = [
            // A separate process bounds even a blocked database socket and never
            // changes the connection used by authentication or the application.
            'postgres' => fn (): bool => Process::timeout(3)
                ->input(json_encode(DB::connection()->getConfig(), JSON_THROW_ON_ERROR))
                ->run([PHP_BINARY, base_path('docker/development/database-health.php')])
                ->successful(),
            'mailpit' => fn (): bool => $this->http('http://mailpit:8025/readyz'),
            'garage' => fn (): bool => $this->http('http://garage:3903/health'),
        ];
        $results = [];

        foreach ($probes as $service => $probe) {
            $started = hrtime(true);

            try {
                $responding = $probe();
            } catch (Throwable) {
                $responding = false;
            }

            $results[$service] = [
                'responding' => $responding,
                'milliseconds' => (int) round((hrtime(true) - $started) / 1_000_000),
            ];
        }

        return $results;
    }

    private function http(string $url): bool
    {
        return Http::connectTimeout(1)->timeout(2)->withoutRedirecting()->get($url)->successful();
    }
}
