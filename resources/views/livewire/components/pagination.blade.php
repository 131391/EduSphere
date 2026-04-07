<div class="flex items-center justify-between border-t border-gray-200 bg-gray-50 px-6 py-4">
    <!-- Info text -->
    <div class="text-sm text-gray-600">
        Showing page <span class="font-medium">{{ $current }}</span> of <span class="font-medium">{{ $this->totalPages() }}</span>
    </div>

    <!-- Pagination controls -->
    <div class="flex gap-2">
        <!-- Previous button -->
        <button 
            wire:click="goTo({{ max($current - 1, 1) }})"
            @disabled($current == 1)
            class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition"
        >
            Previous
        </button>

        <!-- Page numbers -->
        @foreach($this->pages() as $page)
            @if($page === '...')
                <span class="px-3 py-2 text-gray-500">...</span>
            @else
                <button 
                    wire:click="goTo({{ $page }})"
                    @class([
                        'px-3 py-2 text-sm font-medium rounded-md transition',
                        'bg-blue-600 text-white' => $page == $current,
                        'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50' => $page != $current,
                    ])
                >
                    {{ $page }}
                </button>
            @endif
        @endforeach

        <!-- Next button -->
        <button 
            wire:click="goTo({{ min($current + 1, $this->totalPages()) }})"
            @disabled($current == $this->totalPages())
            class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition"
        >
            Next
        </button>
    </div>
</div>
