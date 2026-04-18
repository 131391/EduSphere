@extends('layouts.receptionist')

@section('title', 'Assign Transport Facility')

@section('content')
    <div class="space-y-6" x-data="transportAssignmentManagement()" x-init="init()">
        {{-- Transport Assignment Statistics --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border-t-4 border-blue-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Assigned</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ $stats['total_assigned'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-users text-blue-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border-t-4 border-emerald-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Active Routes</p>
                        <p class="text-3xl font-bold text-emerald-600 dark:text-emerald-400 mt-2">{{ $stats['active_routes'] }} <span class="text-sm text-gray-400 font-medium tracking-tight">Routes</span></p>
                    </div>
                    <div class="w-12 h-12 bg-emerald-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-route text-emerald-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border-t-4 border-amber-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Monthly Revenue</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">₹{{ number_format($stats['total_fees'], 0) }}</p>
                    </div>
                    <div class="w-12 h-12 bg-amber-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-wallet text-amber-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border-t-4 border-purple-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Available Vehicles</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ $stats['available_vehicles'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-bus text-purple-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- Page Header --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 mb-6 border border-teal-100/50">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl font-bold text-gray-800 dark:text-white flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-teal-100 flex items-center justify-center text-teal-600">
                            <i class="fas fa-link text-xs"></i>
                        </div>
                        Transport Assignments
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Assign students to transport routes and bus stops.</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <button @click="openAddModal()"
                        class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-teal-600 to-emerald-600 hover:from-teal-700 hover:to-emerald-700 text-white text-sm font-semibold rounded-xl transition-all shadow-md hover:shadow-lg active:scale-95">
                        <i class="fas fa-plus mr-2"></i>
                        New Assignment
                    </button>
                    <a href="{{ route('receptionist.transport-assign-history.index') }}"
                        class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-slate-700 to-slate-900 hover:from-black hover:to-slate-800 text-white text-sm font-semibold rounded-xl transition-all shadow-md hover:shadow-lg active:scale-95">
                        <i class="fas fa-history mr-2 text-xs"></i>
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
                        return "window.dispatchEvent(new CustomEvent('open-edit-transport-assignment', { detail: ".json_encode([
                            'id' => $row->id,
                            'student_id' => $row->student_id,
                            'route_id' => $row->route_id,
                            'bus_stop_id' => $row->bus_stop_id,
                            'vehicle_id' => $row->vehicle_id,
                            'fee_per_month' => $row->fee_per_month,
                        ])." }))";
                    }
                ],
                [
                    'type' => 'button',
                    'icon' => 'fas fa-trash',
                    'class' => 'text-red-600 hover:text-red-900',
                    'title' => 'Delete',
                    'onclick' => function ($row) {
                        $name = trim($row->student->first_name . ' ' . $row->student->last_name);
                        return "window.dispatchEvent(new CustomEvent('open-delete-transport-assignment', { detail: ".json_encode([
                            'url' => route('receptionist.transport-assignments.destroy', $row->id),
                            'name' => $name
                        ])." }))";
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
        <x-modal name="assignment-modal" maxWidth="3xl"
            alpineTitle="editMode ? 'Edit Transport Assignment' : 'New Transport Assignment'">
            <form @submit.prevent="save" id="assignmentForm" method="POST" class="space-y-6" novalidate>
                @csrf
                <template x-if="editMode">
                    <input type="hidden" name="_method" value="PUT">
                </template>
                    {{-- Student & Route Selection --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label class="modal-label-premium">Student <span class="text-red-600 font-bold">*</span></label>
                                <select name="student_id" x-model="formData.student_id" id="student_id"
                                    @change="clearError('student_id')"
                                    class="modal-input-premium no-select2" :class="{'border-red-500 ring-red-500/10': errors.student_id}">
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
                                <label class="modal-label-premium">Route <span class="text-red-600 font-bold">*</span></label>
                                <select name="route_id" x-model="formData.route_id" id="route_id"
                                    @change="clearError('route_id'); loadBusStops()"
                                    class="modal-input-premium no-select2" :class="{'border-red-500 ring-red-500/10': errors.route_id}">
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
                    <hr class="border-slate-100">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label class="modal-label-premium">Bus Stop <span class="text-red-600 font-bold">*</span></label>
                                <select name="bus_stop_id" x-model="formData.bus_stop_id" id="bus_stop_id"
                                    @change="clearError('bus_stop_id'); updateFee()"
                                    class="modal-input-premium no-select2" :class="{'border-red-500 ring-red-500/10': errors.bus_stop_id}">
                                    <option value="">Select Bus Stop</option>
                                </select>
                                <template x-if="errors.bus_stop_id">
                                    <p class="modal-error-message" x-text="errors.bus_stop_id[0]"></p>
                                </template>
                            </div>

                            <div class="space-y-2">
                                <label class="modal-label-premium">Vehicle</label>
                                <select name="vehicle_id" x-model="formData.vehicle_id" id="vehicle_id"
                                    @change="clearError('vehicle_id')"
                                    class="modal-input-premium no-select2">
                                    <option value="">Select Vehicle</option>
                                    @foreach($vehicles as $vehicle)
                                        <option value="{{ $vehicle->id }}">{{ $vehicle->vehicle_no }} ({{ $vehicle->vehicle_type }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="bg-[#f0f5ff] border border-[#e5edff] p-5 rounded-2xl flex flex-col gap-3 shadow-sm">
                        <label class="modal-label-premium !mb-0">Monthly Fee</label>
                        <div class="relative">
                            <span class="absolute right-5 top-1/2 -translate-y-1/2 text-teal-600 font-black text-xl">₹</span>
                            <input type="number" name="fee_per_month" x-model="formData.fee_per_month" step="0.01" readonly
                                class="w-full pr-12 pl-6 py-4 bg-white border border-slate-200 rounded-xl font-black text-2xl text-slate-800 shadow-sm cursor-not-allowed outline-none">
                        </div>
                    </div>

                    {{-- Administrative Notice --}}
                    <div class="bg-[#f0f5ff] border border-[#e5edff] p-5 rounded-2xl flex items-start gap-4 shadow-sm">
                        <div class="w-10 h-10 rounded-xl bg-white shadow-sm flex items-center justify-center shrink-0">
                            <i class="fas fa-exclamation-triangle text-amber-500 text-sm"></i>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-sm font-bold text-slate-900 leading-tight">Note</span>
                            <p class="text-[10px] text-slate-500 font-bold uppercase mt-1 tracking-wide opacity-80 leading-relaxed">
                                Assignment will be mapped to the <span class="text-indigo-600 font-bold underline decoration-indigo-200">current academic session</span>. Any modifications will reflect in the next billing cycle.
                            </p>
                        </div>
                    </div>

            </form>
            {{-- Modal Footer --}}
            <x-slot name="footer">
                <button type="button" @click="closeModal()" :disabled="submitting" class="btn-premium-cancel px-10">
                    Cancel
                </button>
                <button type="submit" form="assignmentForm" :disabled="submitting" class="btn-premium-primary min-w-[200px]">
                    <template x-if="submitting">
                        <span class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin mr-3 inline-block"></span>
                    </template>
                    <span x-text="submitting ? 'Saving...' : (editMode ? 'Update Assignment' : 'Assign Student')"></span>
                </button>
            </x-slot>
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
                        window.addEventListener('open-add-transport-assignment', () => this.openAddModal());
                        window.addEventListener('open-edit-transport-assignment', (e) => this.openEditModal(e.detail));
                        window.addEventListener('open-delete-transport-assignment', (e) => this.confirmDelete(e.detail));
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
                                        title: result.message || 'Assignment saved'
                                    });
                                }
                                setTimeout(() => window.location.reload(), 1000);
                            } else {
                                if (response.status === 422) {
                                    this.errors = result.errors || {};
                                } else {
                                    throw new Error(result.message || 'Save failed');
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

                    confirmDelete(detail) {
                        window.dispatchEvent(new CustomEvent('open-confirm-modal', {
                            detail: {
                                message: `Remove transport assignment for "${detail.name}"?`,
                                onConfirm: () => this.deleteAssignment(detail.url)
                            }
                        }));
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
                                    window.Toast.fire({ icon: 'success', title: result.message || 'Assignment removed' });
                                }
                                setTimeout(() => window.location.reload(), 1000);
                            } else {
                                throw new Error(result.message || 'Delete failed');
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
                                this.formData.vehicle_id = String(route.vehicle_id);
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
                                option.value = String(stop.id);
                                option.textContent = `${stop.bus_stop_no || ''} - ${stop.bus_stop_name || ''}`;
                                option.setAttribute('data-fee', stop.charge_per_month || '');
                                select.appendChild(option);
                            });
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
                                this.formData.vehicle_id = String(selectedStop.vehicle_id);
                            }
                        }
                    },

                    closeModal() {
                        this.$dispatch('close-modal', 'assignment-modal');
                        this.errors = {};
                    },
                }));
            });
        </script>
    @endpush
@endsection