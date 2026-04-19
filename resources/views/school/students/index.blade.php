@extends('layouts.school')

@section('title', 'Student Directory')

@section('content')
    <div x-data="Object.assign(ajaxDataTable({
        fetchUrl: '{{ route('school.students.fetch') }}',
        defaultSort: 'name',
        defaultDirection: 'asc',
        defaultPerPage: 25,
        defaultFilters: { class_id: '', section_id: '', status: '' },
        initialRows: @js($initialData['rows']),
        initialPagination: @js($initialData['pagination']),
        initialStats: @js($initialData['stats']),
        filterLabels: {
            class_id: { @foreach($classes as $c) '{{ $c->id }}': '{{ $c->name }}', @endforeach },
            status: { 'active': 'Active', 'inactive': 'Inactive', 'withdrawn': 'Withdrawn' }
        }
    }), studentManagement())" class="space-y-6">

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <x-stat-card label="Total Enrolled" :value="$stats['total_formatted']" icon="fas fa-users" color="indigo" alpine-text="stats.total_formatted" />
            <x-stat-card label="Active Students" :value="$stats['active_formatted']" icon="fas fa-user-check" color="emerald" alpine-text="stats.active_formatted" />
            <x-stat-card label="Inactive/Archive" :value="$stats['inactive_formatted']" icon="fas fa-user-slash" color="rose" alpine-text="stats.inactive_formatted" />
            <x-stat-card label="Recent Admissions" :value="$stats['admissions_this_month_formatted']" icon="fas fa-user-clock" color="amber" alpine-text="stats.admissions_this_month_formatted" />
        </div>

        <!-- Header Section -->
        <x-page-header title="Student Directory" description="Manage student profiles, academic records, and institutional lifecycle." icon="fas fa-user-graduate">
            <a href="{{ route('school.admission.index') }}"
                class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-teal-600 to-emerald-600 hover:from-teal-700 hover:to-emerald-700 text-white text-sm font-semibold rounded-xl transition-all shadow-md hover:shadow-lg active:scale-95">
                <i class="fas fa-user-plus mr-2 text-xs"></i>
                New Admission
            </a>
            <button @click="exportCSV()"
                class="inline-flex items-center px-4 py-2 bg-slate-800 hover:bg-black text-white text-sm font-semibold rounded-xl transition-all shadow-md hover:shadow-lg active:scale-95">
                <i class="fas fa-file-export mr-2 text-xs"></i>
                Export Results
            </button>
        </x-page-header>

        <!-- AJAX Data Table -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <!-- Table Header with Search and Filters -->
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div class="flex-1 flex flex-col md:flex-row md:items-center gap-4">
                        <h2 class="text-lg font-semibold text-gray-800 dark:text-white">Active Registry</h2>
                        <x-table.search placeholder="Search by name, roll, or phone..." />
                    </div>

                    <div class="flex items-center gap-3">
                        <x-table.filter-select
                            model="filters.status"
                            action="applyFilter('status', $event.target.value)"
                            placeholder="All Status"
                            :options="['active' => 'Active', 'inactive' => 'Inactive', 'withdrawn' => 'Withdrawn']"
                        />

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
                            <div class="flex items-center gap-1 bg-teal-50 text-teal-700 border border-teal-100 px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider">
                                <span x-text="key.replace('_', ' ') + ': ' + getFilterLabel(key, value)"></span>
                                <button @click="removeFilter(key)" class="ml-1 hover:text-teal-900 transition-colors">
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
                            <x-table.sort-header column="name" label="Student Profile" sort-var="sort" direction-var="direction" />
                            <x-table.sort-header column="admission_no" label="Admission No" sort-var="sort" direction-var="direction" />
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Academic Placement</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                            <x-table.sort-header column="admission_date" label="Joined On" sort-var="sort" direction-var="direction" />
                            <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider w-32">Actions</th>
                        </tr>
                    </thead>

                    <!-- Initial Render (Blade) -->
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700" :class="{ 'hidden': true }">
                        @foreach($initialData['rows'] as $row)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/50 transition-colors group">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-gray-50 flex items-center justify-center overflow-hidden border border-gray-100 shadow-sm">
                                            @if($row['photo'])
                                                <img src="{{ $row['photo'] }}" class="w-full h-full object-cover">
                                            @else
                                                <div class="w-full h-full bg-gradient-to-br from-teal-50 to-teal-100 flex items-center justify-center text-teal-600 font-bold text-xs uppercase">{{ $row['initials'] }}</div>
                                            @endif
                                        </div>
                                        <div>
                                            <div class="text-sm font-bold text-gray-800 dark:text-gray-100 group-hover:text-teal-600 transition-colors">{{ $row['full_name'] }}</div>
                                            <div class="text-[10px] font-medium text-gray-400 italic">Contact: {{ $row['phone'] }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-[10px] font-black text-teal-600 bg-teal-50 px-2.5 py-1.5 rounded-lg border border-teal-100 uppercase tabular-nums tracking-widest">#{{ $row['admission_no'] }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex flex-col">
                                        <div class="text-xs font-bold text-gray-700 dark:text-gray-200">{{ $row['class_name'] }}</div>
                                        <div class="text-[10px] font-medium text-emerald-600">Section {{ $row['section_name'] }}</div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $statusStyles = [
                                            'active' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
                                            'inactive' => 'bg-yellow-50 text-yellow-700 border-yellow-100',
                                            'withdrawn' => 'bg-rose-50 text-rose-700 border-rose-100'
                                        ];
                                        $style = $statusStyles[$row['status']] ?? 'bg-gray-50 text-gray-700 border-gray-100';
                                    @endphp
                                    <span class="px-3 py-1 text-[10px] font-black uppercase rounded-full border tracking-wide {{ $style }}">
                                        {{ $row['status'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 tabular-nums">
                                    {{ $row['admission_date'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="{{ route('school.students.show', $row['id']) }}" class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center hover:bg-blue-100 transition-colors" title="View Profile"><i class="fas fa-eye text-xs"></i></a>
                                        <a href="{{ route('school.students.edit', $row['id']) }}" class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center hover:bg-indigo-100 transition-colors" title="Edit"><i class="fas fa-edit text-xs"></i></a>
                                        <button @click="confirmArchive(@js($row))" class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center hover:bg-rose-100 transition-colors" title="Archive"><i class="fas fa-trash-alt text-xs"></i></button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>

                    <!-- Dynamic Render (Alpine) -->
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700 transition-opacity duration-150" x-cloak :class="loading && rows.length &gt; 0 ? 'opacity-50' : 'opacity-100'">
                        <template x-for="row in rows" :key="row.id">
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/50 transition-colors group">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-gray-50 flex items-center justify-center overflow-hidden border border-gray-100 shadow-sm relative">
                                            <template x-if="row.photo">
                                                <img :src="row.photo" class="w-full h-full object-cover">
                                            </template>
                                            <template x-if="!row.photo">
                                                <div class="w-full h-full bg-gradient-to-br from-teal-50 to-teal-100 flex items-center justify-center text-teal-600 font-bold text-xs uppercase" x-text="row.initials"></div>
                                            </template>
                                        </div>
                                        <div>
                                            <div class="text-sm font-bold text-gray-800 dark:text-gray-100 group-hover:text-teal-600 transition-colors" x-text="row.full_name"></div>
                                            <div class="text-[10px] font-medium text-gray-400 italic" x-text="'Contact: ' + row.phone"></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-[10px] font-black text-teal-600 bg-teal-50 px-2.5 py-1.5 rounded-lg border border-teal-100 uppercase tabular-nums tracking-widest" x-text="'#' + row.admission_no"></span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex flex-col">
                                        <div class="text-xs font-bold text-gray-700 dark:text-gray-200" x-text="row.class_name"></div>
                                        <div class="text-[10px] font-medium text-emerald-600" x-text="'Section ' + row.section_name"></div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-3 py-1 text-[10px] font-black uppercase rounded-full border tracking-wide"
                                        :class="{
                                            'bg-emerald-50 text-emerald-700 border-emerald-100': row.status === 'active',
                                            'bg-yellow-50 text-yellow-700 border-yellow-100': row.status === 'inactive',
                                            'bg-rose-50 text-rose-700 border-rose-100': row.status === 'withdrawn',
                                            'bg-gray-50 text-gray-700 border-gray-100': !['active', 'inactive', 'withdrawn'].includes(row.status)
                                        }"
                                        x-text="row.status">
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 tabular-nums" x-text="row.admission_date"></td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <a :href="'/school/students/' + row.id" class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center hover:bg-blue-100 transition-colors" title="View Profile"><i class="fas fa-eye text-xs"></i></a>
                                        <a :href="'/school/students/' + row.id + '/edit'" class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center hover:bg-indigo-100 transition-colors" title="Edit"><i class="fas fa-edit text-xs"></i></a>
                                        <button @click="confirmArchive(row)" class="w-8 h-8 rounded-lg bg-rose-50 text-red-600 flex items-center justify-center hover:bg-rose-100 transition-colors" title="Archive"><i class="fas fa-trash-alt text-xs"></i></button>
                                    </div>
                                </td>
                            </tr>
                        </template>

                        <x-table.empty-state :colspan="6" icon="fas fa-user-slash" message="No student records matched your search." />
                    </tbody>
                </table>
            </div>

            <x-table.pagination />
        </div>

        <x-confirm-modal />
    </div>

    @push('scripts')
        <script>
            function studentManagement() {
                return {
                    confirmArchive(student) {
                        const self = this;
                        window.dispatchEvent(new CustomEvent('open-confirm-modal', {
                            detail: {
                                title: 'Archive Student Record',
                                message: `Are you sure you want to archive the record for "${student.full_name}"? This will hide them from active lists but keep their historical data.`,
                                callback: async () => {
                                    try {
                                        const response = await fetch(`/school/students/${student.id}`, {
                                            method: 'POST',
                                            headers: {
                                                'Content-Type': 'application/json',
                                                'Accept': 'application/json',
                                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                            },
                                            body: JSON.stringify({ _method: 'DELETE' })
                                        });

                                        const result = await response.json();

                                        if (response.ok) {
                                            if (window.Toast) window.Toast.fire({ icon: 'success', title: result.message });
                                            if (typeof self.refreshTable === 'function') self.refreshTable();
                                        } else {
                                            if (window.Toast) window.Toast.fire({ icon: 'error', title: result.message || 'Archive failed' });
                                        }
                                    } catch (error) {
                                        if (window.Toast) window.Toast.fire({ icon: 'error', title: 'Archive operation failed' });
                                    }
                                }
                            }
                        }));
                    },

                    exportCSV() {
                        // Implement export if needed, or simply fire a toast
                        if (window.Toast) window.Toast.fire({ icon: 'info', title: 'Preparing student data for export...' });
                    }
                }
            }
        </script>
    @endpush
@endsection
