<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Teacher Portal - ' . config('app.name'))</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">

    <!-- Dark Mode Persistence -->
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
            if (nav && activeItem) {
                activeItem.scrollIntoView({ block: 'nearest' });
            }
        },
        init() {
            if (this.isMobile) {
                this.sidebarCollapsed = false;
            }
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
                if (!this.isMobile) {
                    this.sidebarOpen = false;
                }
            });
            // Remove no-transition after first paint so subsequent transitions are smooth
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
            if (!this.sidebarCollapsed) {
                this.$nextTick(() => this.scrollActiveItemIntoView());
            }
        }
    }">
        <!-- Mobile Sidebar Overlay -->
        <div x-show="sidebarOpen" @click="sidebarOpen = false"
            x-transition:enter="transition-opacity ease-linear duration-300" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition-opacity ease-linear duration-300"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-gray-600 bg-opacity-75 z-40 lg:hidden" style="display: none;"></div>
        
        <!-- Sidebar -->
        <aside
            class="fixed inset-y-0 left-0 z-50 bg-[#1a237e] text-white flex flex-col transform transition-all duration-300 ease-in-out lg:translate-x-0 lg:static lg:inset-0 no-transition"
            :style="(isMobile || sidebarOpen) ? 'width: 16rem;' : (sidebarCollapsed ? 'width: 5rem;' : 'width: 16rem;')" :class="{
                   '-translate-x-full': !sidebarOpen, 
                   'translate-x-0': sidebarOpen,
                   'sidebar-collapsed': sidebarCollapsed && !isMobile,
                   'mobile-open': sidebarOpen
               }">
            <!-- Logo Section -->
            @php
                $school = Auth::user()->school;
            @endphp
            <div class="p-4 border-b border-[#283593] flex-shrink-0 relative group">
                <div class="flex items-center justify-center mb-2">
                    <div class="bg-white rounded-full flex items-center justify-center transition-all duration-300 logo-container"
                        style="width: 4rem; height: 4rem;"
                        :style="sidebarCollapsed ? 'width: 2.5rem; height: 2.5rem;' : 'width: 4rem; height: 4rem;'">
                        @if(isset($school) && $school->logo)
                            <img src="{{ asset('storage/' . $school->logo) }}" alt="{{ $school->name }}"
                                class="rounded-full object-cover logo-img bg-white" style="width: 4rem; height: 4rem;"
                                :style="sidebarCollapsed ? 'width: 2.5rem; height: 2.5rem;' : 'width: 4rem; height: 4rem;'">
                        @else
                            <i class="fas fa-chalkboard-teacher text-[#1a237e] logo-img" :class="sidebarCollapsed ? 'text-lg' : 'text-2xl'"></i>
                        @endif
                    </div>
                </div>

                <div x-show="!sidebarCollapsed" x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="sidebar-text">
                    <h2 class="text-xs font-bold text-center leading-tight">
                        {{ strtoupper($school->name ?? 'TEACHER PORTAL') }}
                    </h2>
                    <p class="text-[10px] uppercase font-bold tracking-widest text-indigo-300 text-center mt-1">
                        Teacher Portal
                    </p>
                </div>

                <!-- Toggle Button -->
                <button @click="toggleSidebar()"
                    class="absolute top-6 -right-4 w-8 h-8 flex items-center justify-center bg-teal-50 text-teal-600 rounded-full shadow-lg hover:bg-white hover:text-teal-700 transition-all duration-200 hidden lg:flex focus:outline-none z-50 border border-gray-200">
                    <i class="fas fa-chevron-left" x-show="!sidebarCollapsed"></i>
                    <i class="fas fa-chevron-right" x-show="sidebarCollapsed" style="display: none;"></i>
                </button>
            </div>

            <!-- Navigation Menu -->
            <nav class="flex-1 py-4 sidebar-scroll overflow-y-auto">
                <ul class="space-y-1 px-2">
                    @php
                        $navItems = [
                            ['route' => 'teacher.dashboard', 'icon' => 'fa-home', 'label' => 'Dashboard'],
                            ['route' => 'teacher.attendance.index', 'icon' => 'fa-calendar-check', 'label' => 'Mark Attendance'],
                            ['route' => 'teacher.students.index', 'icon' => 'fa-users', 'label' => 'My Students'],
                        ];
                    @endphp

                    @foreach($navItems as $item)
                        @if(Route::has($item['route']))
                        <li>
                            <a href="{{ route($item['route']) }}"
                                class="flex items-center px-4 py-2 rounded-lg {{ request()->routeIs(str_replace('.index', '.*', $item['route'])) ? 'bg-[#283593] text-white' : 'text-indigo-100 hover:bg-[#283593]' }}"
                                :class="{ 'justify-center': sidebarCollapsed }">
                                <i class="fas {{ $item['icon'] }} w-5" :class="{ 'mr-3': !sidebarCollapsed }"></i>
                                <span x-show="!sidebarCollapsed" class="sidebar-text whitespace-nowrap">{{ $item['label'] }}</span>
                            </a>
                        </li>
                        @endif
                    @endforeach
                    
                    <li class="mt-4 border-t border-[#283593] pt-4">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                class="w-full flex items-center px-4 py-2 rounded-lg text-indigo-100 hover:bg-[#283593] text-left"
                                :class="{ 'justify-center': sidebarCollapsed }">
                                <i class="fas fa-sign-out-alt w-5" :class="{ 'mr-3': !sidebarCollapsed }"></i>
                                <span x-show="!sidebarCollapsed">Logout</span>
                            </button>
                        </form>
                    </li>
                </ul>
            </nav>

            <!-- Footer -->
            <div class="p-4 border-t border-[#283593] text-xs text-indigo-100 text-center sidebar-text flex-shrink-0" x-show="!sidebarCollapsed" x-cloak>
                <p>{{ Auth::user()->name ?? 'Teacher' }}</p>
                <p class="mt-1 opacity-75">{{ date('Y') }} © EduSphere</p>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Header -->
            <header class="bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700 transition-colors">
                <div class="flex items-center justify-between px-4 sm:px-6 py-4">
                    <!-- Left: Menu & Title -->
                    <div class="flex items-center space-x-3 sm:space-x-4 flex-1">
                        <button @click="sidebarOpen = !sidebarOpen"
                            class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-100 lg:hidden focus:outline-none transition-colors">
                            <i class="fas fa-bars text-xl sm:text-2xl"></i>
                        </button>
                        <h1 class="text-lg font-semibold text-gray-800 dark:text-white hidden sm:block">@yield('page-title', 'Teacher Portal')</h1>
                    </div>

                    <!-- Right: Actions & User -->
                    <div class="flex items-center space-x-2 sm:space-x-4" x-data="headerActions">
                        
                        <h1 class="text-lg font-semibold text-gray-800 dark:text-white block sm:hidden">@yield('page-title', 'Teacher Portal')</h1>

                        <!-- Fullscreen -->
                        <button @click="toggleFullscreen()"
                            class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-100 transition-colors hidden md:block"
                            title="Toggle Fullscreen">
                            <i class="text-xl" :class="isFullscreen ? 'fas fa-compress' : 'fas fa-expand'" x-cloak></i>
                            <i class="text-xl fas fa-expand ssr-icon-fallback"></i>
                        </button>

                        <!-- Dark Mode -->
                        <button @click="toggleDarkMode()"
                            class="transition-colors hidden sm:block"
                            :class="isDark ? 'text-yellow-400 dark:text-yellow-400 hover:text-yellow-300' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-100'"
                            title="Toggle Dark Mode">
                            <i class="text-xl" :class="isDark ? 'fas fa-sun' : 'far fa-moon'" x-cloak></i>
                            <i class="text-xl far fa-moon ssr-icon-fallback"></i>
                        </button>

                        <!-- User Dropdown -->
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open"
                                class="flex items-center space-x-1 sm:space-x-2 focus:outline-none">
                                <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center text-white font-bold text-xs">
                                    {{ strtoupper(substr(Auth::user()->name ?? 'T', 0, 1)) }}
                                </div>
                                <span class="text-gray-700 dark:text-gray-200 font-medium hidden sm:inline text-sm">{{ Auth::user()->name ?? 'Teacher' }}</span>
                                <i class="fas fa-chevron-down text-gray-500 dark:text-gray-400 text-xs hidden sm:inline"></i>
                            </button>

                            <!-- Dropdown Menu -->
                            <div x-show="open" x-cloak @click.outside="open = false"
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 scale-95"
                                x-transition:enter-end="opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-150"
                                x-transition:leave-start="opacity-100 scale-100"
                                x-transition:leave-end="opacity-0 scale-95"
                                class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 py-1 z-50">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit"
                                        class="w-full text-left px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 flex items-center">
                                        <i class="fas fa-sign-out-alt mr-3"></i>
                                        Logout
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

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data("headerActions", () => ({
                isFullscreen: false,
                isDark: localStorage.getItem("darkMode") === "true",

                init() {
                    this.$el.querySelectorAll('.ssr-icon-fallback').forEach(el => el.remove());

                    // Listen for fullscreen changes
                    document.addEventListener('fullscreenchange', () => {
                        this.isFullscreen = !!document.fullscreenElement;
                    });
                },

                toggleFullscreen() {
                    if (!document.fullscreenElement) {
                        document.documentElement.requestFullscreen().catch(err => {
                            console.error(`Error attempting to enable full-screen mode: ${err.message} (${err.name})`);
                        });
                    } else {
                        if (document.exitFullscreen) {
                            document.exitFullscreen();
                        }
                    }
                },

                toggleDarkMode() {
                    this.isDark = !this.isDark;
                    localStorage.setItem('darkMode', this.isDark);
                    if (this.isDark) {
                        document.documentElement.classList.add('dark');
                    } else {
                        document.documentElement.classList.remove('dark');
                    }
                }
            }));
        });
    </script>
    
    <!-- jQuery (required for Select2) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- Select2 Initialization -->
    <script>
        window.select2Initialized = window.select2Initialized || new Set();

        function initSelect2($select) {
            if ($select.hasClass('select2-hidden-accessible') || $select.hasClass('no-select2') || $select.is('[data-table-select]')) {
                return false;
            }

            const selectId = $select.attr('id') || $select.attr('name') || $select[0].outerHTML;
            if (window.select2Initialized.has(selectId)) return false;

            window.select2Initialized.add(selectId);

            try {
                $select.select2({
                    placeholder: function () { return $(this).data('placeholder') || 'Select an option'; },
                    allowClear: $(this).data('allow-clear') !== undefined ? $(this).data('allow-clear') : false,
                    width: '100%'
                });

                if ($select.attr('name') && $select.attr('name').includes('country_id')) {
                    let hasExplicitSelection = $select.find('option[selected]').length > 0;
                    if (!hasExplicitSelection || !$select.val() || $select.val() === "") {
                        setTimeout(() => $select.val('102').trigger('change'), 50);
                    }
                }

                return true;
            } catch (e) {
                window.select2Initialized.delete(selectId);
                return false;
            }
        }

        $(document).ready(function () {
            $('select').each(function () { initSelect2($(this)); });

            let initTimeout;
            function debouncedInitSelect2($selects) {
                clearTimeout(initTimeout);
                initTimeout = setTimeout(function () { $selects.each(function () { initSelect2($(this)); }); }, 50);
            }

            const observer = new MutationObserver(function (mutations) {
                let newSelects = [];
                mutations.forEach(function (mutation) {
                    if (mutation.addedNodes.length) {
                        $(mutation.addedNodes).find('select').each(function () {
                            const $select = $(this);
                            if (!$select.hasClass('select2-hidden-accessible') && !$select.hasClass('no-select2') && !$select.is('[data-table-select]') && $select.closest('[x-cloak]').length === 0) {
                                newSelects.push(this);
                            }
                        });
                    }
                });
                if (newSelects.length > 0) debouncedInitSelect2($(newSelects));
            });

            observer.observe(document.body, { childList: true, subtree: true });
        });

        window.showToast = function(type, message) {
            const event = new CustomEvent('show-toast', { detail: { type, message } });
            window.dispatchEvent(event);
        };
    </script>

    <!-- Global Confirmation Modal Handler -->
    <script>
        (function () {
            function initConfirmModal() {
                if (typeof window.confirmModal !== 'undefined') return;

                window.confirmModal = {
                    currentModal: null, currentCallback: null,
                    show(modalId, callback, options = {}) {
                        const modal = document.getElementById(modalId);
                        if (!modal) return false;

                        if (options.title) { const t = modal.querySelector('h3'); if (t) t.textContent = options.title; }
                        if (options.message) { const m = modal.querySelector('p'); if (m) m.textContent = options.message; }
                        if (options.confirmText) { const c = document.getElementById(modalId + '-confirm-btn'); if (c) c.textContent = options.confirmText; }
                        if (options.cancelText) { const c = modal.querySelector('button[onclick*="confirmModalCancel"]'); if (c) c.textContent = options.cancelText; }

                        this.currentModal = modalId;
                        this.currentCallback = callback;
                        modal.classList.remove('hidden');
                        document.body.style.overflow = 'hidden';

                        const confirmBtn = document.getElementById(modalId + '-confirm-btn');
                        if (confirmBtn) {
                            confirmBtn.onclick = null;
                            confirmBtn.addEventListener('click', () => {
                                if (this.currentCallback) this.currentCallback();
                                this.hide(modalId);
                            }, { once: true });
                        }
                        return true;
                    },
                    hide(modalId) {
                        const modal = document.getElementById(modalId);
                        if (modal) modal.classList.add('hidden');
                        document.body.style.overflow = '';
                        this.currentModal = null; this.currentCallback = null;
                    }
                };

                window.confirmModalCancel = function (modalId) { if (window.confirmModal) window.confirmModal.hide(modalId); };
                document.addEventListener('keydown', function (e) {
                    if (e.key === 'Escape' && window.confirmModal && window.confirmModal.currentModal) {
                        window.confirmModal.hide(window.confirmModal.currentModal);
                    }
                });
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initConfirmModal);
            } else {
                initConfirmModal();
            }
        })();
    </script>

    <!-- Global Form Validation Error Handler -->
    <script src="{{ asset('js/form-validation-handler.js') }}"></script>
    <!-- Location Cascade Handler -->
    <script src="{{ asset('js/location-cascade.js') }}"></script>
    <x-toast />
    
    @stack('scripts')
</body>

</html>
