<x-filament-widgets::widget>
    <div class="gs-admin-overview">
        <section class="gs-admin-welcome" aria-labelledby="catalogue-welcome-title">
            <div>
                <p class="gs-admin-eyebrow">{{ __('admin.overview.eyebrow') }}</p>
                <h2 id="catalogue-welcome-title">{{ __('admin.overview.title') }}</h2>
                <p>{{ __('admin.overview.description') }}</p>
            </div>
            <div class="gs-admin-quick-actions">
                <x-filament::button tag="a" :href="$links['createGame']" icon="heroicon-o-plus">{{ __('admin.overview.add_game') }}</x-filament::button>
                <x-filament::button tag="a" :href="$links['createExpansion']" color="gray" icon="heroicon-o-rectangle-stack">{{ __('admin.overview.add_expansion') }}</x-filament::button>
            </div>
        </section>

        <div class="gs-admin-stat-grid" aria-label="{{ __('admin.overview.content_label') }}">
            @foreach (['games' => [__('admin.overview.games'), __('admin.overview.games_description'), 'heroicon-o-puzzle-piece'], 'catalogs' => [__('admin.overview.catalogs'), __('admin.overview.catalogs_description'), 'heroicon-o-circle-stack'], 'expansions' => [__('admin.overview.expansions'), __('admin.overview.expansions_description'), 'heroicon-o-rectangle-stack']] as $key => [$label, $description, $icon])
                <a class="gs-admin-stat" href="{{ $links[$key] }}">
                    <x-filament::icon :icon="$icon" class="gs-admin-stat-icon" />
                    <span class="gs-admin-stat-number">{{ \Illuminate\Support\Number::format($totals[$key], locale: app()->getLocale()) }}</span>
                    <span class="gs-admin-stat-label">{{ $label }}</span>
                    <span class="gs-admin-stat-caption">{{ $description }} <span aria-hidden="true">→</span></span>
                </a>
            @endforeach
            <div class="gs-admin-stat">
                <x-filament::icon icon="heroicon-o-language" class="gs-admin-stat-icon" />
                <span class="gs-admin-stat-number">{{ \Illuminate\Support\Number::format($totals['editions'], locale: app()->getLocale()) }}</span>
                <span class="gs-admin-stat-label">{{ __('admin.overview.editions') }}</span>
                <span class="gs-admin-stat-caption">{{ __('admin.overview.editions_help') }}</span>
            </div>
        </div>

        <section class="gs-admin-guide" aria-labelledby="catalogue-guide-title">
            <div class="gs-admin-guide-heading">
                <h2 id="catalogue-guide-title">{{ __('admin.overview.guide_title') }}</h2>
                <p>{{ __('admin.overview.guide_description') }}</p>
            </div>
            <ol class="gs-admin-steps">
                <li><span class="gs-admin-step-number">1</span><div><a href="{{ $links['games'] }}">{{ __('admin.overview.choose_game') }}</a><p>{{ __('admin.overview.choose_game_help') }}</p></div></li>
                <li><span class="gs-admin-step-number">2</span><div><a href="{{ $links['catalogs'] }}">{{ __('admin.overview.organize_catalogs') }}</a><p>{{ __('admin.overview.organize_catalogs_help') }}</p></div></li>
                <li><span class="gs-admin-step-number">3</span><div><a href="{{ $links['expansions'] }}">{{ __('admin.overview.add_expansion') }}</a><p>{{ __('admin.overview.series_prefix') }} <a href="{{ $links['series'] }}">{{ __('admin.overview.series_link') }}</a> {{ __('admin.overview.series_suffix') }}</p></div></li>
                <li><span class="gs-admin-step-number">4</span><div><strong>{{ __('admin.overview.releases_title') }}</strong><p>{{ __('admin.overview.releases_help') }}</p></div></li>
            </ol>
        </section>
        <p class="gs-admin-footnote">{{ __('admin.overview.footnote') }}</p>
    </div>
</x-filament-widgets::widget>
