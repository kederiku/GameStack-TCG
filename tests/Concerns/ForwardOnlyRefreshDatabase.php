<?php

namespace Tests\Concerns;

use Illuminate\Foundation\Testing\RefreshDatabase;

trait ForwardOnlyRefreshDatabase
{
    use RefreshDatabase;
    use UsesLocalizedSchema;

    public function refreshDatabase(): void
    {
        try {
            $this->createLocalizedSchema(registerCleanup: false);
            $this->beginDatabaseTransaction();
        } finally {
            // Register after Laravel's transaction rollback so after-commit and
            // savepoint semantics are unchanged and no transaction owns the schema.
            $this->beforeApplicationDestroyed(fn () => $this->dropLocalizedSchema());
        }
    }
}
