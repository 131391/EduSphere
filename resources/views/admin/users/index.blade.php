@extends('layouts.admin')

@section('title', 'Global Users')

@section('content')
    <div x-data="ajaxDataTable({
        fetchUrl: '{{ route('admin.users.index') }}',
        defaultSort: 'created_at',
        defaultDirection: 'desc',
        defaultPerPage: 25,
        defaultFilters: { school_id: '', role: '', status: '' },
        initialRows: @js($initialData['rows']),
        initialPagination: @js($initialData['pagination']),
        initialStats: @js($initialData['stats']),
        filterLabels: {
            school_id: {
                @foreach($schools as $school)
                    '{{ $school->id }}': '{{ addslashes($school->name) }}',
                @endforeach
            },
            role: {
                @foreach($roles as $role)
                    '{{ $role->slug }}': '{{ addslashes($role->name) }}',
                @endforeach
            },
            status: {
                @foreach(\App\Enums\UserStatus::cases() as $s)
                    '{{ $s->value }}': '{{ $s->label() }}',
                @endforeach
            }
        }
    })" class="space-y-6">

        <!-- Statistics Cards -->
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-4">
            <x-stat-card label="Total Users" :value="$stats['total']" icon="fas fa-users" color="blue" alpine-text="stats.total" />
            <x-stat-card label="Active" :value="$stats['active']" icon="fas fa-check-circle" color="emerald" alpine-text="stats.active" />
            <x-stat-card label="Inactive" :value="$stats['inactive']" icon="fas fa-pause-circle" color="gray" alpine-text="stats.inactive" />
            <x-stat-card label="Suspended" :value="$stats['suspended']" icon="fas fa-ban" color="rose" alpine-text="stats.suspended" />
            <x-stat-card label="Pending" :value="$stats['pending']" icon="fas fa-clock" color="amber" alpine-text="stats.pending" />
        </div>

        <!-- Page Header -->
        <x-page-header title="Global User Registry" description="Manage all users across the EduSphere network" icon="fas fa-users-cog">
            <button @click="exportData('csv')" :disabled="exporting"
                class="min-w-[140px] justify-center inline-flex items-center px-4 py-2 bg-gradient-to-r from-slate-700 to-slate-900 hover:from-black hover:to-slate-800 text-white text-sm font-semibold rounded-xl transition-all shadow-md hover:shadow-lg active:scale-95 disabled:opacity-50">
                <span x-show="exporting" class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin mr-2 inline-block" x-cloak></span>
                <i x-show="!exporting" class="fas fa-file-excel mr-2 text-xs"></i>
                <span x-text="exporting ? 'Exporting...' : 'Export CSV'">Export CSV</span>
            </button>
        </x-page-header>

        <!-- AJAX Data Table -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <!-- Table Header with Search and Filters -->
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <!-- Left: Title and Search -->
                    <div class="flex-1 flex flex-col md:flex-row md:items-center gap-4">
                        <h2 class="text-lg font-semibold text-gray-800 dark:text-white">User List</h2>
                        <x-table.search placeholder="Search name, email, phone..." />
                    </div>

                    <!-- Right: Filters -->
                    <div class="flex items-center gap-3">
                        <x-table.filter-select
                            model="filters.school_id"
                            action="applyFilter('school_id', $event.target.value)"
                            placeholder="School"
                            :options="$schools->mapWithKeys(fn($s) => [$s->id => $s->name])->toArray()"
                        />

                        <x-table.filter-select
                            model="filters.role"
                            action="applyFilter('role', $event.target.value)"
                            placeholder="Role"
                            :options="$roles->mapWithKeys(fn($r) => [$r->slug => $r->name])->toArray()"
                        />

                        <x-table.filter-select
                            model="filters.status"
                            action="applyFilter('status', $event.target.value)"
                            placeholder="Status"
                            :options="collect(\App\Enums\UserStatus::cases())->mapWithKeys(fn($s) => [$s->value => $s->label()])->toArray()"
                        />

                        <x-table.per-page model="perPage" action="changePerPage($event.target.value)" :default="25" />
                    </div>
                </div>

                <!-- Active Filters Display -->
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
                        <i class="fas fa-times-circle"></i>
                        <span>Clear All</span>
                    </button>
                </div>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto relative ajax-table-wrapper">
                <x-table.loading-overlay message="Loading users..." />

                <table class="w-full text-left border-collapse">
                    <thead class="bg-gray-50/50 dark:bg-gray-700/50 border-b border-gray-100 dark:border-gray-700">
                        <tr>
                            <x-table.sort-header column="name" label="User Identity" sort-var="sort" direction-var="direction" />
                            <x-table.sort-header column="role" label="System Role" sort-var="sort" direction-var="direction" />
                            <x-table.sort-header column="school" label="Assigned School" sort-var="sort" direction-var="direction" />
                            <x-table.sort-header column="status" label="Account Status" sort-var="sort" direction-var="direction" />
                            <x-table.sort-header column="last_login_at" label="Recent Activity" sort-var="sort" direction-var="direction" />
                        </tr>
                    </thead>

                    {{-- Server-rendered rows: visible instantly, hidden once Alpine initializes --}}
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700" x-show="!hydrated" x-cloak>
                        @if(empty($initialData['rows']))
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <i class="fas fa-users-slash text-4xl text-gray-300 mb-4"></i>
                                    <p class="text-lg text-gray-500">No users found matching your criteria.</p>
                                </div>
                            </td>
                        </tr>
                        @endif
                        @foreach($initialData['rows'] as $row)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-500 flex items-center justify-center text-white font-bold text-xs shadow-sm">{{ $row['initials'] }}</div>
                                    <div>
                                        <div class="text-sm font-bold text-gray-800 dark:text-gray-100">{{ $row['name'] }}</div>
                                        <div class="text-[10px] font-medium text-gray-400">{{ $row['email'] }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider rounded-lg bg-indigo-50 text-indigo-700 border border-indigo-100">{{ $row['role'] }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-1.5 text-xs font-medium text-gray-600 dark:text-gray-300">
                                    <i class="fas fa-school text-[10px] text-gray-400"></i>
                                    {{ $row['school'] }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php $config = $row['status_config']; @endphp
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full border text-[10px] font-bold uppercase tracking-wider {{ $config['bg'] }} {{ $config['text'] }} {{ $config['border'] }}">
                                    <i class="fas {{ $config['icon'] }} text-[8px]"></i>
                                    {{ $row['status_label'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-1.5 text-[10px] font-semibold text-gray-500">
                                    <i class="far fa-clock text-gray-400"></i>
                                    {{ $row['last_login'] }}
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>

                    {{-- Alpine-managed rows: takes over once initialized --}}
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700 transition-opacity duration-150" x-cloak :class="loading && rows.length > 0 ? 'opacity-50' : 'opacity-100'">
                        <template x-for="row in rows" :key="row.id">
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-500 flex items-center justify-center text-white font-bold text-xs shadow-sm" x-text="row.initials"></div>
                                        <div>
                                            <div class="text-sm font-bold text-gray-800 dark:text-gray-100" x-text="row.name"></div>
                                            <div class="text-[10px] font-medium text-gray-400" x-text="row.email"></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider rounded-lg bg-indigo-50 text-indigo-700 border border-indigo-100" x-text="row.role"></span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-1.5 text-xs font-medium text-gray-600 dark:text-gray-300">
                                        <i class="fas fa-school text-[10px] text-gray-400"></i>
                                        <span x-text="row.school"></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full border text-[10px] font-bold uppercase tracking-wider shadow-sm"
                                          :class="`${row.status_config.bg} ${row.status_config.text} ${row.status_config.border}`">
                                        <i class="fas text-[8px]" :class="row.status_config.icon"></i>
                                        <span x-text="row.status_label"></span>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-1.5 text-[10px] font-semibold text-gray-500">
                                        <i class="far fa-clock text-gray-400"></i>
                                        <span x-text="row.last_login"></span>
                                    </div>
                                </td>
                            </tr>
                        </template>

                        <x-table.empty-state :colspan="5" icon="fas fa-users-slash" message="No global users found matching your criteria." />
                    </tbody>
                </table>
            </div>

            <!-- Server-rendered pagination: visible instantly, hidden once Alpine takes over -->
            <x-table.pagination />
        </div>
    </div>
@endsection
