<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Receptionist Dashboard - ' . config('app.name'))</title>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    @stack('styles')
    
    <style>
        [x-cloak] { display: none !important; }
        .sidebar-scroll {
            scrollbar-width: thin;
            scrollbar-color: rgba(200, 200, 200, 0.3) rgba(200, 200, 200, 0.1);
        }
    </style>
</head>
<body class="bg-gray-50 dark:bg-gray-900 font-sans antialiased">
    <div x-data="{ sidebarOpen: true, darkMode: localStorage.getItem('darkMode') === 'true' }" 
         :class="{ 'dark': darkMode }"
         class="min-h-screen flex">
        
        <!-- Sidebar -->
        <aside :class="sidebarOpen ? 'w-64' : 'w-20'" 
               class="bg-[#1a237e] text-white transition-all duration-300 flex flex-col">
            
            <!-- Logo -->
            <div class="p-4 flex items-center justify-between border-b border-[#283593]">
                <div class="flex items-center space-x-3" x-show="sidebarOpen">
                    <div class="w-10 h-10 bg-white rounded-full flex items-center justify-center">
                        <i class="fas fa-school text-[#1a237e]"></i>
                    </div>
                    <div>
                        <h1 class="font-bold text-lg">{{ auth()->user()->school->name ?? 'School' }}</h1>
                        <p class="text-xs text-indigo-200">Receptionist</p>
                    </div>
                </div>
                <button @click="sidebarOpen = !sidebarOpen" class="text-white hover:bg-[#283593] p-2 rounded">
                    <i class="fas fa-bars"></i>
                </button>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 overflow-y-auto sidebar-scroll p-4 space-y-2">
                <a href="{{ route('receptionist.dashboard') }}" 
                   class="flex items-center px-4 py-3 rounded-lg {{ request()->routeIs('receptionist.dashboard') ? 'bg-[#283593]' : 'hover:bg-[#283593]' }} transition-colors">
                    <i class="fas fa-home w-5"></i>
                    <span x-show="sidebarOpen" class="ml-3">Dashboard</span>
                </a>

                <div class="pt-4">
                    <p x-show="sidebarOpen" class="px-4 text-xs font-semibold text-blue-300 uppercase mb-2">Front Desk</p>
                </div>

                <a href="{{ route('receptionist.visitors.index') }}" 
                   class="flex items-center px-4 py-3 rounded-lg {{ request()->routeIs('receptionist.visitors.*') ? 'bg-[#283593]' : 'hover:bg-[#283593]' }} transition-colors">
                    <i class="fas fa-users w-5"></i>
                    <span x-show="sidebarOpen" class="ml-3">Visitor Entry</span>
                </a>

                <a href="#" class="flex items-center px-4 py-3 rounded-lg hover:bg-[#283593] transition-colors">
                    <i class="fas fa-user-plus w-5"></i>
                    <span x-show="sidebarOpen" class="ml-3">Admission Enquiry</span>
                </a>

                <a href="#" class="flex items-center px-4 py-3 rounded-lg hover:bg-[#283593] transition-colors">
                    <i class="fas fa-envelope w-5"></i>
                    <span x-show="sidebarOpen" class="ml-3">Postal Enquiry</span>
                </a>
            </nav>

            <!-- User Profile -->
            <div class="p-4 border-t border-[#283593]">
                <div class="flex items-center space-x-3" x-show="sidebarOpen">
                    <div class="w-10 h-10 bg-[#283593] rounded-full flex items-center justify-center">
                        <span class="text-sm font-bold">{{ substr(auth()->user()->name, 0, 2) }}</span>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-semibold">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-indigo-200">{{ auth()->user()->email }}</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}" class="mt-2">
                    @csrf
                    <button type="submit" class="w-full flex items-center px-4 py-2 rounded-lg hover:bg-[#283593] transition-colors">
                        <i class="fas fa-sign-out-alt w-5"></i>
                        <span x-show="sidebarOpen" class="ml-3">Logout</span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Header -->
            <header class="bg-white shadow-sm border-b border-gray-200">
                <div class="flex items-center justify-between px-6 py-4">
                    <!-- Left: Menu & Search -->
                    <div class="flex items-center space-x-4 flex-1">
                        <button class="text-gray-500 hover:text-gray-700 lg:hidden">
                            <i class="fas fa-bars text-xl"></i>
                        </button>
                        <div class="relative flex-1 max-w-md">
                            <input type="text" placeholder="Search..." class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        </div>
                    </div>

                    <!-- Right: Actions & User -->
                    <div class="flex items-center space-x-4">
                        <!-- Dark Mode Toggle -->
                        <button @click="darkMode = !darkMode; localStorage.setItem('darkMode', darkMode)" 
                                class="text-gray-500 hover:text-gray-700 transition-colors">
                            <i class="fas fa-moon text-xl" x-show="!darkMode"></i>
                            <i class="fas fa-sun text-xl" x-show="darkMode"></i>
                        </button>

                        <!-- Fullscreen Toggle -->
                        <button onclick="document.documentElement.requestFullscreen()" 
                                class="text-gray-500 hover:text-gray-700 transition-colors">
                            <i class="fas fa-expand text-xl"></i>
                        </button>

                        <!-- User Dropdown -->
                        <div class="relative" x-data="{ open: false }" x-cloak>
                            <button @click="open = !open" class="flex items-center space-x-2 focus:outline-none">
                                <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center text-white">
                                    <i class="fas fa-user text-sm"></i>
                                </div>
                                <span class="text-gray-700 font-medium">{{ Auth::user()->name ?? 'Receptionist' }}</span>
                                <i class="fas fa-chevron-down text-gray-500 text-xs"></i>
                            </button>
                            
                            <!-- Dropdown Menu -->
                            <div x-show="open" @click.away="open = false"
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 scale-95"
                                 x-transition:enter-end="opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-150"
                                 x-transition:leave-start="opacity-100 scale-100"
                                 x-transition:leave-end="opacity-0 scale-95"
                                 class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-50">
                                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center">
                                    <i class="fas fa-user-circle mr-3 text-gray-500"></i>
                                    Profile
                                </a>
                                <div class="border-t border-gray-200 my-1"></div>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 flex items-center">
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
            <main class="flex-1 overflow-y-auto bg-gray-50 p-6">
                @yield('content')
            </main>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
