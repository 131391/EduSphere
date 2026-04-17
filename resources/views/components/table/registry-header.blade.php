@props([
    'title' => 'Registry List',
    'icon' => 'fas fa-list',
    'searchModel' => 'search',
    'perPageModel' => 'perPage',
    'perPageAction' => 'changePerPage($event.target.value)',
    'filterToggle' => 'searchOpen = !searchOpen',
    'searchPlaceholder' => 'Search records...',
    'showFilters' => true,
    'showPerPage' => true,
    'showSearch' => true,
    'defaultPerPage' => null,
])

<div {{ $attributes->merge(['class' => 'bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 mb-6 overflow-hidden']) }}>
    <div class="p-4 border-b border-gray-50 dark:border-gray-700/50">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <!-- Left: Title & Search -->
            <div class="flex items-center gap-6 flex-1">
                <div class="flex items-center gap-3 shrink-0">
                    <div class="w-8 h-8 rounded-lg bg-teal-50 dark:bg-teal-900/30 flex items-center justify-center text-teal-600 dark:text-teal-400">
                        <i class="{{ $icon }} text-xs"></i>
                    </div>
                    <h2 class="text-sm font-bold text-gray-800 dark:text-white uppercase tracking-wider">{{ $title }}</h2>
                </div>
                
                @if($showSearch)
                <div class="relative flex-1 max-w-md group">
                    <input type="text" x-model="{{ $searchModel }}" placeholder="{{ $searchPlaceholder }}"
                        class="w-full h-10 pl-10 pr-4 bg-gray-50 dark:bg-gray-700/50 border-gray-100 dark:border-gray-600 rounded-xl text-xs text-gray-700 dark:text-gray-200 focus:ring-4 focus:ring-teal-500/10 focus:border-teal-500 transition-all outline-none">
                    <div class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-teal-500 transition-colors">
                        <i class="fas fa-search text-[10px]"></i>
                    </div>
                </div>
                @endif
            </div>

            <!-- Right: Tools & Actions -->
            <div class="flex items-center gap-2">
                @if($showPerPage)
                <div class="flex items-center bg-gray-50 dark:bg-gray-700/50 rounded-xl px-3 border border-gray-100 dark:border-gray-600 mr-2">
                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mr-2">Rows</span>
                    <div class="w-16">
                        <x-table.per-page model="{{ $perPageModel }}" action="{{ $perPageAction }}" :default="$defaultPerPage" />
                    </div>
                </div>
                @endif

                <div class="flex items-center gap-1.5 {{ ($showPerPage || $showSearch) ? 'border-l border-gray-100 dark:border-gray-600 pl-4 ml-2' : '' }}">
                    {{ $slot }}

                    @if($showFilters)
                    <button type="button" @click="{{ $filterToggle }}"
                        class="px-4 h-9 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 text-gray-700 dark:text-gray-200 text-[10px] font-bold uppercase tracking-widest rounded-xl hover:bg-gray-50 transition-all shadow-sm flex items-center gap-2">
                        <i class="fas fa-filter text-teal-500"></i>
                        Filters
                    </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Advanced Filters Container Slot (Optional content that appears below the header) -->
    @if(isset($filters))
        {{ $filters }}
    @endif
</div>
