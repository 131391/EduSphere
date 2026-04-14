@extends('layouts.receptionist')

@section('title', 'Assign Transport Facility')

@section('content')
    <div class="space-y-6" x-data="transportAssignmentManagement()" x-init="init()">
        <!-- Success Message -->

        <!-- Error Banner -->
        @if($errors->any())
            <div id="error-banner" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
                class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg relative">
                <div class="flex items-center justify-between">
                    <div>
                        <strong class="font-bold">Please fix the following errors:</strong>
                        <ul class="list-disc list-inside mt-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    <button @click="show = false" class="absolute top-0 right-0 px-4 py-3">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        @endif

    <div class="space-y-6" x-data="transportAssignmentManagement()" x-init="init()">
        {{-- Transport Assignment Statistics --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
            <div class="bg-white/80 backdrop-blur-md rounded-2xl shadow-sm border border-gray-100 p-5 transition-all hover:shadow-md group">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[10px] text-gray-400 uppercase font-black tracking-widest mb-1">Assigned Registry</p>
                        <p class="text-2xl font-black text-gray-800">{{ $stats['total_assigned'] }}</p>
                    </div>
                    <div class="bg-blue-50 p-3 rounded-2xl group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-users text-blue-500 text-lg"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white/80 backdrop-blur-md rounded-2xl shadow-sm border border-gray-100 p-5 transition-all hover:shadow-md group">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[10px] text-gray-400 uppercase font-black tracking-widest mb-1">Network Utilization</p>
                        <p class="text-2xl font-black text-emerald-600">{{ $stats['active_routes'] }} <span class="text-xs text-gray-400 uppercase">Routes</span></p>
                    </div>
                    <div class="bg-emerald-50 p-3 rounded-2xl group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-route text-emerald-500 text-lg"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white/80 backdrop-blur-md rounded-2xl shadow-sm border border-gray-100 p-5 transition-all hover:shadow-md group">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[10px] text-gray-400 uppercase font-black tracking-widest mb-1">Revenue Stream</p>
                        <p class="text-2xl font-black text-gray-800">₹{{ number_format($stats['total_fees'], 0) }}</p>
                    </div>
                    <div class="bg-amber-50 p-3 rounded-2xl group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-wallet text-amber-500 text-lg"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white/80 backdrop-blur-md rounded-2xl shadow-sm border border-gray-100 p-5 transition-all hover:shadow-md group">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[10px] text-gray-400 uppercase font-black tracking-widest mb-1">Operational Fleet</p>
                        <p class="text-2xl font-black text-gray-800">{{ $stats['available_vehicles'] }}</p>
                    </div>
                    <div class="bg-purple-50 p-3 rounded-2xl group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-bus text-purple-500 text-lg"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- Page Header --}}
        <div class="bg-white/40 backdrop-blur-xl rounded-2xl p-6 border border-white/20 shadow-sm mb-8">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6">
                <div class="flex items-center gap-4">
                    <div class="bg-gradient-to-br from-teal-500 to-emerald-600 p-3 rounded-2xl shadow-lg shadow-teal-100">
                        <i class="fas fa-link text-white text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-2xl font-black text-gray-800 tracking-tight">Transit Assignments</h2>
                        <p class="text-sm text-gray-500 font-medium">Map student profiles to institutional network nodes</p>
                    </div>
                </div>
                <div class="flex flex-wrap gap-3">
                    <button @click="openAddModal()"
                        class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-teal-600 to-teal-700 hover:from-teal-700 hover:to-teal-800 text-white text-sm font-black rounded-xl transition-all shadow-lg shadow-teal-100 group">
                        <i class="fas fa-plus mr-2 group-hover:rotate-90 transition-transform duration-300"></i>
                        New Assignment
                    </button>
                    <a href="{{ route('receptionist.transport-assignments.history') }}"
                        class="inline-flex items-center px-6 py-3 bg-white border border-gray-100 text-gray-700 text-sm font-bold rounded-xl hover:bg-gray-50 transition-all shadow-sm">
                        <i class="fas fa-history mr-2 text-indigo-500"></i>
                        Transit History
                    </a>
                </div>
            </div>
        </div>

        <!-- Data Table -->
        @php
            $tableColumns = [
                [
                    'key' => 'sr_no',
                    'label' => 'SR NO',
                    'render' => function ($row, $index, $data) use ($assignments) {
                        return ($assignments->currentPage() - 1) * $assignments->perPage() + $index + 1;
                    }
                ],
                [
                    'key' => 'student_name',
                    'label' => 'STUDENT NAME',
                    'render' => function ($row) {
                        if (!$row->student)
                            return 'N/A';
                        return trim($row->student->first_name . ' ' . $row->student->middle_name . ' ' . $row->student->last_name) ?: 'N/A';
                    }
                ],
                [
                    'key' => 'admission_no',
                    'label' => 'ADMISSION NO',
                    'render' => function ($row) {
                        return $row->student->admission_no ?? 'N/A';
                    }
                ],
                [
                    'key' => 'class',
                    'label' => 'CLASS',
                    'render' => function ($row) {
                        return $row->student->class->name ?? 'N/A';
                    }
                ],
                [
                    'key' => 'vehicle_no',
                    'label' => 'VEHICLE',
                    'render' => function ($row) {
                        return $row->vehicle->vehicle_no ?? 'N/A';
                    }
                ],
                [
                    'key' => 'route_name',
                    'label' => 'ROUTE',
                    'render' => function ($row) {
                        return $row->route->route_name ?? 'N/A';
                    }
                ],
                [
                    'key' => 'bus_stop_name',
                    'label' => 'BUS STOP',
                    'render' => function ($row) {
                        return ($row->busStop->bus_stop_no ?? '') . ' - ' . ($row->busStop->bus_stop_name ?? 'N/A');
                    }
                ],
                [
                    'key' => 'fee_per_month',
                    'label' => 'FEE/MONTH',
                    'render' => function ($row) {
                        return '₹' . number_format($row->fee_per_month, 2);
                    }
                ],
            ];

            $tableActions = [
                [
                    'type' => 'button',
                    'icon' => 'fas fa-edit',
                    'class' => 'text-blue-600 hover:text-blue-900',
                    'title' => 'Edit',
                    'onclick' => function ($row) {
                        return "openEditModal(" . json_encode([
                            'id' => $row->id,
                            'student_id' => $row->student_id,
                            'route_id' => $row->route_id,
                            'bus_stop_id' => $row->bus_stop_id,
                            'vehicle_id' => $row->vehicle_id,
                            'fee_per_month' => $row->fee_per_month,
                        ]) . ")";
                    }
                ],
                [
                    'type' => 'button',
                    'icon' => 'fas fa-trash',
                    'class' => 'text-red-600 hover:text-red-900',
                    'title' => 'Delete',
                    'onclick' => function ($row) {
                        $name = trim($row->student->first_name . ' ' . $row->student->last_name);
                        return "confirmDelete('" . route('receptionist.transport-assignments.destroy', $row->id) . "', '{$name}')";
                    }
                ],
            ];

            // Prepare filters
            $filters = [
                [
                    'name' => 'class_id',
                    'label' => 'Class',
                    'options' => $classes->pluck('name', 'id')->toArray()
                ],
                [
                    'name' => 'vehicle_id',
                    'label' => 'Vehicle',
                    'options' => $vehicles->pluck('vehicle_no', 'id')->toArray()
                ],
                [
                    'name' => 'route_id',
                    'label' => 'Route',
                    'options' => $routes->pluck('route_name', 'id')->toArray()
                ],
            ];
        @endphp

        <x-data-table :columns="$tableColumns" :data="$assignments" :actions="$tableActions" :searchable="true"
            :filterable="true" :filters="$filters">
            Transport Assignments
        </x-data-table>

        {{-- Add/Edit Transport Assignment Modal --}}
        <x-modal name="assignment-modal" maxWidth="4xl"
            alpineTitle="editMode ? 'Modify Student Transit Registry' : 'Initialize Transport Facility Mapping'">
            <form @submit.prevent="save" method="POST" class="p-0 relative" novalidate>
                @csrf
                <template x-if="editMode">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <div class="p-8 space-y-8">
                    {{-- Student & Route Selection --}}
                    <div>
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-10 h-10 rounded-xl bg-teal-50 flex items-center justify-center text-teal-600 border border-teal-100 shadow-sm">
                                <i class="fas fa-user-link text-sm"></i>
                            </div>
                            <h4 class="text-sm font-black text-slate-800 uppercase tracking-wider">Candidate Mapping</h4>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label class="modal-label-premium">Target Student Profile <span class="text-red-600 font-bold">*</span></label>
                                <select name="student_id" x-model="formData.student_id" id="student_id"
                                    @change="clearError('student_id')"
                                    class="modal-input-premium" :class="{'border-red-500 ring-red-500/10': errors.student_id}">
                                    <option value="">Select Student</option>
                                    @foreach($students as $student)
                                        <option value="{{ $student->id }}">
                                            {{ trim($student->first_name . ' ' . $student->middle_name . ' ' . $student->last_name) }}
                                            ({{ $student->admission_no }})
                                        </option>
                                    @endforeach
                                </select>
                                <template x-if="errors.student_id">
                                    <p class="modal-error-message" x-text="errors.student_id[0]"></p>
                                </template>
                            </div>

                            <div class="space-y-2">
                                <label class="modal-label-premium">Operational Transit Route <span class="text-red-600 font-bold">*</span></label>
                                <select name="route_id" x-model="formData.route_id" id="route_id"
                                    @change="clearError('route_id'); loadBusStops()"
                                    class="modal-input-premium" :class="{'border-red-500 ring-red-500/10': errors.route_id}">
                                    <option value="">Select Route</option>
                                    @foreach($routes as $route)
                                        <option value="{{ $route->id }}">{{ $route->route_name }}</option>
                                    @endforeach
                                </select>
                                <template x-if="errors.route_id">
                                    <p class="modal-error-message" x-text="errors.route_id[0]"></p>
                                </template>
                            </div>
                        </div>
                    </div>

                    <hr class="border-slate-100">

                    {{-- Node & Asset Allocation --}}
                    <div>
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-600 border border-indigo-100 shadow-sm">
                                <i class="fas fa-bus-alt text-sm"></i>
                            </div>
                            <h4 class="text-sm font-black text-slate-800 uppercase tracking-wider">Node & Asset Allocation</h4>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label class="modal-label-premium">Designated Stoppage (Node) <span class="text-red-600 font-bold">*</span></label>
                                <select name="bus_stop_id" x-model="formData.bus_stop_id" id="bus_stop_id"
                                    @change="clearError('bus_stop_id'); updateFee()"
                                    class="modal-input-premium" :class="{'border-red-500 ring-red-500/10': errors.bus_stop_id}">
                                    <option value="">Select Bus Stop</option>
                                </select>
                                <template x-if="errors.bus_stop_id">
                                    <p class="modal-error-message" x-text="errors.bus_stop_id[0]"></p>
                                </template>
                            </div>

                            <div class="space-y-2">
                                <label class="modal-label-premium">Assigned Fleet Asset</label>
                                <select name="vehicle_id" x-model="formData.vehicle_id" id="vehicle_id"
                                    @change="clearError('vehicle_id')"
                                    class="modal-input-premium">
                                    <option value="">Select Vehicle</option>
                                    @foreach($vehicles as $vehicle)
                                        <option value="{{ $vehicle->id }}">{{ $vehicle->vehicle_no }} ({{ $vehicle->vehicle_type }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- Fee Propagation --}}
                    <div class="bg-slate-50 border border-slate-100 p-6 rounded-2xl shadow-inner group">
                        <label class="modal-label-premium text-slate-500 mb-3 block">Computed Monthly Tariff (Incurred)</label>
                        <div class="relative group-focus-within:scale-[1.01] transition-transform duration-300">
                            <span class="absolute left-5 top-1/2 -translate-y-1/2 text-teal-600 font-black text-xl">₹</span>
                            <input type="number" name="fee_per_month" x-model="formData.fee_per_month" step="0.01" readonly
                                class="w-full pl-12 pr-6 py-5 bg-white border border-slate-200 rounded-2xl font-black text-2xl text-slate-800 shadow-sm cursor-not-allowed outline-none"
                                :class="{'border-red-500 ring-red-500/10': errors.fee_per_month}">
                        </div>
                        <div class="flex items-center gap-2 mt-3 ml-2">
                            <i class="fas fa-shield-alt text-[10px] text-teal-500"></i>
                            <p class="text-[10px] text-teal-600 font-black uppercase tracking-widest">Locked to Network Node Tariff</p>
                        </div>
                        <template x-if="errors.fee_per_month">
                            <p class="modal-error-message" x-text="errors.fee_per_month[0]"></p>
                        </template>
                    </div>

                    {{-- Administrative Notice --}}
                    <div class="bg-[#f0f9ff] border border-[#e0f2fe] p-5 rounded-2xl flex items-start gap-4 shadow-sm">
                        <div class="w-10 h-10 rounded-xl bg-white shadow-sm flex items-center justify-center shrink-0">
                            <i class="fas fa-exclamation-triangle text-amber-500 text-sm"></i>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-[13px] font-bold text-slate-900 leading-tight">Registry Notice</span>
                            <p class="text-[10px] text-slate-500 font-bold uppercase mt-1 tracking-wide opacity-80 leading-relaxed">
                                Assignment will be mapped to the <span class="text-blue-600 underline underline-offset-2">current academic session</span>. Any modifications will reflect in the next billing cycle.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Modal Footer --}}
                <x-slot name="footer">
                    <button type="button" @click="closeModal()" :disabled="submitting" class="btn-premium-cancel px-10">
                        Discard
                    </button>
                    <button type="submit" :disabled="submitting" class="btn-premium-primary min-w-[200px]">
                        <template x-if="submitting">
                            <span class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin mr-3 inline-block"></span>
                        </template>
                        <span x-text="submitting ? 'Propagating...' : (editMode ? 'Update Assignment' : 'Assign Facility')"></span>
                    </button>
                </x-slot>
            </form>
        </x-modal>

        <x-confirm-modal title="Strike Transport Record?"
            message="This will terminate the student's transport facility access. This record will be archived."
            confirm-text="Strike Record" confirm-color="red" />
    </div>

    @push('scripts')
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('transportAssignmentManagement', () => ({
                    editMode: false,
                    assignmentId: null,
                    allBusStops: @json($busStops),
                    allRoutes: @json($routes),
                    allVehicles: @json($vehicles),
                    busStops: [],
                    routeVehicleId: null,
                    formData: {
                        student_id: '',
                        route_id: '',
                        bus_stop_id: '',
                        vehicle_id: '',
                        fee_per_month: '',
                    },
                    errors: {},
                    submitting: false,

                    clearError(field) {
                        if (this.errors[field]) {
                            delete this.errors[field];
                        }
                    },

                    async init() {
                        this.$nextTick(() => {
                            if (typeof $ !== 'undefined') {
                                // Initialize Select2 Sync
                                $('select[name="student_id"], select[name="route_id"], select[name="bus_stop_id"], select[name="vehicle_id"]').on('change', (e) => {
                                    const field = e.target.getAttribute('name');
                                    if (field && this.formData.hasOwnProperty(field)) {
                                        this.formData[field] = e.target.value;
                                        if (field === 'route_id') this.loadBusStops();
                                        if (field === 'bus_stop_id') this.updateFee();
                                        this.clearError(field);
                                    }
                                });
                            }
                        });
                    },

                    async save() {
                        this.submitting = true;
                        this.errors = {};

                        const url = this.editMode
                            ? `/receptionist/transport-assignments/${this.assignmentId}`
                            : '{{ route('receptionist.transport-assignments.store') }}';

                        const method = this.editMode ? 'PUT' : 'POST';

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
                                    _method: method
                                })
                            });

                            const result = await response.json();

                            if (response.ok) {
                                if (window.Toast) {
                                    window.Toast.fire({
                                        icon: 'success',
                                        title: result.message || 'Assignment state synchronized'
                                    });
                                }
                                setTimeout(() => window.location.reload(), 1000);
                            } else {
                                if (response.status === 422) {
                                    this.errors = result.errors || {};
                                } else {
                                    throw new Error(result.message || 'Transmission failed');
                                }
                            }
                        } catch (error) {
                            if (window.Toast) {
                                window.Toast.fire({ icon: 'error', title: error.message });
                            }
                        } finally {
                            this.submitting = false;
                        }
                    },

                    async deleteAssignment(url) {
                        try {
                            const response = await fetch(url, {
                                method: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Accept': 'application/json'
                                }
                            });

                            const result = await response.json();

                            if (response.ok) {
                                if (window.Toast) {
                                    window.Toast.fire({ icon: 'success', title: result.message || 'Assignment terminated' });
                                }
                                setTimeout(() => window.location.reload(), 1000);
                            } else {
                                throw new Error(result.message || 'Severing record failed');
                            }
                        } catch (error) {
                            if (window.Toast) {
                                window.Toast.fire({ icon: 'error', title: error.message });
                            }
                        }
                    },

                    openAddModal() {
                        this.editMode = false;
                        this.assignmentId = null;
                        this.errors = {};
                        this.formData = {
                            student_id: '',
                            route_id: '',
                            bus_stop_id: '',
                            vehicle_id: '',
                            fee_per_month: '',
                        };
                        this.busStops = [];
                        this.$dispatch('open-modal', 'assignment-modal');
                        this.$nextTick(() => {
                            if (typeof $ !== 'undefined') {
                                $('select[name="student_id"], select[name="route_id"], select[name="bus_stop_id"], select[name="vehicle_id"]').val('').trigger('change');
                            }
                        });
                    },

                    openEditModal(assignment) {
                        this.editMode = true;
                        this.assignmentId = assignment.id;
                        this.errors = {};
                        this.formData = {
                            student_id: assignment.student_id ? String(assignment.student_id) : '',
                            route_id: assignment.route_id ? String(assignment.route_id) : '',
                            bus_stop_id: assignment.bus_stop_id ? String(assignment.bus_stop_id) : '',
                            vehicle_id: assignment.vehicle_id ? String(assignment.vehicle_id) : '',
                            fee_per_month: assignment.fee_per_month || '',
                        };

                        // Initial load of stops for this route
                        if (this.formData.route_id) {
                            this.loadBusStops(true);
                        }

                        this.$dispatch('open-modal', 'assignment-modal');
                        this.$nextTick(() => {
                            setTimeout(() => {
                                if (typeof $ !== 'undefined') {
                                    $('select[name="student_id"]').val(this.formData.student_id).trigger('change');
                                    $('select[name="route_id"]').val(this.formData.route_id).trigger('change');
                                    $('select[name="vehicle_id"]').val(this.formData.vehicle_id).trigger('change');

                                    // Specific timing for bus stop because it's dynamic
                                    setTimeout(() => {
                                        $('select[name="bus_stop_id"]').val(this.formData.bus_stop_id).trigger('change');
                                    }, 200);
                                }
                            }, 150);
                        });
                    },

                    loadBusStops(isInitial = false) {
                        if (!this.formData.route_id) {
                            this.busStops = [];
                            if (!isInitial) {
                                this.formData.bus_stop_id = '';
                                this.formData.fee_per_month = '';
                            }
                            this.updateBusStopSelect();
                            return;
                        }

                        const routeId = Number(this.formData.route_id);
                        this.busStops = this.allBusStops.filter(stop => Number(stop.route_id) === routeId);

                        this.updateBusStopSelect();

                        if (!isInitial) {
                            // Preselect the route's default vehicle if present
                            const route = this.allRoutes.find(r => Number(r.id) === routeId);
                            if (route && route.vehicle_id) {
                                this.formData.vehicle_id = route.vehicle_id;
                                if (typeof $ !== 'undefined') $('select[name="vehicle_id"]').val(route.vehicle_id).trigger('change');
                            }
                        }
                    },

                    updateBusStopSelect() {
                        this.$nextTick(() => {
                            const select = document.getElementById('bus_stop_id');
                            if (!select) return;

                            // Clear and add options
                            while (select.options.length > 1) select.remove(1);

                            this.busStops.forEach(stop => {
                                const option = document.createElement('option');
                                option.value = stop.id;
                                option.textContent = `${stop.bus_stop_no || ''} - ${stop.bus_stop_name || ''}`;
                                option.setAttribute('data-fee', stop.charge_per_month || '');
                                select.appendChild(option);
                            });

                            if (typeof $ !== 'undefined') $(select).trigger('change');
                        });
                    },

                    updateFee() {
                        if (!this.formData.bus_stop_id) {
                            this.formData.fee_per_month = '';
                            return;
                        }

                        const selectedStop = this.busStops.find(stop => Number(stop.id) === Number(this.formData.bus_stop_id));
                        if (selectedStop) {
                            this.formData.fee_per_month = selectedStop.charge_per_month ? parseFloat(selectedStop.charge_per_month).toFixed(2) : '0.00';

                            // If stop has a specific vehicle, we could pre-select it, but usually route-level vehicle is primary.
                            if (selectedStop.vehicle_id) {
                                this.formData.vehicle_id = selectedStop.vehicle_id;
                                if (typeof $ !== 'undefined') $('select[name="vehicle_id"]').val(selectedStop.vehicle_id).trigger('change');
                            }
                        }
                    },

                    closeModal() {
                        this.$dispatch('close-modal', 'assignment-modal');
                        this.errors = {};
                    },
                }));
            });

            // Global Helpers
            function openEditModal(assignment) {
                const el = document.querySelector('[x-data*="transportAssignmentManagement"]');
                if (el) Alpine.$data(el).openEditModal(assignment);
            }

            function confirmDelete(url, name) {
                window.dispatchEvent(new CustomEvent('open-confirm-modal', {
                    detail: {
                        message: `Strike transport facility record for "${name}"?`,
                        onConfirm: () => {
                            const el = document.querySelector('[x-data*="transportAssignmentManagement"]');
                            if (el) Alpine.$data(el).deleteAssignment(url);
                        }
                    }
                }));
            }
        </script>
    @endpush
@endsection