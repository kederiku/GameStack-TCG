<?php

namespace Database\Seeders;

use App\Actions\Catalog\SeedCatalogExamples;
use Illuminate\Database\Seeder;

class TcgCatalogExamplesSeeder extends Seeder
{
    public function run(SeedCatalogExamples $seed): void
    {
        $counts = $seed->handle();

        $this->command?->info(sprintf(
            'Catalogue TCG : %d fiches créées, %d conservées, %d recréées.',
            $counts['created'],
            $counts['preserved'],
            $counts['recreated'],
        ));
    }
}
