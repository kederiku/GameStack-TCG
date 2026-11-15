<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Catalogs\CatalogResource;
use App\Filament\Resources\Expansions\ExpansionResource;
use App\Filament\Resources\Games\GameResource;
use App\Filament\Resources\Series\SeriesResource;
use App\Models\Catalog;
use App\Models\Expansion;
use App\Models\ExpansionEdition;
use App\Models\Game;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Support\Components\Attributes\Unexposed;
use Filament\Widgets\Widget;

class CatalogOverview extends Widget
{
    protected string $view = 'filament.widgets.catalog-overview';

    protected int|string|array $columnSpan = 'full';

    protected static bool $isLazy = false;

    #[Unexposed]
    public static function canView(): bool
    {
        $user = auth()->user();

        return $user instanceof User && $user->canAccessPanel(Filament::getPanel('admin'));
    }

    /** @return array<string, mixed> */
    protected function getViewData(): array
    {
        abort_unless(static::canView(), 403);

        return [
            'totals' => [
                'games' => Game::query()->count(),
                'catalogs' => Catalog::query()->count(),
                'expansions' => Expansion::query()->count(),
                'editions' => ExpansionEdition::query()->count(),
            ],
            'links' => [
                'games' => GameResource::getUrl(),
                'catalogs' => CatalogResource::getUrl(),
                'series' => SeriesResource::getUrl(),
                'expansions' => ExpansionResource::getUrl(),
                'createGame' => GameResource::getUrl('create'),
                'createExpansion' => ExpansionResource::getUrl('create'),
            ],
        ];
    }
}
