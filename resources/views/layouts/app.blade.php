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

    <!-- Livewire Styles -->
    @livewireStyles

    @stack('styles')
</head>
<body class="bg-gray-50 antialiased font-sans">
    <!-- Google Analytics -->
    @production
        <script async src="https://www.googletagmanager.com/gtag/js?id=GA_ID"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());
            gtag('config', 'GA_ID');
        </script>
    @endproduction
    
    @yield('content')
    
    <!-- Livewire Scripts -->
    @livewireScripts
    
    <script src="{{ mix('js/app.js') ?? asset('js/app.js') }}"></script>
    
    @stack('scripts')
</body>
</html>

