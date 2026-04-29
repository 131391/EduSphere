{{--
    Usage:
    @include('partials.school-sidebar.nav-group', [
        'icon'    => 'fas fa-user-plus',
        'label'   => 'Admission',
        'isOpen'  => $activeStudent,
        'groupId' => 'student',
        'items'   => [
            ['route' => 'school.student-enquiries.index', 'label' => 'Enquiry'],
        ],
    ])
--}}
@php
    $isOpen  = $isOpen  ?? false;
    $items   = $items   ?? [];
    $groupId = $groupId ?? 'group';

    // Is any child active?
    $anyActive = false;
    foreach ($items as $item) {
        try {
            if (request()->routeIs(rtrim($item['route'], '.index') . '*')) {
                $anyActive = true;
                break;
            }
        } catch (\Throwable $e) {}
    }
    $isOpen = $isOpen || $anyActive;
@endphp

<li x-data="{
        open: {{ $isOpen ? 'true' : 'false' }},
        flyoutStyle: '',
        syncFlyout() {
            if (!this.sidebarCollapsed || !this.open || !this.$refs.trigger) return;
            const rect = this.$refs.trigger.getBoundingClientRect();
            const menuH = {{ count($items) }} * 40 + 48;
            const top = Math.max(8, Math.min(rect.top, window.innerHeight - menuH - 8));
            this.flyoutStyle = 'top:' + top + 'px;left:' + (rect.right + 4) + 'px';
        },
        toggle() {
            this.open = !this.open;
            if (this.sidebarCollapsed && this.open) this.$nextTick(() => this.syncFlyout());
        },
        close() { if (this.sidebarCollapsed) this.open = false; }
    }"
    class="relative"
    @resize.window="syncFlyout()"
    @scroll.window="syncFlyout()"
    @keydown.escape.window="close()">

    {{-- ── Trigger button ──────────────────────────────────────────────── --}}
    <button x-ref="trigger" @click="toggle()" title="{{ $label }}"
        class="group w-full flex items-center justify-between px-3 py-2 rounded-lg text-sm transition-colors duration-150 relative
               {{ $anyActive ? 'text-teal-300 font-medium' : 'text-indigo-100 hover:bg-white/10 hover:text-white' }}"
        :class="{ 'justify-center': sidebarCollapsed }">

        @if($anyActive)
            <span class="absolute left-0 top-1 bottom-1 w-0.5 bg-teal-400 rounded-full"></span>
        @endif

        <div class="flex items-center min-w-0">
            <i class="{{ $icon }} w-5 flex-shrink-0 text-center {{ $anyActive ? 'text-teal-400' : '' }}" :class="{ 'mr-3': !sidebarCollapsed }"></i>
            <span x-show="!sidebarCollapsed" class="sidebar-text truncate">{{ $label }}</span>
        </div>
        <i class="fas fa-chevron-down text-xs flex-shrink-0 transition-transform duration-200 ml-1"
            :class="{ 'rotate-180': open }"
            x-show="!sidebarCollapsed"></i>

        {{-- Collapsed tooltip --}}
        <span x-show="sidebarCollapsed && !isMobile"
            class="absolute left-full ml-3 px-2 py-1 bg-gray-900 text-white text-xs rounded shadow-lg whitespace-nowrap opacity-0 group-hover:opacity-100 pointer-events-none transition-opacity z-[80]"
            style="display:none">
            {{ $label }}
        </span>
    </button>

    {{-- ── Expanded submenu ────────────────────────────────────────────── --}}
    <ul x-show="!sidebarCollapsed && open"
        x-collapse
        class="mt-0.5 space-y-0.5 pl-3"
        @if(!$isOpen) x-cloak @endif>
        @foreach($items as $item)
            @php
                $childActive = false;
                try {
                    $childActive = request()->routeIs(rtrim($item['route'], '.index') . '*');
                } catch (\Throwable $e) {}
            @endphp
            <li>
                <a href="{{ route($item['route']) }}"
                    class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-sm transition-colors duration-150 relative
                           {{ $childActive
                                ? 'text-teal-300 font-medium'
                                : 'text-indigo-300 hover:text-white hover:bg-white/10' }}">
                    @if($childActive)
                        <span class="absolute left-0 top-1 bottom-1 w-0.5 bg-teal-400 rounded-full"></span>
                    @endif
                    <span class="w-1.5 h-1.5 rounded-full flex-shrink-0 {{ $childActive ? 'bg-teal-400' : 'bg-indigo-500' }}"></span>
                    <span class="truncate">{{ $item['label'] }}</span>
                </a>
            </li>
        @endforeach
    </ul>

    {{-- ── Collapsed flyout (teleported to body) ──────────────────────── --}}
    <template x-teleport="body">
        <div x-show="sidebarCollapsed && open"
            x-cloak
            @click.outside="close()"
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 -translate-x-1"
            x-transition:enter-end="opacity-100 translate-x-0"
            x-transition:leave="transition ease-in duration-100"
            x-transition:leave-start="opacity-100 translate-x-0"
            x-transition:leave-end="opacity-0 -translate-x-1"
            class="fixed w-52 bg-[#1a237e] rounded-lg shadow-2xl border border-[#283593] z-[80] overflow-hidden"
            :style="flyoutStyle"
            style="display:none">
            <div class="px-4 py-2.5 bg-[#283593] text-xs font-bold text-white tracking-wide uppercase">
                {{ $label }}
            </div>
            <ul class="p-1.5 space-y-0.5">
                @foreach($items as $item)
                    @php
                        $childActive = false;
                        try {
                            $childActive = request()->routeIs(rtrim($item['route'], '.index') . '*');
                        } catch (\Throwable $e) {}
                    @endphp
                    <li>
                        <a href="{{ route($item['route']) }}"
                            class="flex items-center gap-2 px-3 py-2 rounded-md text-sm transition-colors duration-150 relative
                                   {{ $childActive
                                        ? 'text-teal-300 font-medium'
                                        : 'text-indigo-300 hover:text-white hover:bg-white/10' }}">
                            @if($childActive)
                                <span class="absolute left-0 top-1 bottom-1 w-0.5 bg-teal-400 rounded-full"></span>
                            @endif
                            <span class="w-1.5 h-1.5 rounded-full flex-shrink-0 {{ $childActive ? 'bg-teal-400' : 'bg-indigo-500' }}"></span>
                            {{ $item['label'] }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    </template>
</li>
