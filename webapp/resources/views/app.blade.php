<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title inertia>{{ config('app.name', 'Laravel') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.googleapis.com/css2?family=Geist+Mono:wght@100..900&display=swap" rel="stylesheet">

    @routes
    @vite(['resources/js/app.ts'])
    @inertiaHead

    <script>
        window.translations = {!! json_encode($translations) !!};
        window.__locale = '{{ $locale }}';
    </script>
</head>

<body class="font-sans antialiased">
    @inertia
</body>

</html>
