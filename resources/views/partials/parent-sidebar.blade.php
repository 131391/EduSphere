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
            <div class="logo-container bg-white rounded-full flex items-center justify-center overflow-hidden flex-shrink-0 transition-all duration-300"
                :style="sidebarCollapsed ? 'width:2.5rem;height:2.5rem' : 'width:4rem;height:4rem'"
                style="width:4rem;height:4rem">
                <i class="fas fa-user-friends text-[#1a237e]" :class="sidebarCollapsed ? 'text-lg' : 'text-2xl'"></i>
            </div>
        </div>
        <div x-show="!sidebarCollapsed" class="sidebar-text text-center">
            <h2 class="text-xs font-bold leading-tight">PARENT PORTAL</h2>
            <p class="text-[10px] font-bold tracking-widest text-indigo-300 mt-0.5 uppercase">{{ config('app.name') }}</p>
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
                'route'  => 'parent.dashboard',
                'icon'   => 'fas fa-tachometer-alt',
                'label'  => 'Dashboard',
                'active' => request()->routeIs('parent.dashboard'),
            ])

            @include('partials.school-sidebar.section-label', ['label' => 'My Children'])

            @include('partials.school-sidebar.nav-item', [
                'route'  => 'parent.children.index',
                'icon'   => 'fas fa-child',
                'label'  => 'Children',
                'active' => request()->routeIs('parent.children.*'),
            ])

            @include('partials.school-sidebar.nav-item', [
                'route'  => 'parent.attendance.index',
                'icon'   => 'fas fa-calendar-check',
                'label'  => 'Attendance',
                'active' => request()->routeIs('parent.attendance.*'),
            ])

            @include('partials.school-sidebar.nav-item', [
                'route'  => 'parent.results.index',
                'icon'   => 'fas fa-trophy',
                'label'  => 'Results',
                'active' => request()->routeIs('parent.results.*'),
            ])

            @include('partials.school-sidebar.nav-item', [
                'route'  => 'parent.fees.index',
                'icon'   => 'fas fa-receipt',
                'label'  => 'Fee Details',
                'active' => request()->routeIs('parent.fees.*'),
            ])

            @include('partials.school-sidebar.section-label', ['label' => 'Account'])

            @include('partials.school-sidebar.nav-item', [
                'route'  => 'parent.profile.show',
                'icon'   => 'fas fa-user-circle',
                'label'  => 'My Profile',
                'active' => request()->routeIs('parent.profile.*'),
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
