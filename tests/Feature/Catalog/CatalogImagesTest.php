<?php

namespace Tests\Feature\Catalog;

use App\Models\ExpansionEdition;
use App\Models\Series;
use App\Models\SeriesLocalization;
use Closure;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Tests\Concerns\BuildsCatalog;
use Tests\TestCase;

class CatalogImagesTest extends TestCase
{
    use BuildsCatalog;
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();

        config(['game-images.disk' => 's3']);
        Storage::fake('s3');
        Storage::fake('public');
    }

    public function test_series_localizations_store_independent_avif_images_in_the_configured_namespace(): void
    {
        $series = $this->series($this->catalog());
        $english = $this->localization($series, 'en', 'Scarlet & Violet');
        $french = $this->localization($series, 'fr', 'Écarlate et Violet');

        $this->assertMatchesRegularExpression('/\Acatalog\/[a-f0-9-]{36}\/scarlet-violet\.avif\z/', $english->fresh()->image);
        $this->assertMatchesRegularExpression('/\Acatalog\/[a-f0-9-]{36}\/ecarlate-et-violet\.avif\z/', $french->fresh()->image);
        $this->assertNotSame(dirname($english->image), dirname($french->image));
        $disk = Storage::disk('s3');
        $this->assertSame('image/avif', $disk->mimeType($english->image));
        $this->assertSame('image/avif', $disk->mimeType($french->image));
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_edition_image_replacement_and_removal_follow_the_committed_database_state(): void
    {
        $edition = $this->edition($this->expansion($this->catalog()));
        $edition = $this->saveCatalog($edition, ['image' => UploadedFile::fake()->image('initial.png')]);
        $original = $edition->image;
        $disk = Storage::disk('s3');

        $edition = $this->saveCatalog($edition, ['name' => 'Renamed']);
        $this->assertSame($original, $edition->fresh()->image);
        $edition = $this->saveCatalog($edition, ['image' => $original]);
        $stale = $edition;
        $this->assertSame([$original], $disk->allFiles());

        DB::beginTransaction();
        try {
            $edition = $this->saveCatalog($edition, ['image' => UploadedFile::fake()->image('replacement.png')]);
            $replacement = $edition->image;
            $this->assertStringEndsWith('/renamed.avif', $replacement);
            $this->assertNotSame(dirname($original), dirname($replacement));
            $disk->assertExists([$original, $replacement]);
        } finally {
            DB::commit();
        }

        $disk->assertMissing($original);
        $disk->assertExists($replacement);
        $this->assertInvalidImage(fn () => $this->saveCatalog($stale, ['image' => $original]));

        $edition = $this->saveCatalog($edition, ['image' => null]);
        $this->assertNull($edition->fresh()->image);
        $disk->assertMissing($replacement);
    }

    public function test_outer_rollback_keeps_the_previous_image_and_removes_the_replacement(): void
    {
        $series = $this->series($this->catalog());
        $localization = $this->localization($series, 'en', 'English');
        $original = $localization->image;
        $disk = Storage::disk('s3');

        DB::beginTransaction();
        try {
            $replacement = $this->saveCatalog($localization, ['image' => UploadedFile::fake()->image('replacement.png')])->image;
            $this->assertNotSame($original, $replacement);
            $disk->assertExists([$original, $replacement]);
        } finally {
            DB::rollBack();
        }

        $this->assertSame($original, $localization->fresh()->image);
        $this->assertSame([$original], $disk->allFiles());
    }

    public function test_submitted_paths_can_only_retain_the_locked_records_own_image(): void
    {
        $series = $this->series($this->catalog());
        $owner = $this->localization($series, 'en', 'English');
        $edition = $this->edition($this->expansion($series->catalog));
        $unownedPath = 'catalog/'.Str::uuid().'/unowned.avif';
        $disk = Storage::disk('s3');
        $disk->put($unownedPath, 'unowned object');

        $this->assertInvalidImage(fn () => $this->saveCatalog($edition, ['image' => $owner->image]));
        $this->assertInvalidImage(fn () => $edition->update(['image' => $owner->image]));
        $this->assertInvalidImage(fn () => $this->saveCatalog($edition, ['image' => $unownedPath]));
        $this->assertInvalidImage(fn () => $edition->update(['image' => UploadedFile::fake()->image('direct-upload.png')]));
        $this->assertInvalidImage(fn () => $series->localizations()->create([
            'language_id' => $this->language('fr', 'Français')->id,
            'name' => 'Français',
            'image' => $unownedPath,
        ]));
        $this->assertInvalidImage(fn () => $this->saveCatalog($series, ['image' => $owner->image]));

        $this->assertNull($edition->fresh()->image);
        $this->assertSame($owner->image, $owner->fresh()->image);
        $this->assertEqualsCanonicalizing([$owner->image, $unownedPath], $disk->allFiles());
    }

    public function test_invalid_images_leave_the_previous_image_and_storage_untouched(): void
    {
        $series = $this->series($this->catalog());
        $localization = $this->localization($series, 'en', 'English');

        $uploads = [
            UploadedFile::fake()->image('too-wide.png', 4097, 1),
            UploadedFile::fake()->image('too-heavy.png')->size(5121),
            UploadedFile::fake()->createWithContent('not-an-image.png', 'Not an image'),
        ];
        foreach ($uploads as $upload) {
            $this->assertInvalidImage(fn () => $this->saveCatalog($localization, ['image' => $upload]));
        }

        $this->assertSame($localization->image, $localization->fresh()->image);
        $this->assertSame([$localization->image], Storage::disk('s3')->allFiles());
    }

    public function test_model_save_cancelled_after_storage_removes_the_new_object(): void
    {
        $edition = $this->edition($this->expansion($this->catalog()));
        ExpansionEdition::saving(fn (): bool => false);

        try {
            $this->saveCatalog($edition, ['image' => UploadedFile::fake()->image('cancelled.png')]);
            $this->fail('The cancelled save should be reported.');
        } catch (RuntimeException $exception) {
            $this->assertSame('L’enregistrement de la fiche a été annulé.', $exception->getMessage());
            $this->assertNull($edition->fresh()->image);
            $this->assertSame([], Storage::disk('s3')->allFiles());
        }
    }

    public function test_deleting_a_series_cleans_its_localizations_only_after_commit(): void
    {
        $series = $this->series($this->catalog());
        $english = $this->localization($series, 'en', 'English');
        $french = $this->localization($series, 'fr', 'Français');
        $paths = [$english->image, $french->image];
        $disk = Storage::disk('s3');

        DB::beginTransaction();
        try {
            $series->delete();
            $this->assertNull($english->fresh());
            $this->assertNull($french->fresh());
            $disk->assertExists($paths);
        } finally {
            DB::rollBack();
        }

        $series = Series::findOrFail($series->id);
        $this->assertSame(2, $series->localizations()->count());
        $disk->assertExists($paths);

        $series->delete();
        $disk->assertMissing($paths);
    }

    public function test_deleting_an_image_owner_keeps_a_revision_still_referenced_by_another_catalog_table(): void
    {
        $series = $this->series($this->catalog());
        $localization = $this->localization($series, 'en', 'English');
        $edition = $this->edition($this->expansion($series->catalog));
        $path = $localization->image;

        // Simulate an existing shared reference introduced outside ordinary model writes.
        DB::table('expansion_editions')->where('id', $edition->id)->update(['image' => $path]);
        $localization->delete();
        Storage::disk('s3')->assertExists($path);

        $edition->delete();
        Storage::disk('s3')->assertMissing($path);
    }

    private function localization(Series $series, string $code, string $name): SeriesLocalization
    {
        return $this->saveCatalog(new SeriesLocalization, [
            'series_id' => $series->id,
            'language_id' => $this->language($code, $name)->id,
            'name' => $name,
            'image' => UploadedFile::fake()->image('series.png', 30, 15),
        ]);
    }

    private function assertInvalidImage(Closure $operation): void
    {
        try {
            $operation();
            $this->fail('The image should have been rejected.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('image', $exception->errors());
        }
    }
}
