@php
    $school = app('currentSchool') ?? Auth::user()->school ?? null;
    $currentAcademicYear = $school
        ? (\App\Models\AcademicYear::where('school_id', $school->id)->where('is_current', true)->first()
            ?? \App\Models\AcademicYear::where('school_id', $school->id)->orderBy('start_date', 'desc')->first())
        : null;

    // Active group detection
    $activeStudent     = request()->routeIs('school.students.*', 'school.student-enquiries.*', 'school.student-registrations.*', 'school.admission.*', 'school.student-promotions.*');
    $activeAttendance  = request()->routeIs('school.reports.attendance.*');
    $activeFee         = request()->routeIs('school.fees.*', 'school.fee-payments.*', 'school.waivers.*', 'school.late-fee.*', 'school.fee-master.*', 'school.reports.fees.*', 'school.ad-hoc-fees.*');
    $activeFeeSetup    = request()->routeIs('school.fee-types.*', 'school.fee-names.*', 'school.miscellaneous-fees.*', 'school.payment-methods.*', 'school.school-banks.*');
    $activeExam        = request()->routeIs('school.examination.*');
    $activeAcademic    = request()->routeIs('school.academic-years.*', 'school.classes.*', 'school.sections.*', 'school.subjects.*');
    $activeTransport   = request()->routeIs('school.transport.*');
    $activeHostel      = request()->routeIs('school.hostel.*');
    $activeLibrary     = request()->routeIs('school.library.*');
    $activeMasterData  = request()->routeIs('school.student-types.*', 'school.boarding-types.*', 'school.blood-groups.*', 'school.religions.*', 'school.categories.*', 'school.qualifications.*', 'school.corresponding-relatives.*');
    $activeAdmSetup    = request()->routeIs('school.admission-codes.*', 'school.registration-codes.*', 'school.admission-news.*');
    $activeSystem      = request()->routeIs('school.users.*', 'school.settings.*', 'school.profile.*');
@endphp

