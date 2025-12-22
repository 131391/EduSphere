<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'School Dashboard - ' . config('app.name'))</title>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
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
        /* Hide elements until Alpine.js is ready */
        [x-cloak] {
            display: none !important;
        }
        
        /* Custom Scrollbar for Sidebar */
        .sidebar-scroll {
            scrollbar-width: thin;
            scrollbar-color: rgba(200, 200, 200, 0.3) rgba(200, 200, 200, 0.1);
        }
        
        .sidebar-scroll:hover {
            scrollbar-color: rgba(200, 200, 200, 0.5) rgba(200, 200, 200, 0.2);
        }
        
        /* Webkit browsers (Chrome, Safari, Edge) */
        .sidebar-scroll::-webkit-scrollbar {
            width: 8px;
        }
        
        .sidebar-scroll::-webkit-scrollbar-track {
            background: rgba(200, 200, 200, 0.1);
            border-radius: 4px;
        }
        
        .sidebar-scroll::-webkit-scrollbar-thumb {
            background: rgba(200, 200, 200, 0.3);
            border-radius: 4px;
            transition: all 0.3s ease;
        }
        
        .sidebar-scroll:hover::-webkit-scrollbar-thumb {
            background: rgba(200, 200, 200, 0.5);
        }
        
        .sidebar-scroll::-webkit-scrollbar-thumb:hover {
            background: rgba(200, 200, 200, 0.7);
        }
    </style>
