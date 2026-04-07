<div 
    @class([
        'rounded-lg border px-4 py-3',
        'bg-blue-50 border-blue-200 text-blue-800' => $type === 'info',
        'bg-green-50 border-green-200 text-green-800' => $type === 'success',
        'bg-yellow-50 border-yellow-200 text-yellow-800' => $type === 'warning',
        'bg-red-50 border-red-200 text-red-800' => $type === 'error',
    ])
    role="alert"
>
    <div class="flex items-center justify-between gap-3">
        <p class="text-sm">{{ $message }}</p>
        @if($dismissible)
            <button 
                wire:click="dismiss"
                class="ml-2 text-gray-400 hover:text-gray-600 transition"
                aria-label="Dismiss alert"
            >
                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
            </button>
        @endif
    </div>
</div>
