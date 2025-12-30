<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'EduSphere'))</title>
    
    <!-- Vite Assets - Make sure to run 'npm run dev' or 'npm run build' -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Fallback if Vite is not running -->
    @production
        <!-- Production: Vite will be built -->
    @else
        <!-- Development: Make sure npm run dev is running -->
        @if(!app()->environment('local'))
            <script>
                console.warn('Vite dev server not running. Run: npm run dev');
            </script>
        @endif
    @endproduction
    
    @stack('styles')
</head>
<body class="bg-gray-50">
    @yield('content')
    
    @stack('scripts')
    <!-- Global Form Validation Error Handler -->
    <script src="{{ asset('js/form-validation-handler.js') }}"></script>
</body>
</html>

