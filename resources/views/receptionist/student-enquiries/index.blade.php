@extends('layouts.receptionist')

@section('title', 'Student Enquiry Registry')

@section('content')
    <div x-data="ajaxDataTable({
        fetchUrl: '{{ route('receptionist.student-enquiries.index') }}',
        defaultSort: 'created_at',
        defaultDirection: 'desc',
        defaultPerPage: 25,
        defaultFilters: { status: '', class_id: '', academic_year_id: '', search: '' },
        initialRows: @js($initialData['rows']),
        initialPagination: @js($initialData['pagination']),
        initialStats: @js($initialData['stats']),
        filterLabels: {
            status: {
                @foreach(\App\Enums\EnquiryStatus::cases() as $s) '{{ $s->value }}': '{{ $s->label() }}', @endforeach
            },
            class_id: {
                @foreach($classes as $c) '{{ $c->id }}': '{{ $c->class_name }}', @endforeach
            },
            academic_year_id: {
                @foreach($academicYears as $y) '{{ $y->id }}': '{{ $y->year_name }}', @endforeach
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

        <!-- Page Header & Filters -->
        <div x-data="{ searchOpen: true }">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 border border-teal-100/50 dark:border-gray-700 mb-6">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-teal-100 flex items-center justify-center text-teal-600">
                            <i class="fas fa-user-graduate text-xs"></i>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-gray-800 dark:text-white">Enquiry Registry</h2>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5" x-text="'Managing ' + stats.total + ' enquiries in the registry'">Managing {{ number_format($stats['total']) }} enquiries in the registry</p>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <button type="button" @click="searchOpen = !searchOpen"
                            class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 text-gray-700 dark:text-gray-200 text-xs font-semibold rounded-xl hover:bg-gray-50 transition-all shadow-sm">
                            <i class="fas fa-filter mr-2 text-teal-500"></i>
                            Advanced Filters
                        </button>
                        <a href="{{ route('receptionist.student-enquiries.create') }}"
                            class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-teal-600 to-emerald-600 hover:from-teal-700 hover:to-emerald-700 text-white text-sm font-semibold rounded-xl transition-all shadow-md hover:shadow-lg active:scale-95">
                            <i class="fas fa-plus mr-2"></i>
                            New Enquiry
                        </a>
                        <button @click="exportData()"
                            class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-slate-700 to-slate-900 hover:from-black hover:to-slate-800 text-white text-sm font-semibold rounded-xl transition-all shadow-md hover:shadow-lg active:scale-95">
                            <i class="fas fa-file-excel mr-2 text-xs"></i>
                            Export CSV
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
                        <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1.5">Search Enquiries</label>
                        <div class="relative group">
                            <input type="text" x-model="search" placeholder="Name, Father, Enquiry No..."
                                class="w-full h-11 pl-10 bg-gray-50 dark:bg-gray-700 border-gray-200 dark:border-gray-600 rounded-xl text-sm text-gray-700 dark:text-gray-200 focus:ring-4 focus:ring-teal-500/10 focus:border-teal-500 transition-all outline-none">
                            <div class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-teal-500 transition-colors">
                                <i class="fas fa-search text-xs"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Status Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1.5">Form Status</label>
                        <select x-model="filters.status" @change="applyFilter('status', $event.target.value)"
                            class="w-full h-11 px-4 bg-gray-50 dark:bg-gray-700 border-gray-200 dark:border-gray-600 rounded-xl text-sm text-gray-700 dark:text-gray-200 focus:ring-4 focus:ring-teal-500/10 focus:border-teal-500 transition-all outline-none">
                            <option value="">All Statuses</option>
                            @foreach(\App\Enums\EnquiryStatus::cases() as $status)
                                <option value="{{ $status->value }}">{{ $status->label() }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Class Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1.5">Class</label>
                        <select x-model="filters.class_id" @change="applyFilter('class_id', $event.target.value)"
                            class="w-full h-11 px-4 bg-gray-50 dark:bg-gray-700 border-gray-200 dark:border-gray-600 rounded-xl text-sm text-gray-700 dark:text-gray-200 focus:ring-4 focus:ring-teal-500/10 focus:border-teal-500 transition-all outline-none">
                            <option value="">All Classes</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}">{{ $class->class_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-end gap-2 lg:col-span-2">
                        <button type="button" @click="clearAllFilters()"
                            x-show="hasActiveFilters() || search !== ''"
                            class="flex-1 h-11 flex items-center justify-center bg-rose-50 dark:bg-rose-900/20 text-rose-600 dark:text-rose-400 border border-rose-100 dark:border-rose-900/30 rounded-xl hover:bg-rose-100 dark:hover:bg-rose-900/40 transition-all shadow-sm font-semibold text-xs px-4">
                            <i class="fas fa-trash-alt text-[10px] mr-2"></i> Reset
                        </button>
                        <div class="w-24">
                            <x-table.per-page model="perPage" action="changePerPage($event.target.value)" />
                        </div>
                    </div>
                </div>

                <!-- Active Tags -->
                <div class="mt-4 flex flex-wrap gap-2" x-show="hasActiveFilters() || search !== ''" x-cloak>
                    <div x-show="search !== ''" class="flex items-center gap-1 bg-teal-100 text-teal-800 px-3 py-1 rounded-full text-xs">
                        <span>Search: <span x-text="search" class="font-semibold"></span></span>
                        <button @click="search = ''" class="ml-1 hover:text-teal-600"><i class="fas fa-times"></i></button>
                    </div>
                    <template x-for="(value, key) in filters" :key="key">
                        <div x-show="value !== ''" class="flex items-center gap-1 bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-xs">
                            <span x-text="getFilterLabel(key, value)"></span>
                            <button @click="removeFilter(key)" class="ml-1 hover:text-blue-600"><i class="fas fa-times"></i></button>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- AJAX Data Table -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="overflow-x-auto relative min-h-[400px]">
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
                    <tbody x-show="!rows.length || (initialLoad && rows.length && initialRows.length === rows.length)" class="divide-y divide-gray-100 dark:divide-gray-700">
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
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700" x-show="rows.length && !initialLoad" x-cloak>
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

            <div class="px-6 py-4 bg-gray-50/50 dark:bg-gray-700/50 border-t border-gray-100 dark:border-gray-700">
                <x-table.pagination />
            </div>
        </div>
    </div>
@endsection

