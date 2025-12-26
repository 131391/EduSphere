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
    
    <style>
        [x-cloak] { display: none !important; }
        .sidebar-scroll {
            scrollbar-width: thin;
            scrollbar-color: rgba(200, 200, 200, 0.3) rgba(200, 200, 200, 0.1);
        }
        
        /* Select2 Custom Styling */
        .select2-container--default .select2-selection--single {
            height: 42px;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            padding: 0.5rem 1rem;
            background-color: white;
        }
        
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 26px;
            color: #374151;
            padding-left: 0;
        }
        
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 40px;
            right: 8px;
        }
        
        .select2-container--default .select2-selection--single:focus,
        .select2-container--default.select2-container--focus .select2-selection--single {
            outline: none;
            border-color: #14b8a6;
            box-shadow: 0 0 0 3px rgba(20, 184, 166, 0.1);
        }
        
        /* Dark mode support for Select2 */
        .dark .select2-container--default .select2-selection--single {
            background-color: #374151;
            border-color: #4b5563;
        }
        
        .dark .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #ffffff;
        }
        
        .dark .select2-dropdown {
            background-color: #374151;
            border-color: #4b5563;
        }
        
        .dark .select2-container--default .select2-results__option {
            color: #ffffff;
        }
        
        .dark .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: #14b8a6;
        }
        
        .dark .select2-container--default .select2-search--dropdown .select2-search__field {
            background-color: #4b5563;
            border-color: #6b7280;
            color: #ffffff;
        }
        
        /* Select2 dropdown styling */
        .select2-dropdown {
            border-radius: 0.5rem;
            border: 1px solid #d1d5db;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        
        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: #14b8a6;
        }
        
        .select2-container--default .select2-search--dropdown .select2-search__field {
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            padding: 0.5rem;
        }
        
        /* Prevent double scrollbar */
        body {
            overflow: hidden;
        }
    </style>
</head>
<body class="bg-gray-100">
    @php
        $school = auth()->user()->school;
        $currentAcademicYear = $school ? \App\Models\AcademicYear::where('school_id', $school->id)->where('is_current', true)->first() : null;
    @endphp

    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <aside class="w-64 bg-[#1a237e] text-white flex flex-col">
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
                hostelOpen: {{ request()->routeIs('receptionist.hostels.*') || request()->routeIs('receptionist.hostel-floors.*') || request()->routeIs('receptionist.hostel-rooms.*') ? 'true' : 'false' }},
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
                           class="flex items-center px-4 py-2 rounded-lg {{ request()->routeIs('receptionist.hostel-rooms.*') ? 'bg-[#283593] text-white' : 'text-indigo-100 hover:bg-[#283593]' }} transition-colors text-sm">
                            <i class="fas fa-door-open w-5 mr-3"></i>
                            <span>Rooms</span>
                        </a>
                    </div>
                </div>

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
        $(document).ready(function() {
            // Initialize Select2 on all select elements (except datatable selects)
            $('select').not('.no-select2, .select2-hidden-accessible, [data-table-select]').select2({
                placeholder: function() {
                    return $(this).data('placeholder') || 'Select an option';
                },
                allowClear: true,
                width: '100%'
            });
            
            // Re-initialize Select2 when new content is loaded dynamically
            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.addedNodes.length) {
                        $(mutation.addedNodes).find('select').not('.no-select2, .select2-hidden-accessible, [data-table-select]').select2({
                            placeholder: function() {
                                return $(this).data('placeholder') || 'Select an option';
                            },
                            allowClear: true,
                            width: '100%'
                        });
                    }
                });
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
