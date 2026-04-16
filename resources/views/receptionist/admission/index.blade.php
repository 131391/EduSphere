@extends('layouts.receptionist')

@section('title', 'Admission Confirmation Ledger')

@section('content')
    <div x-data="ajaxDataTable({
        fetchUrl: '{{ route('receptionist.admission.index') }}',
        defaultSort: 'created_at',
        defaultDirection: 'desc',
        defaultPerPage: 25,
        defaultFilters: { class_id: '', section_id: '', search: '' },
        initialRows: @js($initialData['rows']),
        initialPagination: @js($initialData['pagination']),
        initialStats: @js($initialData['stats']),
        filterLabels: {
            class_id: {
                @foreach($classes as $c) '{{ $c->id }}': '{{ $c->class_name }}', @endforeach
            },
            section_id: {
                @foreach($sections as $s) '{{ $s->id }}': '{{ $s->section_name }}', @endforeach
            }
        }
    })" class="space-y-6">
        
        <!-- Institutional Analytics -->
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <x-stat-card label="Total Registrations" :value="$stats['total_registration']" icon="fas fa-file-alt" color="blue" alpine-text="stats.total_registration" />
            <x-stat-card label="Confirmed Admitted" :value="$stats['admission_done']" icon="fas fa-user-check" color="emerald" alpine-text="stats.admission_done" />
            <x-stat-card label="Pending Approval" :value="$stats['pending_registration']" icon="fas fa-clock" color="amber" alpine-text="stats.pending_registration" />
            <x-stat-card label="Cancelled Nodes" :value="$stats['cancelled_registration']" icon="fas fa-times-circle" color="rose" alpine-text="stats.cancelled_registration" />
            <x-stat-card label="Source Leads" :value="$stats['total_enquiry']" icon="fas fa-question-circle" color="purple" alpine-text="stats.total_enquiry" />
        </div>

        <!-- Page Header & Action Bar -->
        <div x-data="{ searchOpen: true }">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 border border-teal-100/50 dark:border-gray-700 mb-6">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-teal-100 flex items-center justify-center text-teal-600 shadow-sm">
                            <i class="fas fa-user-graduate text-xs"></i>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-gray-800 dark:text-white">Admission Registry</h2>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 uppercase font-bold tracking-widest leading-tight">Institutional Enrollment Hierarchy</p>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <button type="button" @click="searchOpen = !searchOpen"
                            class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 text-gray-700 dark:text-gray-200 text-xs font-semibold rounded-xl hover:bg-gray-50 transition-all shadow-sm border-dashed">
                            <i class="fas fa-filter mr-2 text-teal-500"></i>
                            Protocol Filters
                        </button>
                        <a href="{{ route('receptionist.admission.create') }}"
                            class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-teal-600 to-emerald-600 hover:from-teal-700 hover:to-emerald-700 text-white text-sm font-semibold rounded-xl transition-all shadow-md hover:shadow-lg active:scale-95">
                            <i class="fas fa-user-plus mr-2 text-xs"></i>
                            New Admission
                        </a>
                        <button @click="exportData()"
                            class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-slate-700 to-slate-900 hover:from-black hover:to-slate-800 text-white text-sm font-semibold rounded-xl transition-all shadow-md hover:shadow-lg active:scale-95 border border-slate-600/50">
                            <i class="fas fa-file-csv mr-2 text-xs text-amber-500"></i>
                            Export CSV
                        </button>
                    </div>
                </div>
            </div>

            <!-- Advanced Filter Grid -->
            <div x-show="searchOpen" x-collapse x-cloak
                class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 mb-6">
                <div class="grid grid-cols-1 md:grid-cols-4 lg:grid-cols-6 gap-6">
                    <!-- Global Search -->
                    <div class="lg:col-span-2">
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5 px-1">Identity Search</label>
                        <div class="relative group">
                            <input type="text" x-model="search" placeholder="Name, Admission ID, Father's Name..."
                                class="w-full h-11 pl-10 bg-gray-50 dark:bg-gray-700 border-gray-200 dark:border-gray-600 rounded-xl text-sm text-gray-700 dark:text-gray-200 focus:ring-4 focus:ring-teal-500/10 focus:border-teal-500 transition-all outline-none">
                            <div class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-teal-500 transition-colors">
                                <i class="fas fa-search text-xs"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Academic Cluster -->
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5 px-1">Cluster node</label>
                        <select x-model="filters.class_id" @change="applyFilter('class_id', $event.target.value)"
                            class="w-full h-11 px-4 bg-gray-50 dark:bg-gray-700 border-gray-200 dark:border-gray-600 rounded-xl text-sm text-gray-700 dark:text-gray-200 focus:ring-4 focus:ring-teal-500/10 focus:border-teal-500 transition-all outline-none">
                            <option value="">All Classes</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}">{{ $class->class_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Section -->
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5 px-1">Section segment</label>
                        <select x-model="filters.section_id" @change="applyFilter('section_id', $event.target.value)"
                            class="w-full h-11 px-4 bg-gray-50 dark:bg-gray-700 border-gray-200 dark:border-gray-600 rounded-xl text-sm text-gray-700 dark:text-gray-200 focus:ring-4 focus:ring-teal-500/10 focus:border-teal-500 transition-all outline-none font-bold tabular-nums">
                            <option value="">All Sections</option>
                            @foreach($sections as $section)
                                <option value="{{ $section->id }}">{{ $section->section_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-end gap-2 lg:col-span-2">
                        <button type="button" @click="clearAllFilters()"
                            x-show="hasActiveFilters() || search !== ''"
                            class="flex-1 h-11 flex items-center justify-center bg-rose-50 dark:bg-rose-900/20 text-rose-600 dark:text-rose-400 border border-rose-100 dark:border-rose-900/30 rounded-xl hover:bg-rose-100 dark:hover:bg-rose-900/40 transition-all shadow-sm px-4">
                            <i class="fas fa-trash-alt text-[10px] mr-2"></i> 
                            <span class="text-[10px] font-black uppercase tracking-widest">Reset Protocol</span>
                        </button>
                        <div class="w-24">
                            <x-table.per-page model="perPage" action="changePerPage($event.target.value)" />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- AJAX Data Ledger Table -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden shadow-xl shadow-teal-500/5">
            <div class="overflow-x-auto relative min-h-[400px]">
                <x-table.loading-overlay />
                
                <table class="w-full text-left border-collapse">
                    <thead class="bg-gray-50/50 dark:bg-gray-700/50 border-b border-gray-100 dark:border-gray-700">
                        <tr>
                            <x-table.sort-header column="admission_no" label="Admission ID" sort-var="sort" direction-var="direction" />
                            <x-table.sort-header column="first_name" label="Student Identity" sort-var="sort" direction-var="direction" />
                            <th class="px-6 py-3 text-[10px] font-black text-gray-400 uppercase tracking-widest">Academic Cluster</th>
                            <th class="px-6 py-3 text-[10px] font-black text-gray-400 uppercase tracking-widest">Guardian Entity</th>
                            <th class="px-6 py-3 text-[10px] font-black text-gray-400 uppercase tracking-widest text-center">Protocol Stance</th>
                            <x-table.sort-header column="admission_date" label="Logged Date" sort-var="sort" direction-var="direction" />
                            <th class="px-6 py-3 text-center text-[10px] font-black text-gray-400 uppercase tracking-widest w-32">Operations</th>
                        </tr>
                    </thead>

                    <!-- Initial Blade Render (Zero Blink) -->
                    <tbody x-show="!rows.length || (initialLoad && rows.length && initialRows.length === rows.length)" class="divide-y divide-gray-100 dark:divide-gray-700">
                        @foreach($initialData['rows'] as $row)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/50 transition-colors group">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-[10px] font-black text-teal-600 bg-teal-50 px-2.5 py-1.5 rounded-lg border border-teal-100 uppercase tabular-nums tracking-widest">#{{ $row['admission_no'] }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-gray-50 flex items-center justify-center overflow-hidden border border-gray-100 shadow-sm relative">
                                        @if($row['photo'])
                                            <img src="{{ $row['photo'] }}" class="w-full h-full object-cover">
                                        @else
                                            <div class="w-full h-full bg-gradient-to-br from-teal-50 to-teal-100 flex items-center justify-center text-teal-600 font-bold text-xs">{{ $row['initials'] }}</div>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="text-xs font-black text-gray-800 dark:text-gray-100 uppercase tracking-tight">{{ $row['full_name'] }}</div>
                                        <div class="text-[9px] font-bold text-gray-400 uppercase tracking-widest mt-0.5">Primary Student Node</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex flex-col">
                                    <span class="text-xs font-black text-gray-700 dark:text-gray-200 uppercase tracking-tight">{{ $row['class_name'] }}</span>
                                    <span class="text-[10px] font-bold text-teal-500 uppercase leading-none mt-0.5 tracking-widest">Section {{ $row['section_name'] }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="space-y-1">
                                    <div class="text-xs font-bold text-gray-700 dark:text-gray-200 uppercase">{{ $row['father_name'] }}</div>
                                    <div class="text-[10px] font-black text-gray-400 uppercase tabular-nums tracking-tighter">PH: {{ $row['phone'] }}</div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border bg-emerald-50 text-emerald-700 border-emerald-100 text-[9px] font-black uppercase tracking-widest shadow-sm">
                                    <i class="fas fa-check-circle text-[8px] animate-pulse"></i>
                                    Confirmed
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-[10px] font-black text-gray-400 uppercase tabular-nums tracking-widest">{{ $row['admission_date'] }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <div class="flex items-center justify-center gap-1.5 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <a href="{{ route('receptionist.admission.pdf', $row['id']) }}" class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center hover:bg-rose-100 transition-colors" title="PDF Profile"><i class="fas fa-file-pdf text-xs"></i></a>
                                    <a href="{{ route('receptionist.admission.show', $row['id']) }}" class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center hover:bg-blue-100 transition-colors" title="Analyze Identity"><i class="fas fa-eye text-xs"></i></a>
                                    <a href="{{ route('receptionist.admission.edit', $row['id']) }}" class="w-8 h-8 rounded-lg bg-teal-50 text-teal-600 flex items-center justify-center hover:bg-teal-100 transition-colors" title="Modify Index"><i class="fas fa-edit text-xs"></i></a>
                                    <button @click="quickAction(`/receptionist/admission/${row['id']}`, 'Purge Student Record', 'DELETE')" class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center hover:bg-rose-100 transition-colors" title="Purge Record"><i class="fas fa-trash-alt text-xs"></i></button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>

                    <!-- Dynamic Table Body (Successive Hydration) -->
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700" x-show="rows.length && !initialLoad" x-cloak>
                        <template x-for="row in rows" :key="row.id">
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/50 transition-colors group">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-[10px] font-black text-teal-600 bg-teal-50 px-2.5 py-1.5 rounded-lg border border-teal-100 uppercase tabular-nums tracking-widest" x-text="'#' + row.admission_no"></span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-gray-50 flex items-center justify-center overflow-hidden border border-gray-100 shadow-sm relative">
                                            <template x-if="row.photo">
                                                <img :src="row.photo" class="w-full h-full object-cover">
                                            </template>
                                            <template x-if="!row.photo">
                                                <div class="w-full h-full bg-gradient-to-br from-teal-50 to-teal-100 flex items-center justify-center text-teal-600 font-bold text-xs" x-text="row.initials"></div>
                                            </template>
                                        </div>
                                        <div>
                                            <div class="text-xs font-black text-gray-800 dark:text-gray-100 uppercase tracking-tight" x-text="row.full_name"></div>
                                            <div class="text-[9px] font-bold text-gray-400 uppercase tracking-widest mt-0.5">Primary Student Node</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex flex-col">
                                        <span class="text-xs font-black text-gray-700 dark:text-gray-200 uppercase tracking-tight" x-text="row.class_name"></span>
                                        <span class="text-[10px] font-bold text-teal-500 uppercase leading-none mt-0.5 tracking-widest" x-text="'Section ' + row.section_name"></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="space-y-1">
                                        <div class="text-xs font-bold text-gray-700 dark:text-gray-200 uppercase" x-text="row.father_name"></div>
                                        <div class="text-[10px] font-black text-gray-400 uppercase tabular-nums tracking-tighter" x-text="'PH: ' + row.phone"></div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border bg-emerald-50 text-emerald-700 border-emerald-100 text-[9px] font-black uppercase tracking-widest shadow-sm">
                                        <i class="fas fa-check-circle text-[8px] animate-pulse"></i>
                                        Confirmed
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-[10px] font-black text-gray-400 uppercase tabular-nums tracking-widest" x-text="row.admission_date"></span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="flex items-center justify-center gap-1.5 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <a :href="`/receptionist/admission/${row.id}/pdf`" class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center hover:bg-rose-100 transition-colors" title="PDF Profile"><i class="fas fa-file-pdf text-xs"></i></a>
                                        <a :href="`/receptionist/admission/${row.id}`" class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center hover:bg-blue-100 transition-colors" title="Analyze Identity"><i class="fas fa-eye text-xs"></i></a>
                                        <a :href="`/receptionist/admission/${row.id}/edit`" class="w-8 h-8 rounded-lg bg-teal-50 text-teal-600 flex items-center justify-center hover:bg-teal-100 transition-colors" title="Modify Index"><i class="fas fa-edit text-xs"></i></a>
                                        <button @click="quickAction(`/receptionist/admission/${row.id}`, 'Purge Student Record', 'DELETE')" class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center hover:bg-rose-100 transition-colors" title="Purge Record"><i class="fas fa-trash-alt text-xs"></i></button>
                                    </div>
                                </td>
                            </tr>
                        </template>

                        <x-table.empty-state :colspan="7" icon="fas fa-user-graduate" message="No student nodes found in the institutional matrix." />
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4 bg-gray-50/50 dark:bg-gray-700/50 border-t border-gray-100 dark:border-gray-700">
                <x-table.pagination />
            </div>
        </div>
    </div>
@endsection


