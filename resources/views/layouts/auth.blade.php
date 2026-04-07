<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="EduSphere - Enterprise School ERP Platform">
    <title>@yield('title', config('app.name', 'EduSphere')) - School ERP</title>

    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')
</head>
<body class="bg-gray-50 antialiased font-sans min-h-screen">
    @yield('content')

    @stack('scripts')
</body>
</html>
