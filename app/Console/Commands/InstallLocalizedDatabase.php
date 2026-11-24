<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Foundation\MaintenanceMode;
use Illuminate\Support\Facades\DB;

class InstallLocalizedDatabase extends Command
{
    protected $signature = 'localization:install';

    protected $description = 'Initialize an empty database or migrate an already finalized installation';

    public function handle(MaintenanceMode $maintenance): int
    {
        $db = DB::connection();
        // Hold a session lock across Laravel's separate migration transactions.
        $lock = "hashtext('gamestackr.localization.install'), hashtext(current_schema())";
        $db->selectOne("select pg_advisory_lock({$lock})");
        try {
            if (FinalizeLocalization::isFinalized($db)) {
                return $this->call('migrate', ['--force' => true]);
            }
            if ($db->scalar("select exists (select from pg_depend where refclassid = 'pg_namespace'::regclass and refobjid = current_schema()::regnamespace)")) {
                $this->error('Existing schema requires a backed-up, audited cutover. Follow docs/localization.md before running migrations.');

                return self::FAILURE;
            }
            $wasDown = $maintenance->active();
            if (! $wasDown) {
                $maintenance->activate(['status' => 503]);
            }
            if ($this->call('migrate', ['--force' => true]) !== self::SUCCESS || $this->call('localization:finalize') !== self::SUCCESS) {
                $this->error('Installation remains in maintenance. Follow the recovery procedure in docs/localization.md.');

                return self::FAILURE;
            }
            if (! $wasDown) {
                $maintenance->deactivate();
            }

            return self::SUCCESS;
        } finally {
            $db->selectOne("select pg_advisory_unlock({$lock})");
        }
    }
}
