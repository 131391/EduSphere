<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Parent Portal - ' . config('app.name'))</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">

    <!-- Dark Mode Persistence (must run before CSS/paint to avoid FOUC) -->
    <script>
        if (localStorage.getItem('darkMode') === 'true' || (!('darkMode' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
        integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')

    @include('partials.sidebar-scripts')
</head>

<body class="bg-gray-100 dark:bg-gray-900 h-screen overflow-hidden transition-colors">
    <div class="flex h-screen overflow-hidden" x-data="{
        sidebarOpen: false,
        isMobile: window.innerWidth < 1024,
        sidebarCollapsed: localStorage.getItem('sidebarCollapsed') === 'true',
        scrollActiveItemIntoView() {
            if (this.sidebarCollapsed) return;
            const nav = document.querySelector('aside nav');
            const activeItem = nav ? nav.querySelector('a.text-teal-300') : null;
            if (nav && activeItem) activeItem.scrollIntoView({ block: 'nearest' });
        },
        init() {
            if (this.isMobile) this.sidebarCollapsed = false;
            document.documentElement.classList.toggle('sidebar-collapsed', this.sidebarCollapsed);
            window.addEventListener('resize', () => {
                this.isMobile = window.innerWidth < 1024;
                if (this.isMobile) {
                    this.sidebarCollapsed = false;
                    document.documentElement.classList.remove('sidebar-collapsed');
                } else {
                    this.sidebarCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
                    document.documentElement.classList.toggle('sidebar-collapsed', this.sidebarCollapsed);
                }
                if (!this.isMobile) this.sidebarOpen = false;
            });
            requestAnimationFrame(() => requestAnimationFrame(() => {
                const aside = document.querySelector('aside');
                if (aside) aside.classList.remove('no-transition');
                this.scrollActiveItemIntoView();
            }));
        },
        toggleSidebar() {
            this.sidebarCollapsed = !this.sidebarCollapsed;
            localStorage.setItem('sidebarCollapsed', this.sidebarCollapsed);
            document.documentElement.classList.toggle('sidebar-collapsed', this.sidebarCollapsed);
            if (!this.sidebarCollapsed) this.$nextTick(() => this.scrollActiveItemIntoView());
        }
    }">
        <!-- Mobile Sidebar Overlay -->
        <div x-show="sidebarOpen" @click="sidebarOpen = false"
            x-transition:enter="transition-opacity ease-linear duration-300" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition-opacity ease-linear duration-300"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-gray-600 bg-opacity-75 z-40 lg:hidden" style="display:none"></div>

        @include('partials.parent-sidebar')

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Header -->
            <header class="bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700 transition-colors">
                <div class="flex items-center justify-between px-4 sm:px-6 py-4">
                    <div class="flex items-center space-x-3 sm:space-x-4 flex-1">
                        <button @click="sidebarOpen = !sidebarOpen"
                            class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-100 lg:hidden focus:outline-none transition-colors">
                            <i class="fas fa-bars text-xl sm:text-2xl"></i>
                        </button>
                    </div>

                    <!-- Right: Actions & User -->
                    <div class="flex items-center space-x-2 sm:space-x-4" x-data="{
                        isFullscreen: false,
                        isDark: localStorage.getItem('darkMode') === 'true',
                        init() {
                            document.addEventListener('fullscreenchange', () => { this.isFullscreen = !!document.fullscreenElement; });
                        },
                        toggleFullscreen() {
                            if (!document.fullscreenElement) {
                                document.documentElement.requestFullscreen().catch(e => console.error(e));
                            } else if (document.exitFullscreen) {
                                document.exitFullscreen();
                            }
                        },
                        toggleDarkMode() {
                            this.isDark = !this.isDark;
                            localStorage.setItem('darkMode', this.isDark);
                            document.documentElement.classList.toggle('dark', this.isDark);
                        }
                    }">
                        <!-- Fullscreen -->
                        <button @click="toggleFullscreen()"
                            class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-100 transition-colors hidden md:block"
                            title="Toggle Fullscreen">
                            <i class="text-xl fas fa-expand"></i>
                        </button>

                        <!-- Dark Mode -->
                        <button @click="toggleDarkMode()"
                            class="transition-colors hidden sm:block"
                            :class="isDark ? 'text-yellow-400 hover:text-yellow-300' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-100'"
                            title="Toggle Dark Mode">
                            <i class="text-xl" :class="isDark ? 'fas fa-sun' : 'far fa-moon'" x-cloak></i>
                            <i class="text-xl far fa-moon" x-show="false" style="display:inline-block" id="parent-dark-icon"></i>
                        </button>
                        <script>if(localStorage.getItem('darkMode')==='true'||(!('darkMode'in localStorage)&&window.matchMedia('(prefers-color-scheme: dark)').matches)){var _d=document.getElementById('parent-dark-icon');if(_d){_d.className='text-xl fas fa-sun';}}</script>

                        <!-- User Dropdown -->
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" class="flex items-center space-x-1 sm:space-x-2 focus:outline-none">
                                <div class="w-8 h-8 bg-indigo-600 rounded-full flex items-center justify-center text-white font-bold text-xs">
                                    {{ strtoupper(substr(Auth::user()->name ?? 'P', 0, 1)) }}
                                </div>
                                <span class="text-gray-700 dark:text-gray-200 font-medium hidden sm:inline text-sm">{{ Auth::user()->name ?? 'Parent' }}</span>
                                <i class="fas fa-chevron-down text-gray-500 dark:text-gray-400 text-xs hidden sm:inline"></i>
                            </button>
                            <div x-show="open" x-cloak @click.outside="open = false"
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 scale-95"
                                x-transition:enter-end="opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-150"
                                x-transition:leave-start="opacity-100 scale-100"
                                x-transition:leave-end="opacity-0 scale-95"
                                class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 py-1 z-50">
                                <a href="#" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center">
                                    <i class="fas fa-user-circle mr-3 text-gray-400 dark:text-gray-500"></i>Profile
                                </a>
                                <div class="border-t border-gray-200 dark:border-gray-700 my-1"></div>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 flex items-center">
                                        <i class="fas fa-sign-out-alt mr-3"></i>Logout
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto bg-gray-50 dark:bg-gray-900 p-4 sm:p-6 transition-colors">
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

    <x-delete-confirmation />
    <x-toast />

    @stack('scripts')
</body>

</html>
