{{-- Usage: @include('admin.schools.partials._detail_row', ['icon' => 'fa-school', 'label' => 'Name', 'value' => $value]) --}}
<div class="flex items-start gap-4 p-1 group">
    <div class="w-9 h-9 rounded-xl bg-gray-50 dark:bg-gray-700/50 flex items-center justify-center shrink-0 border border-gray-100 dark:border-gray-600 transition-colors group-hover:border-indigo-200 dark:group-hover:border-indigo-800">
        <i class="fas {{ $icon }} text-xs text-gray-400 dark:text-gray-500 group-hover:text-indigo-500 transition-colors"></i>
    </div>
    <div class="min-w-0">
        <p class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-1">{{ $label }}</p>
        <p class="text-sm font-bold text-gray-800 dark:text-gray-200 break-words leading-tight">{{ $value ?: '—' }}</p>
    </div>
</div>
