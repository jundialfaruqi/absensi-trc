<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ isset($title) ? $title . ' - Portall Personnel TRC' : 'Login Personnel - TRC Pekanbaru' }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @livewireStyles
</head>

<body class="font-sans antialiased text-slate-200 bg-[#0a192f]">
    {{ $slot }}

    @livewireScripts
</body>

</html>
