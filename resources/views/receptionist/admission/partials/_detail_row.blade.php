{{-- Usage: @include('receptionist.admission.partials._detail_row', ['icon' => 'fa-user', 'label' => 'Name', 'value' => $value]) --}}
<div class="flex items-start gap-3">
    <div class="w-7 h-7 rounded-lg bg-gray-100 dark:bg-gray-700 flex items-center justify-center shrink-0 mt-0.5">
        <i class="fas {{ $icon }} text-[10px] text-gray-400"></i>
    </div>
    <div class="min-w-0">
        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide leading-none mb-1">{{ $label }}</p>
        <p class="text-sm font-medium text-gray-800 dark:text-gray-200 break-words">{{ $value ?: '—' }}</p>
    </div>
</div>