{{-- ── Sidebar ──────────────────────────────────────────────────────────── --}}
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
                    <i class="fas fa-school text-[#1a237e]" :class="sidebarCollapsed ? 'text-lg' : 'text-2xl'"></i>
                @endif
            </div>
        </div>
        <div x-show="!sidebarCollapsed" class="sidebar-text text-center">
            <h2 class="text-xs font-bold leading-tight">{{ strtoupper($school->name ?? 'SCHOOL NAME') }}</h2>
            @if($school)
                <p class="text-xs text-indigo-200 mt-0.5">{{ $school->city->name ?? '' }}{{ ($school->city && $school->state) ? ', ' : '' }}{{ $school->state->name ?? '' }}</p>
            @endif
        </div>
        {{-- Collapse toggle (desktop only) --}}
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

            {{-- ════════════════════════════════════════════════════════════
                 MAIN
            ════════════════════════════════════════════════════════════ --}}
            @include('partials.school-sidebar.section-label', ['label' => 'Main'])

            @include('partials.school-sidebar.nav-item', [
                'route'  => 'school.dashboard',
                'icon'   => 'fas fa-tachometer-alt',
                'label'  => 'Dashboard',
                'active' => request()->routeIs('school.dashboard'),
            ])

            {{-- ════════════════════════════════════════════════════════════
                 STUDENTS
            ════════════════════════════════════════════════════════════ --}}
            @include('partials.school-sidebar.section-label', ['label' => 'Students'])

            @include('partials.school-sidebar.nav-item', [
                'route'  => 'school.admission.index',
                'icon'   => 'fas fa-user-graduate',
                'label'  => 'All Students',
                'active' => request()->routeIs('school.admission.*') && !request()->routeIs('school.admission.create', 'school.admission.edit'),
            ])

            @include('partials.school-sidebar.nav-group', [
                'icon'    => 'fas fa-user-plus',
                'label'   => 'Admission',
                'isOpen'  => $activeStudent,
                'groupId' => 'student',
                'items'   => [
                    ['route' => 'school.student-enquiries.index',    'label' => 'Enquiry'],
                    ['route' => 'school.student-registrations.index','label' => 'Registration'],
                    ['route' => 'school.admission.index',            'label' => 'Admission'],
                ],
            ])

            @include('partials.school-sidebar.nav-item', [
                'route'  => 'school.student-promotions.index',
                'icon'   => 'fas fa-level-up-alt',
                'label'  => 'Promotions',
                'active' => request()->routeIs('school.student-promotions.*'),
            ])

            {{-- ════════════════════════════════════════════════════════════
                 ATTENDANCE
            ════════════════════════════════════════════════════════════ --}}
            @include('partials.school-sidebar.section-label', ['label' => 'Attendance'])

            @include('partials.school-sidebar.nav-group', [
                'icon'    => 'fas fa-clipboard-check',
                'label'   => 'Attendance',
                'isOpen'  => $activeAttendance,
                'groupId' => 'attendance',
                'items'   => [
                    ['route' => 'school.reports.attendance.daily',   'label' => 'Daily Attendance'],
                    ['route' => 'school.reports.attendance.monthly', 'label' => 'Monthly Report'],
                    ['route' => 'school.reports.attendance.student', 'label' => 'Student Report'],
                ],
            ])

            {{-- ════════════════════════════════════════════════════════════
                 FEE MANAGEMENT
            ════════════════════════════════════════════════════════════ --}}
            @include('partials.school-sidebar.section-label', ['label' => 'Fee Management'])

            @include('partials.school-sidebar.nav-item', [
                'route'  => 'school.fee-payments.index',
                'icon'   => 'fas fa-hand-holding-usd',
                'label'  => 'Collect Fee',
                'active' => request()->routeIs('school.fee-payments.*'),
            ])

            @include('partials.school-sidebar.nav-item', [
                'route'  => 'school.fees.create',
                'icon'   => 'fas fa-file-invoice-dollar',
                'label'  => 'Generate Fee',
                'active' => request()->routeIs('school.fees.create'),
            ])

            @include('partials.school-sidebar.nav-item', [
                'route'  => 'school.ad-hoc-fees.create',
                'icon'   => 'fas fa-plus-circle',
                'label'  => 'Ad-Hoc Fee',
                'active' => request()->routeIs('school.ad-hoc-fees.*'),
            ])

            @include('partials.school-sidebar.nav-item', [
                'route'  => 'school.fee-master.index',
                'icon'   => 'fas fa-money-bill-wave',
                'label'  => 'Fee Master',
                'active' => request()->routeIs('school.fee-master.*'),
            ])

            @include('partials.school-sidebar.nav-item', [
                'route'  => 'school.waivers.index',
                'icon'   => 'fas fa-percent',
                'label'  => 'Waivers',
                'active' => request()->routeIs('school.waivers.*'),
            ])

            @include('partials.school-sidebar.nav-item', [
                'route'  => 'school.late-fee.index',
                'icon'   => 'fas fa-clock',
                'label'  => 'Late Fee',
                'active' => request()->routeIs('school.late-fee.*'),
            ])

            @include('partials.school-sidebar.nav-group', [
                'icon'    => 'fas fa-chart-pie',
                'label'   => 'Fee Reports',
                'isOpen'  => request()->routeIs('school.reports.fees.*'),
                'groupId' => 'feereports',
                'items'   => [
                    ['route' => 'school.reports.fees.index',             'label' => 'Overview'],
                    ['route' => 'school.reports.fees.daily-collection',  'label' => 'Daily Collection'],
                    ['route' => 'school.reports.fees.defaulters',        'label' => 'Defaulters'],
                ],
            ])

            {{-- ════════════════════════════════════════════════════════════
                 FEE SETUP
            ════════════════════════════════════════════════════════════ --}}
            @include('partials.school-sidebar.section-label', ['label' => 'Fee Setup'])

            @include('partials.school-sidebar.nav-item', [
                'route'  => 'school.fee-types.index',
                'icon'   => 'fas fa-tags',
                'label'  => 'Fee Types',
                'active' => request()->routeIs('school.fee-types.*'),
            ])

            @include('partials.school-sidebar.nav-item', [
                'route'  => 'school.fee-names.index',
                'icon'   => 'fas fa-list-alt',
                'label'  => 'Fee Names',
                'active' => request()->routeIs('school.fee-names.*'),
            ])

            @include('partials.school-sidebar.nav-item', [
                'route'  => 'school.miscellaneous-fees.index',
                'icon'   => 'fas fa-coins',
                'label'  => 'Misc. Fees',
                'active' => request()->routeIs('school.miscellaneous-fees.*'),
            ])

            @include('partials.school-sidebar.nav-item', [
                'route'  => 'school.payment-methods.index',
                'icon'   => 'fas fa-credit-card',
                'label'  => 'Payment Methods',
                'active' => request()->routeIs('school.payment-methods.*'),
            ])

            @include('partials.school-sidebar.nav-item', [
                'route'  => 'school.school-banks.index',
                'icon'   => 'fas fa-university',
                'label'  => 'School Banks',
                'active' => request()->routeIs('school.school-banks.*'),
            ])

            {{-- ════════════════════════════════════════════════════════════
                 EXAMINATION
            ════════════════════════════════════════════════════════════ --}}
            @include('partials.school-sidebar.section-label', ['label' => 'Examination'])

            @include('partials.school-sidebar.nav-group', [
                'icon'    => 'fas fa-file-alt',
                'label'   => 'Examination',
                'isOpen'  => $activeExam,
                'groupId' => 'exam',
                'items'   => [
                    ['route' => 'school.examination.exam-types.index', 'label' => 'Exam Types'],
                    ['route' => 'school.examination.exams.index',      'label' => 'Exam Schedule'],
                    ['route' => 'school.examination.subjects.index',   'label' => 'Subjects'],
                    ['route' => 'school.examination.marks.index',      'label' => 'Marks Entry'],
                    ['route' => 'school.examination.grades.index',     'label' => 'Grades'],
                ],
            ])

            {{-- ════════════════════════════════════════════════════════════
                 ACADEMIC SETUP
            ════════════════════════════════════════════════════════════ --}}
            @include('partials.school-sidebar.section-label', ['label' => 'Academic Setup'])

            @include('partials.school-sidebar.nav-item', [
                'route'  => 'school.academic-years.index',
                'icon'   => 'fas fa-calendar-alt',
                'label'  => 'Academic Years',
                'active' => request()->routeIs('school.academic-years.*'),
            ])

            @include('partials.school-sidebar.nav-item', [
                'route'  => 'school.classes.index',
                'icon'   => 'fas fa-chalkboard',
                'label'  => 'Classes',
                'active' => request()->routeIs('school.classes.*'),
            ])

            @include('partials.school-sidebar.nav-item', [
                'route'  => 'school.sections.index',
                'icon'   => 'fas fa-object-group',
                'label'  => 'Sections',
                'active' => request()->routeIs('school.sections.*'),
            ])

            @include('partials.school-sidebar.nav-item', [
                'route'  => 'school.subjects.index',
                'icon'   => 'fas fa-book-open',
                'label'  => 'Subjects',
                'active' => request()->routeIs('school.subjects.*'),
            ])

            {{-- ════════════════════════════════════════════════════════════
                 FACILITIES
            ════════════════════════════════════════════════════════════ --}}
            @include('partials.school-sidebar.section-label', ['label' => 'Facilities'])

            @include('partials.school-sidebar.nav-group', [
                'icon'    => 'fas fa-bus',
                'label'   => 'Transport',
                'isOpen'  => $activeTransport,
                'groupId' => 'transport',
                'items'   => [
                    ['route' => 'school.transport.vehicles.index',            'label' => 'Vehicles'],
                    ['route' => 'school.transport.transport_routes.index',    'label' => 'Routes'],
                    ['route' => 'school.transport.bus_stops.index',           'label' => 'Bus Stops'],
                    ['route' => 'school.transport.assignments.index',         'label' => 'Assignments'],
                    ['route' => 'school.transport.transport_attendance.index','label' => 'Attendance'],
                ],
            ])

            @include('partials.school-sidebar.nav-group', [
                'icon'    => 'fas fa-hotel',
                'label'   => 'Hostel',
                'isOpen'  => $activeHostel,
                'groupId' => 'hostel',
                'items'   => [
                    ['route' => 'school.hostel.hostels.index',    'label' => 'Hostels'],
                    ['route' => 'school.hostel.floors.index',     'label' => 'Floors'],
                    ['route' => 'school.hostel.rooms.index',      'label' => 'Rooms'],
                    ['route' => 'school.hostel.assignments.index','label' => 'Assignments'],
                    ['route' => 'school.hostel.attendance.index', 'label' => 'Attendance'],
                ],
            ])

            @include('partials.school-sidebar.nav-group', [
                'icon'    => 'fas fa-book-reader',
                'label'   => 'Library',
                'isOpen'  => $activeLibrary,
                'groupId' => 'library',
                'items'   => [
                    ['route' => 'school.library.index',  'label' => 'Catalog'],
                    ['route' => 'school.library.issues', 'label' => 'Circulation'],
                    ['route' => 'school.library.history','label' => 'History & Fines'],
                ],
            ])

            {{-- ════════════════════════════════════════════════════════════
                 HR
            ════════════════════════════════════════════════════════════ --}}
            @include('partials.school-sidebar.section-label', ['label' => 'HR'])

            @include('partials.school-sidebar.nav-item', [
                'route'  => 'school.users.index',
                'icon'   => 'fas fa-users-cog',
                'label'  => 'Users',
                'active' => request()->routeIs('school.users.*'),
            ])

            {{-- ════════════════════════════════════════════════════════════
                 MASTER DATA
            ════════════════════════════════════════════════════════════ --}}
            @include('partials.school-sidebar.section-label', ['label' => 'Master Data'])

            @include('partials.school-sidebar.nav-item', [
                'route'  => 'school.student-types.index',
                'icon'   => 'fas fa-user-tag',
                'label'  => 'Student Types',
                'active' => request()->routeIs('school.student-types.*'),
            ])

            @include('partials.school-sidebar.nav-item', [
                'route'  => 'school.boarding-types.index',
                'icon'   => 'fas fa-bed',
                'label'  => 'Boarding Types',
                'active' => request()->routeIs('school.boarding-types.*'),
            ])

            @include('partials.school-sidebar.nav-item', [
                'route'  => 'school.blood-groups.index',
                'icon'   => 'fas fa-tint',
                'label'  => 'Blood Groups',
                'active' => request()->routeIs('school.blood-groups.*'),
            ])

            @include('partials.school-sidebar.nav-item', [
                'route'  => 'school.religions.index',
                'icon'   => 'fas fa-pray',
                'label'  => 'Religions',
                'active' => request()->routeIs('school.religions.*'),
            ])

            @include('partials.school-sidebar.nav-item', [
                'route'  => 'school.categories.index',
                'icon'   => 'fas fa-layer-group',
                'label'  => 'Categories',
                'active' => request()->routeIs('school.categories.*'),
            ])

            @include('partials.school-sidebar.nav-item', [
                'route'  => 'school.qualifications.index',
                'icon'   => 'fas fa-graduation-cap',
                'label'  => 'Qualifications',
                'active' => request()->routeIs('school.qualifications.*'),
            ])

            @include('partials.school-sidebar.nav-item', [
                'route'  => 'school.corresponding-relatives.index',
                'icon'   => 'fas fa-people-arrows',
                'label'  => 'Relatives',
                'active' => request()->routeIs('school.corresponding-relatives.*'),
            ])

            {{-- ════════════════════════════════════════════════════════════
                 ADMISSION SETUP
            ════════════════════════════════════════════════════════════ --}}
            @include('partials.school-sidebar.section-label', ['label' => 'Admission Setup'])

            @include('partials.school-sidebar.nav-item', [
                'route'  => 'school.admission-codes.index',
                'icon'   => 'fas fa-barcode',
                'label'  => 'Admission Codes',
                'active' => request()->routeIs('school.admission-codes.*'),
            ])

            @include('partials.school-sidebar.nav-item', [
                'route'  => 'school.registration-codes.index',
                'icon'   => 'fas fa-qrcode',
                'label'  => 'Registration Codes',
                'active' => request()->routeIs('school.registration-codes.*'),
            ])

            @include('partials.school-sidebar.nav-item', [
                'route'  => 'school.admission-news.index',
                'icon'   => 'fas fa-newspaper',
                'label'  => 'Admission News',
                'active' => request()->routeIs('school.admission-news.*'),
            ])

            {{-- ════════════════════════════════════════════════════════════
                 SYSTEM
            ════════════════════════════════════════════════════════════ --}}
            @include('partials.school-sidebar.section-label', ['label' => 'System'])

            @include('partials.school-sidebar.nav-group', [
                'icon'    => 'fas fa-cog',
                'label'   => 'Settings',
                'isOpen'  => request()->routeIs('school.settings.*'),
                'groupId' => 'settings',
                'items'   => [
                    ['route' => 'school.settings.basic-info',              'label' => 'Basic Info'],
                    ['route' => 'school.settings.logo',                    'label' => 'Logo'],
                    ['route' => 'school.settings.general',                 'label' => 'General'],
                    ['route' => 'school.settings.session',                 'label' => 'Session'],
                    ['route' => 'school.settings.receipt-note',            'label' => 'Receipt Note'],
                    ['route' => 'school.settings.registration-fee.index',  'label' => 'Registration Fee'],
                    ['route' => 'school.settings.admission-fee.index',     'label' => 'Admission Fee'],
                ],
            ])

            @include('partials.school-sidebar.nav-item', [
                'route'  => 'school.profile.show',
                'icon'   => 'fas fa-user-circle',
                'label'  => 'My Profile',
                'active' => request()->routeIs('school.profile.*'),
            ])

        </ul>
    </nav>

    {{-- ── Footer: Support + Logout ──────────────────────────────────────── --}}
    <div class="flex-shrink-0 border-t border-[#283593]">
        <a href="{{ route('school.support') }}"
            title="Support"
            class="flex items-center px-4 py-3 text-indigo-200 hover:bg-white/10 hover:text-white transition-colors {{ request()->routeIs('school.support') ? 'text-teal-300 font-medium' : '' }}"
            :class="{ 'justify-center': sidebarCollapsed }">
            <i class="fas fa-life-ring w-5 flex-shrink-0" :class="{ 'mr-3': !sidebarCollapsed }"></i>
            <span x-show="!sidebarCollapsed" class="sidebar-text text-sm">Support</span>
        </a>
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
