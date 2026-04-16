@props([
    'message' => 'Loading...',
    'showVar' => 'showSpinner',
])

<div x-show="{{ $showVar }}" x-cloak
     x-transition:enter="transition ease-out duration-150"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-100"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="absolute inset-0 bg-white/70 z-10 flex items-center justify-center min-h-[200px]">
    <div class="flex flex-col items-center">
        <div class="w-8 h-8 rounded-full border-4 border-blue-200 border-t-blue-600 animate-spin"></div>
        <p class="mt-2 text-sm text-gray-500 font-medium">{{ $message }}</p>
    </div>
</div>
