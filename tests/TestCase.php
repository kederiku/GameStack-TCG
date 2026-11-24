<?php

namespace Tests;

use Database\Seeders\ContentLanguagesSeeder;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\Attributes\Seeder;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Laravel\Fortify\Features;
use LogicException;

#[Seeder(ContentLanguagesSeeder::class)]
abstract class TestCase extends BaseTestCase
{
    public function createApplication(): Application
    {
        $app = parent::createApplication();

        if (! $app->environment('testing')
            || config('database.default') !== 'pgsql'
            || config('database.connections.pgsql.database') !== 'gamestackr_testing') {
            throw new LogicException('Tests require the dedicated gamestackr_testing PostgreSQL database. Clear the configuration cache first.');
        }

        return $app;
    }

    protected function skipUnlessFortifyHas(string $feature, ?string $message = null): void
    {
        if (! Features::enabled($feature)) {
            $this->markTestSkipped($message ?? "Fortify feature [{$feature}] is not enabled.");
        }
    }
}
