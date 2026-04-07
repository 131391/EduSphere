<div x-data="{ open: @entangle('isOpen') }">
    <!-- Transparent backdrop -->
    <div 
        x-show="open" 
        class="fixed inset-0 z-40 bg-black/50 transition-opacity duration-300"
        @click="@if($closeOnBackdropClick) open = false @endif"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
    ></div>

    <!-- Modal container -->
    <div 
        x-show="open"
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
    >
        <div class="w-full {{ $this->sizeClass() }} bg-white rounded-lg shadow-xl">
            <!-- Header -->
            <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4">
                <h2 class="text-xl font-semibold text-gray-900">{{ $title }}</h2>
                @if($showCloseButton)
                    <button 
                        wire:click="close"
                        class="text-gray-400 hover:text-gray-600 transition"
                    >
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                @endif
            </div>

            <!-- Body -->
            <div class="px-6 py-4">
                {{ $slot }}
            </div>
        </div>
    </div>
</div>
