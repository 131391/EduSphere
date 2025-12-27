<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Receptionist Dashboard - ' . config('app.name'))</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    @stack('styles')
    
    <!-- Dark Mode Persistence -->
    <script>
        if (localStorage.getItem('darkMode') === 'true' || (!('darkMode' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
    @include('partials.sidebar-scripts')
</head>
<body class="bg-gray-100">
    @php
        $school = auth()->user()->school;
        $currentAcademicYear = $school ? \App\Models\AcademicYear::where('school_id', $school->id)->where('is_current', true)->first() : null;
    @endphp

    <div class="flex h-screen overflow-hidden" x-data="{ 
        sidebarOpen: false, 
        sidebarCollapsed: localStorage.getItem('sidebarCollapsed') === 'true',
        init() {
            document.documentElement.classList.remove('sidebar-collapsed');
            // Remove no-transition class after a small delay to allow initial paint
            setTimeout(() => {
                document.querySelector('aside').classList.remove('no-transition');
            }, 100);
        },
        toggleSidebar() {
            this.sidebarCollapsed = !this.sidebarCollapsed;
            localStorage.setItem('sidebarCollapsed', this.sidebarCollapsed);
        }
    }">
        <!-- Mobile Sidebar Overlay -->
        <div x-show="sidebarOpen" 
             @click="sidebarOpen = false"
             x-transition:enter="transition-opacity ease-linear duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-gray-600 bg-opacity-75 z-40 lg:hidden"
             style="display: none;"></div>

        <!-- Sidebar -->
        <aside class="fixed inset-y-0 left-0 z-50 bg-[#1a237e] text-white flex flex-col transform transition-all duration-300 ease-in-out lg:translate-x-0 lg:static lg:inset-0 no-transition"
               style="width: 16rem;"
               :style="sidebarCollapsed ? 'width: 5rem;' : 'width: 16rem;'"
               :class="{ 
                   '-translate-x-full': !sidebarOpen, 
                   'translate-x-0': sidebarOpen,
                   'sidebar-collapsed': sidebarCollapsed
               }">
            <!-- Logo Section -->
            <div class="p-4 border-b border-[#283593] flex-shrink-0 relative group">
                <div class="flex items-center justify-center mb-2">
                    <div class="bg-white rounded-full flex items-center justify-center transition-all duration-300 logo-container"
                         style="width: 4rem; height: 4rem;"
                         :style="sidebarCollapsed ? 'width: 2.5rem; height: 2.5rem;' : 'width: 4rem; height: 4rem;'">
                        @if($school && $school->logo)
                            <img src="{{ asset('storage/' . $school->logo) }}" alt="{{ $school->name }}" class="rounded-full object-cover logo-img"
                                 style="width: 4rem; height: 4rem;"
                                 :style="sidebarCollapsed ? 'width: 2.5rem; height: 2.5rem;' : 'width: 4rem; height: 4rem;'">
                        @else
                            <i class="fas fa-book text-[#1a237e]" :class="sidebarCollapsed ? 'text-lg' : 'text-2xl'"></i>
                        @endif
                    </div>
                </div>
                
                <div x-show="!sidebarCollapsed" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="sidebar-text">
                    <h2 class="text-xs font-bold text-center leading-tight">{{ strtoupper($school->name ?? 'SCHOOL NAME') }}</h2>
                    @if($school)
                        <p class="text-xs text-indigo-100 text-center mt-1">{{ $school->city ?? '' }}, {{ $school->state ?? '' }}</p>
                    @endif
                </div>

                <!-- Toggle Button -->
                <button @click="toggleSidebar()" class="absolute top-6 -right-4 w-8 h-8 flex items-center justify-center bg-teal-50 text-teal-600 rounded-full shadow-lg hover:bg-white hover:text-teal-700 transition-all duration-200 hidden lg:flex focus:outline-none z-50 border border-gray-200">
                    <i class="fas fa-chevron-left" x-show="!sidebarCollapsed"></i>
                    <i class="fas fa-chevron-right" x-show="sidebarCollapsed" style="display: none;"></i>
                </button>
            </div>

            <!-- Session Info -->
            <div class="px-4 py-2 bg-[#283593] text-xs flex-shrink-0 overflow-hidden whitespace-nowrap">
                <p class="font-semibold sidebar-text" x-show="!sidebarCollapsed">SESSION: {{ $currentAcademicYear?->name ?? '2025 - 2026' }}</p>
                <p class="font-semibold text-center" x-show="sidebarCollapsed" style="display: none;" :style="sidebarCollapsed ? 'display: block;' : 'display: none;'">{{ substr($currentAcademicYear?->name ?? '25-26', 2, 2) }}-{{ substr($currentAcademicYear?->name ?? '25-26', -2) }}</p>
            </div>

            <!-- Navigation Menu - Scrollable -->
            <!-- Navigation Menu - Scrollable -->
            @php
                $frontDeskOpen = request()->routeIs('receptionist.visitors.*') || request()->routeIs('receptionist.student-enquiries.*') || request()->routeIs('receptionist.student-registrations.*') || request()->routeIs('receptionist.admission.*');
                $transportOpen = request()->routeIs('receptionist.vehicles.*') || request()->routeIs('receptionist.routes.*') || request()->routeIs('receptionist.bus-stops.*');
                $staffOpen = request()->routeIs('receptionist.staff.*');
            @endphp
            <nav class="flex-1 overflow-y-auto py-4 sidebar-scroll" 
                 x-data="{
                    frontDeskOpen: {{ $frontDeskOpen ? 'true' : 'false' }},
                    transportOpen: {{ $transportOpen ? 'true' : 'false' }},
                    staffOpen: {{ $staffOpen ? 'true' : 'false' }}
                 }">
                <ul class="space-y-1 px-2">
                    <!-- Dashboard -->
                    <li>
                        <a href="{{ route('receptionist.dashboard') }}" 
                           class="flex items-center px-4 py-2 rounded-lg {{ request()->routeIs('receptionist.dashboard') ? 'bg-[#283593] text-white' : 'text-indigo-100 hover:bg-[#283593]' }}"
                           :class="{ 'justify-center': sidebarCollapsed }">
                            <i class="fas fa-tachometer-alt w-5" :class="{ 'mr-3': !sidebarCollapsed }"></i>
                            <span x-show="!sidebarCollapsed" class="sidebar-text">Dashboard</span>
                        </a>
                    </li>

                    <!-- Front Desk -->
                    <li>
                        <button @click="frontDeskOpen = !frontDeskOpen" 
                                class="w-full flex items-center justify-between px-4 py-2 rounded-lg text-indigo-100 hover:bg-[#283593]"
                                :class="{ 'justify-center': sidebarCollapsed }">
                            <div class="flex items-center">
                                <i class="fas fa-concierge-bell w-5" :class="{ 'mr-3': !sidebarCollapsed }"></i>
                                <span x-show="!sidebarCollapsed">Front Desk</span>
                            </div>
                            <i class="fas fa-chevron-down text-xs transition-transform" 
                               :class="{ 'rotate-180': frontDeskOpen }"
                               x-show="!sidebarCollapsed"></i>
                        </button>
                        <ul x-show="frontDeskOpen" x-collapse {{ $frontDeskOpen ? '' : 'x-cloak' }} class="ml-4 mt-1 space-y-1">
                            <li>
                                <a href="{{ route('receptionist.visitors.index') }}" class="flex items-center px-4 py-2 rounded-lg text-sm {{ request()->routeIs('receptionist.visitors.*') ? 'bg-[#283593] text-white' : 'text-indigo-100 hover:bg-[#283593]' }}">
                                    <i class="fas fa-user-friends w-4 mr-3"></i>
                                    <span>Visitor List</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('receptionist.student-enquiries.index') }}" class="flex items-center px-4 py-2 rounded-lg text-sm {{ request()->routeIs('receptionist.student-enquiries.*') ? 'bg-[#283593] text-white' : 'text-indigo-100 hover:bg-[#283593]' }}">
                                    <i class="fas fa-question-circle w-4 mr-3"></i>
                                    <span>Student Enquiry</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('receptionist.student-registrations.index') }}" class="flex items-center px-4 py-2 rounded-lg text-sm {{ request()->routeIs('receptionist.student-registrations.*') ? 'bg-[#283593] text-white' : 'text-indigo-100 hover:bg-[#283593]' }}">
                                    <i class="fas fa-clipboard-list w-4 mr-3"></i>
                                    <span>Student Registration</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('receptionist.admission.index') }}" class="flex items-center px-4 py-2 rounded-lg text-sm {{ request()->routeIs('receptionist.admission.*') ? 'bg-[#283593] text-white' : 'text-indigo-100 hover:bg-[#283593]' }}">
                                    <i class="fas fa-user-graduate w-4 mr-3"></i>
                                    <span>Admission List</span>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <!-- Transport Management -->
                    <li>
                        <button @click="transportOpen = !transportOpen" 
                                class="w-full flex items-center justify-between px-4 py-2 rounded-lg text-indigo-100 hover:bg-[#283593]"
                                :class="{ 'justify-center': sidebarCollapsed }">
                            <div class="flex items-center">
                                <i class="fas fa-bus w-5" :class="{ 'mr-3': !sidebarCollapsed }"></i>
                                <span x-show="!sidebarCollapsed">Transport Management</span>
                            </div>
                            <i class="fas fa-chevron-down text-xs transition-transform" 
                               :class="{ 'rotate-180': transportOpen }"
                               x-show="!sidebarCollapsed"></i>
                        </button>
                        <ul x-show="transportOpen" x-collapse {{ $transportOpen ? '' : 'x-cloak' }} class="ml-4 mt-1 space-y-1">
                            <li>
                                <a href="{{ route('receptionist.routes.index') }}" class="flex items-center px-4 py-2 rounded-lg text-sm {{ request()->routeIs('receptionist.routes.*') ? 'bg-[#283593] text-white' : 'text-indigo-100 hover:bg-[#283593]' }}">
                                    <i class="fas fa-route w-4 mr-3"></i>
                                    <span>Routes</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('receptionist.vehicles.index') }}" class="flex items-center px-4 py-2 rounded-lg text-sm {{ request()->routeIs('receptionist.vehicles.*') ? 'bg-[#283593] text-white' : 'text-indigo-100 hover:bg-[#283593]' }}">
                                    <i class="fas fa-bus-alt w-4 mr-3"></i>
                                    <span>Vehicles</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('receptionist.bus-stops.index') }}" class="flex items-center px-4 py-2 rounded-lg text-sm {{ request()->routeIs('receptionist.bus-stops.*') ? 'bg-[#283593] text-white' : 'text-indigo-100 hover:bg-[#283593]' }}">
                                    <i class="fas fa-map-marker-alt w-4 mr-3"></i>
                                    <span>Bus Stops</span>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <!-- Staff -->
                    <li>
                        <button @click="staffOpen = !staffOpen" 
                                class="w-full flex items-center justify-between px-4 py-2 rounded-lg text-indigo-100 hover:bg-[#283593]"
                                :class="{ 'justify-center': sidebarCollapsed }">
                            <div class="flex items-center">
                                <i class="fas fa-users w-5" :class="{ 'mr-3': !sidebarCollapsed }"></i>
                                <span x-show="!sidebarCollapsed">Staff</span>
                            </div>
                            <i class="fas fa-chevron-down text-xs transition-transform" 
                               :class="{ 'rotate-180': staffOpen }"
                               x-show="!sidebarCollapsed"></i>
                        </button>
                        <ul x-show="staffOpen" x-collapse {{ $staffOpen ? '' : 'x-cloak' }} class="ml-4 mt-1 space-y-1">
                            <li>
                                <a href="{{ route('receptionist.staff.index') }}" class="flex items-center px-4 py-2 rounded-lg text-sm {{ request()->routeIs('receptionist.staff.*') ? 'bg-[#283593] text-white' : 'text-indigo-100 hover:bg-[#283593]' }}">
                                    <i class="fas fa-list w-4 mr-3"></i>
                                    <span>Staff List</span>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <!-- Logout -->
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full flex items-center px-4 py-2 rounded-lg text-indigo-100 hover:bg-[#283593] text-left"
                                    :class="{ 'justify-center': sidebarCollapsed }">
                                <i class="fas fa-sign-out-alt w-5" :class="{ 'mr-3': !sidebarCollapsed }"></i>
                                <span x-show="!sidebarCollapsed">LogOut</span>
                            </button>
                        </form>
                    </li>
                </ul>
            </nav>

            <!-- Footer -->
            <div class="p-4 border-t border-[#283593] text-xs text-indigo-100 text-center">
                <p>{{ date('Y') }} ©</p>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Header -->
            <header class="bg-white shadow-sm border-b border-gray-200">
                <div class="flex items-center justify-between px-4 sm:px-6 py-4">
                    <!-- Left: Menu & Search -->
                    <div class="flex items-center space-x-3 sm:space-x-4 flex-1">
                        <button @click="sidebarOpen = !sidebarOpen" class="text-gray-500 hover:text-gray-700 lg:hidden focus:outline-none">
                            <i class="fas fa-bars text-xl sm:text-2xl"></i>
                        </button>
                        <div class="relative flex-1 max-w-md">
                            <input type="text" placeholder="Search..." class="w-full pl-8 sm:pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                            <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-sm"></i>
                        </div>
                    </div>

                    <!-- Right: Actions & User -->
                    <div class="flex items-center space-x-2 sm:space-x-4" x-data="headerActions">
                        <!-- Star (Favorite) -->
                        <button 
                            @click="toggleFavorite()" 
                            class="text-gray-500 hover:text-gray-700 transition-colors hidden sm:block"
                            :class="isFavorite ? 'text-yellow-500 hover:text-yellow-600' : 'text-gray-500 hover:text-gray-700'"
                            title="Add to Favorites"
                        >
                            <i class="text-xl far fa-star" :class="isFavorite ? 'fas fa-star text-xl' : 'far fa-star text-xl'"></i>
                        </button>

                        <!-- Bookmark (Saved List) -->
                        <div class="relative hidden md:block">
                            <button 
                                @click="showFavorites = !showFavorites" 
                                class="text-gray-500 hover:text-gray-700 transition-colors"
                                title="Saved Pages"
                            >
                                <i class="far fa-bookmark text-xl"></i>
                            </button>
                            
                            <!-- Favorites Dropdown -->
                            <div 
                                x-show="showFavorites" 
                                @click.away="showFavorites = false"
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 scale-95"
                                x-transition:enter-end="opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-150"
                                x-transition:leave-start="opacity-100 scale-100"
                                x-transition:leave-end="opacity-0 scale-95"
                                class="absolute right-0 mt-2 w-64 bg-white rounded-lg shadow-lg border border-gray-200 py-2 z-50"
                                x-cloak
                            >
                                <div class="px-4 py-2 border-b border-gray-100">
                                    <h3 class="text-sm font-semibold text-gray-700">Saved Pages</h3>
                                </div>
                                <div class="max-h-64 overflow-y-auto">
                                    <template x-if="favorites.length === 0">
                                        <div class="px-4 py-4 text-center text-gray-500 text-sm">
                                            No saved pages yet.
                                        </div>
                                    </template>
                                    <template x-for="fav in favorites" :key="fav.id">
                                        <div class="group flex items-center justify-between px-4 py-2 hover:bg-gray-50">
                                            <a :href="fav.url" class="text-sm text-gray-700 hover:text-blue-600 truncate flex-1" x-text="fav.title"></a>
                                            <button @click="removeFavorite(fav.id)" class="ml-2 text-gray-400 hover:text-red-500 opacity-0 group-hover:opacity-100 transition-opacity">
                                                <i class="fas fa-times text-xs"></i>
                                            </button>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <!-- Fullscreen -->
                        <button 
                            @click="toggleFullscreen()" 
                            class="text-gray-500 hover:text-gray-700 transition-colors hidden md:block"
                            title="Toggle Fullscreen"
                        >
                            <i class="fas fa-expand text-xl" :class="isFullscreen ? 'fa-compress' : 'fa-expand'"></i>
                        </button>

                        <!-- Dark Mode -->
                        <button 
                            @click="toggleDarkMode()" 
                            class="text-gray-500 hover:text-gray-700 transition-colors hidden sm:block"
                            title="Toggle Dark Mode"
                        >
                            <i class="far fa-moon text-xl" :class="isDark ? 'fa-sun' : 'fa-moon'"></i>
                        </button>
                        
                        <!-- User Dropdown -->
                        <div class="relative" x-data="{ open: false }">
                            <button 
                                @click="open = !open"
                                class="flex items-center space-x-1 sm:space-x-2 focus:outline-none"
                            >
                                <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center text-white">
                                    <i class="fas fa-user text-sm"></i>
                                </div>
                                <span class="text-gray-700 font-medium hidden sm:inline text-sm">{{ Auth::user()->name ?? 'Receptionist' }}</span>
                                <i class="fas fa-chevron-down text-gray-500 text-xs hidden sm:inline"></i>
                            </button>
                            
                            <!-- Dropdown Menu -->
                            <div 
                                x-show="open" 
                                x-cloak
                                @click.away="open = false"
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 scale-95"
                                x-transition:enter-end="opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-150"
                                x-transition:leave-start="opacity-100 scale-100"
                                x-transition:leave-end="opacity-0 scale-95"
                                class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-50"
                            >
                                <a 
                                    href="#" 
                                    class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center"
                                >
                                    <i class="fas fa-user-circle mr-3 text-gray-500"></i>
                                    Profile
                                </a>
                                <div class="border-t border-gray-200 my-1"></div>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button 
                                        type="submit"
                                        class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 flex items-center"
                                    >
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
            <main class="flex-1 overflow-y-auto bg-gray-50 p-4 sm:p-6">
                @yield('content')
            </main>
        </div>
    </div>


    <!-- jQuery (required for Select2) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    
    <!-- Global Select2 Initialization -->
    <script>
        // Track initialized selects to prevent double initialization
        window.select2Initialized = window.select2Initialized || new Set();
        
        // Helper function to safely initialize Select2
        function initSelect2($select) {
            // Skip if already initialized or should be excluded
            if ($select.hasClass('select2-hidden-accessible') || 
                $select.hasClass('no-select2') || 
                $select.is('[data-table-select]')) {
                return false;
            }
            
            // Check if this select has already been processed
            const selectId = $select.attr('id') || $select.attr('name') || $select[0].outerHTML;
            if (window.select2Initialized.has(selectId)) {
                return false;
            }
            
            // Mark as initialized before actually initializing (prevents race conditions)
            window.select2Initialized.add(selectId);
            
            // Initialize Select2
            try {
                $select.select2({
                    placeholder: function() {
                        return $(this).data('placeholder') || 'Select an option';
                    },
                    allowClear: $(this).data('allow-clear') !== undefined ? $(this).data('allow-clear') : false,
                    width: '100%'
                });
                return true;
            } catch (e) {
                // If initialization fails, remove from set so it can be retried
                window.select2Initialized.delete(selectId);
                console.warn('Select2 initialization failed:', e);
                return false;
            }
        }
        
        $(document).ready(function() {
            // Initialize Select2 immediately on load
            $('select').each(function() {
                initSelect2($(this));
            });
            
            // Debounce function to prevent multiple rapid initializations
            let initTimeout;
            
            function debouncedInitSelect2($selects) {
                clearTimeout(initTimeout);
                initTimeout = setTimeout(function() {
                    $selects.each(function() {
                        initSelect2($(this));
                    });
                }, 50); // Reduced delay for snappier UI
            }
            
            // Re-initialize Select2 when new content is loaded dynamically
            const observer = new MutationObserver(function(mutations) {
                let newSelects = [];
                mutations.forEach(function(mutation) {
                    if (mutation.addedNodes.length) {
                        $(mutation.addedNodes).find('select').each(function() {
                            const $select = $(this);
                            // Skip if already initialized, excluded, or inside x-cloak
                            if (!$select.hasClass('select2-hidden-accessible') && 
                                !$select.hasClass('no-select2') && 
                                !$select.is('[data-table-select]') &&
                                $select.closest('[x-cloak]').length === 0) {
                                newSelects.push(this);
                            }
                        });
                    }
                });
                
                if (newSelects.length > 0) {
                    debouncedInitSelect2($(newSelects));
                }
            });
            
            observer.observe(document.body, {
                childList: true,
                subtree: true
            });
        });
    </script>

    <script>
        // Listen for fullscreen changes (e.g., when user presses ESC)
        document.addEventListener('fullscreenchange', function() {
            // This will be handled by Alpine.js state in the header
            const event = new CustomEvent('fullscreen-changed', { 
                detail: { isFullscreen: !!document.fullscreenElement } 
            });
            document.dispatchEvent(event);
        });
    </script>

    @stack('scripts')
    
    <x-delete-confirmation />
    
    <!-- Alpine.js Plugins -->
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Alpine.js with defer - standard approach that ensures DOM ready and proper initialization order -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('headerActions', () => ({
                isFullscreen: false,
                isDark: localStorage.getItem('darkMode') === 'true',
                isFavorite: false,
                favorites: [],
                showFavorites: false,
                
                init() {
                    this.checkFavorite();
                    this.loadFavorites();
                    
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
                },
                
                toggleFavorite() {
                    const currentUrl = window.location.href;
                    const currentTitle = document.title;
                    
                    if (this.isFavorite) {
                        // Remove from favorites
                        this.favorites = this.favorites.filter(f => f.url !== currentUrl);
                        this.isFavorite = false;
                    } else {
                        // Add to favorites
                        this.favorites.push({
                            id: Date.now(),
                            title: currentTitle,
                            url: currentUrl
                        });
                        this.isFavorite = true;
                    }
                    
                    // Save to localStorage
                    localStorage.setItem('receptionist_favorites', JSON.stringify(this.favorites));
                },
                
                checkFavorite() {
                    const currentUrl = window.location.href;
                    this.isFavorite = this.favorites.some(f => f.url === currentUrl);
                },
                
                loadFavorites() {
                    try {
                        const stored = localStorage.getItem('receptionist_favorites');
                        this.favorites = stored ? JSON.parse(stored) : [];
                    } catch (error) {
                        console.error('Error loading favorites:', error);
                        this.favorites = [];
                    }
                },
                
                removeFavorite(id) {
                    this.favorites = this.favorites.filter(f => f.id !== id);
                    localStorage.setItem('receptionist_favorites', JSON.stringify(this.favorites));
                    
                    // If we removed the current page, update the star icon
                    const currentUrl = window.location.href;
                    this.isFavorite = this.favorites.some(f => f.url === currentUrl);
                }
            }));
        });
    </script>
</body>
</html>
