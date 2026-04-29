{{--
    Usage:
    @include('partials.school-sidebar.nav-item', [
        'route'  => 'school.dashboard',
        'icon'   => 'fas fa-tachometer-alt',
        'label'  => 'Dashboard',
        'active' => request()->routeIs('school.dashboard'),
    ])
--}}
@php $isActive = $active ?? false; @endphp
<li>
    <a href="{{ route($route) }}"
        title="{{ $label }}"
        class="group flex items-center px-3 py-2 rounded-lg text-sm transition-colors duration-150 relative
               {{ $isActive
                    ? 'bg-[#283593] text-white font-medium'
                    : 'text-indigo-100 hover:bg-[#283593] hover:text-white' }}"
        :class="{ 'justify-center': sidebarCollapsed }">

        {{-- Active left-border accent --}}
        @if($isActive)
            <span class="absolute left-0 top-1 bottom-1 w-0.5 bg-teal-400 rounded-full"></span>
        @endif

        <i class="{{ $icon }} w-5 flex-shrink-0 text-center" :class="{ 'mr-3': !sidebarCollapsed }"></i>
        <span x-show="!sidebarCollapsed" class="sidebar-text truncate">{{ $label }}</span>

        {{-- Tooltip shown only when collapsed --}}
        <span x-show="sidebarCollapsed && !isMobile"
            class="absolute left-full ml-3 px-2 py-1 bg-gray-900 text-white text-xs rounded shadow-lg whitespace-nowrap opacity-0 group-hover:opacity-100 pointer-events-none transition-opacity z-[80]"
            style="display:none">
            {{ $label }}
        </span>
    </a>
</li>
