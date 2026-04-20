@props([
    'pagination' => 'pagination',
    'pages' => 'paginationPages',
    'showing' => 'showingText',
    'action' => 'changePage',
    'initial' => null,
])

@php
    $hasInitial    = is_array($initial) && ($initial['total'] ?? 0) > 0;
    $initialText   = $hasInitial
        ? "Showing {$initial['from']} to {$initial['to']} of {$initial['total']} results"
        : '';
@endphp

<div x-show="{{ $pagination }}.last_page > 1 || {{ $pagination }}.total > 0" @unless($hasInitial) x-cloak @endunless
     class="px-6 py-4 border-t border-gray-200 bg-gray-50">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <!-- Info -->
        <div class="text-sm text-gray-700" x-text="{{ $showing }}">{{ $initialText }}</div>

        <!-- Pagination Links -->
        <div x-show="{{ $pagination }}.last_page > 1" class="flex items-center gap-1">
            <!-- Previous -->
            <button @click="{{ $action }}({{ $pagination }}.current_page - 1)"
                    :disabled="{{ $pagination }}.current_page === 1"
                    class="px-3 py-1.5 text-sm border rounded-lg hover:bg-gray-100 disabled:opacity-40 disabled:cursor-not-allowed transition-colors">
                <i class="fas fa-chevron-left text-xs"></i>
            </button>

            <!-- Page Numbers -->
            <template x-for="(pg, idx) in {{ $pages }}" :key="'pg-'+idx">
                <span>
                    <template x-if="pg === '...'">
                        <span class="px-3 py-1.5 text-sm text-gray-400">...</span>
                    </template>
                    <template x-if="pg !== '...'">
                        <button @click="{{ $action }}(pg)"
                                class="px-3 py-1.5 text-sm border rounded-lg transition-colors"
                                :class="pg === {{ $pagination }}.current_page 
                                    ? 'bg-blue-600 text-white border-blue-600' 
                                    : 'hover:bg-gray-100 text-gray-700'">
                            <span x-text="pg"></span>
                        </button>
                    </template>
                </span>
            </template>

            <!-- Next -->
            <button @click="{{ $action }}({{ $pagination }}.current_page + 1)"
                    :disabled="{{ $pagination }}.current_page === {{ $pagination }}.last_page"
                    class="px-3 py-1.5 text-sm border rounded-lg hover:bg-gray-100 disabled:opacity-40 disabled:cursor-not-allowed transition-colors">
                <i class="fas fa-chevron-right text-xs"></i>
            </button>
        </div>
    </div>
</div>
