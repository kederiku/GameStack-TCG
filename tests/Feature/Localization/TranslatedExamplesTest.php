<?php

namespace Tests\Feature\Localization;

use App\Actions\Catalog\SaveTranslations;
use App\Actions\Catalog\SeedCatalogExamples;
use App\Models\CatalogExampleBinding;
use App\Models\Series;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\ForwardOnlyDatabaseMigrations;
use Tests\TestCase;

class TranslatedExamplesTest extends TestCase
{
    use ForwardOnlyDatabaseMigrations;

    public function test_seed_preserves_edits_and_images_and_recreates_only_a_missing_translation(): void
    {
        Storage::fake('s3');
        config(['game-images.disk' => 's3']);
        $seed = app(SeedCatalogExamples::class);
        $seed->handle();
        $key = 'series-localization:pokemon/international/scarlet-violet/';
        $fr = CatalogExampleBinding::query()->findOrFail($key.'fr');
        $en = CatalogExampleBinding::query()->findOrFail($key.'en');
        $this->assertSame(['series', 'fr', $en->record_id], [$fr->record_type, $fr->locale, $fr->record_id]);
        $this->assertSame('en', $en->locale);
        $series = Series::query()->findOrFail($fr->record_id);
        $series->imageForLocale('fr')->delete();
        $save = app(SaveTranslations::class);
        $save->handle($series, 'fr', ['display_name' => 'Titre personnel', 'description' => null]);
        $save->handle($series, 'en', [], remove: true);
        $save->handle($series->refresh(), 'en', ['description' => 'A hand-edited description']);
        $files = Storage::disk('s3')->allFiles();
        $path = $series->imageForLocale('en')->image;

        $this->assertSame(1, $seed->handle()['recreated']);
        $this->assertSame(['en' => 'Scarlet & Violet', 'fr' => 'Titre personnel'], $series->refresh()->getTranslations('display_name'));
        $this->assertSame('A hand-edited description', $series->getTranslations('description')['en']);
        $this->assertArrayHasKey('fr', $series->getTranslations('description'));
        $this->assertNull($series->getTranslations('description')['fr']);
        $this->assertNull($series->imageForLocale('fr'));
        $this->assertSame($path, $series->imageForLocale('en')->image);
        $this->assertSame($files, Storage::disk('s3')->allFiles());
        $this->assertFalse($series->is_visible);
    }

    public function test_missing_translation_binding_cannot_be_recovered_from_a_mutable_game_slug(): void
    {
        Storage::fake('s3');
        config(['game-images.disk' => 's3']);
        $seed = app(SeedCatalogExamples::class);
        $seed->handle();
        $key = 'series:pokemon/international/scarlet-violet';
        $binding = CatalogExampleBinding::query()->findOrFail($key);
        $series = Series::query()->findOrFail($binding->record_id);
        app(SaveTranslations::class)->handle($series, 'en', [], remove: true);
        // Simulate a damaged stored manifest, as can occur in a historical installation.
        $binding->delete();

        try {
            $seed->handle();
            $this->fail('Restoring a translation requires its original parent binding.');
        } catch (\RuntimeException $error) {
            $this->assertStringContainsString('series-localization:pokemon/international/scarlet-violet', $error->getMessage());
        }
        $this->assertArrayNotHasKey('en', $series->refresh()->getTranslations('display_name'));
        $this->assertModelMissing($binding);
    }
}
