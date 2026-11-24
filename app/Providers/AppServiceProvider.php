<?php

namespace App\Providers;

use App\Http\Routing\LocalIntendedRedirector;
use App\Models\Catalog;
use App\Models\Expansion;
use App\Models\ExpansionEdition;
use App\Models\ExpansionRelation;
use App\Models\ExpansionRelease;
use App\Models\Language;
use App\Models\Market;
use App\Models\Series;
use App\Models\SeriesImage;
use App\Policies\CatalogPolicy;
use App\Support\Localization\VendorTranslationLoader;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Translation\Loader;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Spatie\Translatable\Translatable;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton('redirect', function (): LocalIntendedRedirector {
            $redirector = new LocalIntendedRedirector($this->app->make('url'));
            $redirector->setSession($this->app->make('session.store'));

            return $redirector;
        });
        $this->app->extend('translation.loader', fn (Loader $loader) => new VendorTranslationLoader($loader));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        app(Translatable::class)->allowNullForTranslation()->allowEmptyStringForTranslation();

        foreach ([
            Catalog::class, Series::class, Expansion::class, Language::class, Market::class,
            SeriesImage::class, ExpansionEdition::class,
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
