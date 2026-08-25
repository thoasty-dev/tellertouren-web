<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $metadata->title ?? 'Tellertouren' }}</title>
    @isset($metadata)
        @if($metadata->canonical)
            <link rel="canonical" href="{{ $metadata->canonical }}">
        @endif
        @if($metadata->description)
            <meta name="description" content="{{ $metadata->description }}">
        @endif
        @foreach($metadata->openGraph() as $property => $content)
            <meta property="{{ $property }}" content="{{ $content }}">
        @endforeach
        @if($metadata->jsonLd)
            <script type="application/ld+json">{!! json_encode($metadata->jsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR) !!}</script>
        @endif
    @endisset
    @stack('layout.head')
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="icon" type="image/png" sizes="96x96" href="{{ asset('favicon-96x96.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">
    <meta name="theme-color" content="#202020" media="(prefers-color-scheme: dark)">
    <meta name="theme-color" content="#ffffff" media="(prefers-color-scheme: light)">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen bg-base-100 text-base-content antialiased">
    @stack('layout.body')
    @livewireScripts
</body>
</html>
