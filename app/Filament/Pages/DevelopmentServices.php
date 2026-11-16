<?php

namespace App\Filament\Pages;

use App\Models\User;
use App\Support\Development\DockerSnapshot;
use App\Support\Development\ServiceCatalog;
use App\Support\Development\ServiceHealth;
use App\Support\Localization\AdminPanels;
use App\Support\Localization\TranslationFiles;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Infolists\Components\TextEntry;
use Filament\Pages\Page;
use Filament\Panel;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Components\Attributes\Unexposed;
use Filament\Support\Icons\Heroicon;
use Livewire\Attributes\Locked;
use UnitEnum;

class DevelopmentServices extends Page
{
    public static function getSlug(?Panel $panel = null): string
    {
        $panel ??= Filament::getCurrentOrDefaultPanel();

        return app(TranslationFiles::class)->routes(AdminPanels::locale($panel->getId()))['admin.development-services'];
    }

    public function getTitle(): string
    {
        return __('admin.services_title');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.services_title');
    }

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return __('admin.groups.development');
    }

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedServerStack;

    protected static ?int $navigationSort = 10;

    public function getSubheading(): ?string
    {
        return __('admin.services.subtitle');
    }

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
        return [Action::make('refresh')->label(__('admin.labels.refresh'))->icon(Heroicon::OutlinedArrowPath)
            ->color('gray')->action(fn () => $this->refreshStatus())];
    }

    #[Unexposed]
    public function content(Schema $schema): Schema
    {
        return $schema
            ->extraAttributes(['wire:poll.15s.visible' => 'refreshStatus'])
            ->components([
                Section::make(__('admin.labels.overview'))
                    ->icon(Heroicon::OutlinedSignal)
                    ->description(__('admin.services.refresh_help'))
                    ->schema([
                        TextEntry::make('availability')->label(__('admin.labels.available-services'))
                            ->state(fn (): string => count(array_filter($this->health, fn (array $result): bool => $result['responding'])).' / 3')
                            ->badge()->color(fn (): string => count(array_filter($this->health, fn (array $result): bool => $result['responding'])) === 3 ? 'success' : 'warning'),
                        TextEntry::make('last_check')->label(__('admin.labels.last-check'))
                            ->state(fn (): string => $this->checkedAt)->icon(Heroicon::OutlinedClock),
                        TextEntry::make('docker_report')->label(__('admin.labels.docker-snapshot'))
                            ->state(fn (): string => $this->docker === [] ? __('admin.labels.unavailable') : __('admin.labels.up-to-date'))
                            ->helperText(fn (): string => $this->docker === [] ? __('admin.services.snapshot_expired') : __('admin.services.snapshot_interval'))
                            ->badge()->color(fn (): string => $this->docker === [] ? 'warning' : 'success'),
                    ])->columns(['md' => 3]),
                Grid::make(['md' => 2, 'xl' => 3])->schema([
                    $this->serviceCard('postgres', 'PostgreSQL', __('admin.labels.database'), Heroicon::OutlinedCircleStack),
                    $this->serviceCard('mailpit', 'Mailpit', __('admin.labels.development-emails'), Heroicon::OutlinedEnvelope, 'mailpit'),
                    $this->serviceCard('garage', 'Garage', __('admin.labels.image-storage'), Heroicon::OutlinedArchiveBox, 'garage-webui'),
                ]),
                Section::make(__('admin.labels.other-containers'))
                    ->description(__('admin.services.containers_help'))
                    ->icon(Heroicon::OutlinedCpuChip)
                    ->schema([
                        $this->containerEntry('app', __('admin.labels.application')),
                        $this->containerEntry('queue', __('admin.labels.background-jobs')),
                        $this->containerEntry('vite', 'Vite'),
                        $this->containerEntry('garage-webui', 'Garage Web UI'),
                        $this->containerEntry('service-status', __('admin.labels.docker-collector')),
                    ])->columns(['md' => 2, 'xl' => 3]),
            ]);
    }

    private function serviceCard(string $service, string $name, string $description, Heroicon $icon, ?string $link = null): Section
    {
        $section = Section::make($name)->description($description)->icon($icon)->schema([
            TextEntry::make($service.'_response')->label(__('admin.labels.availability'))
                ->state(fn (): string => $this->health[$service]['responding'] ? __('admin.labels.responding') : __('admin.labels.not-responding'))
                ->badge()->color(fn (): string => $this->health[$service]['responding'] ? 'success' : 'danger')
                ->icon(fn (): Heroicon => $this->health[$service]['responding'] ? Heroicon::OutlinedCheckCircle : Heroicon::OutlinedExclamationCircle),
            TextEntry::make($service.'_latency')->label(__('admin.labels.check-duration'))
                ->state(fn (): string => $this->health[$service]['milliseconds'].' ms'),
            $this->containerEntry($service, __('admin.labels.docker-container')),
        ]);

        if ($link !== null) {
            $destination = app(ServiceCatalog::class)->links()[$link];
            $section->footerActions([
                Action::make('open_'.$service)->label(__('admin.service_actions.open', ['service' => $destination['label']]))
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
            return ['label' => __('admin.labels.unavailable'), 'color' => 'gray'];
        }

        $containers = $this->docker['services'][$service] ?? [];

        if ($containers === []) {
            return ['label' => __('admin.labels.missing'), 'color' => 'gray'];
        }

        $states = array_unique(array_column($containers, 'state'));
        $health = array_column($containers, 'health');
        $state = count($states) === 1 ? $states[0] : 'mixed';
        $label = match ($state) {
            'running' => __('admin.labels.running'), 'exited' => __('admin.labels.stopped'), 'restarting' => __('admin.labels.restarting'),
            'paused' => __('admin.labels.paused'), 'created' => __('admin.labels.created'), 'removing' => __('admin.labels.removing'),
            'dead' => __('admin.labels.failed'), 'mixed' => __('admin.labels.mixed-status'), default => __('admin.labels.unknown'),
        };
        $color = match ($state) {
            'running' => 'success', 'exited', 'dead' => 'danger', 'unknown' => 'gray', default => 'warning',
        };

        if ($state === 'running' && in_array('unhealthy', $health, true)) {
            $label .= __('admin.labels.degraded');
            $color = 'danger';
        } elseif ($state === 'running' && in_array('starting', $health, true)) {
            $label .= __('admin.initializing');
            $color = 'warning';
        } elseif ($state === 'running' && ! in_array('none', $health, true)) {
            $label .= __('admin.healthy');
        }

        return ['label' => $label, 'color' => $color];
    }
}
