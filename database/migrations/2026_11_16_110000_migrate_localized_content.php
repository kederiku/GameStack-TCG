<?php

use App\Support\Localization\Locales;
use Database\Migrations\Support\LocalizedContentV1;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

require_once __DIR__.'/support/LocalizedContentV1.php';

return new class extends Migration
{
    public function up(): void
    {
        $connection = DB::connection($this->getConnection());
        $connection->transaction(function () use ($connection): void {
            // Synchronize before capturing immutable conversion digests. Preserve
            // existing historic casing/names through the lower(code) unique index.
            foreach (Locales::all() as $locale) {
                $connection->table('languages')->insertOrIgnore([
                    'code' => $locale['code'], 'name' => $locale['label'],
                    // A synthesized reference has no historical creation or update date.
                ]);
            }
            (new LocalizedContentV1)->up($connection);
        });
    }

    public function down(): void
    {
        throw new RuntimeException('Localized content requires a coherent backup restore; destructive rollback is not supported.');
    }
};
