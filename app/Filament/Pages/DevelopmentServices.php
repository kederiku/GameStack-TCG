<?php

namespace App\Filament\Pages;

use App\Models\User;
use App\Support\Development\DockerSnapshot;
use App\Support\Development\ServiceCatalog;
use App\Support\Development\ServiceHealth;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Infolists\Components\TextEntry;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Components\Attributes\Unexposed;
use Filament\Support\Icons\Heroicon;
use Livewire\Attributes\Locked;
use UnitEnum;

class DevelopmentServices extends Page
{
    protected static ?string $title = 'État des services';

    protected static string|UnitEnum|null $navigationGroup = 'Développement';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedServerStack;

    protected static ?int $navigationSort = 10;

    protected ?string $subheading = 'Votre environnement local, en un coup d’œil.';

    /** @var array<string, array{responding: bool, milliseconds: int}> */
    #[Locked]
    public array $health = [];

    /** @var array{generated_at?: int, services?: array<string, list<array{state: string, health: string}>>} */
    #[Locked]
    public array $docker = [];

    #[Locked]
    public string $checkedAt = '';

    #[Unexposed]
    public static function canAccess(): bool
    {
        $user = auth()->user();

        return app()->environment('local') && $user instanceof User && $user->is_admin && $user->hasVerifiedEmail();
    }

    public function mount(): void
    {
        $this->refreshStatus();
    }

    public function refreshStatus(): void
    {
        abort_unless(static::canAccess(), 403);
        $this->health = app(ServiceHealth::class)->check();
        $this->docker = app(DockerSnapshot::class)->read();
        $this->checkedAt = now()->format('d/m/Y H:i:s T');
    }

    /** @return array<Action> */
    protected function getHeaderActions(): array
    {
        return [Action::make('refresh')->label('Actualiser')->icon(Heroicon::OutlinedArrowPath)
            ->color('gray')->action(fn () => $this->refreshStatus())];
    }

    #[Unexposed]
    public function content(Schema $schema): Schema
    {
        return $schema
            ->extraAttributes(['wire:poll.15s.visible' => 'refreshStatus'])
            ->components([
                Section::make('Vue d’ensemble')
                    ->icon(Heroicon::OutlinedSignal)
                    ->description('Actualisation automatique toutes les 15 secondes pendant la consultation.')
                    ->schema([
                        TextEntry::make('availability')->label('Services disponibles')
                            ->state(fn (): string => count(array_filter($this->health, fn (array $result): bool => $result['responding'])).' / 3')
                            ->badge()->color(fn (): string => count(array_filter($this->health, fn (array $result): bool => $result['responding'])) === 3 ? 'success' : 'warning'),
                        TextEntry::make('last_check')->label('Dernier contrôle')
                            ->state(fn (): string => $this->checkedAt)->icon(Heroicon::OutlinedClock),
                        TextEntry::make('docker_report')->label('Relevé Docker')
                            ->state(fn (): string => $this->docker === [] ? 'Indisponible' : 'À jour')
                            ->helperText(fn (): string => $this->docker === [] ? 'Le collecteur est arrêté ou son relevé est périmé.' : 'Collecte toutes les 5 secondes.')
                            ->badge()->color(fn (): string => $this->docker === [] ? 'warning' : 'success'),
                    ])->columns(['md' => 3]),
                Grid::make(['md' => 2, 'xl' => 3])->schema([
                    $this->serviceCard('postgres', 'PostgreSQL', 'Base de données', Heroicon::OutlinedCircleStack),
                    $this->serviceCard('mailpit', 'Mailpit', 'E-mails de développement', Heroicon::OutlinedEnvelope, 'mailpit'),
                    $this->serviceCard('garage', 'Garage', 'Stockage des images', Heroicon::OutlinedArchiveBox, 'garage-webui'),
                ]),
                Section::make('Autres conteneurs')
                    ->description('État Docker des outils qui accompagnent l’application. Un conteneur en cours ne garantit pas que son service répond.')
                    ->icon(Heroicon::OutlinedCpuChip)
                    ->schema([
                        $this->containerEntry('app', 'Application'),
                        $this->containerEntry('queue', 'Tâches en arrière-plan'),
                        $this->containerEntry('vite', 'Vite'),
                        $this->containerEntry('garage-webui', 'Garage Web UI'),
                        $this->containerEntry('service-status', 'Collecteur Docker'),
                    ])->columns(['md' => 2, 'xl' => 3]),
            ]);
    }

    private function serviceCard(string $service, string $name, string $description, Heroicon $icon, ?string $link = null): Section
    {
        $section = Section::make($name)->description($description)->icon($icon)->schema([
            TextEntry::make($service.'_response')->label('Disponibilité')
                ->state(fn (): string => $this->health[$service]['responding'] ? 'Répond' : 'Ne répond pas')
                ->badge()->color(fn (): string => $this->health[$service]['responding'] ? 'success' : 'danger')
                ->icon(fn (): Heroicon => $this->health[$service]['responding'] ? Heroicon::OutlinedCheckCircle : Heroicon::OutlinedExclamationCircle),
            TextEntry::make($service.'_latency')->label('Durée du contrôle')
                ->state(fn (): string => $this->health[$service]['milliseconds'].' ms'),
            $this->containerEntry($service, 'Conteneur Docker'),
        ]);

        if ($link !== null) {
            $destination = app(ServiceCatalog::class)->links()[$link];
            $section->footerActions([
                Action::make('open_'.$service)->label('Ouvrir '.$destination['label'])
                    ->url($destination['url'])->openUrlInNewTab()->color('gray')
                    ->icon(Heroicon::OutlinedArrowTopRightOnSquare),
            ]);
        }

        return $section;
    }

    private function containerEntry(string $service, string $label): TextEntry
    {
        return TextEntry::make($service.'_container')->label($label)
            ->state(fn (): string => $this->containerStatus($service)['label'])
            ->badge()->color(fn (): string => $this->containerStatus($service)['color']);
    }

    /** @return array{label: string, color: string} */
    private function containerStatus(string $service): array
    {
        if ($this->docker === []) {
            return ['label' => 'Indisponible', 'color' => 'gray'];
        }

        $containers = $this->docker['services'][$service] ?? [];

        if ($containers === []) {
            return ['label' => 'Absent', 'color' => 'gray'];
        }

        $states = array_unique(array_column($containers, 'state'));
        $health = array_column($containers, 'health');
        $state = count($states) === 1 ? $states[0] : 'mixed';
        $label = match ($state) {
            'running' => 'En cours', 'exited' => 'Arrêté', 'restarting' => 'Redémarrage',
            'paused' => 'En pause', 'created' => 'Créé', 'removing' => 'Suppression',
            'dead' => 'En échec', 'mixed' => 'État mixte', default => 'Inconnu',
        };
        $color = match ($state) {
            'running' => 'success', 'exited', 'dead' => 'danger', 'unknown' => 'gray', default => 'warning',
        };

        if ($state === 'running' && in_array('unhealthy', $health, true)) {
            $label .= ' · Dégradé';
            $color = 'danger';
        } elseif ($state === 'running' && in_array('starting', $health, true)) {
            $label .= ' · Initialisation';
            $color = 'warning';
        } elseif ($state === 'running' && ! in_array('none', $health, true)) {
            $label .= ' · Sain';
        }

        return ['label' => $label, 'color' => $color];
    }
}
