@props([
    'message' => 'Loading...',
    'showVar' => 'showSpinner',
])

{{-- Overlay shown during AJAX fetch. Only shown when rows exist (avoids
     layering a white wash over the empty-state icon). The wrapper already
     has min-height:260px so it never collapses while loading. --}}
<div x-show="{{ $showVar }} && rows.length > 0" x-cloak
     x-transition:enter="transition ease-out duration-150"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-100"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="absolute inset-0 bg-white/60 dark:bg-gray-800/60 backdrop-blur-[1px] z-10 flex items-center justify-center"
     style="min-height:260px">
    <div class="flex flex-col items-center gap-2">
        <div class="w-7 h-7 rounded-full border-[3px] border-indigo-200 dark:border-indigo-900 border-t-indigo-600 animate-spin"></div>
        <p class="text-xs text-gray-500 dark:text-gray-400 font-semibold tracking-wide">{{ $message }}</p>
    </div>
</div>
