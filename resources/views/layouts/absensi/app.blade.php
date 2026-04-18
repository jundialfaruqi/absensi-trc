<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<!--

  Name              : ABSENSI TRC
  Version           : 1.0
  Date              : April 14, 2026
  Url               : -
  Type              : Web APP
  Project Analyst   : Deni Hidayat
  Developer         : M. Jundi Al faruqi

  ============================================================================
  NOTE :
  Website ini dibuat oleh tim DISKOMINFOTIKSAN Pekanbaru.
  ============================================================================

-->

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ isset($title) ? $title . ' - ' . config('app.name') : config('app.name') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @livewireStyles
    <script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
</head>

<body class="font-sans">
    {{ $slot }}

    @livewireScripts
</body>

</html>
