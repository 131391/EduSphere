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
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <x-stat-card label="Total Users" :value="$stats['total']" icon="fas fa-users" color="blue" alpine-text="stats.total" />
            <x-stat-card label="Active Users" :value="$stats['active']" icon="fas fa-check-circle" color="emerald" alpine-text="stats.active" />
            <x-stat-card label="Inactive Users" :value="$stats['inactive']" icon="fas fa-pause-circle" color="gray" alpine-text="stats.inactive" />
            <x-stat-card label="Suspended Users" :value="$stats['suspended']" icon="fas fa-ban" color="rose" alpine-text="stats.suspended" />
            <x-stat-card label="Pending Users" :value="$stats['pending']" icon="fas fa-clock" color="amber" alpine-text="stats.pending" />
        </div>

        <!-- Inner Wrapper for Search Toggle -->
        <div x-data="{ searchOpen: true }">
            <!-- Header Section -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 border border-blue-100/50 dark:border-gray-700 mb-6">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center text-blue-600">
                            <i class="fas fa-users-cog text-xs"></i>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-gray-800 dark:text-white">Global User Registry</h2>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5" x-text="'Managing ' + stats.total + ' users across the EduSphere network'">Managing {{ number_format($stats['total']) }} users across the EduSphere network</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" @click="searchOpen = !searchOpen"
                            class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 text-gray-700 dark:text-gray-200 text-xs font-semibold rounded-xl hover:bg-gray-50 dark:hover:bg-gray-600 transition-all shadow-sm">
                            <i class="fas fa-filter mr-2 text-blue-500"></i>
                            Advanced Filters
                        </button>
                        <button @click="exportData('csv')" :disabled="exporting"
                            class="min-w-[140px] justify-center inline-flex items-center px-4 py-2 bg-gradient-to-r from-slate-700 to-slate-900 hover:from-black hover:to-slate-800 text-white text-sm font-semibold rounded-xl transition-all shadow-md hover:shadow-lg active:scale-95 disabled:opacity-50">
                            <span x-show="exporting" class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin mr-2 inline-block" x-cloak></span>
                            <i x-show="!exporting" class="fas fa-file-excel mr-2 text-xs"></i>
                            <span x-text="exporting ? 'Exporting...' : 'Export CSV'">Export CSV</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Filters Section -->
            <div x-show="searchOpen" x-collapse x-cloak
                class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 mb-6">
                <div class="grid grid-cols-1 md:grid-cols-4 lg:grid-cols-6 gap-6">
                    <!-- Search -->
                    <div class="lg:col-span-2">
                        <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1.5">Search Identifier</label>
                        <div class="relative group">
                            <input type="text" x-model="search" placeholder="Name, email, phone..."
                                class="w-full h-11 pl-10 bg-gray-50 dark:bg-gray-700 border-gray-200 dark:border-gray-600 rounded-xl text-sm text-gray-700 dark:text-gray-200 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none">
                            <div class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-blue-500 transition-colors">
                                <i class="fas fa-search text-xs"></i>
                            </div>
                        </div>
                    </div>

                    <!-- School Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1.5">School Affiliation</label>
                        <select x-model="filters.school_id" @change="applyFilter('school_id', $event.target.value)"
                            class="w-full h-11 px-4 bg-gray-50 dark:bg-gray-700 border-gray-200 dark:border-gray-600 rounded-xl text-sm text-gray-700 dark:text-gray-200 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none">
                            <option value="">All Schools</option>
                            @foreach($schools as $school)
                                <option value="{{ $school->id }}">{{ $school->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Role Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1.5">System Role</label>
                        <select x-model="filters.role" @change="applyFilter('role', $event.target.value)"
                            class="w-full h-11 px-4 bg-gray-50 dark:bg-gray-700 border-gray-200 dark:border-gray-600 rounded-xl text-sm text-gray-700 dark:text-gray-200 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none">
                            <option value="">All Roles</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->slug }}">{{ $role->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Status Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1.5">Account Status</label>
                        <select x-model="filters.status" @change="applyFilter('status', $event.target.value)"
                            class="w-full h-11 px-4 bg-gray-50 dark:bg-gray-700 border-gray-200 dark:border-gray-600 rounded-xl text-sm text-gray-700 dark:text-gray-200 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none">
                            <option value="">All Statuses</option>
                            @foreach(\App\Enums\UserStatus::cases() as $status)
                                <option value="{{ $status->value }}">{{ $status->label() }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-end gap-2 lg:col-span-1">
                        <button type="button" @click="clearAllFilters()"
                            x-show="hasActiveFilters() || search !== ''"
                            class="flex-1 h-11 flex items-center justify-center bg-rose-50 dark:bg-rose-900/20 text-rose-600 dark:text-rose-400 border border-rose-100 dark:border-rose-900/30 rounded-xl hover:bg-rose-100 dark:hover:bg-rose-900/40 transition-all shadow-sm font-semibold text-xs whitespace-nowrap px-4">
                            <i class="fas fa-trash-alt text-[10px] mr-2"></i> Reset
                        </button>
                        <!-- Per Page -->
                        <div class="w-24">
                            <x-table.per-page model="perPage" action="changePerPage($event.target.value)" />
                        </div>
                    </div>
                </div>

                <!-- Active Filters Tags -->
                <div class="mt-4 flex flex-wrap gap-2" x-show="hasActiveFilters() || search !== ''" x-cloak>
                    <!-- Search Tag -->
                    <div x-show="search !== ''" class="flex items-center gap-1 bg-purple-100 text-purple-800 px-3 py-1 rounded-full text-xs">
                        <span>Search: <span x-text="search" class="font-semibold"></span></span>
                        <button @click="search = ''" class="ml-1 hover:text-purple-600">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <!-- Other Tags -->
                    <template x-for="(value, key) in filters" :key="key">
                        <div x-show="value !== ''" class="flex items-center gap-1 bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-xs">
                            <span x-text="getFilterLabel(key, value)"></span>
                            <button @click="removeFilter(key)" class="ml-1 hover:text-blue-600">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </template>
                </div>
            </div>

            <!-- AJAX Data Table -->
            <div class="bg-white rounded-lg shadow overflow-hidden relative">
                
                <div class="overflow-x-auto relative">
                    <x-table.loading-overlay message="Loading users..." />

                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <x-table.sort-header column="name" label="User Identity" />
                                <x-table.sort-header column="role" label="System Role" sort-var="sort" direction-var="direction" />
                                <x-table.sort-header column="school" label="Assigned School" sort-var="sort" direction-var="direction" />
                                <x-table.sort-header column="status" label="Account Status" sort-var="sort" direction-var="direction" />
                                <x-table.sort-header column="last_login_at" label="Recent Activity" sort-var="sort" direction-var="direction" />
                            </tr>
                        </thead>

                        <!-- Server-rendered rows for instant load -->
                        <tbody class="bg-white divide-y divide-gray-200" :class="{ 'hidden': true }">
                            @foreach($initialData['rows'] as $row)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 rounded-xl bg-blue-50 border border-blue-100 flex items-center justify-center text-blue-600 font-bold text-xs shadow-sm ring-2 ring-white">{{ $row['initials'] }}</div>
                                        <div>
                                            <div class="text-sm font-bold text-gray-800">{{ $row['name'] }}</div>
                                            <div class="text-xs font-semibold text-gray-400">{{ $row['email'] }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider rounded-lg bg-indigo-50 text-indigo-700 border border-indigo-100">{{ $row['role'] }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <div class="w-1.5 h-1.5 rounded-full bg-blue-400 shadow-sm shadow-blue-200"></div>
                                        <span class="text-xs font-semibold text-gray-600">{{ $row['school'] }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php $config = $row['status_config']; @endphp
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full {{ $config['bg'] }} {{ $config['text'] }} {{ $config['border'] }} border text-[10px] font-bold uppercase tracking-wider shadow-sm">
                                        <i class="fas {{ $config['icon'] }} text-[8px]"></i>
                                        {{ $row['status_label'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <i class="far fa-clock text-gray-400 text-[10px]"></i>
                                        <div class="text-[10px] font-semibold text-gray-500">{{ $row['last_login'] }}</div>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>

                        <!-- Dynamic Alpine Table Body -->
                        <tbody class="bg-white divide-y divide-gray-200 transition-opacity duration-150" x-cloak :class="loading ? 'opacity-50' : 'opacity-100'">
                            <template x-for="row in rows" :key="row.id">
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center gap-4">
                                            <div class="w-10 h-10 rounded-xl bg-blue-50 border border-blue-100 flex items-center justify-center text-blue-600 font-bold text-xs shadow-sm ring-2 ring-white" x-text="row.initials"></div>
                                            <div>
                                                <div class="text-sm font-bold text-gray-800" x-text="row.name"></div>
                                                <div class="text-xs font-semibold text-gray-400" x-text="row.email"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider rounded-lg bg-indigo-50 text-indigo-700 border border-indigo-100" x-text="row.role"></span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center gap-2">
                                            <div class="w-1.5 h-1.5 rounded-full bg-blue-400 shadow-sm shadow-blue-200"></div>
                                            <span class="text-xs font-semibold text-gray-600" x-text="row.school"></span>
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
                                        <div class="flex items-center gap-2">
                                            <i class="far fa-clock text-gray-400 text-[10px]"></i>
                                            <div class="text-[10px] font-semibold text-gray-500" x-text="row.last_login"></div>
                                        </div>
                                    </td>
                                </tr>
                            </template>

                            <x-table.empty-state :colspan="5" icon="fas fa-users-slash" message="No global users found matching your criteria." />
                        </tbody>
                    </table>
                </div>

                <x-table.pagination />
            </div>
        </div>
    </div>
@endsection