@extends('layouts.school')

@section('title', 'Student Registration Registry')

@section('content')
    <div x-data="Object.assign(ajaxDataTable({
        fetchUrl: '{{ route('school.student-registrations.fetch') }}',
        defaultSort: 'created_at',
        defaultDirection: 'desc',
        defaultPerPage: 25,
        defaultFilters: { admission_status: '', class_id: '' },
        initialRows: @js($initialData['rows']),
        initialPagination: @js($initialData['pagination']),
        initialStats: @js($initialData['stats']),
        filterLabels: {
            admission_status: {
                @foreach(\App\Enums\AdmissionStatus::cases() as $s) '{{ $s->value }}': '{{ $s->label() }}', @endforeach
            },
            class_id: {
                @foreach($classes as $c) '{{ $c->id }}': '{{ $c->name }}', @endforeach
            }
        }
    }), studentRegistration())" class="space-y-6">
        
        <!-- Institutional Analytics -->
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <x-stat-card label="Total Registrations" :value="$stats['total']" icon="fas fa-file-signature" color="blue" alpine-text="stats.total" />
            <x-stat-card label="Confirmed Admitted" :value="$stats['admitted']" icon="fas fa-user-graduate" color="emerald" alpine-text="stats.admitted" />
            <x-stat-card label="Pending Approval" :value="$stats['pending']" icon="fas fa-hourglass-half" color="amber" alpine-text="stats.pending" />
            <x-stat-card label="Cancelled Nodes" :value="$stats['cancelled']" icon="fas fa-ban" color="rose" alpine-text="stats.cancelled" />
            <x-stat-card label="Source Leads" :value="$stats['total_enquiry']" icon="fas fa-search-dollar" color="purple" alpine-text="stats.total_enquiry" />
        </div>

        <!-- Header Section -->
        <x-page-header title="Student Registration Registry" description="Manage student registration records and track admission workflow." icon="fas fa-id-card">
            <button type="button" @click="$dispatch('open-modal', 'import-modal')"
                class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl transition-all shadow-md hover:shadow-lg active:scale-95">
                <i class="fas fa-upload mr-2 text-xs"></i>
                Bulk Import
            </button>
            <a href="{{ route('school.student-registrations.create') }}"
                class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-teal-600 to-emerald-600 hover:from-teal-700 hover:to-emerald-700 text-white text-sm font-semibold rounded-xl transition-all shadow-md hover:shadow-lg active:scale-95">
                <i class="fas fa-plus mr-2 text-xs"></i>
                New Registration
            </a>
        </x-page-header>


        <!-- AJAX Data Table -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <!-- Table Header with Search and Filters -->
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <!-- Left: Title and Search -->
                    <div class="flex-1 flex flex-col md:flex-row md:items-center gap-4">
                        <h2 class="text-lg font-semibold text-gray-800 dark:text-white">Registration List</h2>
                        <x-table.search placeholder="Search registry records..." />
                    </div>

                    <!-- Right: Filters and Actions -->
                    <div class="flex items-center gap-3">
                        <x-table.filter-select
                            model="filters.admission_status"
                            action="applyFilter('admission_status', $event.target.value)"
                            placeholder="Status"
                            :options="collect(\App\Enums\AdmissionStatus::cases())->mapWithKeys(fn($s) => [$s->value => $s->label()])->toArray()"
                        />

                        <x-table.filter-select
                            model="filters.class_id"
                            action="applyFilter('class_id', $event.target.value)"
                            placeholder="Class"
                            :options="$classes->pluck('name', 'id')->toArray()"
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
                            <x-table.sort-header column="registration_no" label="Registration No" sort-var="sort" direction-var="direction" />
                            <x-table.sort-header column="first_name" label="Student Name" sort-var="sort" direction-var="direction" />
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Parent & Contact</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Class</th>
                            <x-table.sort-header column="admission_status" label="Status" sort-var="sort" direction-var="direction" />
                            <x-table.sort-header column="created_at" label="Registered On" sort-var="sort" direction-var="direction" />
                            <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider w-32">Actions</th>
                        </tr>
                    </thead>

                    <!-- Initial Blade Render (Zero Blink) -->
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700" :class="{ 'hidden': true }">
                        @if(empty($initialData['rows']))
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <i class="fas fa-folder-open text-4xl text-gray-300 mb-4"></i>
                                    <p class="text-lg text-gray-500">No registrations found matching your criteria.</p>
                                </div>
                            </td>
                        </tr>
                        @endif
                        @foreach($initialData['rows'] as $row)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/50 transition-colors group">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-[10px] font-black text-teal-600 bg-teal-50 px-2.5 py-1.5 rounded-lg border border-teal-100 uppercase tabular-nums tracking-widest">#{{ $row['registration_no'] }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-gray-50 flex items-center justify-center overflow-hidden border border-gray-100 shadow-sm relative">
                                        @if($row['student_photo'])
                                            <img src="{{ $row['student_photo'] }}" class="w-full h-full object-cover">
                                        @else
                                            <div class="w-full h-full bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center text-gray-400 font-bold text-xs">{{ $row['initials'] }}</div>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="text-sm font-bold text-gray-800 dark:text-gray-100">{{ $row['full_name'] }}</div>
                                        <div class="text-[10px] font-medium text-gray-400">#{{ $row['registration_no'] }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="space-y-1">
                                    <div class="text-xs font-bold text-gray-700 dark:text-gray-200">{{ $row['father_name'] }}</div>
                                    <div class="flex items-center gap-1.5 text-[10px] text-gray-400 tabular-nums font-medium">
                                        <i class="fas fa-phone-alt text-[8px] text-teal-500"></i>
                                        {{ $row['mobile_no'] }}
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="space-y-1">
                                    <div class="text-xs font-bold text-gray-700 dark:text-gray-200">{{ $row['class_name'] }}</div>
                                    <div class="text-[10px] text-gray-400 tabular-nums">AY: {{ $row['academic_year'] }}</div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php $config = $row['status_config']; @endphp
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border text-[9px] font-black uppercase tracking-widest shadow-sm {{ $config['bg'] }} {{ $config['text'] }} {{ $config['border'] }}">
                                    <i class="fas {{ $config['icon'] }} text-[8px] animate-pulse"></i>
                                    {{ $row['status_label'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="space-y-1 tabular-nums">
                                    <div class="text-xs font-bold text-gray-700 dark:text-gray-300">
                                        <i class="far fa-calendar-check mr-1.5 text-teal-500"></i>
                                        {{ $row['registration_date'] }}
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('school.student-registrations.show', $row['id']) }}" class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center hover:bg-emerald-100 transition-colors" title="View"><i class="fas fa-eye text-xs"></i></a>
                                    <a href="{{ route('school.student-registrations.pdf', $row['id']) }}" class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center hover:bg-blue-100 transition-colors" title="Download PDF"><i class="fas fa-file-pdf text-xs"></i></a>
                                    <a href="{{ route('school.student-registrations.edit', $row['id']) }}" class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center hover:bg-amber-100 transition-colors" title="Edit"><i class="fas fa-edit text-xs"></i></a>
                                    <button @click="quickAction(`/school/student-registrations/${row['id']}`, 'Delete Registration', 'DELETE')" class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center hover:bg-rose-100 transition-colors" title="Delete"><i class="fas fa-trash-alt text-xs"></i></button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>

                    <!-- Dynamic Table Body (Successive Hydration) -->
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700 transition-opacity duration-150" x-cloak :class="loading && rows.length &gt; 0 ? 'opacity-50' : 'opacity-100'">
                        <template x-for="row in rows" :key="row.id">
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/50 transition-colors group">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-[10px] font-black text-teal-600 bg-teal-50 px-2.5 py-1.5 rounded-lg border border-teal-100 uppercase tabular-nums tracking-widest" x-text="'#' + row.registration_no"></span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-gray-50 flex items-center justify-center overflow-hidden border border-gray-100 shadow-sm relative">
                                            <template x-if="row.student_photo">
                                                <img :src="row.student_photo" class="w-full h-full object-cover">
                                            </template>
                                            <template x-if="!row.student_photo">
                                                <div class="w-full h-full bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center text-gray-400 font-bold text-xs" x-text="row.initials"></div>
                                            </template>
                                        </div>
                                        <div>
                                            <div class="text-sm font-bold text-gray-800 dark:text-gray-100" x-text="row.full_name"></div>
                                            <div class="text-[10px] font-medium text-gray-400" x-text="'#' + row.registration_no"></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="space-y-1">
                                        <div class="text-xs font-bold text-gray-700 dark:text-gray-200" x-text="row.father_name"></div>
                                        <div class="flex items-center gap-1.5 text-[10px] text-gray-400 tabular-nums font-medium">
                                            <i class="fas fa-phone-alt text-[8px] text-teal-500"></i>
                                            <span x-text="row.mobile_no"></span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="space-y-1">
                                        <div class="text-xs font-bold text-gray-700 dark:text-gray-200" x-text="row.class_name"></div>
                                        <div class="text-[10px] text-gray-400 tabular-nums">AY: <span x-text="row.academic_year"></span></div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border text-[9px] font-black uppercase tracking-widest shadow-sm"
                                          :class="`${row.status_config.bg} ${row.status_config.text} ${row.status_config.border}`">
                                        <i class="fas text-[8px] animate-pulse" :class="row.status_config.icon"></i>
                                        <span x-text="row.status_label"></span>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="space-y-1 tabular-nums">
                                        <div class="text-xs font-bold text-gray-700 dark:text-gray-300">
                                            <i class="far fa-calendar-check mr-1.5 text-teal-500"></i>
                                            <span x-text="row.registration_date"></span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <a :href="`/school/student-registrations/${row.id}`" class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center hover:bg-emerald-100 transition-colors" title="View"><i class="fas fa-eye text-xs"></i></a>
                                        <a :href="`/school/student-registrations/${row.id}/pdf`" class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center hover:bg-blue-100 transition-colors" title="Download PDF"><i class="fas fa-file-pdf text-xs"></i></a>
                                        <a :href="`/school/student-registrations/${row.id}/edit`" class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center hover:bg-amber-100 transition-colors" title="Edit"><i class="fas fa-edit text-xs"></i></a>
                                        <button @click="quickAction(`/school/student-registrations/${row.id}`, 'Delete Registration', 'DELETE')" class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center hover:bg-rose-100 transition-colors" title="Delete"><i class="fas fa-trash-alt text-xs"></i></button>
                                    </div>
                                </td>
                            </tr>
                        </template>

                        <x-table.empty-state :colspan="7" icon="fas fa-folder-open" message="No registrations found matching your criteria." />
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

        <!-- Import Modal (Preserved Protocol) -->
        <x-modal name="import-modal" title="Bulk Registration Interface" max-width="lg">
            <form action="{{ route('school.student-registrations.import') }}" method="POST" enctype="multipart/form-data" class="p-0">
                @csrf
                <div class="p-8 space-y-8">
                    <div class="bg-indigo-50/50 border border-indigo-100 rounded-2xl p-6 flex flex-col gap-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-indigo-100 flex items-center justify-center text-indigo-600 shadow-sm">
                                <i class="fas fa-info-circle text-sm"></i>
                            </div>
                            <div>
                                <h4 class="text-xs font-black text-indigo-900 uppercase tracking-wider text-[10px]">Interface Guidelines</h4>
                                <p class="text-[9px] text-indigo-600 font-bold uppercase tracking-widest mt-0.5">Established Enrollment Protocol</p>
                            </div>
                        </div>
                        <p class="text-xs text-indigo-700/70 font-medium leading-relaxed">
                            To ensure institutional data integrity, please utilize the standardized CSV template. Map all required student nodes before initiating the transmission.
                        </p>
                        <a href="{{ route('school.student-registrations.download-template') }}" 
                            class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-white border border-indigo-100 text-indigo-600 text-[10px] font-black uppercase tracking-widest rounded-xl hover:bg-indigo-500 hover:text-white transition-all shadow-sm w-full">
                            <i class="fas fa-download text-[8px]"></i>
                            Download Registry Template
                        </a>
                    </div>

                    <div class="space-y-3">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">CSV Data Segment <span class="text-red-500 font-bold">*</span></label>
                        <div class="relative group">
                            <input type="file" name="file" accept=".csv" required 
                                class="w-full text-xs text-slate-500 font-bold
                                file:mr-4 file:py-3 file:px-6
                                file:rounded-xl file:border-0
                                file:text-[10px] file:font-black file:uppercase file:tracking-widest
                                file:bg-slate-900 file:text-white
                                hover:file:bg-slate-800 transition-all
                                cursor-pointer bg-slate-50 border border-slate-100 rounded-2xl pr-4">
                            <div class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-300 pointer-events-none group-focus-within:text-indigo-500 transition-colors">
                                <i class="fas fa-file-csv text-[10px]"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="px-8 py-6 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-4 rounded-b-3xl">
                    <button type="button" @click="$dispatch('close-modal', 'import-modal')"
                        class="px-6 py-3 text-[10px] font-black text-slate-400 hover:text-slate-600 uppercase tracking-widest transition-colors font-bold tracking-widest">
                        Cancel Protocol
                    </button>
                    <button type="submit" 
                        class="px-8 py-3 bg-gray-900 text-white text-[10px] font-black rounded-xl transition-all shadow-lg uppercase tracking-widest flex items-center gap-2 group hover:bg-black">
                        <i class="fas fa-upload text-[10px] group-hover:-translate-y-1 transition-transform"></i>
                        Initialize Node Import
                    </button>
                </div>
            </form>
        </x-modal>
    </div>

    @push('scripts')
        <script>
            function studentRegistration() {
                return {

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



