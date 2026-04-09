<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Parent Portal - ' . config('app.name'))</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
    <script>
        if (localStorage.getItem('darkMode') === 'true' || (!('darkMode' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
</head>
<body class="bg-gray-100 dark:bg-gray-900 h-screen overflow-hidden">

<div class="flex h-screen overflow-hidden" x-data="{
    sidebarOpen: false,
    sidebarCollapsed: localStorage.getItem('sidebarCollapsed') === 'true',
    toggleSidebar() {
        this.sidebarCollapsed = !this.sidebarCollapsed;
        localStorage.setItem('sidebarCollapsed', this.sidebarCollapsed);
    }
}">
    <!-- Mobile Overlay -->
    <div x-show="sidebarOpen" @click="sidebarOpen = false"
         x-transition:enter="transition-opacity ease-linear duration-300"
         x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-300"
         x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-gray-600 bg-opacity-75 z-40 lg:hidden" style="display:none;"></div>

    <!-- Sidebar -->
    <aside class="fixed inset-y-0 left-0 z-50 bg-[#1a237e] text-white flex flex-col transform transition-all duration-300 ease-in-out lg:translate-x-0 lg:static lg:inset-0"
           :style="sidebarCollapsed ? 'width:5rem;' : 'width:16rem;'"
           :class="{ '-translate-x-full': !sidebarOpen, 'translate-x-0': sidebarOpen }">

        <!-- Logo -->
        <div class="p-4 border-b border-[#283593] flex-shrink-0 relative">
            <div class="flex items-center justify-center mb-2">
                <div class="bg-white rounded-full flex items-center justify-center transition-all duration-300"
                     :style="sidebarCollapsed ? 'width:2.5rem;height:2.5rem;' : 'width:4rem;height:4rem;'">
                    <i class="fas fa-user-friends text-[#1a237e]" :class="sidebarCollapsed ? 'text-lg' : 'text-2xl'"></i>
                </div>
            </div>
            <div x-show="!sidebarCollapsed" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                <h2 class="text-xs font-bold text-center leading-tight">PARENT PORTAL</h2>
                <p class="text-xs text-indigo-200 text-center mt-1">{{ config('app.name') }}</p>
            </div>
            <button @click="toggleSidebar()" class="absolute top-6 -right-4 w-8 h-8 flex items-center justify-center bg-teal-50 text-teal-600 rounded-full shadow-lg hover:bg-white hover:text-teal-700 transition-all duration-200 hidden lg:flex focus:outline-none z-50 border border-gray-200">
                <i class="fas" :class="sidebarCollapsed ? 'fa-chevron-right' : 'fa-chevron-left'" style="font-size:0.65rem;"></i>
            </button>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 overflow-y-auto py-4 px-2 space-y-1">
            @php
                $navItems = [
                    ['route' => 'parent.dashboard', 'icon' => 'fa-home', 'label' => 'Dashboard'],
                    ['route' => 'parent.children.index', 'icon' => 'fa-child', 'label' => 'My Children'],
                    ['route' => 'parent.results.index', 'icon' => 'fa-trophy', 'label' => 'Results'],
                    ['route' => 'parent.fees.index', 'icon' => 'fa-receipt', 'label' => 'Fee Details'],
                ];
            @endphp

            @foreach($navItems as $item)
                @if(Route::has($item['route']))
                <a href="{{ route($item['route']) }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all duration-200 group
                          {{ request()->routeIs(str_replace('.index', '.*', $item['route'])) ? 'bg-white/20 text-white' : 'text-indigo-200 hover:bg-white/10 hover:text-white' }}">
                    <i class="fas {{ $item['icon'] }} w-5 text-center flex-shrink-0" :class="sidebarCollapsed ? 'text-base' : 'text-sm'"></i>
                    <span x-show="!sidebarCollapsed" x-transition class="text-sm font-medium whitespace-nowrap">{{ $item['label'] }}</span>
                </a>
                @endif
            @endforeach
        </nav>

        <!-- User Info & Logout -->
        <div class="border-t border-[#283593] p-3 flex-shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-indigo-400 flex items-center justify-center flex-shrink-0 text-xs font-bold">
                    {{ strtoupper(substr(Auth::user()->name ?? 'P', 0, 1)) }}
                </div>
                <div x-show="!sidebarCollapsed" x-transition class="flex-1 min-w-0">
                    <p class="text-xs font-medium truncate">{{ Auth::user()->name ?? 'Parent' }}</p>
                    <p class="text-xs text-indigo-300 truncate">Parent</p>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}" class="mt-2">
                @csrf
                <button type="submit"
                        class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-indigo-200 hover:bg-white/10 hover:text-white transition-all duration-200 text-sm">
                    <i class="fas fa-sign-out-alt w-5 text-center flex-shrink-0"></i>
                    <span x-show="!sidebarCollapsed" x-transition class="whitespace-nowrap">Logout</span>
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col overflow-hidden">
        <!-- Top Bar -->
        <header class="bg-white dark:bg-gray-800 shadow-sm flex-shrink-0 z-30">
            <div class="flex items-center justify-between h-14 px-4">
                <div class="flex items-center gap-3">
                    <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden text-gray-500 hover:text-gray-700 dark:text-gray-400">
                        <i class="fas fa-bars text-lg"></i>
                    </button>
                    <h1 class="text-lg font-semibold text-gray-800 dark:text-white">@yield('page-title', 'Dashboard')</h1>
                </div>
                <div class="flex items-center gap-3">
                    <button onclick="document.documentElement.classList.toggle('dark'); localStorage.setItem('darkMode', document.documentElement.classList.contains('dark'))"
                            class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-white transition-colors">
                        <i class="fas fa-moon dark:hidden"></i>
                        <i class="fas fa-sun hidden dark:inline"></i>
                    </button>
                    <span class="text-sm text-gray-600 dark:text-gray-300">{{ Auth::user()->name ?? '' }}</span>
                </div>
            </div>
        </header>

        <!-- Page Content -->
        <main class="flex-1 overflow-y-auto p-6 dark:bg-gray-900">
            @if(session('success'))
                <div class="mb-4 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 rounded-lg text-green-700 dark:text-green-400 text-sm">
                    <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="mb-4 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 rounded-lg text-red-700 dark:text-red-400 text-sm">
                    <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
                </div>
            @endif
            @yield('content')
        </main>
    </div>
</div>

@stack('scripts')
</body>
</html>
