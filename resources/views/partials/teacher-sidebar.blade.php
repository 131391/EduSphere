@php
    $school = Auth::user()->school;
@endphp

<aside
    class="fixed inset-y-0 left-0 z-50 bg-[#1a237e] text-white flex flex-col transform transition-all duration-300 ease-in-out lg:translate-x-0 lg:static lg:inset-0 no-transition"
    :style="(isMobile || sidebarOpen) ? 'width:16rem' : (sidebarCollapsed ? 'width:5rem' : 'width:16rem')"
    :class="{
        '-translate-x-full': !sidebarOpen,
        'translate-x-0':      sidebarOpen,
        'sidebar-collapsed':  sidebarCollapsed && !isMobile,
        'mobile-open':        sidebarOpen
    }">

    {{-- ── Logo ──────────────────────────────────────────────────────────── --}}
    <div class="p-4 border-b border-[#283593] flex-shrink-0 relative">
        <div class="flex items-center justify-center mb-2">
            <div class="bg-white rounded-full flex items-center justify-center transition-all duration-300"
                :style="sidebarCollapsed ? 'width:2.5rem;height:2.5rem' : 'width:4rem;height:4rem'"
                style="width:4rem;height:4rem">
                @if($school?->logo)
                    <img src="{{ asset('storage/'.$school->logo) }}" alt="{{ $school->name }}"
                        class="rounded-full object-cover"
                        :style="sidebarCollapsed ? 'width:2.5rem;height:2.5rem' : 'width:4rem;height:4rem'"
                        style="width:4rem;height:4rem">
                @else
                    <i class="fas fa-chalkboard-teacher text-[#1a237e]" :class="sidebarCollapsed ? 'text-lg' : 'text-2xl'"></i>
                @endif
            </div>
        </div>
        <div x-show="!sidebarCollapsed" class="sidebar-text text-center">
            <h2 class="text-xs font-bold leading-tight">{{ strtoupper($school->name ?? 'TEACHER PORTAL') }}</h2>
            <p class="text-[10px] font-bold tracking-widest text-indigo-300 mt-0.5 uppercase">Teacher Portal</p>
        </div>
        <button @click="toggleSidebar()"
            class="absolute top-6 -right-4 w-8 h-8 hidden lg:flex items-center justify-center bg-white text-[#1a237e] rounded-full shadow-lg border border-gray-200 hover:bg-indigo-50 transition-colors z-50 focus:outline-none">
            <i class="fas fa-chevron-left text-xs" x-show="!sidebarCollapsed"></i>
            <i class="fas fa-chevron-right text-xs" x-show="sidebarCollapsed" style="display:none"></i>
        </button>
    </div>

    {{-- ── Navigation ────────────────────────────────────────────────────── --}}
    <nav class="flex-1 overflow-y-auto py-3 sidebar-scroll">
        <ul class="space-y-0.5 px-2">

            @include('partials.school-sidebar.section-label', ['label' => 'Main'])

            @include('partials.school-sidebar.nav-item', [
                'route'  => 'teacher.dashboard',
                'icon'   => 'fas fa-tachometer-alt',
                'label'  => 'Dashboard',
                'active' => request()->routeIs('teacher.dashboard'),
            ])

            @include('partials.school-sidebar.section-label', ['label' => 'Classroom'])

            @include('partials.school-sidebar.nav-item', [
                'route'  => 'teacher.students.index',
                'icon'   => 'fas fa-users',
                'label'  => 'My Students',
                'active' => request()->routeIs('teacher.students.*'),
            ])

            @include('partials.school-sidebar.nav-item', [
                'route'  => 'teacher.timetable.index',
                'icon'   => 'fas fa-clock',
                'label'  => 'Timetable',
                'active' => request()->routeIs('teacher.timetable.*'),
            ])

            @include('partials.school-sidebar.nav-item', [
                'route'  => 'teacher.attendance.index',
                'icon'   => 'fas fa-calendar-check',
                'label'  => 'Attendance',
                'active' => request()->routeIs('teacher.attendance.*'),
            ])

            @include('partials.school-sidebar.nav-item', [
                'route'  => 'teacher.marks.index',
                'icon'   => 'fas fa-pen-nib',
                'label'  => 'Marks Entry',
                'active' => request()->routeIs('teacher.marks.*'),
            ])

        </ul>
    </nav>

    {{-- ── Footer: Logout ────────────────────────────────────────────────── --}}
    <div class="flex-shrink-0 border-t border-[#283593]">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" title="Logout"
                class="w-full flex items-center px-4 py-3 text-indigo-200 hover:bg-red-700 hover:text-white transition-colors"
                :class="{ 'justify-center': sidebarCollapsed }">
                <i class="fas fa-sign-out-alt w-5 flex-shrink-0" :class="{ 'mr-3': !sidebarCollapsed }"></i>
                <span x-show="!sidebarCollapsed" class="sidebar-text text-sm">Logout</span>
            </button>
        </form>
        <div x-show="!sidebarCollapsed" class="px-4 py-2 text-xs text-indigo-300 text-center sidebar-text">
            &copy; {{ date('Y') }} EduSphere
        </div>
    </div>
</aside>
