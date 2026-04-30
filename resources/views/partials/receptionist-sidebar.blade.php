@php
    $school = auth()->user()->school;
    $currentAcademicYear = $school
        ? (\App\Models\AcademicYear::where('school_id', $school->id)->where('is_current', true)->first()
            ?? \App\Models\AcademicYear::where('school_id', $school->id)->orderBy('start_date', 'desc')->first())
        : null;

    $activeFrontDesk = request()->routeIs(
        'receptionist.visitors.*',
        'receptionist.student-enquiries.*',
        'receptionist.student-registrations.*',
        'receptionist.admission.*'
    );
    $activeTransport = request()->routeIs(
        'receptionist.vehicles.*',
        'receptionist.routes.*',
        'receptionist.bus-stops.*',
        'receptionist.transport-assignments.*',
        'receptionist.transport-assign-history.*',
        'receptionist.transport-attendance.*'
    );
    $activeHostel = request()->routeIs(
        'receptionist.hostels.*',
        'receptionist.hostel-floors.*',
        'receptionist.hostel-rooms.*',
        'receptionist.hostel-bed-assignments.*',
        'receptionist.hostel-attendance.*'
    );
    $activeReports = request()->routeIs(
        'receptionist.transport-attendance.month-wise-report',
        'receptionist.hostel-attendance.report'
    );
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
            <div class="bg-white rounded-full flex items-center justify-center transition-all duration-300 sidebar-logo-container"
                :style="sidebarCollapsed ? 'width:2.5rem;height:2.5rem' : 'width:4rem;height:4rem'">
                @if($school?->logo)
                    <img src="{{ asset('storage/'.$school->logo) }}" alt="{{ $school->name }}"
                        class="rounded-full object-cover sidebar-logo-img"
                        :style="sidebarCollapsed ? 'width:2.5rem;height:2.5rem' : 'width:4rem;height:4rem'">
                @else
                    <i class="fas fa-school text-[#1a237e] sidebar-logo-icon" :class="sidebarCollapsed ? 'text-lg' : 'text-2xl'"></i>
                @endif
            </div>
        </div>
        <div x-show="!sidebarCollapsed" class="sidebar-text text-center">
            <h2 class="text-xs font-bold leading-tight">{{ strtoupper($school->name ?? 'SCHOOL NAME') }}</h2>
            @if($school)
                <p class="text-xs text-indigo-200 mt-0.5">
                    {{ $school->city->name ?? '' }}{{ ($school->city && $school->state) ? ', ' : '' }}{{ $school->state->name ?? '' }}
                </p>
            @endif
        </div>
        <button @click="toggleSidebar()"
            class="absolute top-6 -right-4 w-8 h-8 hidden lg:flex items-center justify-center bg-white text-[#1a237e] rounded-full shadow-lg border border-gray-200 hover:bg-indigo-50 transition-colors z-50 focus:outline-none">
            <i class="fas fa-chevron-left text-xs" x-show="!sidebarCollapsed"></i>
            <i class="fas fa-chevron-right text-xs" x-show="sidebarCollapsed" style="display:none"></i>
        </button>
    </div>

    {{-- ── Session badge ─────────────────────────────────────────────────── --}}
    <div class="px-4 py-2 bg-[#283593] text-xs flex-shrink-0 overflow-hidden whitespace-nowrap">
        <p class="font-semibold sidebar-text" x-show="!sidebarCollapsed">
            SESSION: {{ $currentAcademicYear?->name ?? '—' }}
        </p>
        <p class="font-semibold text-center" x-show="sidebarCollapsed && !isMobile" style="display:none">
            {{ preg_replace('/^.*?(\d{2})\D*(\d{2})$/', '$1-$2', $currentAcademicYear?->name ?? '--') }}
        </p>
    </div>

    {{-- ── Navigation ────────────────────────────────────────────────────── --}}
    <nav class="flex-1 overflow-y-auto py-3 sidebar-scroll">
        <ul class="space-y-0.5 px-2">

            {{-- ════ MAIN ════ --}}
            @include('partials.school-sidebar.section-label', ['label' => 'Main'])

            @include('partials.school-sidebar.nav-item', [
                'route'  => 'receptionist.dashboard',
                'icon'   => 'fas fa-tachometer-alt',
                'label'  => 'Dashboard',
                'active' => request()->routeIs('receptionist.dashboard'),
            ])

            {{-- ════ FRONT DESK ════ --}}
            @include('partials.school-sidebar.section-label', ['label' => 'Front Desk'])

            @include('partials.school-sidebar.nav-item', [
                'route'  => 'receptionist.visitors.index',
                'icon'   => 'fas fa-user-friends',
                'label'  => 'Visitors',
                'active' => request()->routeIs('receptionist.visitors.*'),
            ])

            @include('partials.school-sidebar.nav-group', [
                'icon'    => 'fas fa-user-plus',
                'label'   => 'Admission',
                'isOpen'  => $activeFrontDesk && !request()->routeIs('receptionist.visitors.*'),
                'groupId' => 'frontdesk',
                'items'   => [
                    ['route' => 'receptionist.student-enquiries.index',    'label' => 'Enquiry'],
                    ['route' => 'receptionist.student-registrations.index','label' => 'Registration'],
                    ['route' => 'receptionist.admission.index',            'label' => 'Admission'],
                ],
            ])

            {{-- ════ TRANSPORT ════ --}}
            @include('partials.school-sidebar.section-label', ['label' => 'Transport'])

            @include('partials.school-sidebar.nav-group', [
                'icon'    => 'fas fa-bus',
                'label'   => 'Transport',
                'isOpen'  => $activeTransport,
                'groupId' => 'transport',
                'items'   => [
                    ['route' => 'receptionist.vehicles.index',                      'label' => 'Vehicles'],
                    ['route' => 'receptionist.routes.index',                        'label' => 'Routes'],
                    ['route' => 'receptionist.bus-stops.index',                     'label' => 'Bus Stops'],
                    ['route' => 'receptionist.transport-assignments.index',         'label' => 'Assignments'],
                    ['route' => 'receptionist.transport-attendance.index',          'label' => 'Attendance'],
                    ['route' => 'receptionist.transport-attendance.month-wise-report','label' => 'Monthly Report'],
                ],
            ])

            {{-- ════ HOSTEL ════ --}}
            @include('partials.school-sidebar.section-label', ['label' => 'Hostel'])

            @include('partials.school-sidebar.nav-group', [
                'icon'    => 'fas fa-hotel',
                'label'   => 'Hostel',
                'isOpen'  => $activeHostel,
                'groupId' => 'hostel',
                'items'   => [
                    ['route' => 'receptionist.hostels.index',              'label' => 'Hostels'],
                    ['route' => 'receptionist.hostel-floors.index',        'label' => 'Floors'],
                    ['route' => 'receptionist.hostel-rooms.index',         'label' => 'Rooms'],
                    ['route' => 'receptionist.hostel-bed-assignments.index','label' => 'Bed Assignments'],
                    ['route' => 'receptionist.hostel-attendance.index',    'label' => 'Attendance'],
                    ['route' => 'receptionist.hostel-attendance.report',   'label' => 'Attendance Report'],
                ],
            ])

            {{-- ════ HR ════ --}}
            @include('partials.school-sidebar.section-label', ['label' => 'HR'])

            @include('partials.school-sidebar.nav-item', [
                'route'  => 'receptionist.staff.index',
                'icon'   => 'fas fa-id-badge',
                'label'  => 'Staff',
                'active' => request()->routeIs('receptionist.staff.*'),
            ])

            {{-- ════ ACCOUNT ════ --}}
            @include('partials.school-sidebar.section-label', ['label' => 'Account'])

            @include('partials.school-sidebar.nav-item', [
                'route'  => 'receptionist.profile.show',
                'icon'   => 'fas fa-user-circle',
                'label'  => 'My Profile',
                'active' => request()->routeIs('receptionist.profile.*'),
            ])

        </ul>
    </nav>

    {{-- ── Footer: Logout ────────────────────────────────────────────────── --}}
    <div class="flex-shrink-0 border-t border-[#283593]">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
                title="Logout"
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
