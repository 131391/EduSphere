@extends('layouts.receptionist')

@section('title', 'Student Registration Registry')

@section('content')
    <div x-data="ajaxDataTable({
        fetchUrl: '{{ route('receptionist.student-registrations.index') }}',
        defaultSort: 'created_at',
        defaultDirection: 'desc',
        defaultPerPage: 25,
        defaultFilters: { admission_status: '', class_id: '', search: '' },
        initialRows: @js($initialData['rows']),
        initialPagination: @js($initialData['pagination']),
        initialStats: @js($initialData['stats']),
        filterLabels: {
            admission_status: {
                @foreach(\App\Enums\AdmissionStatus::cases() as $s) '{{ $s->value }}': '{{ $s->label() }}', @endforeach
            },
            class_id: {
                @foreach($classes as $c) '{{ $c->id }}': '{{ $c->class_name }}', @endforeach
            }
        }
    })" class="space-y-6">
        
        <!-- Institutional Analytics -->
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <x-stat-card label="Total Registrations" :value="$stats['total']" icon="fas fa-file-signature" color="blue" alpine-text="stats.total" />
            <x-stat-card label="Confirmed Admitted" :value="$stats['admitted']" icon="fas fa-user-graduate" color="emerald" alpine-text="stats.admitted" />
            <x-stat-card label="Pending Approval" :value="$stats['pending']" icon="fas fa-hourglass-half" color="amber" alpine-text="stats.pending" />
            <x-stat-card label="Cancelled Nodes" :value="$stats['cancelled']" icon="fas fa-ban" color="rose" alpine-text="stats.cancelled" />
            <x-stat-card label="Source Leads" :value="$stats['total_enquiry']" icon="fas fa-search-dollar" color="purple" alpine-text="stats.total_enquiry" />
        </div>

        <!-- Page Header & Action Bar -->
        <div x-data="{ searchOpen: true }">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 border border-teal-100/50 dark:border-gray-700 mb-6">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-teal-100 flex items-center justify-center text-teal-600 shadow-sm">
                            <i class="fas fa-id-card text-xs"></i>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-gray-800 dark:text-white">Registration Ledger</h2>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 uppercase font-bold tracking-widest leading-tight">Institutional Enrollment Registry</p>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <button type="button" @click="$dispatch('open-modal', 'import-modal')"
                            class="inline-flex items-center px-4 py-2 bg-indigo-50 dark:bg-indigo-900/20 text-indigo-600 dark:text-indigo-400 border border-indigo-100 dark:border-indigo-900/30 text-xs font-semibold rounded-xl hover:bg-indigo-600 hover:text-white transition-all shadow-sm">
                            <i class="fas fa-upload mr-2 text-[10px]"></i>
                            Bulk Import
                        </button>
                        <button type="button" @click="searchOpen = !searchOpen"
                            class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 text-gray-700 dark:text-gray-200 text-xs font-semibold rounded-xl hover:bg-gray-50 transition-all shadow-sm border-dashed">
                            <i class="fas fa-filter mr-2 text-teal-500"></i>
                            Protocol Filters
                        </button>
                        <a href="{{ route('receptionist.student-registrations.create') }}"
                            class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-teal-600 to-emerald-600 hover:from-teal-700 hover:to-emerald-700 text-white text-sm font-semibold rounded-xl transition-all shadow-md hover:shadow-lg active:scale-95">
                            <i class="fas fa-plus mr-2"></i>
                            New Node
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
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5 px-1">Registry Search</label>
                        <div class="relative group">
                            <input type="text" x-model="search" placeholder="Name, ID, Mobile No..."
                                class="w-full h-11 pl-10 bg-gray-50 dark:bg-gray-700 border-gray-200 dark:border-gray-600 rounded-xl text-sm text-gray-700 dark:text-gray-200 focus:ring-4 focus:ring-teal-500/10 focus:border-teal-500 transition-all outline-none">
                            <div class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-teal-500 transition-colors">
                                <i class="fas fa-search text-xs"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Admission Status -->
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5 px-1">Protocol Stance</label>
                        <select x-model="filters.admission_status" @change="applyFilter('admission_status', $event.target.value)"
                            class="w-full h-11 px-4 bg-gray-50 dark:bg-gray-700 border-gray-200 dark:border-gray-600 rounded-xl text-sm text-gray-700 dark:text-gray-200 focus:ring-4 focus:ring-teal-500/10 focus:border-teal-500 transition-all outline-none font-bold uppercase tracking-wider text-[10px]">
                            <option value="">All Statuses</option>
                            @foreach(\App\Enums\AdmissionStatus::cases() as $status)
                                <option value="{{ $status->value }}">{{ $status->label() }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Academic Cluster -->
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5 px-1">Cluster</label>
                        <select x-model="filters.class_id" @change="applyFilter('class_id', $event.target.value)"
                            class="w-full h-11 px-4 bg-gray-50 dark:bg-gray-700 border-gray-200 dark:border-gray-600 rounded-xl text-sm text-gray-700 dark:text-gray-200 focus:ring-4 focus:ring-teal-500/10 focus:border-teal-500 transition-all outline-none">
                            <option value="">All Classes</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }} text-xs font-bold">{{ $class->class_name }}</option>
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

                <!-- Active Filter Tags -->
                <div class="mt-4 flex flex-wrap gap-2" x-show="hasActiveFilters() || search !== ''" x-cloak>
                    <div x-show="search !== ''" class="flex items-center gap-1 bg-teal-100 text-teal-800 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider">
                        <span>Query: <span x-text="search"></span></span>
                        <button @click="search = ''" class="ml-1 hover:text-teal-600"><i class="fas fa-times"></i></button>
                    </div>
                    <template x-for="(value, key) in filters" :key="key">
                        <div x-show="value !== ''" class="flex items-center gap-1 bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider">
                            <span x-text="getFilterLabel(key, value)"></span>
                            <button @click="removeFilter(key)" class="ml-1 hover:text-blue-600"><i class="fas fa-times"></i></button>
                        </div>
                    </template>
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
                            <x-table.sort-header column="registration_no" label="Registry ID" sort-var="sort" direction-var="direction" />
                            <x-table.sort-header column="first_name" label="Student Identity" sort-var="sort" direction-var="direction" />
                            <th class="px-6 py-3 text-[10px] font-black text-gray-400 uppercase tracking-widest">Parent / Contact</th>
                            <th class="px-6 py-3 text-[10px] font-black text-gray-400 uppercase tracking-widest">Cluster Node</th>
                            <x-table.sort-header column="admission_status" label="Protocol Stance" sort-var="sort" direction-var="direction" />
                            <x-table.sort-header column="created_at" label="Logged Dates" sort-var="sort" direction-var="direction" />
                            <th class="px-6 py-3 text-center text-[10px] font-black text-gray-400 uppercase tracking-widest w-32">Operations</th>
                        </tr>
                    </thead>

                    <!-- Initial Blade Render (Zero Blink) -->
                    <tbody x-show="!rows.length || (initialLoad && rows.length && initialRows.length === rows.length)" class="divide-y divide-gray-100 dark:divide-gray-700">
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
                                        <div class="text-xs font-black text-gray-800 dark:text-gray-100 uppercase tracking-tight">{{ $row['full_name'] }}</div>
                                        <div class="text-[9px] font-bold text-gray-400 uppercase tracking-widest mt-0.5">Primary Node Identity</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="space-y-1">
                                    <div class="text-xs font-bold text-gray-700 dark:text-gray-200">{{ $row['father_name'] }}</div>
                                    <div class="flex items-center gap-1.5 text-[10px] text-gray-400 tabular-nums font-bold">
                                        <i class="fas fa-phone-alt text-[8px] text-teal-500"></i>
                                        {{ $row['mobile_no'] }}
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="space-y-1">
                                    <div class="text-xs font-black text-gray-700 dark:text-gray-200 uppercase tracking-tighter">{{ $row['class_name'] }}</div>
                                    <div class="text-[9px] font-bold text-gray-400 uppercase tabular-nums">AY: {{ $row['academic_year'] }}</div>
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
                                    <div class="text-[10px] font-black text-gray-700 dark:text-gray-300 uppercase tracking-widest">
                                        <i class="far fa-calendar-check mr-1.5 text-teal-500"></i>
                                        {{ $row['registration_date'] }}
                                    </div>
                                    <div class="text-[9px] font-bold text-gray-400 uppercase tracking-widest">Logged Instance</div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <div class="flex items-center justify-center gap-1.5 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <a href="{{ route('receptionist.student-registrations.pdf', $row['id']) }}" class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center hover:bg-rose-100 transition-colors" title="PDF Archive"><i class="fas fa-file-pdf text-xs"></i></a>
                                    <a href="{{ route('receptionist.student-registrations.edit', $row['id']) }}" class="w-8 h-8 rounded-lg bg-teal-50 text-teal-600 flex items-center justify-center hover:bg-teal-100 transition-colors" title="Modify Node"><i class="fas fa-edit text-xs"></i></a>
                                    <button @click="quickAction(`/receptionist/student-registrations/${row['id']}`, 'Purge Registration Node', 'DELETE')" class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center hover:bg-rose-100 transition-colors" title="Delete"><i class="fas fa-trash-alt text-xs"></i></button>
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
                                            <div class="text-xs font-black text-gray-800 dark:text-gray-100 uppercase tracking-tight" x-text="row.full_name"></div>
                                            <div class="text-[9px] font-bold text-gray-400 uppercase tracking-widest mt-0.5">Primary Node Identity</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="space-y-1">
                                        <div class="text-xs font-bold text-gray-700 dark:text-gray-200" x-text="row.father_name"></div>
                                        <div class="flex items-center gap-1.5 text-[10px] text-gray-400 tabular-nums font-bold">
                                            <i class="fas fa-phone-alt text-[8px] text-teal-500"></i>
                                            <span x-text="row.mobile_no"></span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="space-y-1">
                                        <div class="text-xs font-black text-gray-700 dark:text-gray-200 uppercase tracking-tighter" x-text="row.class_name"></div>
                                        <div class="text-[9px] font-bold text-gray-400 uppercase tabular-nums">AY: <span x-text="row.academic_year"></span></div>
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
                                        <div class="text-[10px] font-black text-gray-700 dark:text-gray-300 uppercase tracking-widest">
                                            <i class="far fa-calendar-check mr-1.5 text-teal-500"></i>
                                            <span x-text="row.registration_date"></span>
                                        </div>
                                        <div class="text-[9px] font-bold text-gray-400 uppercase tracking-widest">Logged Instance</div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="flex items-center justify-center gap-1.5 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <a :href="`/receptionist/student-registrations/${row.id}/pdf`" class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center hover:bg-rose-100 transition-colors" title="PDF Archive"><i class="fas fa-file-pdf text-xs"></i></a>
                                        <a :href="`/receptionist/student-registrations/${row.id}/edit`" class="w-8 h-8 rounded-lg bg-teal-50 text-teal-600 flex items-center justify-center hover:bg-teal-100 transition-colors" title="Modify Node"><i class="fas fa-edit text-xs"></i></a>
                                        <button @click="quickAction(`/receptionist/student-registrations/${row.id}`, 'Purge Registration Node', 'DELETE')" class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center hover:bg-rose-100 transition-colors" title="Delete"><i class="fas fa-trash-alt text-xs"></i></button>
                                    </div>
                                </td>
                            </tr>
                        </template>

                        <x-table.empty-state :colspan="7" icon="fas fa-folder-open" message="No registration nodes found in the institutional matrix." />
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4 bg-gray-50/50 dark:bg-gray-700/50 border-t border-gray-100 dark:border-gray-700">
                <x-table.pagination />
            </div>
        </div>

        <!-- Import Modal (Preserved Protocol) -->
        <x-modal name="import-modal" title="Bulk Registration Interface" max-width="lg">
            <form action="{{ route('receptionist.registrations.import') }}" method="POST" enctype="multipart/form-data" class="p-0">
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
                        <a href="{{ route('receptionist.registrations.download-template') }}" 
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
@endsection


