<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title inertia>{{ config('app.name', 'Bilimsel Program') }}</title>

        <!-- Favicon -->
        <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg width='32' height='32' viewBox='0 0 32 32' fill='none' xmlns='http://www.w3.org/2000/svg'><circle cx='16' cy='16' r='16' fill='%233B82F6'/><rect x='6' y='8' width='20' height='18' rx='3' fill='white'/><rect x='6' y='8' width='20' height='6' rx='3' fill='%231E40AF'/><rect x='9' y='16' width='2' height='2' rx='0.5' fill='%236B7280'/><rect x='13' y='16' width='2' height='2' rx='0.5' fill='%236B7280'/><rect x='17' y='16' width='2' height='2' rx='0.5' fill='%236B7280'/><rect x='21' y='16' width='2' height='2' rx='0.5' fill='%236B7280'/><rect x='9' y='20' width='2' height='2' rx='0.5' fill='%236B7280'/><rect x='13' y='20' width='2' height='2' rx='0.5' fill='%236B7280'/><rect x='17' y='20' width='2' height='2' rx='0.5' fill='%236B7280'/><rect x='21' y='20' width='2' height='2' rx='0.5' fill='%236B7280'/><rect x='10' y='5' width='1.5' height='6' rx='0.75' fill='%231E40AF'/><rect x='20.5' y='5' width='1.5' height='6' rx='0.75' fill='%231E40AF'/></svg>">
        <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
        <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
        <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
        

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @routes
        @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>