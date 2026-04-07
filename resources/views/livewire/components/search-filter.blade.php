<div class="space-y-4 rounded-lg border border-gray-200 bg-white p-4">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
        <div class="flex-1">
            <input 
                type="text"
                wire:model.live="search"
                placeholder="Search..."
                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm placeholder-gray-400 focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
            >
        </div>
        <select 
            wire:model.live="sortBy"
            class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
        >
            <option value="name">Sort by Name</option>
            <option value="date">Sort by Date</option>
            <option value="status">Sort by Status</option>
        </select>
        <button 
            wire:click="setSortBy('{{ $sortBy }}')"
            class="inline-flex items-center gap-2 rounded-lg bg-gray-100 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200"
        >
            <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
        </button>
    </div>

    @if($this->results->count())
        <div class="space-y-2">
            @foreach($this->results as $item)
                <div class="rounded-lg border border-gray-100 p-3 hover:bg-gray-50">
                    {{ $item }}
                </div>
            @endforeach
        </div>
    @else
        <div class="rounded-lg bg-gray-50 p-4 text-center text-sm text-gray-600">
            No results found
        </div>
    @endif
</div>
