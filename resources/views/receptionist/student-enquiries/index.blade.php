@extends('layouts.receptionist')

@section('title', 'Student Enquiry Registry')

@section('content')
    <div x-data="ajaxDataTable({
        fetchUrl: '{{ route('receptionist.student-enquiries.fetch') }}',
        defaultSort: 'created_at',
        defaultDirection: 'desc',
        defaultPerPage: 25,
        defaultFilters: { status: '', class_id: '', academic_year_id: '' },
        initialRows: @js($initialData['rows']),
        initialPagination: @js($initialData['pagination']),
        initialStats: @js($initialData['stats']),
        filterLabels: {
            status: {
                @foreach(\App\Enums\EnquiryStatus::cases() as $s) '{{ $s->value }}': '{{ $s->label() }}', @endforeach
            },
            class_id: {
                @foreach($classes as $c) '{{ $c->id }}': '{{ $c->name }}', @endforeach
            },
            academic_year_id: {
                @foreach($academicYears as $y) '{{ $y->id }}': '{{ $y->name }}', @endforeach
            }
        }
    })" class="space-y-6">
        
        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <x-stat-card label="Total Enquiries" :value="$stats['total']" icon="fas fa-clipboard-list" color="blue" alpine-text="stats.total" />
            <x-stat-card label="Pending" :value="$stats['pending']" icon="fas fa-clock" color="amber" alpine-text="stats.pending" />
            <x-stat-card label="Cancelled" :value="$stats['cancelled']" icon="fas fa-times-circle" color="rose" alpine-text="stats.cancelled" />
            <x-stat-card label="Registrations" :value="$stats['registration']" icon="fas fa-user-check" color="indigo" alpine-text="stats.registration" />
            <x-stat-card label="Admitted" :value="$stats['admitted']" icon="fas fa-graduation-cap" color="emerald" alpine-text="stats.admitted" />
        </div>

        <!-- Header Section -->
        <x-page-header title="Student Enquiry Registry" description="Manage and track all student enquiries and follow-ups." icon="fas fa-clipboard-list">
            <a href="{{ route('receptionist.student-enquiries.create') }}"
                class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-teal-600 to-emerald-600 hover:from-teal-700 hover:to-emerald-700 text-white text-sm font-semibold rounded-xl transition-all shadow-md hover:shadow-lg active:scale-95">
                <i class="fas fa-plus mr-2 text-xs"></i>
                New Enquiry
            </a>
            <button @click="exportData('csv')" :disabled="exporting"
                class="min-w-[140px] justify-center inline-flex items-center px-4 py-2 bg-gradient-to-r from-slate-700 to-slate-900 hover:from-black hover:to-slate-800 text-white text-sm font-semibold rounded-xl transition-all shadow-md hover:shadow-lg active:scale-95 disabled:opacity-50">
                <span x-show="exporting" class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin mr-2 inline-block" x-cloak></span>
                <i x-show="!exporting" class="fas fa-file-excel mr-2 text-xs"></i>
                <span x-text="exporting ? 'Exporting...' : 'Excel Export'">Excel Export</span>
            </button>
        </x-page-header>

        <!-- AJAX Data Table -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <!-- Table Header with Search and Filters -->
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <!-- Left: Title and Search -->
                    <div class="flex-1 flex flex-col md:flex-row md:items-center gap-4">
                        <h2 class="text-lg font-semibold text-gray-800 dark:text-white">Enquiry List</h2>
                        <x-table.search placeholder="Search enquiries..." />
                    </div>

                    <!-- Right: Filters and Actions -->
                    <div class="flex items-center gap-3">
                        <x-table.filter-select
                            model="filters.status"
                            action="applyFilter('status', $event.target.value)"
                            placeholder="Status"
                            :options="collect(\App\Enums\EnquiryStatus::cases())->mapWithKeys(fn($s) => [$s->value => $s->label()])->toArray()"
                        />

                        <x-table.filter-select
                            model="filters.class_id"
                            action="applyFilter('class_id', $event.target.value)"
                            placeholder="Class"
                            :options="$classes->pluck('name', 'id')->toArray()"
                        />

                        <x-table.filter-select
                            model="filters.academic_year_id"
                            action="applyFilter('academic_year_id', $event.target.value)"
                            placeholder="Academic Year"
                            :options="$academicYears->pluck('name', 'id')->toArray()"
                        />

                        <x-table.per-page model="perPage" action="changePerPage($event.target.value)" :default="25" />
                    </div>
                </div>

                <!-- Active Filters Display -->
                <div class="mt-3 flex flex-wrap gap-2" x-show="hasActiveFilters()" x-cloak>
                    <template x-for="(value, key) in filters" :key="key">
                        <div x-show="value" class="flex items-center gap-1 bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-xs">
                            <span x-text="getFilterLabel(key, value)"></span>
                            <button @click="removeFilter(key)" class="ml-1 hover:text-blue-600">
                                <i class="fas fa-times"></i>
                            </button>
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
                <x-table.loading-overlay />
                
                <table class="w-full text-left border-collapse">
                    <thead class="bg-gray-50/50 dark:bg-gray-700/50 border-b border-gray-100 dark:border-gray-700">
                        <tr>
                            <x-table.sort-header column="enquiry_no" label="Enquiry ID" sort-var="sort" direction-var="direction" />
                            <x-table.sort-header column="student_name" label="Student Details" sort-var="sort" direction-var="direction" />
                            <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Guardian Info</th>
                            <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Class & Academic</th>
                            <x-table.sort-header column="form_status" label="Status" sort-var="sort" direction-var="direction" />
                            <x-table.sort-header column="created_at" label="Dates" sort-var="sort" direction-var="direction" />
                            <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider w-32">Actions</th>
                        </tr>
                    </thead>

                    <!-- Initial Blade Render (Zero Blink) -->
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700" :class="{ 'hidden': true }">
                        @if(empty($initialData['rows']))
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <i class="fas fa-clipboard-list text-4xl text-gray-300 mb-4"></i>
                                    <p class="text-lg text-gray-500">No enquiries found matching your criteria.</p>
                                </div>
                            </td>
                        </tr>
                        @endif
                        @foreach($initialData['rows'] as $row)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-xs font-bold text-teal-600 bg-teal-50 px-2 py-1 rounded-lg border border-teal-100">{{ $row['enquiry_no'] }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-teal-500 to-emerald-500 flex items-center justify-center text-white font-bold text-xs shadow-sm">{{ $row['initials'] }}</div>
                                    <div>
                                        <div class="text-sm font-bold text-gray-800 dark:text-gray-100">{{ $row['student_name'] }}</div>
                                        <div class="text-[10px] font-medium text-gray-400">Student Name</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="space-y-1">
                                    <div class="text-xs font-bold text-gray-700 dark:text-gray-200">{{ $row['father_name'] }}</div>
                                    <div class="flex items-center gap-1.5 text-[10px] text-gray-400">
                                        <i class="fas fa-phone text-[9px]"></i>
                                        {{ $row['contact_no'] }}
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="space-y-1">
                                    <div class="text-xs font-bold text-gray-700 dark:text-gray-200">{{ $row['class_name'] }}</div>
                                    <div class="text-[10px] text-gray-500 dark:text-gray-400">AY: {{ $row['academic_year'] }}</div>
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
                                <div class="space-y-1">
                                    <div class="text-[10px] font-bold text-gray-700 dark:text-gray-300">
                                        <i class="far fa-calendar mr-1 text-gray-400"></i>
                                        Enq: {{ $row['enquiry_date'] }}
                                    </div>
                                    <div class="text-[9px] text-rose-500 font-medium">Follow: {{ $row['follow_up'] }}</div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('receptionist.student-enquiries.edit', $row['id']) }}" class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center hover:bg-blue-100 transition-colors" title="Edit"><i class="fas fa-edit text-xs"></i></a>
                                    <button @click="quickAction('{{ route('receptionist.student-enquiries.destroy', $row['id']) }}', 'Delete Enquiry', 'DELETE')" class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center hover:bg-rose-100 transition-colors" title="Delete"><i class="fas fa-trash text-xs"></i></button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>

                    <!-- Dynamic Table Body -->
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700 transition-opacity duration-150" x-cloak :class="loading ? 'opacity-50' : 'opacity-100'">
                        <template x-for="row in rows" :key="row.id">
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-xs font-bold text-teal-600">
                                    <span class="bg-teal-50 px-2 py-1 rounded-lg border border-teal-100" x-text="row.enquiry_no"></span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-teal-500 to-emerald-500 flex items-center justify-center text-white font-bold text-xs shadow-sm" x-text="row.initials"></div>
                                        <div>
                                            <div class="text-sm font-bold text-gray-800 dark:text-gray-100" x-text="row.student_name"></div>
                                            <div class="text-[10px] font-medium text-gray-400">Student Name</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="space-y-1">
                                        <div class="text-xs font-bold text-gray-700 dark:text-gray-200" x-text="row.father_name"></div>
                                        <div class="flex items-center gap-1.5 text-[10px] text-gray-400">
                                            <i class="fas fa-phone text-[9px]"></i>
                                            <span x-text="row.contact_no"></span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="space-y-1">
                                        <div class="text-xs font-bold text-gray-700 dark:text-gray-200" x-text="row.class_name"></div>
                                        <div class="text-[10px] text-gray-500 dark:text-gray-400">AY: <span x-text="row.academic_year"></span></div>
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
                                    <div class="space-y-1">
                                        <div class="text-[10px] font-bold text-gray-700 dark:text-gray-300">
                                            <i class="far fa-calendar mr-1 text-gray-400"></i>
                                            Enq: <span x-text="row.enquiry_date"></span>
                                        </div>
                                        <div class="text-[9px] text-rose-500 font-medium">Follow: <span x-text="row.follow_up"></span></div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <a :href="`/receptionist/student-enquiries/${row.id}/edit`" class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center hover:bg-blue-100 transition-colors" title="Edit"><i class="fas fa-edit text-xs"></i></a>
                                        <button @click="quickAction(`/receptionist/student-enquiries/${row.id}`, 'Delete Enquiry', 'DELETE')" class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center hover:bg-rose-100 transition-colors" title="Delete"><i class="fas fa-trash text-xs"></i></button>
                                    </div>
                                </td>
                            </tr>
                        </template>

                        <x-table.empty-state :colspan="7" icon="fas fa-clipboard-list" message="No enquiries found matching your criteria." />
                    </tbody>
                </table>
            </div>

            <!-- Server-rendered pagination: visible instantly, hidden once Alpine takes over -->
            @if($initialData['pagination']['total'] > 0)
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50" :class="{ 'hidden': true }">
                <div class="text-sm text-gray-700 dark:text-gray-300">
                    Showing {{ $initialData['pagination']['from'] }} to {{ $initialData['pagination']['to'] }} of {{ $initialData['pagination']['total'] }} results
                </div>
            </div>
            @endif

            <x-table.pagination />
        </div>

        <x-confirm-modal />
    </div>
@endsection

