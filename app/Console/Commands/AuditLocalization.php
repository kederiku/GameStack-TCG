<?php

namespace App\Console\Commands;

use Database\Migrations\Support\LocalizedContentV1;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AuditLocalization extends Command
{
    protected $signature = 'localization:audit';

    protected $description = 'Read-only audit of historical or translated catalog content';

    public function handle(): int
    {
        require_once database_path('migrations/support/LocalizedContentV1.php');
        $audit = (new LocalizedContentV1)->audit(DB::connection());
        foreach ($audit['counts'] as $name => $count) {
            $this->line("{$name}: {$count}");
        }
        foreach ($audit['problems'] as $problem) {
            $this->error($problem);
        }

        return $audit['ok'] ? self::SUCCESS : self::FAILURE;
    }
}
