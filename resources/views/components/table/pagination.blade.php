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
    $alpineWrapperShow = $hasInitial
        ? "hydrated && ({$pagination}.last_page > 1 || {$pagination}.total > 0)"
        : "{$pagination}.last_page > 1 || {$pagination}.total > 0";
    $initialPages = [];

    if ($hasInitial) {
        $total = (int) ($initial['last_page'] ?? 1);
        $current = (int) ($initial['current_page'] ?? 1);

        if ($total <= 7) {
            $initialPages = range(1, $total);
        } else {
            $initialPages[] = 1;

            if ($current > 3) {
                $initialPages[] = '...';
            }

            $start = max(2, $current - 1);
            $end = min($total - 1, $current + 1);

            for ($i = $start; $i <= $end; $i++) {
                $initialPages[] = $i;
            }

            if ($current < $total - 2) {
                $initialPages[] = '...';
            }

            $initialPages[] = $total;
        }
    }
@endphp

@if($hasInitial)
    <div x-show="!hydrated" class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="text-sm text-gray-600 dark:text-gray-400">{{ $initialText }}</div>

            @if(($initial['last_page'] ?? 1) > 1)
                <div class="flex items-center gap-1 pointer-events-none">
                    <button type="button" @disabled(($initial['current_page'] ?? 1) === 1)
                        class="px-3 py-1.5 text-sm border border-gray-200 dark:border-gray-600 rounded-lg dark:text-gray-300 disabled:opacity-40 disabled:cursor-not-allowed transition-colors">
                        <i class="fas fa-chevron-left text-xs"></i>
                    </button>

                    @foreach($initialPages as $pg)
                        @if($pg === '...')
                            <span class="px-3 py-1.5 text-sm text-gray-400 dark:text-gray-500">...</span>
                        @else
                            <button type="button"
                                class="px-3 py-1.5 text-sm border rounded-lg transition-colors {{ (int) $pg === (int) ($initial['current_page'] ?? 1)
                                    ? 'bg-blue-600 text-white border-blue-600'
                                    : 'border-gray-200 dark:border-gray-600 text-gray-700 dark:text-gray-300' }}">
                                {{ $pg }}
                            </button>
                        @endif
                    @endforeach

                    <button type="button" @disabled(($initial['current_page'] ?? 1) === ($initial['last_page'] ?? 1))
                        class="px-3 py-1.5 text-sm border border-gray-200 dark:border-gray-600 rounded-lg dark:text-gray-300 disabled:opacity-40 disabled:cursor-not-allowed transition-colors">
                        <i class="fas fa-chevron-right text-xs"></i>
                    </button>
                </div>
            @endif
        </div>
    </div>
@endif

<div x-show="{{ $alpineWrapperShow }}" x-cloak
     class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div class="text-sm text-gray-600 dark:text-gray-400" x-text="{{ $showing }}">{{ $initialText }}</div>

        <div x-show="{{ $pagination }}.last_page > 1" class="flex items-center gap-1">
            <button @click="{{ $action }}({{ $pagination }}.current_page - 1)"
                    :disabled="{{ $pagination }}.current_page === 1"
                    class="px-3 py-1.5 text-sm border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 dark:text-gray-300 disabled:opacity-40 disabled:cursor-not-allowed transition-colors">
                <i class="fas fa-chevron-left text-xs"></i>
            </button>

            <template x-for="(pg, idx) in {{ $pages }}" :key="'pg-'+idx">
                <span>
                    <template x-if="pg === '...'">
                        <span class="px-3 py-1.5 text-sm text-gray-400 dark:text-gray-500">...</span>
                    </template>
                    <template x-if="pg !== '...'">
                        <button @click="{{ $action }}(pg)"
                                class="px-3 py-1.5 text-sm border rounded-lg transition-colors"
                                :class="pg === {{ $pagination }}.current_page
                                    ? 'bg-blue-600 text-white border-blue-600'
                                    : 'border-gray-200 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300'">
                            <span x-text="pg"></span>
                        </button>
                    </template>
                </span>
            </template>

            <button @click="{{ $action }}({{ $pagination }}.current_page + 1)"
                    :disabled="{{ $pagination }}.current_page === {{ $pagination }}.last_page"
                    class="px-3 py-1.5 text-sm border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 dark:text-gray-300 disabled:opacity-40 disabled:cursor-not-allowed transition-colors">
                <i class="fas fa-chevron-right text-xs"></i>
            </button>
        </div>
    </div>
</div>
