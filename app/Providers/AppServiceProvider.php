<?php

namespace App\Providers;

use App\Models\Catalog;
use App\Models\Expansion;
use App\Models\ExpansionEdition;
use App\Models\ExpansionLocalization;
use App\Models\ExpansionRelation;
use App\Models\ExpansionRelease;
use App\Models\Language;
use App\Models\Market;
use App\Models\Series;
use App\Models\SeriesLocalization;
use App\Policies\CatalogPolicy;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();

        foreach ([
            Catalog::class, Series::class, Expansion::class, Language::class, Market::class,
            SeriesLocalization::class, ExpansionLocalization::class, ExpansionEdition::class,
            ExpansionRelease::class, ExpansionRelation::class,
        ] as $model) {
            Gate::policy($model, CatalogPolicy::class);
        }

        config()->push('livewire.temporary_file_upload.preview_mimes', 'avif');
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
