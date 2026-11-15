<x-filament-widgets::widget>
    <div class="gs-admin-overview">
        <section class="gs-admin-welcome" aria-labelledby="catalogue-welcome-title">
            <div>
                <p class="gs-admin-eyebrow">VOTRE ESPACE DE GESTION</p>
                <h2 id="catalogue-welcome-title">Construisez votre catalogue, étape par étape.</h2>
                <p>Retrouvez vos jeux, organisez leurs extensions et renseignez les éditions dans chaque langue.</p>
            </div>
            <div class="gs-admin-quick-actions">
                <x-filament::button tag="a" :href="$links['createGame']" icon="heroicon-o-plus">Ajouter un jeu</x-filament::button>
                <x-filament::button tag="a" :href="$links['createExpansion']" color="gray" icon="heroicon-o-rectangle-stack">Ajouter une extension</x-filament::button>
            </div>
        </section>

        <div class="gs-admin-stat-grid" aria-label="Contenu du catalogue">
            @foreach (['games' => ['Jeux', 'Univers de cartes', 'heroicon-o-puzzle-piece'], 'catalogs' => ['Catalogues', 'Versions éditoriales', 'heroicon-o-circle-stack'], 'expansions' => ['Extensions', 'Ensembles de cartes', 'heroicon-o-rectangle-stack']] as $key => [$label, $description, $icon])
                <a class="gs-admin-stat" href="{{ $links[$key] }}">
                    <x-filament::icon :icon="$icon" class="gs-admin-stat-icon" />
                    <span class="gs-admin-stat-number">{{ number_format($totals[$key], 0, ',', ' ') }}</span>
                    <span class="gs-admin-stat-label">{{ $label }}</span>
                    <span class="gs-admin-stat-caption">{{ $description }} <span aria-hidden="true">→</span></span>
                </a>
            @endforeach
            <div class="gs-admin-stat">
                <x-filament::icon icon="heroicon-o-language" class="gs-admin-stat-icon" />
                <span class="gs-admin-stat-number">{{ number_format($totals['editions'], 0, ',', ' ') }}</span>
                <span class="gs-admin-stat-label">Éditions linguistiques</span>
                <span class="gs-admin-stat-caption">À gérer depuis une extension</span>
            </div>
        </div>

        <section class="gs-admin-guide" aria-labelledby="catalogue-guide-title">
            <div class="gs-admin-guide-heading">
                <h2 id="catalogue-guide-title">Par où commencer ?</h2>
                <p>Chaque fiche se rattache au niveau précédent. La série est facultative.</p>
            </div>
            <ol class="gs-admin-steps">
                <li><span class="gs-admin-step-number">1</span><div><a href="{{ $links['games'] }}">Choisir un jeu</a><p>Pokémon, Magic, One Piece… Le point de départ du catalogue.</p></div></li>
                <li><span class="gs-admin-step-number">2</span><div><a href="{{ $links['catalogs'] }}">Organiser ses catalogues</a><p>Séparez les ensembles quand leur contenu diffère selon les versions du jeu.</p></div></li>
                <li><span class="gs-admin-step-number">3</span><div><a href="{{ $links['expansions'] }}">Ajouter une extension</a><p>Rattachez-la à une <a href="{{ $links['series'] }}">série ou un bloc</a> si le jeu en utilise.</p></div></li>
                <li><span class="gs-admin-step-number">4</span><div><strong>Préciser les éditions et sorties</strong><p>Depuis l’extension, ajoutez les langues imprimées, puis les marchés et dates de sortie.</p></div></li>
            </ol>
        </section>
        <p class="gs-admin-footnote">Les chiffres incluent les fiches masquées. L’affichage public des extensions est encore en préparation.</p>
    </div>
</x-filament-widgets::widget>
