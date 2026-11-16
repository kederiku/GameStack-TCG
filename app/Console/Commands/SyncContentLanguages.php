<?php

namespace App\Console\Commands;

use App\Models\Language;
use App\Support\Localization\Locales;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Date;

class SyncContentLanguages extends Command
{
    protected $signature = 'localization:sync-languages';

    protected $description = 'Add active interface locales missing from the content language reference';

    public function handle(): int
    {
        $now = Date::now();
        $rows = [];

        foreach (Locales::all() as $locale) {
            $rows[] = [
                'code' => $locale['code'],
                'name' => $locale['label'],
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        // The PostgreSQL unique index on lower(code) also handles concurrent syncs
        // and preserves existing names and codes regardless of their casing.
        $inserted = Language::query()->insertOrIgnore($rows);
        $this->info("Content languages added: {$inserted}.");

        return self::SUCCESS;
    }
}
