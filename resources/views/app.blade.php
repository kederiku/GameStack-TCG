<!DOCTYPE html>
@php($language = \App\Support\Localization\Locales::definition($page['props']['locale']))
<html lang="{{ $language['htmlLang'] }}" dir="{{ $language['direction'] }}" class="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        {{-- Match the public dark theme before the stylesheet loads. --}}
        <style>
            html {
                background-color: #181e29;
                color-scheme: dark;
            }
        </style>

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        @fonts

        @viteReactRefresh
        @vite(['resources/css/app.css', 'resources/js/app.tsx', "resources/js/pages/{$page['component']}.tsx"])
        <x-inertia::head>
            @if($page['props']['metadata']['canonical'])
                <link data-inertia="canonical" rel="canonical" href="{{ $page['props']['metadata']['canonical'] }}">
            @endif
            @foreach($page['props']['metadata']['alternates'] as $lang => $href)
                <link data-inertia="alternate-{{ $lang }}" rel="alternate" hreflang="{{ $lang }}" href="{{ $href }}">
            @endforeach
            @if($page['props']['metadata']['robots'])
                <meta data-inertia="robots" name="robots" content="{{ $page['props']['metadata']['robots'] }}">
            @endif
            <title>{{ config('app.name', 'Laravel') }}</title>
        </x-inertia::head>
    </head>
    <body class="font-sans antialiased">
        <x-inertia::app />
    </body>
</html>
