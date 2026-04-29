@extends('layouts.school')

@section('title', 'Assign Transport Facility - School Admin')
@section('page-title', 'Student Transport Assignments')

@section('content')
    <div x-data="Object.assign(ajaxDataTable({
        fetchUrl: '{{ route('school.transport.assignments.index') }}',
        defaultSort: 'created_at',
        defaultDirection: 'desc',
        defaultPerPage: 25,
        defaultFilters: { search: '', class_id: '', route_id: '' },
        initialRows: @js($initialData['rows']),
        initialPagination: @js($initialData['pagination']),
        initialStats: @js($stats)
    }), assignmentForm())" class="space-y-6" @close-modal.window="if($event.detail === 'assignment-modal') resetForm()">
        
        {{-- High-Density Statistics Dashboard --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <x-stat-card label="Total Assigned" :value="$stats['total']" icon="fas fa-users" color="blue" alpine-text="stats.total" />
            <x-stat-card label="Active Routes" :value="$stats['active_routes']" icon="fas fa-route" color="teal" alpine-text="stats.active_routes" />
            <x-stat-card label="Monthly Revenue" :value="'₹' . number_format($stats['total_revenue'], 0)" icon="fas fa-wallet" color="amber" alpine-text="'₹' + stats.total_revenue" />
            <x-stat-card label="Fleet Utilization" :value="$stats['fleet_count']" icon="fas fa-bus" color="purple" alpine-text="stats.fleet_count" />
        </div>

        <!-- Header Section -->
        <x-page-header title="Transport Assignments" description="Manage student route and stop mappings" icon="fas fa-link">
            <div class="flex items-center gap-3">
                <button @click="open()"
                    class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-teal-600 to-emerald-600 hover:from-teal-700 hover:to-emerald-700 text-white text-sm font-semibold rounded-xl transition-all shadow-md hover:shadow-lg active:scale-95">
                    <i class="fas fa-plus mr-2 text-xs"></i>
                    New Assignment
                </button>
                <a href="{{ route('school.transport.transport_history.index') }}"
                    class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-slate-700 to-slate-900 hover:from-black hover:to-slate-800 text-white text-sm font-semibold rounded-xl transition-all shadow-md hover:shadow-lg active:scale-95">
                    <i class="fas fa-history mr-2 text-xs"></i>
                    Transit History
                </a>
            </div>
        </x-page-header>

        <!-- AJAX Data Table -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div class="flex-1 flex flex-col md:flex-row md:items-center gap-4">
                        <h2 class="text-lg font-semibold text-gray-800 dark:text-white">Active Assignments</h2>
                        <x-table.search placeholder="Search students, admission no..." />
                    </div>
                    <div class="flex items-center gap-3">
                        <x-table.per-page model="perPage" action="changePerPage($event.target.value)" :default="25" />
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto relative ajax-table-wrapper">
                <x-table.loading-overlay />
                
                <table class="w-full text-left border-collapse">
                    <thead class="bg-gray-50/50 dark:bg-gray-700/50 border-b border-gray-100 dark:border-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Student</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Route & Vehicle</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Bus Stop</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Monthly Fee</th>
                            <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider w-32">Actions</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700" x-show="!hydrated" x-cloak>
                        @foreach($initialData['rows'] as $row)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/50 transition-colors group">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-gray-700 flex items-center justify-center text-slate-500 dark:text-gray-400 transition-colors group-hover:bg-teal-100 dark:group-hover:bg-teal-900/40 group-hover:text-teal-600">
                                        <i class="fas fa-user-graduate"></i>
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="text-sm font-bold text-slate-800 dark:text-white leading-tight">{{ $row['student_name'] }}</span>
                                        <span class="text-[10px] font-medium text-slate-400 dark:text-gray-500 mt-0.5">{{ $row['admission_no'] }} | {{ $row['class_name'] }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <span class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ $row['route_name'] }}</span>
                                    <span class="text-[10px] font-medium text-slate-500 dark:text-gray-400 mt-0.5">{{ $row['vehicle_no'] }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <span class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ $row['bus_stop_name'] }}</span>
                                    <span class="text-[10px] font-medium text-slate-500 dark:text-gray-400 mt-0.5">Ref: {{ $row['bus_stop_no'] }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-xs font-bold text-teal-600">{{ $row['fee_formatted'] }}</span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button @click="open(@js($row))" title="Edit" class="w-8 h-8 rounded-lg bg-teal-50 text-teal-600 hover:bg-teal-100 transition-colors flex items-center justify-center">
                                        <i class="fas fa-edit text-xs"></i>
                                    </button>
                                    <button @click="quickAction(`{{ route('school.transport.assignments.index') }}/${row.id}`, 'Remove Assignment', 'DELETE')" title="Delete" class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-100 transition-colors flex items-center justify-center">
                                        <i class="fas fa-trash-alt text-xs"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>

                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700 transition-opacity duration-150" x-show="hydrated" x-cloak :class="loading && rows.length > 0 ? 'opacity-50' : 'opacity-100'">
                        <template x-for="row in rows" :key="row.id">
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/50 transition-colors group">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-gray-700 flex items-center justify-center text-slate-500 dark:text-gray-400 transition-colors group-hover:bg-teal-100 dark:group-hover:bg-teal-900/40 group-hover:text-teal-600">
                                            <i class="fas fa-user-graduate"></i>
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="text-sm font-bold text-slate-800 dark:text-white leading-tight" x-text="row.student_name"></span>
                                            <span class="text-[10px] font-medium text-slate-400 dark:text-gray-500 mt-0.5" x-text="row.admission_no + ' | ' + row.class_name"></span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <span class="text-xs font-bold text-slate-700 dark:text-slate-300" x-text="row.route_name"></span>
                                        <span class="text-[10px] font-medium text-slate-500 dark:text-gray-400 mt-0.5" x-text="row.vehicle_no"></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <span class="text-xs font-bold text-slate-700 dark:text-slate-300" x-text="row.bus_stop_name"></span>
                                        <span class="text-[10px] font-medium text-slate-500 dark:text-gray-400 mt-0.5" x-text="'Ref: ' + row.bus_stop_no"></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-xs font-bold text-teal-600" x-text="row.fee_formatted"></span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button @click="open(row)" title="Edit" class="w-8 h-8 rounded-lg bg-teal-50 text-teal-600 hover:bg-teal-100 transition-colors flex items-center justify-center">
                                            <i class="fas fa-edit text-xs"></i>
                                        </button>
                                        <button @click="quickAction(`{{ route('school.transport.assignments.index') }}/${row.id}`, 'Remove Assignment', 'DELETE')" title="Delete" class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-100 transition-colors flex items-center justify-center">
                                            <i class="fas fa-trash-alt text-xs"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </template>

                        <x-table.empty-state :colspan="5" icon="fas fa-link" message="No assignments found matching your criteria." />
                    </tbody>
                </table>
            </div>

            <x-table.pagination :initial="$initialData['pagination']" />
        </div>

        <x-confirm-modal />

        {{-- Add/Edit Assignment Modal --}}
        <x-modal name="assignment-modal" alpineTitle="editMode ? 'Edit Assignment' : 'New Transport Assignment'" maxWidth="3xl">
            <form @submit.prevent="save" id="assignmentForm" class="p-1">
                @csrf
                <template x-if="editMode">
                    <input type="hidden" name="_method" value="PUT">
                </template>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-4">
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold text-slate-500 dark:text-gray-400 uppercase tracking-widest ml-1">Select Student <span class="text-red-500">*</span></label>
                        <div :class="errors.student_id ? 'border border-red-500 rounded-xl' : 'border border-slate-200 dark:border-gray-600 rounded-xl'">
                        <select x-model="formData.student_id" 
                            class="no-select2 w-full bg-white dark:bg-gray-700 border-0 rounded-xl py-3 px-4 text-sm font-bold text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-teal-500/20 transition-all"
                            @change="clearError('student_id')">
                            <option value="">Select Student</option>
                            @foreach($students as $student)
                                <option value="{{ $student->id }}">{{ $student->first_name }} {{ $student->last_name }} ({{ $student->admission_no }})</option>
                            @endforeach
                        </select>
                        </div>
                        <template x-if="errors.student_id && errors.student_id[0]">
                            <p class="text-[10px] text-red-500 font-bold mt-1 ml-1" x-text="errors.student_id[0]"></p>
                        </template>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold text-slate-500 dark:text-gray-400 uppercase tracking-widest ml-1">Route <span class="text-red-500">*</span></label>
                        <div :class="errors.route_id ? 'border border-red-500 rounded-xl' : 'border border-slate-200 dark:border-gray-600 rounded-xl'">
                        <select x-model="formData.route_id" 
                            class="no-select2 w-full bg-white dark:bg-gray-700 border-0 rounded-xl py-3 px-4 text-sm font-bold text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-teal-500/20 transition-all"
                            @change="clearError('route_id'); fetchBusStops()">
                            <option value="">Select Route</option>
                            @foreach($routes as $route)
                                <option value="{{ $route->id }}">{{ $route->route_name }}</option>
                            @endforeach
                        </select>
                        </div>
                        <template x-if="errors.route_id && errors.route_id[0]">
                            <p class="text-[10px] text-red-500 font-bold mt-1 ml-1" x-text="errors.route_id[0]"></p>
                        </template>
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold text-slate-500 dark:text-gray-400 uppercase tracking-widest ml-1">Bus Stop <span class="text-red-500">*</span></label>
                        <div :class="errors.bus_stop_id ? 'border border-red-500 rounded-xl' : 'border border-slate-200 dark:border-gray-600 rounded-xl'">
                        <select x-model="formData.bus_stop_id" 
                            class="no-select2 w-full bg-white dark:bg-gray-700 border-0 rounded-xl py-3 px-4 text-sm font-bold text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-teal-500/20 transition-all"
                            @change="clearError('bus_stop_id'); updateFee()">
                            <option value="">Select Bus Stop</option>
                            <template x-for="stop in busStops" :key="stop.id">
                                <option :value="stop.id" x-text="stop.bus_stop_name + ' (' + stop.bus_stop_no + ')'"></option>
                            </template>
                        </select>
                        </div>
                        <template x-if="errors.bus_stop_id && errors.bus_stop_id[0]">
                            <p class="text-[10px] text-red-500 font-bold mt-1 ml-1" x-text="errors.bus_stop_id[0]"></p>
                        </template>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold text-slate-500 dark:text-gray-400 uppercase tracking-widest ml-1">Monthly Fee (Override)</label>
                        <div class="relative">
                            <input type="number" step="0.01" x-model="formData.fee_per_month" 
                                class="w-full bg-white dark:bg-gray-700 border rounded-xl py-3 px-4 text-sm font-bold text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-teal-500/20 transition-all"
                                :class="errors.fee_per_month ? 'border-red-500' : 'border-slate-200 dark:border-gray-600'" @input="clearError('fee_per_month')">
                            <span class="absolute right-4 top-1/2 -translate-y-1/2 text-[10px] font-bold text-teal-600">₹</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-6 bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-100 dark:border-indigo-800 p-4 rounded-2xl flex items-start gap-3">
                <i class="fas fa-info-circle text-indigo-600 mt-0.5"></i>
                <div class="flex flex-col">
                    <span class="text-xs font-bold text-slate-900 dark:text-gray-100 leading-tight">Note</span>
                    <p class="text-[11px] text-slate-500 dark:text-gray-400 mt-1 leading-relaxed">
                        Student transport assignments are effective immediately. Fee changes will reflect in the next billing cycle.
                    </p>
                </div>
            </div>
        </form>

        <x-slot name="footer">
            <button @click="$dispatch('close-modal', 'assignment-modal')" class="px-6 py-2.5 text-xs font-bold text-slate-500 dark:text-gray-400 uppercase tracking-widest hover:text-slate-700 dark:hover:text-gray-200 transition-colors">
                Cancel
            </button>
            <button type="submit" form="assignmentForm" :disabled="submitting" 
                class="bg-slate-900 hover:bg-black text-white px-8 py-2.5 rounded-xl text-xs font-bold uppercase tracking-widest transition-all shadow-md active:scale-95 disabled:opacity-50">
                <template x-if="submitting">
                    <i class="fas fa-spinner animate-spin mr-2"></i>
                </template>
                <span x-text="submitting ? 'Saving...' : (editMode ? 'Update Assignment' : 'Assign Student')"></span>
            </button>
            </x-slot>
        </x-modal>
    </div>

    @push('scripts')
    <script>
        function assignmentForm() {
            return {
                editMode: false,
                submitting: false,
                assignmentId: null,
                busStops: [],
                formData: {
                    student_id: '',
                    route_id: '',
                    bus_stop_id: '',
                    fee_per_month: '',
                },
                errors: {},

                clearError(field) {
                    if (this.errors && this.errors[field]) {
                        const e = { ...this.errors }; delete e[field]; this.errors = e;
                    }
                },

                resetForm() {
                    this.editMode = false;
                    this.assignmentId = null;
                    this.busStops = [];
                    this.formData = {
                        student_id: '',
                        route_id: '',
                        bus_stop_id: '',
                        fee_per_month: '',
                    };
                    this.errors = {};
                },

                open(assignment = null) {
                    this.errors = {};
                    if (assignment) {
                        this.editMode = true;
                        this.assignmentId = assignment.id;
                        this.fetchFullData(assignment.id);
                    } else {
                        this.editMode = false;
                        this.assignmentId = null;
                        this.formData = {
                            student_id: '',
                            route_id: '',
                            bus_stop_id: '',
                            fee_per_month: '',
                        };
                    }
                    this.$dispatch('open-modal', 'assignment-modal');
                },

                async fetchFullData(id) {
                    try {
                        const response = await fetch(`{{ route('school.transport.assignments.index') }}/${id}/edit`, {
                            headers: { 'Accept': 'application/json' }
                        });
                        const data = await response.json();
                        if (response.ok) {
                            this.formData = {
                                student_id: data.student_id,
                                route_id: data.route_id,
                                bus_stop_id: data.bus_stop_id,
                                fee_per_month: data.fee_per_month,
                            };
                            await this.fetchBusStops(data.bus_stop_id);
                        }
                    } catch (e) {
                        console.error('Failed to fetch full assignment data');
                    }
                },

                async fetchBusStops(selectedStopId = null) {
                    if (!this.formData.route_id) {
                        this.busStops = [];
                        return;
                    }
                    try {
                        const response = await fetch(`{{ route('school.transport.bus_stops.index') }}?route_id=${this.formData.route_id}&limit=100`, {
                            headers: { 'Accept': 'application/json' }
                        });
                        const data = await response.json();
                        if (response.ok) {
                            this.busStops = data.rows || [];
                            if (!selectedStopId) {
                                this.formData.bus_stop_id = '';
                                this.formData.fee_per_month = '';
                            }
                        }
                    } catch (e) {
                        console.error('Failed to fetch bus stops for route');
                    }
                },

                updateFee() {
                    const stop = this.busStops.find(s => s.id == this.formData.bus_stop_id);
                    if (stop) {
                        this.formData.fee_per_month = stop.raw_charge || 0;
                    }
                },

                async save() {
                    this.submitting = true;
                    this.errors = {};
                    const url = this.editMode 
                        ? `{{ route('school.transport.assignments.index') }}/${this.assignmentId}`
                        : `{{ route('school.transport.assignments.store') }}`;
                    
                    try {
                        const response = await fetch(url, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                ...this.formData,
                                _method: this.editMode ? 'PUT' : 'POST'
                            })
                        });

                        const result = await response.json();
                        if (response.ok) {
                            if (window.Toast) window.Toast.fire({ icon: 'success', title: result.message });
                            this.$dispatch('close-modal', 'assignment-modal');
                            this.fetchData();
                        } else {
                            this.errors = result.errors || {};
                        }
                    } catch (e) {
                        if (window.Toast) window.Toast.fire({ icon: 'error', title: 'Failed to save assignment' });
                    } finally {
                        this.submitting = false;
                    }
                }
            }
        }
    </script>
    @endpush
@endsection
