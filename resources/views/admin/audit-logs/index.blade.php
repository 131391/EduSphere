@extends('layouts.admin')

@section('title', 'System Audit Registry')

@section('content')
<div x-data="ajaxDataTable({
    fetchUrl: '{{ route('admin.audit-logs.index') }}',
    defaultSort: 'created_at',
    defaultDirection: 'desc',
    defaultPerPage: 25,
    defaultFilters: { event: '', from_date: '', to_date: '' },
    initialRows: @js($initialData['rows']),
    initialPagination: @js($initialData['pagination']),
    initialStats: @js($initialData['stats']),
    filterLabels: {
        event: {
            'created': 'Created',
            'updated': 'Updated',
            'deleted': 'Deleted'
        }
    }
})" class="space-y-6">

    <!-- Statistics Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-4">
        <x-stat-card label="Global Events"  :value="$stats['total']"   icon="fas fa-database"    color="blue"   alpine-text="stats.total" />
        <x-stat-card label="Today Activity" :value="$stats['today']"   icon="fas fa-bolt"        color="emerald" alpine-text="stats.today" />
        <x-stat-card label="Creations"      :value="$stats['created']" icon="fas fa-plus-circle" color="green"  alpine-text="stats.created" />
        <x-stat-card label="Updates"        :value="$stats['updated']" icon="fas fa-edit"        color="indigo" alpine-text="stats.updated" />
        <x-stat-card label="Deletions"      :value="$stats['deleted']" icon="fas fa-trash-alt"   color="rose"   alpine-text="stats.deleted" />
    </div>

    <!-- Page Header -->
    <x-page-header title="System Audit Registry" description="Monitor application activity and historical state changes across the network" icon="fas fa-history" />

    <!-- Data Table Card -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">

        <!-- Table Header with Search and Filters -->
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <!-- Left: Title and Search -->
                <div class="flex-1 flex flex-col md:flex-row md:items-center gap-4">
                    <h2 class="text-lg font-semibold text-gray-800 dark:text-white whitespace-nowrap">Audit Log</h2>
                    <x-table.search placeholder="Search action, model name..." />
                </div>

                <!-- Right: Filters -->
                <div class="flex items-center gap-3 flex-wrap">
                    <x-table.filter-select
                        model="filters.event"
                        action="applyFilter('event', $event.target.value)"
                        placeholder="Event Type"
                        :options="['created' => 'Created', 'updated' => 'Updated', 'deleted' => 'Deleted']"
                    />

                    <!-- From Date -->
                    <div class="flex items-center gap-2">
                        <input type="date" x-model="filters.from_date" @change="applyFilter('from_date', $event.target.value)"
                            class="h-9 px-3 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-sm text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none">
                        <span class="text-xs text-gray-400 font-medium">to</span>
                        <input type="date" x-model="filters.to_date" @change="applyFilter('to_date', $event.target.value)"
                            class="h-9 px-3 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-sm text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none">
                    </div>

                    <x-table.per-page model="perPage" action="changePerPage($event.target.value)" :default="25" />
                </div>
            </div>

            <!-- Active Filter Tags -->
            <div class="mt-3 flex flex-wrap gap-2" x-show="hasActiveFilters() || search !== ''" x-cloak>
                <div x-show="search !== ''" class="flex items-center gap-1 bg-purple-100 text-purple-800 px-3 py-1 rounded-full text-xs">
                    <span>Search: <span x-text="search" class="font-semibold"></span></span>
                    <button @click="search = ''" class="ml-1 hover:text-purple-600"><i class="fas fa-times"></i></button>
                </div>
                <template x-for="(value, key) in filters" :key="key">
                    <div x-show="value" class="flex items-center gap-1 bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-xs">
                        <span x-text="getFilterLabel(key, value)"></span>
                        <button @click="removeFilter(key)" class="ml-1 hover:text-blue-600"><i class="fas fa-times"></i></button>
                    </div>
                </template>
                <button @click="clearAllFilters()" class="flex items-center gap-1 bg-red-100 text-red-700 px-3 py-1 rounded-full text-xs hover:bg-red-200 transition-colors">
                    <i class="fas fa-times-circle"></i> Clear All
                </button>
            </div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto relative ajax-table-wrapper">
            <x-table.loading-overlay message="Loading audit logs..." />

            <table class="w-full text-left border-collapse">
                <thead class="bg-gray-50/50 dark:bg-gray-700/50 border-b border-gray-100 dark:border-gray-700">
                    <tr>
                        <x-table.sort-header column="created_at" label="Event Timestamp" sort-var="sort" direction-var="direction" />
                        <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Executing Agent</th>
                        <x-table.sort-header column="description" label="Event Type" sort-var="sort" direction-var="direction" />
                        <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Target Entity</th>
                        <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Delta Attributes</th>
                    </tr>
                </thead>

                {{-- Server-rendered rows --}}
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700" x-show="!hydrated" x-cloak>
                    @if(empty($initialData['rows']))
                    <tr>
                        <td colspan="5" class="px-6 py-16 text-center">
                            <i class="fas fa-history text-4xl text-gray-300 mb-4 block"></i>
                            <p class="text-gray-500">No system activity events found.</p>
                        </td>
                    </tr>
                    @endif
                    @foreach($initialData['rows'] as $row)
                    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-xs font-bold text-gray-800 dark:text-gray-100">{{ $row['date'] }}</div>
                            <div class="text-[10px] font-semibold text-blue-500">{{ $row['time'] }} ({{ $row['diff'] }})</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-slate-500 to-gray-600 flex items-center justify-center text-white font-bold text-xs shadow-sm">{{ $row['causer_initials'] }}</div>
                                <div>
                                    <div class="text-sm font-bold text-gray-800 dark:text-gray-100">{{ $row['causer_name'] }}</div>
                                    <div class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">{{ $row['causer_role'] }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php $c = $row['event_config']; @endphp
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full border text-[10px] font-bold uppercase tracking-wider {{ $c['bg'] }} {{ $c['text'] }} {{ $c['border'] }}">
                                <i class="fas {{ $c['icon'] }} text-[8px]"></i>{{ $row['event'] }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-1.5 text-xs font-medium text-gray-600 dark:text-gray-300">
                                <i class="fas fa-link text-[10px] text-gray-400"></i>
                                {{ $row['model'] }} {{ $row['subject_id'] }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($row['has_delta'])
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 text-[10px] font-bold text-blue-600 bg-blue-50 border border-blue-100 rounded-xl uppercase tracking-wider">
                                <i class="fas fa-eye text-[8px]"></i> Has Delta
                            </span>
                            @else
                            <span class="text-[10px] font-semibold text-gray-300 uppercase tracking-widest italic">No Data Delta</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>

                {{-- Alpine-managed rows --}}
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700 transition-opacity duration-150" x-cloak
                    :class="loading && rows.length > 0 ? 'opacity-50' : 'opacity-100'">
                    <template x-for="row in rows" :key="row.id">
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/50 transition-colors">
                            <!-- Timestamp -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-xs font-bold text-gray-800 dark:text-gray-100" x-text="row.date"></div>
                                <div class="text-[10px] font-semibold text-blue-500">
                                    <span x-text="row.time"></span> (<span x-text="row.diff"></span>)
                                </div>
                            </td>

                            <!-- Executing Agent -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-slate-500 to-gray-600 flex items-center justify-center text-white font-bold text-xs shadow-sm" x-text="row.causer_initials"></div>
                                    <div>
                                        <div class="text-sm font-bold text-gray-800 dark:text-gray-100" x-text="row.causer_name"></div>
                                        <div class="text-[10px] font-bold text-gray-400 uppercase tracking-wider" x-text="row.causer_role"></div>
                                    </div>
                                </div>
                            </td>

                            <!-- Event Type -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full border text-[10px] font-bold uppercase tracking-wider"
                                    :class="`${row.event_config.bg} ${row.event_config.text} ${row.event_config.border}`">
                                    <i class="fas text-[8px]" :class="row.event_config.icon"></i>
                                    <span x-text="row.event"></span>
                                </span>
                            </td>

                            <!-- Target Entity -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-1.5 text-xs font-medium text-gray-600 dark:text-gray-300">
                                    <i class="fas fa-link text-[10px] text-gray-400"></i>
                                    <span x-text="row.model + ' ' + row.subject_id"></span>
                                </div>
                            </td>

                            <!-- Delta Attributes -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <template x-if="row.has_delta">
                                    <div x-data="{ open: false }">
                                        <button @click="open = true"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-[10px] font-bold text-blue-600 bg-blue-50 border border-blue-100 rounded-xl hover:bg-blue-100 transition-all active:scale-95 uppercase tracking-wider">
                                            <i class="fas fa-eye text-[8px]"></i> View Delta
                                        </button>

                                        <div x-show="open" x-cloak
                                            class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
                                            @click.self="open = false">
                                            <div class="bg-white dark:bg-gray-800 rounded-3xl w-full max-w-2xl max-h-[85vh] overflow-hidden flex flex-col shadow-2xl ring-1 ring-black/10">
                                                <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between bg-gray-50/50 dark:bg-gray-900/50">
                                                    <div class="flex items-center gap-3">
                                                        <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center text-blue-600">
                                                            <i class="fas fa-project-diagram text-xs"></i>
                                                        </div>
                                                        <h3 class="text-sm font-bold text-gray-800 dark:text-white uppercase tracking-wider">Activity Delta Analysis</h3>
                                                    </div>
                                                    <button @click="open = false" class="text-gray-400 hover:text-gray-600 transition-colors">
                                                        <i class="fas fa-times text-lg"></i>
                                                    </button>
                                                </div>
                                                <div class="p-8 overflow-y-auto font-mono text-xs space-y-6">
                                                    <template x-if="row.old_state">
                                                        <div>
                                                            <div class="flex items-center gap-2 mb-3">
                                                                <div class="w-2 h-2 rounded-full bg-gray-400"></div>
                                                                <div class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Original State</div>
                                                            </div>
                                                            <pre class="bg-gray-50 dark:bg-gray-900/50 p-4 rounded-2xl border border-gray-100 dark:border-gray-700 overflow-x-auto text-[11px] leading-relaxed" x-text="row.old_state"></pre>
                                                        </div>
                                                    </template>
                                                    <div>
                                                        <div class="flex items-center gap-2 mb-3">
                                                            <div class="w-2 h-2 rounded-full bg-emerald-500"></div>
                                                            <div class="text-[10px] font-bold text-emerald-500 uppercase tracking-wider">Modified State</div>
                                                        </div>
                                                        <pre class="bg-emerald-50/30 dark:bg-emerald-900/10 p-4 rounded-2xl border border-emerald-100/50 dark:border-emerald-800/50 overflow-x-auto text-[11px] leading-relaxed text-emerald-900" x-text="row.new_state"></pre>
                                                    </div>
                                                </div>
                                                <div class="px-8 py-5 border-t border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50 text-right">
                                                    <button @click="open = false" class="px-8 py-2.5 bg-gray-900 text-white text-xs font-bold uppercase tracking-widest rounded-xl hover:bg-black transition-all shadow-lg active:scale-95">Dismiss</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                                <template x-if="!row.has_delta">
                                    <span class="text-[10px] font-semibold text-gray-300 uppercase tracking-widest italic">No Data Delta</span>
                                </template>
                            </td>
                        </tr>
                    </template>

                    <x-table.empty-state :colspan="5" icon="fas fa-history" message="No system activity events found matching the specified parameters." />
                </tbody>
            </table>
        </div>

        <!-- Server-rendered pagination fallback -->
        <x-table.pagination :initial="$initialData['pagination']" />
    </div>
</div>
@endsection
