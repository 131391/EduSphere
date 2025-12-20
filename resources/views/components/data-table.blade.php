@props([
    'columns' => [],
    'data' => null,
    'searchable' => true,
    'filterable' => false,
    'filters' => [],
    'actions' => [],
    'emptyMessage' => 'No records found',
    'emptyIcon' => 'fas fa-inbox',
    'route' => null,
    'routeParams' => [],
])

@php
    $currentSort = request('sort', 'id');
    $currentDirection = request('direction', 'asc');
    $currentSearch = request('search', '');
    $currentPage = request('page', 1);
    $perPage = request('per_page', 15);
@endphp

<div class="bg-white rounded-lg shadow overflow-hidden" x-data="dataTable" x-cloak>
    <!-- Table Header with Search and Filters -->
    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <!-- Left: Title and Search -->
            <div class="flex-1 flex flex-col md:flex-row md:items-center gap-4">
                <h2 class="text-lg font-semibold text-gray-800">{{ $slot ?? 'Data Table' }}</h2>
                
                @if($searchable)
                <div class="relative flex-1 max-w-md">
                    <input 
                        type="text" 
                        x-model="searchQuery"
                        @input.debounce.500ms="handleSearch()"
                        @keyup.enter="handleSearch()"
                        placeholder="Search..." 
                        class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        value="{{ $currentSearch }}"
                    >
                    <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                </div>
                @endif
            </div>

            <!-- Right: Filters and Actions -->
            <div class="flex items-center gap-3">
                @if($filterable && count($filters) > 0)
                <div class="flex items-center gap-2">
                    @foreach($filters as $filter)
                    <select 
                        x-model="filters['{{ $filter['name'] }}']"
                        @change="applyFilters()"
                        class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                    >
                        <option value="">{{ $filter['label'] ?? ucfirst($filter['name']) }}</option>
                        @foreach($filter['options'] as $value => $label)
                        <option value="{{ $value }}" {{ request($filter['name']) == $value ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                        @endforeach
                    </select>
                    @endforeach
                </div>
                @endif

                <!-- Per Page Selector -->
                <select 
                    x-model="perPage"
                    @change="changePerPage()"
                    class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                >
                    <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10</option>
                    <option value="15" {{ $perPage == 15 ? 'selected' : '' }}>15</option>
                    <option value="25" {{ $perPage == 25 ? 'selected' : '' }}>25</option>
                    <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100</option>
                </select>

                <!-- Export Button (Optional) -->
                <button 
                    @click="exportData()"
                    class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 text-sm flex items-center"
                    title="Export"
                >
                    <i class="fas fa-download mr-2"></i>
                    Export
                </button>
            </div>
        </div>

        <!-- Active Filters Display -->
        @if($filterable && count($filters) > 0)
        <div class="mt-3 flex flex-wrap gap-2" x-show="hasActiveFilters()">
            <template x-for="(value, key) in filters" :key="key">
                <div x-show="value" class="flex items-center gap-1 bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-xs">
                    <span x-text="getFilterLabel(key, value)"></span>
                    <button @click="removeFilter(key)" class="ml-1 hover:text-blue-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </template>
        </div>
        @endif
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    @foreach($columns as $column)
                    <th 
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider @if(isset($column['sortable']) && $column['sortable']) cursor-pointer hover:bg-gray-100 @endif"
                        @if(isset($column['sortable']) && $column['sortable'])
                        @click.prevent.stop="sort('{{ $column['key'] }}')"
                        @endif
                    >
                        <div class="flex items-center gap-2">
                            <span>{{ $column['label'] ?? ucfirst($column['key']) }}</span>
                            @if(isset($column['sortable']) && $column['sortable'])
                            <div class="flex flex-col">
                                <i 
                                    class="fas fa-sort-up text-xs"
                                    :class="{
                                        'text-blue-600': currentSort === '{{ $column['key'] }}' && currentDirection === 'asc',
                                        'text-gray-300': currentSort !== '{{ $column['key'] }}' || currentDirection !== 'asc'
                                    }"
                                ></i>
                                <i 
                                    class="fas fa-sort-down text-xs -mt-1"
                                    :class="{
                                        'text-blue-600': currentSort === '{{ $column['key'] }}' && currentDirection === 'desc',
                                        'text-gray-300': currentSort !== '{{ $column['key'] }}' || currentDirection !== 'desc'
                                    }"
                                ></i>
                            </div>
                            @endif
                        </div>
                    </th>
                    @endforeach
                    @if(count($actions) > 0)
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    @endif
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @if($data && $data->count() > 0)
                    @foreach($data as $row)
                    <tr class="hover:bg-gray-50 transition-colors" @click.self.stop>
                        @foreach($columns as $column)
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            @if(isset($column['render']))
                                {!! $column['render']($row) !!}
                            @elseif(isset($column['component']))
                                @include($column['component'], ['row' => $row, 'column' => $column])
                            @else
                                {{ $row->{$column['key']} ?? '-' }}
                            @endif
                        </td>
                        @endforeach
                        @if(count($actions) > 0)
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex items-center space-x-2">
                                @foreach($actions as $action)
                                    @if(isset($action['condition']) && !$action['condition']($row))
                                        @continue
                                    @endif
                                    @if($action['type'] === 'link')
                                    <a 
                                        href="{{ $action['url']($row) }}" 
                                        class="{{ $action['class'] ?? 'text-blue-600 hover:text-blue-900' }}"
                                        title="{{ $action['title'] ?? '' }}"
                                        onclick="event.stopPropagation();"
                                    >
                                        <i class="{{ $action['icon'] ?? 'fas fa-eye' }}"></i>
                                    </a>
                                    @elseif($action['type'] === 'button')
                                    <button 
                                        @if(isset($action['onclick']))
                                        onclick="event.stopPropagation(); {{ $action['onclick']($row) }}"
                                        @elseif(isset($action['onClick']))
                                        onclick="event.stopPropagation();" @click="{{ $action['onClick'] }}"
                                        @endif
                                        class="{{ $action['class'] ?? 'text-blue-600 hover:text-blue-900' }}"
                                        title="{{ $action['title'] ?? '' }}"
                                        type="button"
                                    >
                                        <i class="{{ $action['icon'] ?? 'fas fa-edit' }}"></i>
                                    </button>
                                    @elseif($action['type'] === 'form')
                                    <form action="{{ $action['url']($row) }}" method="POST" class="inline" onsubmit="return confirm('{{ $action['confirm'] ?? 'Are you sure?' }}');" onclick="event.stopPropagation();">
                                        @csrf
                                        @method($action['method'] ?? 'DELETE')
                                        <button type="submit" class="{{ $action['class'] ?? 'text-red-600 hover:text-red-900' }}" title="{{ $action['title'] ?? '' }}">
                                            <i class="{{ $action['icon'] ?? 'fas fa-trash' }}"></i>
                                        </button>
                                    </form>
                                    @endif
                                @endforeach
                            </div>
                        </td>
                        @endif
                    </tr>
                    @endforeach
                @else
                <tr>
                    <td colspan="{{ count($columns) + (count($actions) > 0 ? 1 : 0) }}" class="px-6 py-12 text-center">
                        <div class="flex flex-col items-center">
                            <i class="{{ $emptyIcon }} text-4xl text-gray-300 mb-4"></i>
                            <p class="text-lg text-gray-500">{{ $emptyMessage }}</p>
                        </div>
                    </td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>

    <!-- Pagination and Info -->
    @if($data && $data->hasPages())
    <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <!-- Info -->
            <div class="text-sm text-gray-700">
                Showing 
                <span class="font-medium">{{ $data->firstItem() }}</span>
                to 
                <span class="font-medium">{{ $data->lastItem() }}</span>
                of 
                <span class="font-medium">{{ $data->total() }}</span>
                results
            </div>

            <!-- Pagination Links -->
            <div>
                {{ $data->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
    @elseif($data)
    <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
        <div class="text-sm text-gray-700">
            Showing 
            <span class="font-medium">{{ $data->count() }}</span>
            result(s)
        </div>
    </div>
    @endif
</div>

@push('scripts')
<script>
(function() {
    // Initialize immediately if Alpine is already loaded, otherwise wait for it
    function initDataTable() {
        if (typeof Alpine === 'undefined') {
            // Wait for Alpine to load
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initDataTable);
            } else {
                // Check again after a short delay
                setTimeout(initDataTable, 100);
            }
            return;
        }
        
        Alpine.data('dataTable', () => ({
        searchQuery: '{{ $currentSearch }}',
        currentSort: '{{ $currentSort }}',
        currentDirection: '{{ $currentDirection }}',
        perPage: {{ $perPage }},
        filters: {
            @foreach($filters as $filter)
            '{{ $filter['name'] }}': '{{ request($filter['name'], '') }}',
            @endforeach
        },
        filterLabels: {
            @foreach($filters as $filter)
            '{{ $filter['name'] }}': {
                @foreach($filter['options'] as $value => $label)
                '{{ $value }}': '{{ $label }}',
                @endforeach
            },
            @endforeach
        },
        
        handleSearch() {
            this.submitWithParams({ search: this.searchQuery, page: 1 });
        },
        
        sort(column) {
            if (this.currentSort === column) {
                this.currentDirection = this.currentDirection === 'asc' ? 'desc' : 'asc';
            } else {
                this.currentSort = column;
                this.currentDirection = 'asc';
            }
            this.submitWithParams({ 
                sort: this.currentSort, 
                direction: this.currentDirection,
                page: 1 
            });
        },
        
        applyFilters() {
            const params = { page: 1 };
            Object.keys(this.filters).forEach(key => {
                if (this.filters[key]) {
                    params[key] = this.filters[key];
                }
            });
            this.submitWithParams(params);
        },
        
        removeFilter(key) {
            this.filters[key] = '';
            this.applyFilters();
        },
        
        changePerPage() {
            this.submitWithParams({ per_page: this.perPage, page: 1 });
        },
        
        hasActiveFilters() {
            return Object.values(this.filters).some(value => value !== '');
        },
        
        getFilterLabel(key, value) {
            if (this.filterLabels[key] && this.filterLabels[key][value]) {
                return `${key === 'status' ? 'Status' : (key === 'subscription_status' ? 'Subscription' : key)}: ${this.filterLabels[key][value]}`;
            }
            return `${key}: ${value}`;
        },
        
        submitWithParams(params) {
            // Simply reload the page without query parameters in URL
            // Store parameters in sessionStorage and retrieve on page load
            const currentParams = new URLSearchParams(window.location.search);
            
            // Merge current params with new params
            currentParams.forEach((value, key) => {
                if (!params.hasOwnProperty(key)) {
                    params[key] = value;
                }
            });
            
            // Remove empty params
            Object.keys(params).forEach(key => {
                if (params[key] === null || params[key] === undefined || params[key] === '') {
                    delete params[key];
                }
            });
            
            // Build query string
            const queryString = new URLSearchParams(params).toString();
            const newUrl = window.location.pathname + (queryString ? '?' + queryString : '');
            
            // Use replaceState to avoid adding to history, then reload
            window.location.replace(newUrl);
        },

        
        exportData() {
            const form = document.createElement('form');
            form.method = 'GET';
            form.action = window.location.pathname;
            
            // Add all current parameters
            const currentParams = new URLSearchParams(window.location.search);
            currentParams.forEach((value, key) => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = key;
                input.value = value;
                form.appendChild(input);
            });
            
            // Add export parameter
            const exportInput = document.createElement('input');
            exportInput.type = 'hidden';
            exportInput.name = 'export';
            exportInput.value = 'csv';
            form.appendChild(exportInput);
            
            document.body.appendChild(form);
            form.submit();
        }
    }));
    }
    
    // Try to initialize
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initDataTable);
    } else {
        initDataTable();
    }
    
    // Also listen for alpine:init as a fallback
    document.addEventListener('alpine:init', initDataTable);
})();
</script>
@endpush

