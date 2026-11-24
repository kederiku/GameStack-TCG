<?php

namespace Database\Seeders;

use Illuminate\Console\Command;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use RuntimeException;

class ContentLanguagesSeeder extends Seeder
{
    public function run(): void
    {
        if (Artisan::call('localization:sync-languages') !== Command::SUCCESS) {
            throw new RuntimeException('Could not synchronize the active content languages.');
        }
    }
}
