{{-- Usage: @include('partials.school-sidebar.section-label', ['label' => 'Main']) --}}
<li class="pt-3 pb-1 sidebar-text" x-show="!sidebarCollapsed">
    <p class="px-3 text-[10px] font-bold tracking-widest text-blue-300 uppercase">{{ $label }}</p>
</li>
<li class="sidebar-text" x-show="sidebarCollapsed && !isMobile" style="display:none">
    <div class="mx-3 my-2 border-t border-[#283593]"></div>
</li>
