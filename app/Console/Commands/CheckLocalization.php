<?php

namespace App\Console\Commands;

use App\Support\Localization\TranslationFiles;
use Illuminate\Console\Command;

class CheckLocalization extends Command
{
    protected $signature = 'localization:check';

    protected $description = 'Check translation keys, placeholders and plural forms for active interface locales';

    public function handle(TranslationFiles $files): int
    {
        $errors = $files->validate();

        foreach ($errors as $error) {
            $this->error($error);
        }

        if ($errors !== []) {
            return self::FAILURE;
        }

        $this->info('Localization dictionaries are valid.');

        return self::SUCCESS;
    }
}
