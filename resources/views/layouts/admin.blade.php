<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Dashboard - ' . config('app.name'))</title>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    @stack('styles')
    
    <style>
        /* Hide elements until Alpine.js is ready */
        [x-cloak] {
            display: none !important;
        }
        
        /* Prevent flash of unstyled content - hide collapsed menus initially */
        [x-cloak] {
            display: none !important;
        }
        /* Custom Scrollbar for Sidebar - Beautiful UX */
        .sidebar-scroll {
            scrollbar-width: thin;
            scrollbar-color: transparent transparent;
        }
        
        .sidebar-scroll:hover {
            scrollbar-color: rgba(200, 200, 200, 0.3) rgba(200, 200, 200, 0.1);
        }
        
        /* Webkit browsers (Chrome, Safari, Edge) */
        .sidebar-scroll::-webkit-scrollbar {
            width: 10px;
        }
        
        .sidebar-scroll::-webkit-scrollbar-track {
            background: transparent;
            border-radius: 5px;
        }
        
        .sidebar-scroll:hover::-webkit-scrollbar-track {
            background: rgba(200, 200, 200, 0.1);
        }
        
        .sidebar-scroll::-webkit-scrollbar-thumb {
            background: transparent;
            border-radius: 5px;
            transition: all 0.3s ease;
        }
        
        .sidebar-scroll:hover::-webkit-scrollbar-thumb {
            background: rgba(150, 150, 150, 0.4);
        }
        
        .sidebar-scroll:hover::-webkit-scrollbar-thumb:hover {
            background: rgba(150, 150, 150, 0.6);
        }
        
        .sidebar-scroll:hover::-webkit-scrollbar-thumb:active {
            background: rgba(150, 150, 150, 0.8);
        }
        
        /* Scrollbar buttons with arrow icons */
        .sidebar-scroll::-webkit-scrollbar-button {
            display: block;
            height: 16px;
            width: 10px;
            background: transparent;
        }
        
        .sidebar-scroll:hover::-webkit-scrollbar-button {
            background: rgba(200, 200, 200, 0.1);
        }
        
        .sidebar-scroll::-webkit-scrollbar-button:single-button:vertical:decrement {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8' viewBox='0 0 8 8'%3E%3Cpath fill='%23cccccc' d='M4 0l4 4H0z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: center;
            background-size: 6px;
        }
        
        .sidebar-scroll:hover::-webkit-scrollbar-button:single-button:vertical:decrement {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8' viewBox='0 0 8 8'%3E%3Cpath fill='%23999999' d='M4 0l4 4H0z'/%3E%3C/svg%3E");
        }
        
        .sidebar-scroll::-webkit-scrollbar-button:single-button:vertical:increment {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8' viewBox='0 0 8 8'%3E%3Cpath fill='%23cccccc' d='M4 8L0 4h8z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: center;
            background-size: 6px;
        }
        
        .sidebar-scroll:hover::-webkit-scrollbar-button:single-button:vertical:increment {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8' viewBox='0 0 8 8'%3E%3Cpath fill='%23999999' d='M4 8L0 4h8z'/%3E%3C/svg%3E");
        }
        
        /* Smooth scrolling */
        .sidebar-scroll {
            scroll-behavior: smooth;
            -webkit-overflow-scrolling: touch;
        }
    </style>
