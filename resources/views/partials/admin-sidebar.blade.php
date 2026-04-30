{{-- Super Admin Sidebar --}}
@php
    $currentYear = \App\Models\AcademicYear::where('is_current', \App\Enums\YesNo::Yes)->first();
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
                <i class="fas fa-shield-alt text-[#1a237e]" :class="sidebarCollapsed ? 'text-lg' : 'text-2xl'"></i>
            </div>
        </div>
        <div x-show="!sidebarCollapsed" class="sidebar-text text-center">
            <h2 class="text-xs font-bold leading-tight tracking-wide">EDUSPHERE</h2>
            <p class="text-[10px] text-indigo-300 mt-0.5 font-medium uppercase tracking-widest">Super Admin</p>
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
            SESSION: {{ $currentYear?->name ?? '—' }}
        </p>
        <p class="font-semibold text-center" x-show="sidebarCollapsed && !isMobile" style="display:none">
            {{ preg_replace('/^.*?(\d{2})\D*(\d{2})$/', '$1-$2', $currentYear?->name ?? '--') }}
        </p>
    </div>

    {{-- ── Navigation ────────────────────────────────────────────────────── --}}
    <nav class="flex-1 overflow-y-auto py-3 sidebar-scroll">
        <ul class="space-y-0.5 px-2">

            {{-- ════ MAIN ════ --}}
            @include('partials.school-sidebar.section-label', ['label' => 'Main'])

            @include('partials.school-sidebar.nav-item', [
                'route'  => 'admin.dashboard',
                'icon'   => 'fas fa-tachometer-alt',
                'label'  => 'Dashboard',
                'active' => request()->routeIs('admin.dashboard'),
            ])

            {{-- ════ MANAGEMENT ════ --}}
            @include('partials.school-sidebar.section-label', ['label' => 'Management'])

            @include('partials.school-sidebar.nav-item', [
                'route'  => 'admin.schools.index',
                'icon'   => 'fas fa-school',
                'label'  => 'Schools',
                'active' => request()->routeIs('admin.schools.*'),
            ])

            @include('partials.school-sidebar.nav-item', [
                'route'  => 'admin.users.index',
                'icon'   => 'fas fa-users-cog',
                'label'  => 'Users',
                'active' => request()->routeIs('admin.users.*'),
            ])

            @include('partials.school-sidebar.nav-item', [
                'route'  => 'admin.audit-logs.index',
                'icon'   => 'fas fa-clipboard-list',
                'label'  => 'Audit Logs',
                'active' => request()->routeIs('admin.audit-logs.*'),
            ])

            {{-- ════ SYSTEM ════ --}}
            @include('partials.school-sidebar.section-label', ['label' => 'System'])

            @include('partials.school-sidebar.nav-item', [
                'route'  => 'admin.settings.system',
                'icon'   => 'fas fa-cog',
                'label'  => 'System Settings',
                'active' => request()->routeIs('admin.settings.*'),
            ])

            @include('partials.school-sidebar.nav-item', [
                'route'  => 'admin.profile',
                'icon'   => 'fas fa-user-circle',
                'label'  => 'Profile',
                'active' => request()->routeIs('admin.profile'),
            ])

            @include('partials.school-sidebar.nav-item', [
                'route'  => 'admin.change-password',
                'icon'   => 'fas fa-key',
                'label'  => 'Change Password',
                'active' => request()->routeIs('admin.change-password'),
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