</head>
<body class="bg-gray-100">
    @php
        $school = app('currentSchool') ?? Auth::user()->school ?? \App\Models\School::where('status', 'active')->first();
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

            <!-- Navigation Menu - Scrollable -->
            <nav class="flex-1 overflow-y-auto py-4 sidebar-scroll">
                <ul class="space-y-1 px-2">
                    <!-- Main -->
                    <li class="pt-2">
                        <p class="px-4 py-2 text-xs font-semibold text-blue-300 uppercase">Main</p>
                    </li>
                    <li>
                        <a href="{{ route('school.dashboard') }}" class="flex items-center px-4 py-2 rounded-lg {{ request()->routeIs('school.dashboard') ? 'bg-[#283593] text-white' : 'text-indigo-100 hover:bg-[#283593]' }}">
                            <i class="fas fa-tachometer-alt w-5 mr-3"></i>
                            <span>Dashboards</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('school.registrations.index') }}" class="flex items-center px-4 py-2 rounded-lg {{ request()->routeIs('school.registrations.*') ? 'bg-[#283593] text-white' : 'text-indigo-100 hover:bg-[#283593]' }}">
                            <i class="fas fa-file-import w-5 mr-3"></i>
                            <span>Import Registration</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('school.waivers.index') }}" class="flex items-center px-4 py-2 rounded-lg {{ request()->routeIs('school.waivers.*') ? 'bg-[#283593] text-white' : 'text-indigo-100 hover:bg-[#283593]' }}">
                            <i class="fas fa-percent w-5 mr-3"></i>
                            <span>Waiver</span>
                        </a>
                    </li>

                    <!-- Fee Operations -->
                    <li class="pt-2">
                        <p class="px-4 py-2 text-xs font-semibold text-blue-300 uppercase">Fee Operations</p>
                    </li>
                    <li>
                        <a href="{{ route('school.fee-master.index') }}" class="flex items-center px-4 py-2 rounded-lg {{ request()->routeIs('school.fee-master.*') ? 'bg-[#283593] text-white' : 'text-indigo-100 hover:bg-[#283593]' }}">
                            <i class="fas fa-money-bill-wave w-5 mr-3"></i>
                            <span>Fee Management</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('school.late-fee.index') }}" class="flex items-center px-4 py-2 rounded-lg {{ request()->routeIs('school.late-fee.*') ? 'bg-[#283593] text-white' : 'text-indigo-100 hover:bg-[#283593]' }}">
                            <i class="fas fa-clock w-5 mr-3"></i>
                            <span>Manage Late Fee</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('school.fees.create') }}" class="flex items-center px-4 py-2 rounded-lg {{ request()->routeIs('school.fees.create') ? 'bg-[#283593] text-white' : 'text-indigo-100 hover:bg-[#283593]' }}">
                            <i class="fas fa-plus-circle w-5 mr-3"></i>
                            <span>Create New Fee</span>
                        </a>
                    </li>

                    <!-- Academic Setup -->
                    <li class="pt-2">
                        <p class="px-4 py-2 text-xs font-semibold text-blue-300 uppercase">Academic Setup</p>
                    </li>
                    <li>
                        <a href="{{ route('school.academic-years.index') }}" class="flex items-center px-4 py-2 rounded-lg {{ request()->routeIs('school.academic-years.*') ? 'bg-[#283593] text-white' : 'text-indigo-100 hover:bg-[#283593]' }}">
                            <i class="fas fa-calendar-alt w-5 mr-3"></i>
                            <span>Academic Years</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('school.classes.index') }}" class="flex items-center px-4 py-2 rounded-lg {{ request()->routeIs('school.classes.*') ? 'bg-[#283593] text-white' : 'text-indigo-100 hover:bg-[#283593]' }}">
                            <i class="fas fa-graduation-cap w-5 mr-3"></i>
                            <span>Class</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('school.sections.index') }}" class="flex items-center px-4 py-2 rounded-lg {{ request()->routeIs('school.sections.*') ? 'bg-[#283593] text-white' : 'text-indigo-100 hover:bg-[#283593]' }}">
                            <i class="fas fa-users w-5 mr-3"></i>
                            <span>Section</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('school.subjects.index') }}" class="flex items-center px-4 py-2 rounded-lg {{ request()->routeIs('school.subjects.*') ? 'bg-[#283593] text-white' : 'text-indigo-100 hover:bg-[#283593]' }}">
                            <i class="fas fa-book w-5 mr-3"></i>
                            <span>Subject Master</span>
                        </a>
                    </li>

                    <!-- Fee Masters -->
                    <li class="pt-2">
                        <p class="px-4 py-2 text-xs font-semibold text-blue-300 uppercase">Fee Masters</p>
                    </li>
                    <li>
                        <a href="{{ route('school.fee-types.index') }}" class="flex items-center px-4 py-2 rounded-lg {{ request()->routeIs('school.fee-types.*') ? 'bg-[#283593] text-white' : 'text-indigo-100 hover:bg-[#283593]' }}">
                            <i class="fas fa-credit-card w-5 mr-3"></i>
                            <span>Fee type</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('school.miscellaneous-fees.index') }}" class="flex items-center px-4 py-2 rounded-lg {{ request()->routeIs('school.miscellaneous-fees.*') ? 'bg-[#283593] text-white' : 'text-indigo-100 hover:bg-[#283593]' }}">
                            <i class="fas fa-coins w-5 mr-3"></i>
                            <span>Miscellaneous Fee</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('school.fee-names.index') }}" class="flex items-center px-4 py-2 rounded-lg {{ request()->routeIs('school.fee-names.*') ? 'bg-[#283593] text-white' : 'text-indigo-100 hover:bg-[#283593]' }}">
                            <i class="fas fa-list w-5 mr-3"></i>
                            <span>Fee Name</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('school.payment-methods.index') }}" class="flex items-center px-4 py-2 rounded-lg {{ request()->routeIs('school.payment-methods.*') ? 'bg-[#283593] text-white' : 'text-indigo-100 hover:bg-[#283593]' }}">
                            <i class="fas fa-credit-card w-5 mr-3"></i>
                            <span>Payment Method</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('school.school-banks.index') }}" class="flex items-center px-4 py-2 rounded-lg {{ request()->routeIs('school.school-banks.*') ? 'bg-[#283593] text-white' : 'text-indigo-100 hover:bg-[#283593]' }}">
                            <i class="fas fa-university w-5 mr-3"></i>
                            <span>School Bank</span>
                        </a>
                    </li>

                    <!-- Student Masters -->
                    <li class="pt-2">
                        <p class="px-4 py-2 text-xs font-semibold text-blue-300 uppercase">Student Masters</p>
                    </li>
                    <li>
                        <a href="{{ route('school.student-types.index') }}" class="flex items-center px-4 py-2 rounded-lg {{ request()->routeIs('school.student-types.*') ? 'bg-[#283593] text-white' : 'text-indigo-100 hover:bg-[#283593]' }}">
                            <i class="fas fa-user-tag w-5 mr-3"></i>
                            <span>Student Type</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('school.boarding-types.index') }}" class="flex items-center px-4 py-2 rounded-lg {{ request()->routeIs('school.boarding-types.*') ? 'bg-[#283593] text-white' : 'text-indigo-100 hover:bg-[#283593]' }}">
                            <i class="fas fa-bed w-5 mr-3"></i>
                            <span>Boarding Type</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('school.corresponding-relatives.index') }}" class="flex items-center px-4 py-2 rounded-lg {{ request()->routeIs('school.corresponding-relatives.*') ? 'bg-[#283593] text-white' : 'text-indigo-100 hover:bg-[#283593]' }}">
                            <i class="fas fa-users w-5 mr-3"></i>
                            <span>Corresponding Relatives</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('school.blood-groups.index') }}" class="flex items-center px-4 py-2 rounded-lg {{ request()->routeIs('school.blood-groups.*') ? 'bg-[#283593] text-white' : 'text-indigo-100 hover:bg-[#283593]' }}">
                            <i class="fas fa-tint w-5 mr-3"></i>
                            <span>Blood Groups</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('school.religions.index') }}" class="flex items-center px-4 py-2 rounded-lg {{ request()->routeIs('school.religions.*') ? 'bg-[#283593] text-white' : 'text-indigo-100 hover:bg-[#283593]' }}">
                            <i class="fas fa-pray w-5 mr-3"></i>
                            <span>Religions</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('school.categories.index') }}" class="flex items-center px-4 py-2 rounded-lg {{ request()->routeIs('school.categories.*') ? 'bg-[#283593] text-white' : 'text-indigo-100 hover:bg-[#283593]' }}">
                            <i class="fas fa-layer-group w-5 mr-3"></i>
                            <span>Categorys</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('school.qualifications.index') }}" class="flex items-center px-4 py-2 rounded-lg {{ request()->routeIs('school.qualifications.*') ? 'bg-[#283593] text-white' : 'text-indigo-100 hover:bg-[#283593]' }}">
                            <i class="fas fa-graduation-cap w-5 mr-3"></i>
                            <span>Qualification</span>
                        </a>
                    </li>

                    <!-- Admission & News -->
                    <li class="pt-2">
                        <p class="px-4 py-2 text-xs font-semibold text-blue-300 uppercase">Admission & News</p>
                    </li>
                    <li>
                        <a href="{{ route('school.admission-codes.index') }}" class="flex items-center px-4 py-2 rounded-lg {{ request()->routeIs('school.admission-codes.*') ? 'bg-[#283593] text-white' : 'text-indigo-100 hover:bg-[#283593]' }}">
                            <i class="fas fa-code w-5 mr-3"></i>
                            <span>Admission Code</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('school.admission-news.index') }}" class="flex items-center px-4 py-2 rounded-lg {{ request()->routeIs('school.admission-news.*') ? 'bg-[#283593] text-white' : 'text-indigo-100 hover:bg-[#283593]' }}">
                            <i class="fas fa-newspaper w-5 mr-3"></i>
                            <span>Admission News</span>
                        </a>
                    </li>

                    <!-- Examination -->
                    <li class="pt-2">
                        <p class="px-4 py-2 text-xs font-semibold text-blue-300 uppercase">Examination</p>
                    </li>
                    <li x-data="{ open: {{ request()->routeIs('school.examination.*') ? 'true' : 'false' }} }">
                        <button @click="open = !open" class="w-full flex items-center justify-between px-4 py-2 rounded-lg text-indigo-100 hover:bg-[#283593] focus:outline-none">
                            <div class="flex items-center">
                                <i class="fas fa-file-alt w-5 mr-3"></i>
                                <span>Examination</span>
                            </div>
                            <i class="fas fa-chevron-down text-xs transition-transform duration-200" :class="{ 'transform rotate-180': open }"></i>
                        </button>
                        <ul x-show="open" x-collapse class="pl-4 mt-1 space-y-1">
                            <li>
                                <a href="{{ route('school.examination.subjects.index') }}" class="flex items-center px-4 py-2 rounded-lg {{ request()->routeIs('school.examination.subjects.*') ? 'bg-[#283593] text-white' : 'text-indigo-100 hover:bg-[#283593]' }}">
                                    <i class="fas fa-minus w-3 mr-3"></i>
                                    <span>Add Subject</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('school.examination.exam-types.index') }}" class="flex items-center px-4 py-2 rounded-lg {{ request()->routeIs('school.examination.exam-types.*') ? 'bg-[#283593] text-white' : 'text-indigo-100 hover:bg-[#283593]' }}">
                                    <i class="fas fa-minus w-3 mr-3"></i>
                                    <span>Exam Type</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('school.examination.exams.index') }}" class="flex items-center px-4 py-2 rounded-lg {{ request()->routeIs('school.examination.exams.*') ? 'bg-[#283593] text-white' : 'text-indigo-100 hover:bg-[#283593]' }}">
                                    <i class="fas fa-minus w-3 mr-3"></i>
                                    <span>Create Exam</span>
                                </a>
                            </li>
                            <li>
                                <a href="#" class="flex items-center px-4 py-2 rounded-lg text-indigo-100 hover:bg-[#283593]">
                                    <i class="fas fa-minus w-3 mr-3"></i>
                                    <span>Exam Schedule</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('school.examination.grades.index') }}" class="flex items-center px-4 py-2 rounded-lg {{ request()->routeIs('school.examination.grades.*') ? 'bg-[#283593] text-white' : 'text-indigo-100 hover:bg-[#283593]' }}">
                                    <i class="fas fa-minus w-3 mr-3"></i>
                                    <span>Student Grade</span>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <!-- System -->
                    <li class="pt-2">
                        <p class="px-4 py-2 text-xs font-semibold text-blue-300 uppercase">System</p>
                    </li>
                    <li x-data="{ open: {{ request()->routeIs('school.settings.*') || request()->routeIs('school.admission-news.*') || request()->routeIs('school.support') ? 'true' : 'false' }} }">
                        <button @click="open = !open" class="w-full flex items-center justify-between px-4 py-2 rounded-lg text-indigo-100 hover:bg-[#283593] focus:outline-none">
                            <div class="flex items-center">
                                <i class="fas fa-cog w-5 mr-3"></i>
                                <span>Setting</span>
                            </div>
                            <i class="fas fa-chevron-down text-xs transition-transform duration-200" :class="{ 'transform rotate-180': open }"></i>
                        </button>
                        <ul x-show="open" x-collapse class="pl-4 mt-1 space-y-1">
                            <li>
                                <a href="{{ route('school.settings.logo') }}" class="flex items-center px-4 py-2 rounded-lg {{ request()->routeIs('school.settings.logo') ? 'bg-[#283593] text-white' : 'text-indigo-100 hover:bg-[#283593]' }}">
                                    <i class="fas fa-minus w-3 mr-3"></i>
                                    <span>Logo Update</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('school.settings.basic-info') }}" class="flex items-center px-4 py-2 rounded-lg {{ request()->routeIs('school.settings.basic-info') ? 'bg-[#283593] text-white' : 'text-indigo-100 hover:bg-[#283593]' }}">
                                    <i class="fas fa-minus w-3 mr-3"></i>
                                    <span>Basic Information</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('school.settings.registration-fee.index') }}" class="flex items-center px-4 py-2 rounded-lg {{ request()->routeIs('school.settings.registration-fee.*') ? 'bg-[#283593] text-white' : 'text-indigo-100 hover:bg-[#283593]' }}">
                                    <i class="fas fa-minus w-3 mr-3"></i>
                                    <span>Registration Fee</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('school.settings.admission-fee.index') }}" class="flex items-center px-4 py-2 rounded-lg {{ request()->routeIs('school.settings.admission-fee.*') ? 'bg-[#283593] text-white' : 'text-indigo-100 hover:bg-[#283593]' }}">
                                    <i class="fas fa-minus w-3 mr-3"></i>
                                    <span>Admission Fee</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('school.settings.general') }}" class="flex items-center px-4 py-2 rounded-lg {{ request()->routeIs('school.settings.general') ? 'bg-[#283593] text-white' : 'text-indigo-100 hover:bg-[#283593]' }}">
                                    <i class="fas fa-minus w-3 mr-3"></i>
                                    <span>General Settings</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('school.settings.receipt-note') }}" class="flex items-center px-4 py-2 rounded-lg {{ request()->routeIs('school.settings.receipt-note') ? 'bg-[#283593] text-white' : 'text-indigo-100 hover:bg-[#283593]' }}">
                                    <i class="fas fa-minus w-3 mr-3"></i>
                                    <span>Receipt Note</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('school.settings.session') }}" class="flex items-center px-4 py-2 rounded-lg {{ request()->routeIs('school.settings.session') ? 'bg-[#283593] text-white' : 'text-indigo-100 hover:bg-[#283593]' }}">
                                    <i class="fas fa-minus w-3 mr-3"></i>
                                    <span>Set Session</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li>
                        <a href="{{ route('school.support') }}" class="flex items-center px-4 py-2 rounded-lg {{ request()->routeIs('school.support') ? 'bg-[#283593] text-white' : 'text-indigo-100 hover:bg-[#283593]' }}">
                            <i class="fas fa-question-circle w-5 mr-3"></i>
                            <span>Support</span>
                        </a>
                    </li>
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
                    <div class="flex items-center space-x-4" x-data="headerActions">
                        <!-- Star (Favorite) -->
                        <button 
                            @click="toggleFavorite()" 
                            class="text-gray-500 hover:text-gray-700 transition-colors"
                            :class="isFavorite ? 'text-yellow-500 hover:text-yellow-600' : 'text-gray-500 hover:text-gray-700'"
                            title="Add to Favorites"
                        >
                            <i :class="isFavorite ? 'fas fa-star text-xl' : 'far fa-star text-xl'"></i>
                        </button>

                        <!-- Bookmark (Saved List) -->
                        <div class="relative">
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
                            class="text-gray-500 hover:text-gray-700 transition-colors"
                            title="Toggle Fullscreen"
                        >
                            <i class="fas text-xl" :class="isFullscreen ? 'fa-compress' : 'fa-expand'"></i>
                        </button>

                        <!-- Dark Mode -->
                        <button 
                            @click="toggleDarkMode()" 
                            class="text-gray-500 hover:text-gray-700 transition-colors"
                            title="Toggle Dark Mode"
                        >
                            <i class="far text-xl" :class="isDark ? 'fa-sun' : 'fa-moon'"></i>
                        </button>
                        
                        <!-- User Dropdown -->
                        <div class="relative" x-data="{ open: false }" x-cloak>
                            <button 
                                @click="open = !open"
                                class="flex items-center space-x-2 focus:outline-none"
                            >
                                <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center text-white">
                                    <i class="fas fa-user text-sm"></i>
                                </div>
                                <span class="text-gray-700 font-medium">{{ Auth::user()->name ?? 'School Admin' }}</span>
                                <i class="fas fa-chevron-down text-gray-500 text-xs"></i>
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
            <main class="flex-1 overflow-y-auto bg-gray-50 p-6">
                @yield('content')
            </main>
        </div>
    </div>

    @stack('scripts')
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
                
                async toggleFavorite() {
                    try {
                        const response = await fetch('{{ route('school.favorites.toggle') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                title: document.title,
                                url: window.location.href
                            })
                        });
                        const data = await response.json();
                        this.isFavorite = data.status === 'added';
                        this.loadFavorites();
                    } catch (error) {
                        console.error('Error toggling favorite:', error);
                    }
                },
                
                async checkFavorite() {
                    try {
                        const response = await fetch('{{ route('school.favorites.check') }}?url=' + encodeURIComponent(window.location.href));
                        const data = await response.json();
                        this.isFavorite = data.is_favorite;
                    } catch (error) {
                        console.error('Error checking favorite:', error);
                    }
                },
                
                async loadFavorites() {
                    try {
                        const response = await fetch('{{ route('school.favorites.index') }}');
                        this.favorites = await response.json();
                    } catch (error) {
                        console.error('Error loading favorites:', error);
                    }
                },
                
                async removeFavorite(id) {
                    try {
                        await fetch('{{ url('school/favorites') }}/' + id, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            }
                        });
                        this.loadFavorites();
                        // If we are on the page we just removed, update the star icon
                        const removedFav = this.favorites.find(f => f.id === id);
                        if (removedFav && window.location.href === removedFav.url) {
                            this.isFavorite = false;
                        }
                    } catch (error) {
                        console.error('Error removing favorite:', error);
                    }
                }
            }));
        });
    </script>
    <x-delete-confirmation />
</body>
</html>

