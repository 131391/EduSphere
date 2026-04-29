@extends('layouts.school')

@section('title', 'Fee Collection')

@section('content')
    <div x-data="Object.assign(ajaxDataTable({
        fetchUrl: '{{ route('school.fee-payments.index') }}',
        defaultSort: 'first_name',
        defaultDirection: 'asc',
        defaultPerPage: 25,
        defaultFilters: { class_id: '' },
        initialRows: @js($initialData['rows']),
        initialPagination: @js($initialData['pagination']),
        initialStats: @js($initialData['stats']),
        filterLabels: {
            class_id: { @foreach($classes as $c) '{{ $c->id }}': '{{ $c->name }}', @endforeach }
        }
    }), feeCollectionManager())" class="space-y-6">

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <x-stat-card label="Collected Today" :value="'₹' . $stats['collected_today']" icon="fas fa-cash-register" color="emerald" alpine-text="'₹' + stats.collected_today" />
            <x-stat-card label="Monthly Target" :value="'₹' . $stats['total_collections_month']" icon="fas fa-chart-line" color="indigo" alpine-text="'₹' + stats.total_collections_month" />
            <x-stat-card label="Pending Students" :value="$stats['pending_students']" icon="fas fa-users-slash" color="rose" alpine-text="stats.pending_students" />
            <x-stat-card label="Collection Modes" :value="$stats['mode_distribution']" icon="fas fa-credit-card" color="amber" alpine-text="stats.mode_distribution" />
        </div>

        <!-- Header Section -->
        <x-page-header title="Fee Collection" description="Search and identify students to process academic payments, issue receipts, and manage outstanding dues." icon="fas fa-cash-register"></x-page-header>

        <!-- AJAX Data Table -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <!-- Table Header -->
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div class="flex-1 flex flex-col md:flex-row md:items-center gap-4">
                        <h2 class="text-lg font-semibold text-gray-800 dark:text-white">Collection Portal</h2>
                        <x-table.search placeholder="Search by name, admission no..." />
                    </div>

                    <div class="flex items-center gap-3">
                        <x-table.filter-select
                            model="filters.class_id"
                            action="applyFilter('class_id', $event.target.value)"
                            placeholder="All Classes"
                            :options="$classes->pluck('name', 'id')->toArray()"
                        />
                        <x-table.per-page model="perPage" action="changePerPage($event.target.value)" :default="25" />
                    </div>
                </div>

                <!-- Active Filter Tags -->
                <div class="mt-3 flex flex-wrap gap-2" x-show="hasActiveFilters()" x-cloak>
                    <template x-for="(value, key) in filters" :key="key">
                        <template x-if="value">
                            <div class="flex items-center gap-1 bg-indigo-50 text-indigo-700 border border-indigo-100 px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider">
                                <span x-text="key.replace('_', ' ') + ': ' + getFilterLabel(key, value)"></span>
                                <button @click="removeFilter(key)" class="ml-1 hover:text-indigo-900 transition-colors">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </template>
                    </template>
                    <button @click="clearAllFilters()" class="text-[10px] font-bold text-red-600 hover:text-red-700 uppercase tracking-widest ml-2 transition-colors">
                        Clear All
                    </button>
                </div>
            </div>

            <!-- Table Body -->
            <div class="overflow-x-auto relative ajax-table-wrapper">
                <x-table.loading-overlay />

                <table class="w-full text-left border-collapse">
                    <thead class="bg-gray-50/50 dark:bg-gray-700/50 border-b border-gray-100 dark:border-gray-700">
                        <tr>
                            <x-table.sort-header column="first_name" label="Student Identity" sort-var="sort" direction-var="direction" />
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Placement</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Pending Heads</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Net Balance</th>
                            <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider w-40">Actions</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700" x-show="!hydrated" x-cloak>
                        @foreach($initialData['rows'] as $row)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/50 transition-colors group">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 rounded-full bg-indigo-50 dark:bg-indigo-900/40 flex items-center justify-center text-indigo-600 border border-indigo-100 dark:border-indigo-800 font-bold text-sm ring-4 ring-indigo-50/50">
                                            {{ substr($row['full_name'], 0, 1) }}
                                        </div>
                                        <div>
                                            <div class="text-sm font-bold text-gray-800 dark:text-gray-100">{{ $row['full_name'] }}</div>
                                            <div class="text-[10px] font-bold text-gray-400 tracking-widest uppercase">{{ $row['admission_no'] }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex flex-col">
                                        <span class="text-sm font-bold text-gray-600 dark:text-gray-300">{{ $row['class_name'] }}</span>
                                        <span class="text-[10px] font-medium text-indigo-500 uppercase">{{ $row['section_name'] }} Section</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2.5 py-1 bg-amber-50 dark:bg-amber-900/40 text-amber-700 dark:text-amber-400 text-[10px] font-black rounded-lg uppercase tracking-tight border border-amber-100 dark:border-amber-800/50">
                                        {{ $row['pending_count'] }} Dues
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-rose-700 dark:text-rose-400 font-bold bg-rose-50 dark:bg-rose-900/40 px-3 py-1 rounded-lg inline-block border border-rose-100 dark:border-rose-800">
                                        ₹{{ $row['total_due'] }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <a href="{{ $row['collect_url'] }}"
                                        class="inline-flex items-center px-4 py-2 bg-emerald-50 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-400 text-xs font-black rounded-xl hover:bg-emerald-600 hover:text-white transition-all border border-emerald-100 dark:border-emerald-800 group shadow-sm">
                                        <i class="fas fa-money-bill-transfer mr-2 text-[10px] group-hover:rotate-12 transition-transform"></i>
                                        Collect Fees
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>

                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700 transition-opacity duration-150" x-cloak :class="loading && rows.length &gt; 0 ? 'opacity-50' : 'opacity-100'">
                        <template x-for="row in rows" :key="row.id">
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/50 transition-colors group">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 rounded-full bg-indigo-50 dark:bg-indigo-900/40 flex items-center justify-center text-indigo-600 border border-indigo-100 dark:border-indigo-800 font-bold text-sm ring-4 ring-indigo-50/50" x-text="row.full_name.charAt(0)"></div>
                                        <div>
                                            <div class="text-sm font-bold text-gray-800 dark:text-gray-100" x-text="row.full_name"></div>
                                            <div class="text-[10px] font-bold text-gray-400 tracking-widest uppercase" x-text="row.admission_no"></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex flex-col">
                                        <span class="text-sm font-bold text-gray-600 dark:text-gray-300" x-text="row.class_name"></span>
                                        <span class="text-[10px] font-medium text-indigo-500 uppercase" x-text="row.section_name + ' Section'"></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2.5 py-1 bg-amber-50 dark:bg-amber-900/40 text-amber-700 dark:text-amber-400 text-[10px] font-black rounded-lg uppercase tracking-tight border border-amber-100 dark:border-amber-800/50" x-text="row.pending_count + ' Dues'"></span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap font-bold text-rose-700 dark:text-rose-400">
                                    <div class="bg-rose-50 dark:bg-rose-900/40 px-3 py-1 rounded-lg inline-block border border-rose-100 dark:border-rose-800" x-text="'₹' + row.total_due"></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <a :href="row.collect_url"
                                        class="inline-flex items-center px-4 py-2 bg-emerald-50 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-400 text-xs font-black rounded-xl hover:bg-emerald-600 hover:text-white transition-all border border-emerald-100 dark:border-emerald-800 group shadow-sm">
                                        <i class="fas fa-money-bill-transfer mr-2 text-[10px] group-hover:rotate-12 transition-transform"></i>
                                        Collect Fees
                                    </a>
                                </td>
                            </tr>
                        </template>

                        <x-table.empty-state :colspan="5" icon="fas fa-cash-register" message="No students match your search criteria." />
                    </tbody>
                </table>
            </div>

            <x-table.pagination />
        </div>
    </div>

    @push('scripts')
        <script>
            function feeCollectionManager() {
                return {
                    // direct navigation to collection page
                }
            }
        </script>
    @endpush
@endsection
