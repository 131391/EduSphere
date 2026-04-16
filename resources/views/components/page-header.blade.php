@props([
    'title',
    'description' => '',
    'icon' => 'fas fa-list',
])

<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 mb-6 border border-blue-100/50 dark:border-gray-700">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-gray-800 dark:text-white flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center text-blue-600">
                    <i class="{{ $icon }} text-xs"></i>
                </div>
                {{ $title }}
            </h2>
            @if($description)
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $description }}</p>
            @endif
        </div>
        @if($slot->isNotEmpty())
            <div class="flex flex-wrap gap-2">
                {{ $slot }}
            </div>
        @endif
    </div>
</div>
