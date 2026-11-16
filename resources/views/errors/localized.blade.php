@php
    $requested = request()->segment(1);
    $code = is_string($requested) && \App\Support\Localization\Locales::supports($requested)
        ? $requested : \App\Support\Localization\LocalizedRoutes::locale(request());
    $definition = \App\Support\Localization\Locales::definition($code);
    $locale = $definition['laravelLocale'];
    $title = __('common.errors.status-'.$status, [], $locale);
@endphp
<!DOCTYPE html>
<html lang="{{ $definition['htmlLang'] }}" dir="{{ $definition['direction'] }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>{{ $title }} — {{ config('app.name') }}</title>
    <style>body{margin:0;background:#111827;color:#f3f4f6;font-family:system-ui,sans-serif;display:grid;place-items:center;min-height:100vh}main{max-width:40rem;padding:2rem}a{color:#93c5fd}p{line-height:1.6}</style>
</head>
<body>
<main>
    <p>{{ $status }}</p>
    <h1>{{ $title }}</h1>
    <p>{{ __('common.errors.retry', [], $locale) }}</p>
    <a href="{{ \App\Support\Localization\LocalizedRoutes::url('home', [], $code) }}">{{ __('common.home', [], $locale) }}</a>
</main>
</body>
</html>
