@props([
    'label',
    'value',
    'icon',
    'color' => 'blue',
    'alpineText' => null,
])

@php
    $colorMap = [
        'blue'   => ['border' => 'border-blue-500',   'bg' => 'bg-blue-100 dark:bg-blue-900/20',   'icon' => 'text-blue-600 dark:text-blue-400',   'value' => ''],
        'green'  => ['border' => 'border-green-500',  'bg' => 'bg-green-100 dark:bg-green-900/20',  'icon' => 'text-green-600 dark:text-green-400',  'value' => 'text-green-600'],
        'red'    => ['border' => 'border-red-500',    'bg' => 'bg-red-100 dark:bg-red-900/20',    'icon' => 'text-red-600 dark:text-red-400',    'value' => 'text-red-600'],
        'amber'  => ['border' => 'border-amber-500',  'bg' => 'bg-amber-100 dark:bg-amber-900/20',  'icon' => 'text-amber-600 dark:text-amber-400',  'value' => 'text-amber-600'],
        'emerald'=> ['border' => 'border-emerald-500','bg' => 'bg-emerald-100 dark:bg-emerald-900/20','icon' => 'text-emerald-600 dark:text-emerald-400','value' => 'text-emerald-600'],
        'gray'   => ['border' => 'border-gray-400',   'bg' => 'bg-gray-100 dark:bg-gray-700',     'icon' => 'text-gray-600 dark:text-gray-400',   'value' => 'text-gray-500'],
        'rose'   => ['border' => 'border-rose-500',   'bg' => 'bg-rose-100 dark:bg-rose-900/20',   'icon' => 'text-rose-600 dark:text-rose-400',   'value' => 'text-rose-600'],
        'indigo' => ['border' => 'border-indigo-500', 'bg' => 'bg-indigo-100 dark:bg-indigo-900/20', 'icon' => 'text-indigo-600 dark:text-indigo-400', 'value' => 'text-indigo-600'],
    ];
    $c = $colorMap[$color] ?? $colorMap['blue'];
@endphp

<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 border-t-4 {{ $c['border'] }} transition-all duration-300 hover:shadow-md">
    <div class="flex items-center justify-between">
        <div>
            <p class="text-[13px] font-medium text-gray-600 dark:text-gray-400">{{ $label }}</p>
            <h3 class="text-2xl font-bold text-gray-900 dark:text-white mt-1 leading-none"
                @if($alpineText) x-text="{{ $alpineText }}" @endif
            ><span class="{{ $c['value'] }}">{{ $value }}</span></h3>
        </div>
        <div class="w-10 h-10 {{ $c['bg'] }} rounded-full flex items-center justify-center transition-transform duration-300 hover:scale-110">
            <i class="{{ $icon }} {{ $c['icon'] }} text-lg"></i>
        </div>
    </div>
</div>
