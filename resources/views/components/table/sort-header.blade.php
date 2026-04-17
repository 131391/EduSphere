@props([
    'column',
    'label',
    'sortVar' => 'sort',
    'directionVar' => 'direction',
])

<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100"
    @click="applySort('{{ $column }}')">
    <div class="flex items-center gap-2 group">
        <span>{{ $label }}</span>
        <div class="flex flex-col items-center justify-center w-3 h-4 opacity-50 group-hover:opacity-100 transition-opacity">
            <i class="fas fa-sort-up text-[10px] leading-[0] mb-0.5 text-gray-300"
               :class="{{ $sortVar }} === '{{ $column }}' && {{ $directionVar }} === 'asc' ? 'text-blue-600 !opacity-100' : 'text-gray-300'"></i>
            <i class="fas fa-sort-down text-[10px] leading-[0] text-gray-300"
               :class="{{ $sortVar }} === '{{ $column }}' && {{ $directionVar }} === 'desc' ? 'text-blue-600 !opacity-100' : 'text-gray-300'"></i>
        </div>
    </div>
</th>
