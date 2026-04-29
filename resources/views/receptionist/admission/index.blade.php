@extends('layouts.receptionist')

@section('title', 'Admission Confirmation Ledger')

@section('content')
    <div x-data="Object.assign(ajaxDataTable({
        fetchUrl: '{{ route('receptionist.admission.fetch') }}',
        defaultSort: 'created_at',
        defaultDirection: 'desc',
        defaultPerPage: 25,
        defaultFilters: { class_id: '', section_id: '' },
        initialRows: @js($initialData['rows']),
        initialPagination: @js($initialData['pagination']),
        initialStats: @js($initialData['stats']),
        filterLabels: {
            class_id: {
                @foreach($classes as $c) '{{ $c->id }}': '{{ $c->name }}', @endforeach
            },
            section_id: {
                @foreach($sections as $s) '{{ $s->id }}': '{{ $s->name }}', @endforeach
            }
        }
    }), admissionConfirmation())" class="space-y-6">
        
        <!-- Institutional Analytics -->
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <x-stat-card label="Total Registrations" :value="$stats['total_registration']" icon="fas fa-file-alt" color="blue" alpine-text="stats.total_registration" />
            <x-stat-card label="Confirmed Admitted" :value="$stats['admission_done']" icon="fas fa-user-check" color="emerald" alpine-text="stats.admission_done" />
            <x-stat-card label="Pending Approval" :value="$stats['pending_registration']" icon="fas fa-clock" color="amber" alpine-text="stats.pending_registration" />
            <x-stat-card label="Cancelled Nodes" :value="$stats['cancelled_registration']" icon="fas fa-times-circle" color="rose" alpine-text="stats.cancelled_registration" />
            <x-stat-card label="Source Leads" :value="$stats['total_enquiry']" icon="fas fa-question-circle" color="purple" alpine-text="stats.total_enquiry" />
        </div>

        <!-- Header Section -->
        <x-page-header title="Admission Registry" description="Manage confirmed student admissions and academic cluster assignments." icon="fas fa-user-graduate">
            <a href="{{ route('receptionist.admission.create') }}"
                class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-teal-600 to-emerald-600 hover:from-teal-700 hover:to-emerald-700 text-white text-sm font-semibold rounded-xl transition-all shadow-md hover:shadow-lg active:scale-95">
                <i class="fas fa-user-plus mr-2 text-xs"></i>
                New Admission
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
                        <h2 class="text-lg font-semibold text-gray-800 dark:text-white">Admission List</h2>
                        <x-table.search placeholder="Search admission records..." />
                    </div>

                    <!-- Right: Filters and Actions -->
                    <div class="flex items-center gap-3">
                        <x-table.filter-select
                            model="filters.class_id"
                            action="applyFilter('class_id', $event.target.value)"
                            placeholder="Class"
                            :options="$classes->pluck('name', 'id')->toArray()"
                        />

                        <x-table.filter-select
                            model="filters.section_id"
                            action="applyFilter('section_id', $event.target.value)"
                            placeholder="Section"
                            :options="$sections->pluck('name', 'id')->toArray()"
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
                            <x-table.sort-header column="admission_no" label="Admission No" sort-var="sort" direction-var="direction" />
                            <x-table.sort-header column="first_name" label="Student Name" sort-var="sort" direction-var="direction" />
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Class & Section</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Parent & Contact</th>
                            <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                            <x-table.sort-header column="admission_date" label="Admission Date" sort-var="sort" direction-var="direction" />
                            <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider w-32">Actions</th>
                        </tr>
                    </thead>

                    <!-- Initial Blade Render (Zero Blink) -->
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700" data-ssr x-show="!hydrated">
                        @if(empty($initialData['rows']))
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <i class="fas fa-user-graduate text-4xl text-gray-300 mb-4"></i>
                                    <p class="text-lg text-gray-500">No admissions found matching your criteria.</p>
                                </div>
                            </td>
                        </tr>
                        @endif
                        @foreach($initialData['rows'] as $row)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/50 transition-colors group">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-[10px] font-black text-teal-600 bg-teal-50 px-2.5 py-1.5 rounded-lg border border-teal-100 uppercase tabular-nums tracking-widest">#{{ $row['admission_no'] }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-gray-50 flex items-center justify-center overflow-hidden border border-gray-100 shadow-sm relative">
                                        @if($row['student_photo'])
                                            <img src="{{ $row['student_photo'] }}" class="w-full h-full object-cover">
                                        @else
                                            <div class="w-full h-full bg-gradient-to-br from-teal-50 to-teal-100 flex items-center justify-center text-teal-600 font-bold text-xs">{{ $row['initials'] }}</div>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="text-sm font-bold text-gray-800 dark:text-gray-100">{{ $row['full_name'] }}</div>
                                        <div class="text-[10px] font-medium text-gray-400">#{{ $row['admission_no'] }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex flex-col">
                                    <span class="text-xs font-bold text-gray-700 dark:text-gray-200">{{ $row['class_name'] }}</span>
                                    <span class="text-[10px] font-medium text-teal-500 mt-0.5">Section {{ $row['section_name'] }}</span>
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
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full border bg-emerald-50 text-emerald-700 border-emerald-100 text-[10px] font-bold uppercase tracking-wider shadow-sm">
                                    <i class="fas fa-check-circle text-[8px]"></i>
                                    Confirmed
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-xs font-bold text-gray-700 dark:text-gray-300 tabular-nums">
                                    <i class="far fa-calendar-check mr-1.5 text-teal-500"></i>
                                    {{ $row['admission_date'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('receptionist.admission.pdf', $row['id']) }}" class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center hover:bg-rose-100 transition-colors" title="Download PDF"><i class="fas fa-file-pdf text-xs"></i></a>
                                    <a href="{{ route('receptionist.admission.show', $row['id']) }}" class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center hover:bg-blue-100 transition-colors" title="View"><i class="fas fa-eye text-xs"></i></a>
                                    <a href="{{ route('receptionist.admission.edit', $row['id']) }}" class="w-8 h-8 rounded-lg bg-teal-50 text-teal-600 flex items-center justify-center hover:bg-teal-100 transition-colors" title="Edit"><i class="fas fa-edit text-xs"></i></a>
                                    <button @click="quickAction(`/receptionist/admission/${row['id']}`, 'Delete Admission', 'DELETE')" class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center hover:bg-rose-100 transition-colors" title="Delete"><i class="fas fa-trash-alt text-xs"></i></button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>

                    <!-- Dynamic Table Body (Successive Hydration) -->
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700 transition-opacity duration-150" x-show="hydrated" :class="loading && rows.length &gt; 0 ? 'opacity-50' : 'opacity-100'">
                        <template x-for="row in rows" :key="row.id">
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/50 transition-colors group">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-[10px] font-black text-teal-600 bg-teal-50 px-2.5 py-1.5 rounded-lg border border-teal-100 uppercase tabular-nums tracking-widest" x-text="'#' + row.admission_no"></span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-gray-50 flex items-center justify-center overflow-hidden border border-gray-100 shadow-sm relative">
                                            <template x-if="row.student_photo">
                                                <img :src="row.student_photo" class="w-full h-full object-cover">
                                            </template>
                                            <template x-if="!row.student_photo">
                                                <div class="w-full h-full bg-gradient-to-br from-teal-50 to-teal-100 flex items-center justify-center text-teal-600 font-bold text-xs" x-text="row.initials"></div>
                                            </template>
                                        </div>
                                        <div>
                                            <div class="text-sm font-bold text-gray-800 dark:text-gray-100" x-text="row.full_name"></div>
                                            <div class="text-[10px] font-medium text-gray-400" x-text="'#' + row.admission_no"></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex flex-col">
                                        <span class="text-xs font-bold text-gray-700 dark:text-gray-200" x-text="row.class_name"></span>
                                        <span class="text-[10px] font-medium text-teal-500 mt-0.5" x-text="'Section ' + row.section_name"></span>
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
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full border bg-emerald-50 text-emerald-700 border-emerald-100 text-[10px] font-bold uppercase tracking-wider shadow-sm">
                                        <i class="fas fa-check-circle text-[8px]"></i>
                                        Confirmed
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-xs font-bold text-gray-700 dark:text-gray-300 tabular-nums">
                                        <i class="far fa-calendar-check mr-1.5 text-teal-500"></i>
                                        <span x-text="row.admission_date"></span>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <a :href="`/receptionist/admission/${row.id}/pdf`" class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center hover:bg-rose-100 transition-colors" title="Download PDF"><i class="fas fa-file-pdf text-xs"></i></a>
                                        <a :href="`/receptionist/admission/${row.id}`" class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center hover:bg-blue-100 transition-colors" title="View"><i class="fas fa-eye text-xs"></i></a>
                                        <a :href="`/receptionist/admission/${row.id}/edit`" class="w-8 h-8 rounded-lg bg-teal-50 text-teal-600 flex items-center justify-center hover:bg-teal-100 transition-colors" title="Edit"><i class="fas fa-edit text-xs"></i></a>
                                        <button @click="quickAction(`/receptionist/admission/${row.id}`, 'Delete Admission', 'DELETE')" class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center hover:bg-rose-100 transition-colors" title="Delete"><i class="fas fa-trash-alt text-xs"></i></button>
                                    </div>
                                </td>
                            </tr>
                        </template>

                        <x-table.empty-state :colspan="7" icon="fas fa-user-graduate" message="No admissions found matching your criteria." />
                    </tbody>
                </table>
            </div>

            <!-- Server-rendered pagination: visible instantly, hidden once Alpine takes over -->
            <x-table.pagination :initial="$initialData['pagination']" />
        </div>

        <x-confirm-modal />
    </div>
@endsection

@push('scripts')
<script>
    function admissionConfirmation() {
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
