<?php

namespace Tests\Concerns;

/** Fresh owned schema per test; no destructive application migration rollback. */
trait ForwardOnlyDatabaseMigrations
{
    use UsesLocalizedSchema;

    protected function setUpForwardOnlyDatabaseMigrations(): void
    {
        $this->createLocalizedSchema();
    }
}
