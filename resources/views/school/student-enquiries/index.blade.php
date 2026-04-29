@extends('layouts.school')

@section('title', 'Student Enquiry Registry')

@section('content')
    <div x-data="Object.assign(ajaxDataTable({
        fetchUrl: '{{ route('school.student-enquiries.fetch') }}',
        defaultSort: 'created_at',
        defaultDirection: 'desc',
        defaultPerPage: 25,
        defaultFilters: {
            status: '{{ $activeFilters['status'] }}',
            class_id: '{{ $activeFilters['class_id'] }}',
            academic_year_id: '{{ $activeFilters['academic_year_id'] }}',
            follow_up_today: '{{ $activeFilters['follow_up_today'] }}'
        },
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
    }), studentEnquiry())" class="space-y-6">
        
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
            <a href="{{ route('school.student-enquiries.create') }}"
                class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-teal-600 to-emerald-600 hover:from-teal-700 hover:to-emerald-700 text-white text-sm font-semibold rounded-xl transition-all shadow-md hover:shadow-lg active:scale-95">
                <i class="fas fa-plus mr-2 text-xs"></i>
                New Enquiry
            </a>
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

                        {{-- Follow-up Today chip --}}
                        {{-- defaultFilters passes the initial value so Alpine starts correct — no blink. --}}
                        <button @click="applyFilter('follow_up_today', filters.follow_up_today ? '' : '1')"
                                :class="filters.follow_up_today
                                    ? 'bg-rose-500 text-white border-rose-500'
                                    : 'bg-white dark:bg-gray-700 text-gray-600 dark:text-gray-300 border-gray-300 dark:border-gray-600 hover:border-rose-400 hover:text-rose-600'"
                                class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold rounded-lg border transition-colors whitespace-nowrap">
                            <i class="fas fa-bell text-[10px]"></i>
                            Follow-up Today
                        </button>
                            <i class="fas fa-bell text-[10px]"></i>
                            Follow-up Today
                        </button>
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
                            <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider w-40">Actions</th>
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
                                <div class="flex items-center justify-center gap-1.5">
                                    <a href="{{ route('school.student-enquiries.show', $row['id']) }}" class="w-8 h-8 rounded-lg bg-gray-50 text-gray-500 flex items-center justify-center hover:bg-gray-100 transition-colors" title="View"><i class="fas fa-eye text-xs"></i></a>
                                    @if($row['can_edit'])
                                    <a href="{{ route('school.student-enquiries.edit', $row['id']) }}" class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center hover:bg-blue-100 transition-colors" title="Edit"><i class="fas fa-edit text-xs"></i></a>
                                    @endif
                                    <div class="relative" x-data="{ open: false }">
                                        <button @click="open = !open" class="w-8 h-8 rounded-lg bg-teal-50 text-teal-600 flex items-center justify-center hover:bg-teal-100 transition-colors" title="Change Status"><i class="fas fa-exchange-alt text-xs"></i></button>
                                        <div x-show="open" @click.outside="open = false" x-cloak
                                             class="absolute right-0 mt-1 w-40 bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-100 dark:border-gray-700 z-20 py-1">
                                            @foreach(\App\Enums\EnquiryStatus::cases() as $s)
                                            <button @click="updateStatus('{{ route('school.student-enquiries.update-status', $row['id']) }}', {{ $s->value }}, $el.closest('[x-data]')); open = false"
                                                    class="w-full text-left px-3 py-2 text-xs font-semibold hover:bg-gray-50 dark:hover:bg-gray-700 flex items-center gap-2">
                                                <i class="fas {{ match($s) { \App\Enums\EnquiryStatus::Pending=>'fa-clock text-yellow-500', \App\Enums\EnquiryStatus::Completed=>'fa-check-circle text-blue-500', \App\Enums\EnquiryStatus::Cancelled=>'fa-times-circle text-red-500', \App\Enums\EnquiryStatus::Admitted=>'fa-user-check text-green-500' } }} text-[10px]"></i>
                                                {{ $s->label() }}
                                            </button>
                                            @endforeach
                                        </div>
                                    </div>
                                    <button @click="quickAction('{{ route('school.student-enquiries.destroy', $row['id']) }}', 'Delete Enquiry', 'DELETE')" class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center hover:bg-rose-100 transition-colors" title="Delete"><i class="fas fa-trash text-xs"></i></button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>

                    <!-- Dynamic Table Body -->
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700 transition-opacity duration-150" x-show="hydrated" :class="loading && rows.length &gt; 0 ? 'opacity-50' : 'opacity-100'">
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
                                    <div class="flex items-center justify-center gap-1.5">
                                        <a :href="`/school/student-enquiries/${row.id}`" class="w-8 h-8 rounded-lg bg-gray-50 text-gray-500 flex items-center justify-center hover:bg-gray-100 transition-colors" title="View"><i class="fas fa-eye text-xs"></i></a>
                                        <a x-show="row.can_edit" :href="`/school/student-enquiries/${row.id}/edit`" class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center hover:bg-blue-100 transition-colors" title="Edit"><i class="fas fa-edit text-xs"></i></a>
                                        <div class="relative" x-data="{ open: false }">
                                            <button @click="open = !open" class="w-8 h-8 rounded-lg bg-teal-50 text-teal-600 flex items-center justify-center hover:bg-teal-100 transition-colors" title="Change Status"><i class="fas fa-exchange-alt text-xs"></i></button>
                                            <div x-show="open" @click.outside="open = false" x-cloak
                                                 class="absolute right-0 mt-1 w-40 bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-100 dark:border-gray-700 z-20 py-1">
                                                <template x-for="opt in statusOptions" :key="opt.value">
                                                    <button @click="updateStatus(`/school/student-enquiries/${row.id}/status`, opt.value, row); open = false"
                                                            class="w-full text-left px-3 py-2 text-xs font-semibold hover:bg-gray-50 dark:hover:bg-gray-700 flex items-center gap-2">
                                                        <i class="fas text-[10px]" :class="opt.icon"></i>
                                                        <span x-text="opt.label"></span>
                                                    </button>
                                                </template>
                                            </div>
                                        </div>
                                        <button @click="quickAction(`/school/student-enquiries/${row.id}`, 'Delete Enquiry', 'DELETE')" class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center hover:bg-rose-100 transition-colors" title="Delete"><i class="fas fa-trash text-xs"></i></button>
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

            <x-table.pagination :initial="$initialData['pagination']" />
        </div>

        <x-confirm-modal />
    </div>

    @push('scripts')
        <script>
            function studentEnquiry() {
                return {
                    statusOptions: [
                        { value: 1, label: 'Pending',   icon: 'fa-clock text-yellow-500' },
                        { value: 2, label: 'Completed', icon: 'fa-check-circle text-blue-500' },
                        { value: 3, label: 'Cancelled', icon: 'fa-times-circle text-red-500' },
                        { value: 4, label: 'Admitted',  icon: 'fa-user-check text-green-500' },
                    ],

                    async updateStatus(url, statusValue, row) {
                        try {
                            const response = await fetch(url, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify({ _method: 'PATCH', form_status: statusValue })
                            });
                            const result = await response.json();
                            if (response.ok) {
                                row.status_label  = result.status_label;
                                row.status_config = result.status_config;
                                if (typeof this.refreshStats === 'function') this.refreshStats();
                                if (window.Toast) window.Toast.fire({ icon: 'success', title: result.message });
                            } else {
                                if (window.Toast) window.Toast.fire({ icon: 'error', title: result.message || 'Update failed' });
                            }
                        } catch (e) {
                            if (window.Toast) window.Toast.fire({ icon: 'error', title: 'Connection error' });
                        }
                    },

                    async quickAction(url, title, method = 'POST', message = 'Are you sure you want to proceed with this action?') {
                        const self = this;
                        window.dispatchEvent(new CustomEvent('open-confirm-modal', {
                            detail: {
                                title: title,
                                message: message,
                                callback: async () => {
                                    try {
                                        const response = await fetch(url, {
                                            method: 'POST',
                                            headers: {
                                                'Content-Type': 'application/json',
                                                'Accept': 'application/json',
                                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                            },
                                            body: JSON.stringify({ _method: method })
                                        });

                                        const result = await response.json();

                                        if (response.ok) {
                                            if (window.Toast) window.Toast.fire({ icon: 'success', title: result.message || 'Action completed successfully' });
                                            if (typeof self.refreshTable === 'function') self.refreshTable();
                                        } else {
                                            if (window.Toast) window.Toast.fire({ icon: 'error', title: result.message || 'Action failed' });
                                        }
                                    } catch (error) {
                                        if (window.Toast) window.Toast.fire({ icon: 'error', title: 'Connection error' });
                                    }
                                }
                            }
                        }));
                    }
                }
            }
        </script>
    @endpush
@endsection

