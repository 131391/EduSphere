<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Receptionist Dashboard - ' . config('app.name'))</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
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
</head>
<body class="bg-gray-100">
    @php
        $school = auth()->user()->school;
        $currentAcademicYear = $school ? \App\Models\AcademicYear::where('school_id', $school->id)->where('is_current', true)->first() : null;
    @endphp

    <div class="flex h-screen overflow-hidden" x-data="{ sidebarOpen: false }">
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
        <aside class="fixed inset-y-0 left-0 z-50 w-64 bg-[#1a237e] text-white flex flex-col transform transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:inset-0"
               :class="{ '-translate-x-full': !sidebarOpen, 'translate-x-0': sidebarOpen }">
            <!-- Logo Section -->
            <div class="p-4 border-b border-[#283593] flex-shrink-0">
                <div class="flex items-center justify-center mb-2">
                    <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center">
                        @if($school && $school->logo)
                            <img src="{{ asset('storage/' . $school->logo) }}" alt="{{ $school->name }}" class="w-16 h-16 rounded-full object-cover">
                        @else
                            <i class="fas fa-book text-[#1a237e] text-2xl"></i>
                        @endif
                    </div>
                </div>
                <h2 class="text-xs font-bold text-center leading-tight">{{ strtoupper($school->name ?? 'SCHOOL NAME') }}</h2>
                @if($school)
                    <p class="text-xs text-indigo-100 text-center mt-1">{{ $school->city ?? '' }}, {{ $school->state ?? '' }}</p>
                @endif
            </div>

            <!-- Session Info -->
            <div class="px-4 py-2 bg-[#283593] text-xs flex-shrink-0">
                <p class="font-semibold">SESSION: {{ $currentAcademicYear?->name ?? '2025 - 2026' }}</p>
            </div>

            <!-- Navigation Menu - Scrollable -->
            <nav class="flex-1 overflow-y-auto py-4 sidebar-scroll" 
                 x-data="{
                    frontDeskOpen: {{ request()->routeIs('receptionist.visitors.*') || request()->routeIs('receptionist.student-enquiries.*') || request()->routeIs('receptionist.registrations.*') || request()->routeIs('receptionist.admissions.*') ? 'true' : 'false' }},
                    studentOpen: {{ request()->routeIs('receptionist.students.*') ? 'true' : 'false' }},
                    transportOpen: {{ request()->routeIs('receptionist.vehicles.*') || request()->routeIs('receptionist.routes.*') || request()->routeIs('receptionist.bus-stops.*') ? 'true' : 'false' }},
                    staffOpen: {{ request()->routeIs('receptionist.staff.*') ? 'true' : 'false' }}
                 }">
                <ul class="space-y-1 px-2">
                    <!-- Dashboard -->
                    <li>
                        <a href="{{ route('receptionist.dashboard') }}" class="flex items-center px-4 py-2 rounded-lg {{ request()->routeIs('receptionist.dashboard') ? 'bg-[#283593] text-white' : 'text-indigo-100 hover:bg-[#283593]' }}">
                            <i class="fas fa-tachometer-alt w-5 mr-3"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>

                    <!-- Front Desk -->
                    <li>
                        <button @click="frontDeskOpen = !frontDeskOpen" class="w-full flex items-center justify-between px-4 py-2 rounded-lg text-indigo-100 hover:bg-[#283593]">
                            <div class="flex items-center">
                                <i class="fas fa-concierge-bell w-5 mr-3"></i>
                                <span>Front Desk</span>
                            </div>
                            <i class="fas fa-chevron-down text-xs transition-transform" :class="{ 'rotate-180': frontDeskOpen }"></i>
                        </button>
                        <ul x-show="frontDeskOpen" x-collapse class="ml-4 mt-1 space-y-1">
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
                                <a href="{{ route('receptionist.registrations.index') }}" class="flex items-center px-4 py-2 rounded-lg text-sm {{ request()->routeIs('receptionist.registrations.*') ? 'bg-[#283593] text-white' : 'text-indigo-100 hover:bg-[#283593]' }}">
                                    <i class="fas fa-clipboard-list w-4 mr-3"></i>
                                    <span>Student Registration</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('receptionist.admissions.index') }}" class="flex items-center px-4 py-2 rounded-lg text-sm {{ request()->routeIs('receptionist.admissions.*') ? 'bg-[#283593] text-white' : 'text-indigo-100 hover:bg-[#283593]' }}">
                                    <i class="fas fa-user-graduate w-4 mr-3"></i>
                                    <span>Admission List</span>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <!-- Student -->
                    <li>
                        <button @click="studentOpen = !studentOpen" class="w-full flex items-center justify-between px-4 py-2 rounded-lg text-indigo-100 hover:bg-[#283593]">
                            <div class="flex items-center">
                                <i class="fas fa-user-graduate w-5 mr-3"></i>
                                <span>Student</span>
                            </div>
                            <i class="fas fa-chevron-down text-xs transition-transform" :class="{ 'rotate-180': studentOpen }"></i>
                        </button>
                        <ul x-show="studentOpen" x-collapse class="ml-4 mt-1 space-y-1">
                            <li>
                                <a href="{{ route('receptionist.students.index') }}" class="flex items-center px-4 py-2 rounded-lg text-sm {{ request()->routeIs('receptionist.students.*') ? 'bg-[#283593] text-white' : 'text-indigo-100 hover:bg-[#283593]' }}">
                                    <i class="fas fa-list w-4 mr-3"></i>
                                    <span>Student List</span>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <!-- Transport Management -->
                    <li>
                        <button @click="transportOpen = !transportOpen" class="w-full flex items-center justify-between px-4 py-2 rounded-lg text-indigo-100 hover:bg-[#283593]">
                            <div class="flex items-center">
                                <i class="fas fa-bus w-5 mr-3"></i>
                                <span>Transport Management</span>
                            </div>
                            <i class="fas fa-chevron-down text-xs transition-transform" :class="{ 'rotate-180': transportOpen }"></i>
                        </button>
                        <ul x-show="transportOpen" x-collapse class="ml-4 mt-1 space-y-1">
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
                        <button @click="staffOpen = !staffOpen" class="w-full flex items-center justify-between px-4 py-2 rounded-lg text-indigo-100 hover:bg-[#283593]">
                            <div class="flex items-center">
                                <i class="fas fa-users w-5 mr-3"></i>
                                <span>Staff</span>
                            </div>
                            <i class="fas fa-chevron-down text-xs transition-transform" :class="{ 'rotate-180': staffOpen }"></i>
                        </button>
                        <ul x-show="staffOpen" x-collapse class="ml-4 mt-1 space-y-1">
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
                            <button type="submit" class="w-full flex items-center px-4 py-2 rounded-lg text-indigo-100 hover:bg-[#283593] text-left">
                                <i class="fas fa-sign-out-alt w-5 mr-3"></i>
                                <span>LogOut</span>
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
                            <i :class="isFavorite ? 'fas fa-star text-xl' : 'far fa-star text-xl'"></i>
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
                            <i class="fas text-xl" :class="isFullscreen ? 'fa-compress' : 'fa-expand'"></i>
                        </button>

                        <!-- Dark Mode -->
                        <button 
                            @click="toggleDarkMode()" 
                            class="text-gray-500 hover:text-gray-700 transition-colors hidden sm:block"
                            title="Toggle Dark Mode"
                        >
                            <i class="far text-xl" :class="isDark ? 'fa-sun' : 'fa-moon'"></i>
                        </button>
                        
                        <!-- User Dropdown -->
                        <div class="relative" x-data="{ open: false }" x-cloak>
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
        <!-- Sidebar -->
        <aside class="w-64 bg-[#1a237e] text-white flex flex-col relative z-50">
            <!-- Logo Section -->
            <div class="p-4 border-b border-[#283593] flex-shrink-0">
                <div class="flex items-center justify-center mb-2">
                    <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center">
                        @if($school && $school->logo)
                            <img src="{{ asset('storage/' . $school->logo) }}" alt="{{ $school->name }}" class="w-16 h-16 rounded-full object-cover">
                        @else
                            <i class="fas fa-book text-[#1a237e] text-2xl"></i>
                        @endif
                    </div>
                </div>
                <h2 class="text-xs font-bold text-center leading-tight">{{ strtoupper($school->name ?? 'SCHOOL NAME') }}</h2>
                @if($school)
                    <p class="text-xs text-indigo-100 text-center mt-1">{{ $school->city ?? '' }}, {{ $school->state ?? '' }}</p>
                @endif
            </div>

            <!-- Session Info -->
            <div class="px-4 py-2 bg-[#283593] text-xs flex-shrink-0">
                <p class="font-semibold">SESSION: {{ $currentAcademicYear?->name ?? '2025 - 2026' }}</p>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 overflow-y-auto sidebar-scroll p-4 space-y-2" x-data="{ 
                frontDeskOpen: {{ request()->routeIs('receptionist.visitors.*') ? 'true' : 'false' }},
                studentOpen: {{ request()->routeIs('receptionist.student-enquiries.*') || request()->routeIs('receptionist.student-registrations.*') || request()->routeIs('receptionist.admission.*') || request()->routeIs('receptionist.transport-assignments.*') ? 'true' : 'false' }},
                transportOpen: {{ request()->routeIs('receptionist.vehicles.*') || request()->routeIs('receptionist.routes.*') || request()->routeIs('receptionist.bus-stops.*') || request()->routeIs('receptionist.transport-assign-history.*') || request()->routeIs('receptionist.transport-attendance.*') ? 'true' : 'false' }},
                hostelOpen: {{ request()->routeIs('receptionist.hostels.*') || request()->routeIs('receptionist.hostel-floors.*') || request()->routeIs('receptionist.hostel-rooms.*') || request()->routeIs('receptionist.hostel-bed-assignments.*') || request()->routeIs('receptionist.hostel-attendance.*') ? 'true' : 'false' }},
                reportsOpen: {{ request()->routeIs('receptionist.transport-attendance.month-wise-report') ? 'true' : 'false' }},
                toggleFrontDesk() {
                    this.frontDeskOpen = !this.frontDeskOpen;
                    if (this.frontDeskOpen) {
                        this.studentOpen = false;
                        this.transportOpen = false;
                        this.hostelOpen = false;
                        this.reportsOpen = false;
                    }
                },
                toggleStudent() {
                    this.studentOpen = !this.studentOpen;
                    if (this.studentOpen) {
                        this.frontDeskOpen = false;
                        this.transportOpen = false;
                        this.hostelOpen = false;
                        this.reportsOpen = false;
                    }
                },
                toggleTransport() {
                    this.transportOpen = !this.transportOpen;
                    if (this.transportOpen) {
                        this.frontDeskOpen = false;
                        this.studentOpen = false;
                        this.hostelOpen = false;
                        this.reportsOpen = false;
                    }
                },
                toggleHostel() {
                    this.hostelOpen = !this.hostelOpen;
                    if (this.hostelOpen) {
                        this.frontDeskOpen = false;
                        this.studentOpen = false;
                        this.transportOpen = false;
                        this.reportsOpen = false;
                    }
                },
                toggleReports() {
                    this.reportsOpen = !this.reportsOpen;
                    if (this.reportsOpen) {
                        this.frontDeskOpen = false;
                        this.studentOpen = false;
                        this.transportOpen = false;
                        this.hostelOpen = false;
                    }
                }
            }">
                <a href="{{ route('receptionist.dashboard') }}" 
                   class="flex items-center px-4 py-3 rounded-lg {{ request()->routeIs('receptionist.dashboard') ? 'bg-[#283593] text-white' : 'text-indigo-100 hover:bg-[#283593]' }} transition-colors">
                    <i class="fas fa-home w-5 mr-3"></i>
                    <span>Dashboard</span>
                </a>

                <!-- Front Desk Collapsible Menu -->
                <div>
                    <button @click="toggleFrontDesk()" 
                            class="w-full flex items-center justify-between px-4 py-3 rounded-lg text-indigo-100 hover:bg-[#283593] transition-colors">
                        <div class="flex items-center">
                            <i class="fas fa-desktop w-5 mr-3"></i>
                            <span>Front Desk</span>
                        </div>
                        <i class="fas fa-chevron-down transition-transform duration-200" :class="{ 'rotate-180': frontDeskOpen }"></i>
                    </button>
                    
                    <!-- Submenu -->
                    <div x-show="frontDeskOpen" 
                         x-cloak
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 -translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         class="ml-4 mt-1 space-y-1">
                        <a href="{{ route('receptionist.visitors.index') }}" 
                           class="flex items-center px-4 py-2 rounded-lg {{ request()->routeIs('receptionist.visitors.*') ? 'bg-[#283593] text-white' : 'text-indigo-100 hover:bg-[#283593]' }} transition-colors text-sm">
                            <i class="fas fa-users w-5 mr-3"></i>
                            <span>Visitor Entry</span>
                        </a>

                        <a href="#" class="flex items-center px-4 py-2 rounded-lg text-indigo-100 hover:bg-[#283593] transition-colors text-sm">
                            <i class="fas fa-envelope w-5 mr-3"></i>
                            <span>Postal Enquiry</span>
                        </a>

                        <a href="#" class="flex items-center px-4 py-2 rounded-lg text-indigo-100 hover:bg-[#283593] transition-colors text-sm">
                            <i class="fas fa-phone w-5 mr-3"></i>
                            <span>Phone Enquiry</span>
                        </a>

                        <a href="#" class="flex items-center px-4 py-2 rounded-lg text-indigo-100 hover:bg-[#283593] transition-colors text-sm">
                            <i class="fas fa-file-alt w-5 mr-3"></i>
                            <span class="whitespace-nowrap">Online Application Report</span>
                        </a>

                        <a href="#" class="flex items-center px-4 py-2 rounded-lg text-indigo-100 hover:bg-[#283593] transition-colors text-sm">
                            <i class="fas fa-comments w-5 mr-3"></i>
                            <span class="whitespace-nowrap">Complain & Suggestions</span>
                        </a>
                    </div>
                </div>

                <!-- Student Collapsible Menu -->
                <div>
                    <button @click="toggleStudent()" 
                            class="w-full flex items-center justify-between px-4 py-3 rounded-lg text-indigo-100 hover:bg-[#283593] transition-colors">
                        <div class="flex items-center">
                            <i class="fas fa-user-graduate w-5 mr-3"></i>
                            <span>Student</span>
                        </div>
                        <i class="fas fa-chevron-down transition-transform duration-200" :class="{ 'rotate-180': studentOpen }"></i>
                    </button>
                    
                    <!-- Submenu -->
                    <div x-show="studentOpen" 
                         x-cloak
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 -translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         class="ml-4 mt-1 space-y-1">
                        <a href="{{ route('receptionist.student-enquiries.index') }}" 
                           class="flex items-center px-4 py-2 rounded-lg {{ request()->routeIs('receptionist.student-enquiries.*') ? 'bg-[#283593] text-white' : 'text-indigo-100 hover:bg-[#283593]' }} transition-colors text-sm">
                            <i class="fas fa-clipboard-list w-5 mr-3"></i>
                            <span>Enquiry</span>
                        </a>

                        <a href="{{ route('receptionist.student-registrations.index') }}" 
                           class="flex items-center px-4 py-2 rounded-lg {{ request()->routeIs('receptionist.student-registrations.*') ? 'bg-[#283593] text-white' : 'text-indigo-100 hover:bg-[#283593]' }} transition-colors text-sm">
                            <i class="fas fa-user-plus w-5 mr-3"></i>
                            <span>Registration</span>
                        </a>

                        <a href="{{ route('receptionist.admission.index') }}" class="flex items-center px-4 py-2 rounded-lg {{ request()->routeIs('receptionist.admission.*') ? 'bg-[#283593] text-white' : 'text-indigo-100 hover:bg-[#283593]' }} transition-colors text-sm">
                            <i class="fas fa-graduation-cap w-5 mr-3"></i>
                            <span>Admission</span>
                        </a>

                        <a href="#" class="flex items-center px-4 py-2 rounded-lg text-indigo-100 hover:bg-[#283593] transition-colors text-sm">
                            <i class="fas fa-id-card w-5 mr-3"></i>
                            <span>ID Card</span>
                        </a>

                        <a href="{{ route('receptionist.transport-assignments.index') }}" class="flex items-center px-4 py-2 rounded-lg {{ request()->routeIs('receptionist.transport-assignments.*') ? 'bg-[#283593] text-white' : 'text-indigo-100 hover:bg-[#283593]' }} transition-colors text-sm">
                            <i class="fas fa-bus w-5 mr-3"></i>
                            <span class="whitespace-nowrap">Assign Transport Facility</span>
                        </a>
                    </div>
                </div>

                <!-- Transport Management Collapsible Menu -->
                <div>
                    <button @click="toggleTransport()" 
                            class="w-full flex items-center justify-between px-4 py-3 rounded-lg text-indigo-100 hover:bg-[#283593] transition-colors">
                        <div class="flex items-center">
                            <i class="fas fa-bus w-5 mr-3"></i>
                            <span class="whitespace-nowrap">Transport Management</span>
                        </div>
                        <i class="fas fa-chevron-down transition-transform duration-200" :class="{ 'rotate-180': transportOpen }"></i>
                    </button>
                    
                    <!-- Submenu -->
                    <div x-show="transportOpen" 
                         x-cloak
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 -translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         class="ml-4 mt-1 space-y-1">
                        <a href="{{ route('receptionist.vehicles.index') }}" 
                           class="flex items-center px-4 py-2 rounded-lg {{ request()->routeIs('receptionist.vehicles.*') ? 'bg-[#283593] text-white' : 'text-indigo-100 hover:bg-[#283593]' }} transition-colors text-sm">
                            <i class="fas fa-car w-5 mr-3"></i>
                            <span>Vehicle</span>
                        </a>

                        <a href="{{ route('receptionist.routes.index') }}" 
                           class="flex items-center px-4 py-2 rounded-lg {{ request()->routeIs('receptionist.routes.*') ? 'bg-[#283593] text-white' : 'text-indigo-100 hover:bg-[#283593]' }} transition-colors text-sm">
                            <i class="fas fa-route w-5 mr-3"></i>
                            <span>Route</span>
                        </a>

                        <a href="{{ route('receptionist.bus-stops.index') }}" 
                           class="flex items-center px-4 py-2 rounded-lg {{ request()->routeIs('receptionist.bus-stops.*') ? 'bg-[#283593] text-white' : 'text-indigo-100 hover:bg-[#283593]' }} transition-colors text-sm">
                            <i class="fas fa-map-marker-alt w-5 mr-3"></i>
                            <span>Bus Stop</span>
                        </a>

                        <a href="{{ route('receptionist.transport-assign-history.index') }}" 
                           class="flex items-center px-4 py-2 rounded-lg {{ request()->routeIs('receptionist.transport-assign-history.*') ? 'bg-[#283593] text-white' : 'text-indigo-100 hover:bg-[#283593]' }} transition-colors text-sm">
                            <i class="fas fa-history w-5 mr-3"></i>
                            <span class="whitespace-nowrap">Transport Assign History</span>
                        </a>

                        <a href="{{ route('receptionist.transport-attendance.index') }}" 
                           class="flex items-center px-4 py-2 rounded-lg {{ request()->routeIs('receptionist.transport-attendance.*') ? 'bg-[#283593] text-white' : 'text-indigo-100 hover:bg-[#283593]' }} transition-colors text-sm">
                            <i class="fas fa-clipboard-check w-5 mr-3"></i>
                            <span class="whitespace-nowrap">Student Attendance</span>
                        </a>
                    </div>
                </div>

                <!-- Hostel Management Collapsible Menu -->
                <div>
                    <button @click="toggleHostel()" 
                            class="w-full flex items-center justify-between px-4 py-3 rounded-lg text-indigo-100 hover:bg-[#283593] transition-colors">
                        <div class="flex items-center">
                            <i class="fas fa-bed w-5 mr-3"></i>
                            <span>Hostel</span>
                        </div>
                        <i class="fas fa-chevron-down transition-transform duration-200" :class="{ 'rotate-180': hostelOpen }"></i>
                    </button>
                    
                    <!-- Submenu -->
                    <div x-show="hostelOpen" 
                         x-cloak
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 -translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         class="ml-4 mt-1 space-y-1">
                        <a href="{{ route('receptionist.hostels.index') }}" 
                           class="flex items-center px-4 py-2 rounded-lg {{ request()->routeIs('receptionist.hostels.index') ? 'bg-[#283593] text-white' : 'text-indigo-100 hover:bg-[#283593]' }} transition-colors text-sm">
                            <i class="fas fa-bed w-5 mr-3"></i>
                            <span>Hostels</span>
                        </a>
                        <a href="{{ route('receptionist.hostel-floors.index') }}" 
                           class="flex items-center px-4 py-2 rounded-lg {{ request()->routeIs('receptionist.hostel-floors.index') ? 'bg-[#283593] text-white' : 'text-indigo-100 hover:bg-[#283593]' }} transition-colors text-sm">
                            <i class="fas fa-layer-group w-5 mr-3"></i>
                            <span>Floors</span>
                        </a>
                        <a href="{{ route('receptionist.hostel-rooms.index') }}" 
                           class="flex items-center px-4 py-2 rounded-lg {{ request()->routeIs('receptionist.hostel-rooms.index') ? 'bg-[#283593] text-white' : 'text-indigo-100 hover:bg-[#283593]' }} transition-colors text-sm">
                            <i class="fas fa-door-open w-5 mr-3"></i>
                            <span>Rooms</span>
                        </a>
                        <a href="{{ route('receptionist.hostel-bed-assignments.index') }}" 
                           class="flex items-center px-4 py-2 rounded-lg {{ request()->routeIs('receptionist.hostel-bed-assignments.*') ? 'bg-[#283593] text-white' : 'text-indigo-100 hover:bg-[#283593]' }} transition-colors text-sm">
                            <i class="fas fa-user-plus w-5 mr-3"></i>
                            <span>Assign Student Hostel Bed</span>
                        </a>

                        <a href="{{ route('receptionist.hostel-attendance.index') }}" 
                           class="flex items-center px-4 py-2 rounded-lg {{ request()->routeIs('receptionist.hostel-attendance.index') || request()->routeIs('receptionist.hostel-attendance.store') ? 'bg-[#283593] text-white' : 'text-indigo-100 hover:bg-[#283593]' }} transition-colors text-sm">
                            <i class="fas fa-clipboard-check w-5 mr-3"></i>
                            <span>Student Attendance</span>
                        </a>

                        <a href="{{ route('receptionist.hostel-attendance.report') }}" 
                           class="flex items-center px-4 py-2 rounded-lg {{ request()->routeIs('receptionist.hostel-attendance.report') ? 'bg-[#283593] text-white' : 'text-indigo-100 hover:bg-[#283593]' }} transition-colors text-sm">
                            <i class="fas fa-chart-bar w-5 mr-3"></i>
                            <span>Hostel Attendance Report</span>
                        </a>
                    </div>
                </div>

                <!-- Staff Management -->
                <a href="{{ route('receptionist.staff.index') }}" 
                   class="flex items-center px-4 py-3 rounded-lg {{ request()->routeIs('receptionist.staff.*') ? 'bg-[#283593] text-white' : 'text-indigo-100 hover:bg-[#283593]' }} transition-colors">
                    <i class="fas fa-user-tie w-5 mr-3"></i>
                    <span>Staff Management</span>
                </a>

                <!-- Reports Collapsible Menu -->
                <div>
                    <button @click="toggleReports()" 
                            class="w-full flex items-center justify-between px-4 py-3 rounded-lg text-indigo-100 hover:bg-[#283593] transition-colors">
                        <div class="flex items-center">
                            <i class="fas fa-chart-bar w-5 mr-3"></i>
                            <span>Reports</span>
                        </div>
                        <i class="fas fa-chevron-down transition-transform duration-200" :class="{ 'rotate-180': reportsOpen }"></i>
                    </button>
                    
                    <!-- Submenu -->
                    <div x-show="reportsOpen" 
                         x-cloak
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 -translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         class="ml-4 mt-1 space-y-1">
                        <a href="{{ route('receptionist.transport-attendance.month-wise-report') }}" 
                           class="flex items-center px-4 py-2 rounded-lg {{ request()->routeIs('receptionist.transport-attendance.month-wise-report') ? 'bg-[#283593] text-white' : 'text-indigo-100 hover:bg-[#283593]' }} transition-colors text-sm">
                            <i class="fas fa-calendar-alt w-5 mr-3"></i>
                            <span class="whitespace-nowrap">Transport Attendance Month Wise</span>
                        </a>
                    </div>
                </div>

                <div class="pt-4">
                    <p class="px-4 text-xs font-semibold text-blue-300 uppercase mb-2">Account</p>
                </div>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full flex items-center px-4 py-3 rounded-lg text-indigo-100 hover:bg-[#283593] transition-colors text-left">
                        <i class="fas fa-sign-out-alt w-5 mr-3"></i>
                        <span>LogOut</span>
                    </button>
                </form>
            </nav>

            <!-- Footer -->
            <div class="p-4 border-t border-[#283593] text-xs text-indigo-100 text-center">
                <p>{{ date('Y') }} ©</p>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Header -->
            <header class="bg-white shadow-sm border-b border-gray-200" 
                    x-data="{ 
                        darkMode: localStorage.getItem('darkMode') === 'true', 
                        isFullscreen: false,
                        init() {
                            document.addEventListener('fullscreen-changed', (e) => {
                                this.isFullscreen = e.detail.isFullscreen;
                            });
                        },
                        toggleDarkMode() {
                            this.darkMode = !this.darkMode;
                            if (this.darkMode) {
                                document.documentElement.classList.add('dark');
                            } else {
                                document.documentElement.classList.remove('dark');
                            }
                            localStorage.setItem('darkMode', this.darkMode);
                        }
                    }">
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
                        <button @click="toggleDarkMode()" 
                                class="text-gray-500 hover:text-gray-700 transition-colors">
                            <i class="fas fa-moon text-xl" x-show="!darkMode"></i>
                            <i class="fas fa-sun text-xl" x-show="darkMode"></i>
                        </button>

                        <!-- Fullscreen Toggle -->
                        <button @click="
                            if (!document.fullscreenElement) {
                                document.documentElement.requestFullscreen();
                                isFullscreen = true;
                            } else {
                                document.exitFullscreen();
                                isFullscreen = false;
                            }
                        " 
                        class="text-gray-500 hover:text-gray-700 transition-colors">
                            <i class="fas fa-expand text-xl" x-show="!isFullscreen"></i>
                            <i class="fas fa-compress text-xl" x-show="isFullscreen"></i>
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
                $select.attr('data-table-select')) {
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
            // Wait a bit for Alpine.js to finish initializing
            setTimeout(function() {
                // Initialize Select2 on all select elements (except datatable selects)
                $('select').each(function() {
                    initSelect2($(this));
                });
            }, 200);
            
            // Debounce function to prevent multiple rapid initializations
            let initTimeout;
            let pendingSelects = new Set();
            
            function debouncedInitSelect2($selects) {
                clearTimeout(initTimeout);
                
                // Add to pending set
                $selects.each(function() {
                    const selectId = $(this).attr('id') || $(this).attr('name') || this.outerHTML;
                    pendingSelects.add(selectId);
                });
                
                initTimeout = setTimeout(function() {
                    // Process only selects that are still pending and not initialized
                    $selects.each(function() {
                        const $select = $(this);
                        const selectId = $select.attr('id') || $select.attr('name') || this.outerHTML;
                        
                        // Skip if already initialized or not in pending set
                        if (!pendingSelects.has(selectId) || $select.hasClass('select2-hidden-accessible')) {
                            pendingSelects.delete(selectId);
                            return;
                        }
                        
                        // Check if parent is still being rendered (Alpine.js x-cloak)
                        if ($select.closest('[x-cloak]').length > 0) {
                            return; // Skip, will be initialized when Alpine finishes
                        }
                        
                        initSelect2($select);
                        pendingSelects.delete(selectId);
                    });
                }, 300); // Increased delay to allow Alpine.js to finish
            }
            
            // Re-initialize Select2 when new content is loaded dynamically
            const observer = new MutationObserver(function(mutations) {
                const newSelects = $();
                mutations.forEach(function(mutation) {
                    if (mutation.addedNodes.length) {
                        $(mutation.addedNodes).find('select').each(function() {
                            const $select = $(this);
                            // Skip if already initialized, excluded, or inside x-cloak
                            if (!$select.hasClass('select2-hidden-accessible') && 
                                !$select.hasClass('no-select2') && 
                                !$select.attr('data-table-select') &&
                                $select.closest('[x-cloak]').length === 0) {
                                newSelects.push(this);
                            }
                        });
                    }
                });
                
                // Debounce initialization to prevent double loading
                if (newSelects.length > 0) {
                    debouncedInitSelect2(newSelects);
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
</body>
</html>
