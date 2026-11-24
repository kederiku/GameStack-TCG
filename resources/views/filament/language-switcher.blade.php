@php
    $identity = \App\Support\Localization\AdminPanels::identity(request()->route());
    $alternates = \App\Support\Localization\AdminPanels::alternates(request());
@endphp
@if ($identity && count($alternates) > 1)
    <div class="flex items-center gap-2" aria-label="{{ __('admin.interface_language') }}">
        @foreach (\App\Support\Localization\Locales::all() as $locale => $definition)
            @if ($locale !== $identity['locale'])
                {{-- Keep the translated path; table interactions update the browser query independently of the topbar.
                     Native navigation invokes Filament's beforeunload unsaved-change guard. --}}
                <form method="POST" action="{{ \App\Support\Localization\LocalizedRoutes::url('locale.update', [], $identity['locale']) }}"
                    onsubmit="this.elements.target.value = this.elements.target.value.split(/[?#]/)[0] + window.location.search + window.location.hash">
                    @csrf
                    <input type="hidden" name="locale" value="{{ $locale }}">
                    <input type="hidden" name="route" value="admin.{{ $identity['page'] }}">
                    <input type="hidden" name="target" value="{{ $alternates[$locale] }}">
                    <x-filament::button type="submit" color="gray" size="sm" aria-label="{{ __('admin.interface_language') }}: {{ $definition['label'] }}">
                        {{ $definition['label'] }}
                    </x-filament::button>
                </form>
            @endif
        @endforeach
    </div>
@endif
