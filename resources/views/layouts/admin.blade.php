<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Dashboard - ' . config('app.name'))</title>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    @stack('styles')
    
    @include('partials.sidebar-scripts')
</head>
<body class="bg-gray-100 h-screen overflow-hidden dark:bg-gray-900 transition-colors">
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
        <aside class="fixed inset-y-0 left-0 z-50 bg-blue-900 text-white flex flex-col transform transition-all duration-300 ease-in-out lg:translate-x-0 lg:static lg:inset-0 no-transition"
               style="width: 16rem;"
               :style="sidebarCollapsed ? 'width: 5rem;' : 'width: 16rem;'"
               :class="{ 
                   '-translate-x-full': !sidebarOpen, 
                   'translate-x-0': sidebarOpen,
                   'sidebar-collapsed': sidebarCollapsed
               }">
            <!-- Logo Section -->
            <div class="p-4 border-b border-blue-800 relative group">
                <div class="flex items-center justify-center mb-2">
                    <div class="bg-white rounded-full flex items-center justify-center transition-all duration-300 logo-container"
                         style="width: 4rem; height: 4rem;"
                         :style="sidebarCollapsed ? 'width: 2.5rem; height: 2.5rem;' : 'width: 4rem; height: 4rem;'">
                        <i class="fas fa-book text-blue-900 logo-img" :class="sidebarCollapsed ? 'text-lg' : 'text-2xl'"></i>
                    </div>
                </div>
                
                <div x-show="!sidebarCollapsed" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="sidebar-text">
                    <h2 class="text-sm font-bold text-center">EDUSPHERE</h2>
                    <p class="text-xs text-blue-200 text-center mt-1">School ERP System</p>
                </div>

                <!-- Toggle Button -->
                <button @click="toggleSidebar()" class="absolute top-6 -right-4 w-8 h-8 flex items-center justify-center bg-teal-50 text-teal-600 rounded-full shadow-lg hover:bg-white hover:text-teal-700 transition-all duration-200 hidden lg:flex focus:outline-none z-50 border border-gray-200">
                    <i class="fas fa-chevron-left" x-show="!sidebarCollapsed"></i>
                    <i class="fas fa-chevron-right" x-show="sidebarCollapsed" style="display: none;"></i>
                </button>
            </div>

            <!-- Session Info -->
            <div class="px-4 py-2 bg-blue-800 text-xs overflow-hidden whitespace-nowrap">
                <p class="font-semibold sidebar-text" x-show="!sidebarCollapsed">SESSION: {{ \App\Models\AcademicYear::where('is_current', true)->first()?->name ?? '2025 - 2026' }}</p>
                <p class="font-semibold text-center" x-show="sidebarCollapsed" style="display: none;" :style="sidebarCollapsed ? 'display: block;' : 'display: none;'">{{ substr(\App\Models\AcademicYear::where('is_current', true)->first()?->name ?? '25-26', 2, 2) }}-{{ substr(\App\Models\AcademicYear::where('is_current', true)->first()?->name ?? '25-26', -2) }}</p>
            </div>

            <!-- Navigation Menu -->
            <nav class="flex-1 overflow-y-auto py-4 sidebar-scroll" 
                 x-data="{ 
                    openMenus: {
                        examination: false,
                        setting: {{ request()->routeIs('admin.settings.*') ? 'true' : 'false' }}
                    }
                 }">
                <ul class="space-y-1 px-2">
                    <li>
                        <a href="{{ route('admin.dashboard') }}" 
                           class="flex items-center px-4 py-2 rounded-lg {{ request()->routeIs('admin.dashboard') ? 'bg-blue-800 text-white' : 'text-blue-200 hover:bg-blue-800' }}"
                           :class="{ 'justify-center': sidebarCollapsed }">
                            <i class="fas fa-tachometer-alt w-5" :class="{ 'mr-3': !sidebarCollapsed }"></i>
                            <span x-show="!sidebarCollapsed" class="sidebar-text">Dashboards</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.schools.index') }}" 
                           class="flex items-center px-4 py-2 rounded-lg {{ request()->routeIs('admin.schools.*') ? 'bg-blue-800 text-white' : 'text-blue-200 hover:bg-blue-800' }}"
                           :class="{ 'justify-center': sidebarCollapsed }">
                            <i class="fas fa-school w-5" :class="{ 'mr-3': !sidebarCollapsed }"></i>
                            <span x-show="!sidebarCollapsed">Schools</span>
                        </a>
                    </li>
                    

                    <!-- Change Password -->
                    <li>
                        <a href="{{ route('admin.change-password') }}" 
                           class="flex items-center px-4 py-2 rounded-lg {{ request()->routeIs('admin.change-password') ? 'bg-blue-800 text-white' : 'text-blue-200 hover:bg-blue-800' }}"
                           :class="{ 'justify-center': sidebarCollapsed }">
                            <i class="fas fa-key w-5" :class="{ 'mr-3': !sidebarCollapsed }"></i>
                            <span x-show="!sidebarCollapsed">Change Password</span>
                        </a>
                    </li>

                    <!-- LogOut -->
                    <li>
                        <form method="POST" action="{{ route('logout') }}" class="w-full">
                            @csrf
                            <button type="submit" class="w-full flex items-center px-4 py-2 rounded-lg text-blue-200 hover:bg-blue-800"
                                    :class="{ 'justify-center': sidebarCollapsed }">
                                <i class="fas fa-sign-out-alt w-5" :class="{ 'mr-3': !sidebarCollapsed }"></i>
                                <span x-show="!sidebarCollapsed">LogOut</span>
                            </button>
                        </form>
                    </li>
                </ul>
            </nav>

            <!-- Footer -->
            <div class="p-4 border-t border-blue-800 text-xs text-blue-200 text-center">
                <p>{{ date('Y') }} © EduSphere</p>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Header -->
            <header class="bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700 transition-colors">
                <div class="flex items-center justify-between px-4 sm:px-6 py-4">
                    <!-- Left: Menu & Search -->
                    <div class="flex items-center space-x-3 sm:space-x-4 flex-1">
                        <button @click="sidebarOpen = !sidebarOpen" class="text-gray-500 hover:text-gray-700 lg:hidden focus:outline-none">
                            <i class="fas fa-bars text-xl sm:text-2xl"></i>
                        </button>
                        <div class="relative flex-1 max-w-md">
                            <input type="text" placeholder="Search..." class="w-full pl-8 sm:pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                            <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 dark:text-gray-500 text-sm"></i>
                        </div>
                    </div>

                    <!-- Right: Actions & User -->
                    <div class="flex items-center space-x-2 sm:space-x-4" x-data="headerActions()">
                        <!-- Star/Favorite Button -->
                        <button 
                            @click="toggleFavorite()" 
                            class="text-gray-500 hover:text-gray-700 transition-colors hidden sm:block"
                            :class="{ 'text-yellow-500': isFavorited }"
                            title="Add to favorites"
                        >
                            <i class="text-xl far fa-star" :class="isFavorited ? 'fas fa-star' : 'far fa-star'"></i>
                        </button>
                        
                        <!-- Bookmark Button -->
                        <button 
                            @click="toggleBookmark()" 
                            class="text-gray-500 hover:text-gray-700 transition-colors hidden sm:block"
                            :class="{ 'text-blue-500': isBookmarked }"
                            title="Bookmark this page"
                        >
                            <i class="text-xl far fa-bookmark" :class="isBookmarked ? 'fas fa-bookmark' : 'far fa-bookmark'"></i>
                        </button>
                        
                        <!-- Fullscreen Toggle -->
                        <button 
                            @click="toggleFullscreen()" 
                            class="text-gray-500 hover:text-gray-700 transition-colors hidden md:block"
                            title="Toggle fullscreen"
                        >
                            <i class="text-xl fas fa-expand" :class="isFullscreen ? 'fas fa-compress' : 'fas fa-expand'"></i>
                        </button>
                        
                        <!-- Dark Mode Toggle -->
                        <button 
                            @click="toggleDarkMode()" 
                            class="text-gray-500 hover:text-gray-700 transition-colors"
                            :class="{ 'text-yellow-400': isDarkMode }"
                            title="Toggle dark mode"
                        >
                            <i class="text-xl far fa-moon" :class="isDarkMode ? 'fas fa-sun' : 'far fa-moon'"></i>
                        </button>
                        
                        <!-- User Dropdown -->
                        <div class="relative" x-data="{ open: false }" @click.away="open = false">
                            <button @click="open = !open" class="flex items-center space-x-1 sm:space-x-2 focus:outline-none">
                                <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center text-white">
                                    <i class="fas fa-user text-sm"></i>
                                </div>
                                <span class="text-gray-700 dark:text-gray-200 font-medium hidden sm:inline text-sm">{{ Auth::user()->name ?? 'Admin' }}</span>
                                <i class="fas fa-chevron-down text-xs text-gray-500 transition-transform hidden sm:inline" :class="{ 'rotate-180': open }"></i>
                            </button>
                            
                            <!-- Dropdown Menu -->
                            <div x-show="open" 
                                 x-cloak
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="transform opacity-0 scale-95"
                                 x-transition:enter-end="transform opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="transform opacity-100 scale-100"
                                 x-transition:leave-end="transform opacity-0 scale-95"
                                 class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 py-1 z-50"
                                 style="display: none;">
                                <a href="{{ route('admin.profile') }}" class="flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">
                                    <i class="fas fa-user-circle w-4 mr-3 text-gray-400 dark:text-gray-500"></i>
                                    <span>Profile</span>
                                </a>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full flex items-center px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20">
                                        <i class="fas fa-sign-out-alt w-4 mr-3"></i>
                                        <span>Logout</span>
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

    @stack('scripts')
    
    <!-- Global Confirmation Modal Handler -->
    <script>
        (function() {
            // Initialize confirmModal
            function initConfirmModal() {
                if (typeof window.confirmModal !== 'undefined') {
                    return; // Already initialized
                }
                
                window.confirmModal = {
                    currentModal: null,
                    currentCallback: null,
                    
                    show(modalId, callback, options = {}) {
                        const modal = document.getElementById(modalId);
                        if (!modal) {
                            console.error('Modal not found:', modalId);
                            return false;
                        }
                        
                        // Update modal content if options provided
                        if (options.title) {
                            const titleEl = modal.querySelector('h3');
                            if (titleEl) titleEl.textContent = options.title;
                        }
                        if (options.message) {
                            const messageEl = modal.querySelector('p');
                            if (messageEl) messageEl.textContent = options.message;
                        }
                        if (options.confirmText) {
                            const confirmBtn = document.getElementById(modalId + '-confirm-btn');
                            if (confirmBtn) confirmBtn.textContent = options.confirmText;
                        }
                        if (options.cancelText) {
                            const cancelBtn = modal.querySelector('button[onclick*="confirmModalCancel"]');
                            if (cancelBtn) cancelBtn.textContent = options.cancelText;
                        }
                        
                        this.currentModal = modalId;
                        this.currentCallback = callback;
                        
                        modal.classList.remove('hidden');
                        document.body.style.overflow = 'hidden';
                        
                        // Set up confirm button handler
                        const confirmBtn = document.getElementById(modalId + '-confirm-btn');
                        if (confirmBtn) {
                            // Remove existing onclick and add new one
                            confirmBtn.onclick = null;
                            confirmBtn.addEventListener('click', () => {
                                if (this.currentCallback) {
                                    this.currentCallback();
                                }
                                this.hide(modalId);
                            }, { once: true });
                        }
                        
                        return true;
                    },
                    
                    hide(modalId) {
                        const modal = document.getElementById(modalId);
                        if (modal) {
                            modal.classList.add('hidden');
                        }
                        document.body.style.overflow = '';
                        this.currentModal = null;
                        this.currentCallback = null;
                    }
                };
                
                window.confirmModalCancel = function(modalId) {
                    if (window.confirmModal) {
                        window.confirmModal.hide(modalId);
                    }
                };
                
                // Close modal on Escape key
                document.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape' && window.confirmModal && window.confirmModal.currentModal) {
                        window.confirmModal.hide(window.confirmModal.currentModal);
                    }
                });
            }
            
            // Initialize when DOM is ready
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initConfirmModal);
            } else {
                // DOM already loaded
                initConfirmModal();
            }
        })();
    </script>
    
    <script>
        // Header Actions Functionality
        document.addEventListener('alpine:init', () => {
            Alpine.data('headerActions', () => ({
                isFavorited: localStorage.getItem('favorited_' + window.location.pathname) === 'true',
                isBookmarked: localStorage.getItem('bookmarked_' + window.location.pathname) === 'true',
                isFullscreen: false,
                isDarkMode: localStorage.getItem('darkMode') === 'true' || false,
                
                init() {
                    // Apply dark mode on load
                    if (this.isDarkMode) {
                        document.documentElement.classList.add('dark');
                    }
                    
                    // Check fullscreen status
                    this.checkFullscreen();
                    
                    // Listen for fullscreen changes
                    document.addEventListener('fullscreenchange', () => this.checkFullscreen());
                    document.addEventListener('webkitfullscreenchange', () => this.checkFullscreen());
                    document.addEventListener('mozfullscreenchange', () => this.checkFullscreen());
                    document.addEventListener('MSFullscreenChange', () => this.checkFullscreen());
                },
                
                toggleFavorite() {
                    this.isFavorited = !this.isFavorited;
                    localStorage.setItem('favorited_' + window.location.pathname, this.isFavorited);
                    this.showNotification(
                        this.isFavorited ? 'Added to favorites' : 'Removed from favorites',
                        this.isFavorited ? 'success' : 'info'
                    );
                },
                
                toggleBookmark() {
                    this.isBookmarked = !this.isBookmarked;
                    localStorage.setItem('bookmarked_' + window.location.pathname, this.isBookmarked);
                    this.showNotification(
                        this.isBookmarked ? 'Page bookmarked' : 'Bookmark removed',
                        this.isBookmarked ? 'success' : 'info'
                    );
                },
                
                toggleFullscreen() {
                    if (!this.isFullscreen) {
                        if (document.documentElement.requestFullscreen) {
                            document.documentElement.requestFullscreen();
                        } else if (document.documentElement.webkitRequestFullscreen) {
                            document.documentElement.webkitRequestFullscreen();
                        } else if (document.documentElement.mozRequestFullScreen) {
                            document.documentElement.mozRequestFullScreen();
                        } else if (document.documentElement.msRequestFullscreen) {
                            document.documentElement.msRequestFullscreen();
                        }
                    } else {
                        if (document.exitFullscreen) {
                            document.exitFullscreen();
                        } else if (document.webkitExitFullscreen) {
                            document.webkitExitFullscreen();
                        } else if (document.mozCancelFullScreen) {
                            document.mozCancelFullScreen();
                        } else if (document.msExitFullscreen) {
                            document.msExitFullscreen();
                        }
                    }
                },
                
                checkFullscreen() {
                    this.isFullscreen = !!(
                        document.fullscreenElement ||
                        document.webkitFullscreenElement ||
                        document.mozFullScreenElement ||
                        document.msFullscreenElement
                    );
                },
                
                toggleDarkMode() {
                    this.isDarkMode = !this.isDarkMode;
                    localStorage.setItem('darkMode', this.isDarkMode);
                    
                    if (this.isDarkMode) {
                        document.documentElement.classList.add('dark');
                    } else {
                        document.documentElement.classList.remove('dark');
                    }
                    
                    this.showNotification(
                        this.isDarkMode ? 'Dark mode enabled' : 'Light mode enabled',
                        'success'
                    );
                },
                
                showNotification(message, type = 'info') {
                    const notification = document.createElement('div');
                    notification.className = `fixed top-4 right-4 px-4 py-3 rounded-lg shadow-lg z-50 transition-all ${
                        type === 'success' ? 'bg-green-500 text-white' : 
                        type === 'info' ? 'bg-blue-500 text-white' : 
                        'bg-gray-800 text-white'
                    }`;
                    notification.textContent = message;
                    
                    document.body.appendChild(notification);
                    
                    setTimeout(() => {
                        notification.classList.add('opacity-0', 'translate-y-2');
                        setTimeout(() => {
                            if (document.body.contains(notification)) {
                                document.body.removeChild(notification);
                            }
                        }, 300);
                    }, 3000);
                }
            }))
        });
    </script>
    <!-- Select2 Initialization -->
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
</body>
</html>