</head>
<body class="bg-gray-100 dark:bg-gray-900 transition-colors">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <aside class="w-64 bg-blue-900 text-white flex flex-col">
            <!-- Logo Section -->
            <div class="p-4 border-b border-blue-800">
                <div class="flex items-center justify-center mb-2">
                    <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center">
                        <i class="fas fa-book text-blue-900 text-2xl"></i>
                    </div>
                </div>
                <h2 class="text-sm font-bold text-center">EDUSPHERE</h2>
                <p class="text-xs text-blue-200 text-center mt-1">School ERP System</p>
            </div>

            <!-- Session Info -->
            <div class="px-4 py-2 bg-blue-800 text-xs">
                <p class="font-semibold">SESSION: {{ \App\Models\AcademicYear::where('is_current', true)->first()?->name ?? '2025 - 2026' }}</p>
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
                           class="flex items-center px-4 py-2 rounded-lg {{ request()->routeIs('admin.dashboard') ? 'bg-blue-800 text-white' : 'text-blue-200 hover:bg-blue-800' }}">
                            <i class="fas fa-tachometer-alt w-5 mr-3"></i>
                            <span>Dashboards</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.schools.index') }}" 
                           class="flex items-center px-4 py-2 rounded-lg {{ request()->routeIs('admin.schools.*') ? 'bg-blue-800 text-white' : 'text-blue-200 hover:bg-blue-800' }}">
                            <i class="fas fa-school w-5 mr-3"></i>
                            <span>Schools</span>
                        </a>
                    </li>
                    


                    <!-- Change Password -->
                    <li>
                        <a href="{{ route('admin.change-password') }}" 
                           class="flex items-center px-4 py-2 rounded-lg {{ request()->routeIs('admin.change-password') ? 'bg-blue-800 text-white' : 'text-blue-200 hover:bg-blue-800' }}">
                            <i class="fas fa-key w-5 mr-3"></i>
                            <span>Change Password</span>
                        </a>
                    </li>

                    <!-- LogOut -->
                    <li>
                        <form method="POST" action="{{ route('logout') }}" class="w-full">
                            @csrf
                            <button type="submit" class="w-full flex items-center px-4 py-2 rounded-lg text-blue-200 hover:bg-blue-800">
                                <i class="fas fa-sign-out-alt w-5 mr-3"></i>
                                <span>LogOut</span>
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
                <div class="flex items-center justify-between px-6 py-4">
                    <!-- Left: Menu & Search -->
                    <div class="flex items-center space-x-4 flex-1">
                        <button class="text-gray-500 hover:text-gray-700 lg:hidden">
                            <i class="fas fa-bars text-xl"></i>
                        </button>
                        <div class="relative flex-1 max-w-md">
                            <input type="text" placeholder="Search..." class="w-full pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 dark:text-gray-500"></i>
                        </div>
                    </div>

                    <!-- Right: Actions & User -->
                    <div class="flex items-center space-x-4" x-data="headerActions()">
                        <!-- Star/Favorite Button -->
                        <button 
                            @click="toggleFavorite()" 
                            class="text-gray-500 hover:text-gray-700 transition-colors"
                            :class="{ 'text-yellow-500': isFavorited }"
                            title="Add to favorites"
                        >
                            <i class="text-xl" :class="isFavorited ? 'fas fa-star' : 'far fa-star'"></i>
                        </button>
                        
                        <!-- Bookmark Button -->
                        <button 
                            @click="toggleBookmark()" 
                            class="text-gray-500 hover:text-gray-700 transition-colors"
                            :class="{ 'text-blue-500': isBookmarked }"
                            title="Bookmark this page"
                        >
                            <i class="text-xl" :class="isBookmarked ? 'fas fa-bookmark' : 'far fa-bookmark'"></i>
                        </button>
                        
                        <!-- Fullscreen Toggle -->
                        <button 
                            @click="toggleFullscreen()" 
                            class="text-gray-500 hover:text-gray-700 transition-colors"
                            title="Toggle fullscreen"
                        >
                            <i class="text-xl" :class="isFullscreen ? 'fas fa-compress' : 'fas fa-expand'"></i>
                        </button>
                        
                        <!-- Dark Mode Toggle -->
                        <button 
                            @click="toggleDarkMode()" 
                            class="text-gray-500 hover:text-gray-700 transition-colors"
                            :class="{ 'text-yellow-400': isDarkMode }"
                            title="Toggle dark mode"
                        >
                            <i class="text-xl" :class="isDarkMode ? 'fas fa-sun' : 'far fa-moon'"></i>
                        </button>
                        
                        <!-- User Dropdown -->
                        <div class="relative" x-data="{ open: false }" @click.away="open = false">
                            <button @click="open = !open" class="flex items-center space-x-2 focus:outline-none">
                                <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center text-white">
                                    <i class="fas fa-user text-sm"></i>
                                </div>
                                <span class="text-gray-700 dark:text-gray-200 font-medium">{{ Auth::user()->name ?? 'Admin' }}</span>
                                <i class="fas fa-chevron-down text-xs text-gray-500 transition-transform" :class="{ 'rotate-180': open }"></i>
                            </button>
                            
                            <!-- Dropdown Menu -->
                            <div x-show="open" 
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
            <main class="flex-1 overflow-y-auto bg-gray-50 dark:bg-gray-900 p-6 transition-colors">
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
</body>
</html>

