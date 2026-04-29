<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Receptionist Dashboard - ' . config('app.name'))</title>
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

    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

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
            const activeItem = nav ? nav.querySelector('a.text-white') : null;
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
            class="fixed inset-0 bg-gray-600 bg-opacity-75 z-40 lg:hidden" style="display: none;"></div>

        @include('partials.receptionist-sidebar')

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Header -->
            <header class="bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700 transition-colors">
                <div class="flex items-center justify-between px-4 sm:px-6 py-4">
                    <!-- Left: Mobile menu toggle -->
                    <div class="flex items-center space-x-3 sm:space-x-4 flex-1">
                        <button @click="sidebarOpen = !sidebarOpen"
                            class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-100 lg:hidden focus:outline-none transition-colors">
                            <i class="fas fa-bars text-xl sm:text-2xl"></i>
                        </button>
                    </div>

                    <!-- Right: Actions & User -->
                    <div class="flex items-center space-x-2 sm:space-x-4" x-data="headerActions">

                        <!-- Saved Pages -->
                        <div class="relative hidden md:block">
                            <button @click="showFavorites = !showFavorites"
                                class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-100 transition-colors"
                                title="Saved Pages">
                                <i class="far fa-bookmark text-xl"></i>
                            </button>
                            <div x-show="showFavorites" @click.outside="showFavorites = false"
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 scale-95"
                                x-transition:enter-end="opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-150"
                                x-transition:leave-start="opacity-100 scale-100"
                                x-transition:leave-end="opacity-0 scale-95"
                                class="absolute right-0 mt-2 w-64 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 py-2 z-50"
                                x-cloak>
                                <div class="px-4 py-2 border-b border-gray-100 dark:border-gray-700">
                                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-100">Saved Pages</h3>
                                </div>
                                <div class="max-h-64 overflow-y-auto">
                                    <template x-if="favorites.length === 0">
                                        <div class="px-4 py-4 text-center text-gray-500 dark:text-gray-400 text-sm">No saved pages yet.</div>
                                    </template>
                                    <template x-for="fav in favorites" :key="fav.id">
                                        <div class="group flex items-center justify-between px-4 py-2 hover:bg-gray-50 dark:hover:bg-gray-700/60">
                                            <a :href="fav.url" class="text-sm text-gray-700 dark:text-gray-200 hover:text-blue-600 dark:hover:text-blue-400 truncate flex-1" x-text="fav.title"></a>
                                            <button @click="removeFavorite(fav.id)" class="ml-2 text-gray-400 hover:text-red-500 opacity-0 group-hover:opacity-100 transition-opacity">
                                                <i class="fas fa-times text-xs"></i>
                                            </button>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>

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
                            <i class="text-xl far fa-moon" x-show="false" style="display:inline-block" id="dark-mode-fallback-icon"></i>
                        </button>
                        <script>if(localStorage.getItem('darkMode')==='true'||(!('darkMode'in localStorage)&&window.matchMedia('(prefers-color-scheme: dark)').matches)){var _d=document.getElementById('dark-mode-fallback-icon');if(_d){_d.className='text-xl fas fa-sun';}}</script>

                        <!-- User Dropdown -->
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" class="flex items-center space-x-1 sm:space-x-2 focus:outline-none">
                                <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center text-white">
                                    <i class="fas fa-user text-sm"></i>
                                </div>
                                <span class="text-gray-700 dark:text-gray-200 font-medium hidden sm:inline text-sm">{{ Auth::user()->name ?? 'Receptionist' }}</span>
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
                                    <i class="fas fa-user-circle mr-3 text-gray-500 dark:text-gray-400"></i>Profile
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
                @yield('content')
            </main>
        </div>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        window.select2Initialized = window.select2Initialized || new Set();
        function initSelect2($select) {
            if ($select.hasClass('select2-hidden-accessible') || $select.hasClass('no-select2') || $select.is('[data-table-select]')) return false;
            const selectId = $select.attr('id') || $select.attr('name') || $select[0].outerHTML;
            if (window.select2Initialized.has(selectId)) return false;
            window.select2Initialized.add(selectId);
            try {
                $select.select2({
                    placeholder: function() { return $(this).data('placeholder') || 'Select an option'; },
                    allowClear: $select.data('allow-clear') !== undefined ? $select.data('allow-clear') : false,
                    width: '100%'
                });
                if ($select.attr('name') && $select.attr('name').includes('country_id')) {
                    let hasExplicit = $select.find('option[selected]').length > 0;
                    if (!hasExplicit || !$select.val() || $select.val() === '') setTimeout(() => $select.val('102').trigger('change'), 50);
                }
                return true;
            } catch(e) { window.select2Initialized.delete(selectId); return false; }
        }
        $(document).ready(function() {
            $('select').each(function() { initSelect2($(this)); });
            let initTimeout;
            const observer = new MutationObserver(function(mutations) {
                let newSelects = [];
                mutations.forEach(function(mutation) {
                    if (mutation.addedNodes.length) {
                        $(mutation.addedNodes).find('select').each(function() {
                            const $s = $(this);
                            if (!$s.hasClass('select2-hidden-accessible') && !$s.hasClass('no-select2') && !$s.is('[data-table-select]') && $s.closest('[x-cloak]').length === 0) newSelects.push(this);
                        });
                    }
                });
                if (newSelects.length > 0) { clearTimeout(initTimeout); initTimeout = setTimeout(() => $(newSelects).each(function() { initSelect2($(this)); }), 50); }
            });
            observer.observe(document.body, { childList: true, subtree: true });
        });
    </script>

    @stack('scripts')

    <x-delete-confirmation />

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('headerActions', () => ({
                isFullscreen: false,
                isDark: localStorage.getItem('darkMode') === 'true',
                favorites: [],
                showFavorites: false,

                init() {
                    this.$el.querySelectorAll('.ssr-icon-fallback').forEach(el => el.remove());
                    this.loadFavorites();
                    document.addEventListener('fullscreenchange', () => { this.isFullscreen = !!document.fullscreenElement; });
                },

                toggleFullscreen() {
                    if (!document.fullscreenElement) {
                        document.documentElement.requestFullscreen().catch(err => console.error(err));
                    } else if (document.exitFullscreen) {
                        document.exitFullscreen();
                    }
                },

                toggleDarkMode() {
                    this.isDark = !this.isDark;
                    localStorage.setItem('darkMode', this.isDark);
                    document.documentElement.classList.toggle('dark', this.isDark);
                },

                loadFavorites() {
                    try { this.favorites = JSON.parse(localStorage.getItem('receptionist_favorites') || '[]'); }
                    catch(e) { this.favorites = []; }
                },

                removeFavorite(id) {
                    this.favorites = this.favorites.filter(f => f.id !== id);
                    localStorage.setItem('receptionist_favorites', JSON.stringify(this.favorites));
                }
            }));
        });
    </script>
    <script src="{{ asset('js/form-validation-handler.js') }}"></script>
    <script src="{{ asset('js/location-cascade.js') }}"></script>
    <x-toast />
</body>

</html>
