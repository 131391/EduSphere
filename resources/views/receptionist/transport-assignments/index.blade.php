@extends('layouts.receptionist')

@section('title', 'Assign Transport Facility')

@section('content')
<div class="space-y-6" x-data="transportAssignmentManagement()" x-init="init()">
    <!-- Success Message -->

    <!-- Error Banner -->
    @if($errors->any())
    <div id="error-banner" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg relative">
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

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border-t-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Assigned Students</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ $totalAssigned }}</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-users text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border-t-4 border-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Active Routes</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ $activeRoutes }}</p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-route text-green-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border-t-4 border-purple-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Monthly Fees</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">₹{{ number_format($totalFees, 2) }}</p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-rupee-sign text-purple-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Page Header with Actions --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 mb-6">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <h2 class="text-xl font-bold text-gray-800 dark:text-white">Transport Assignments</h2>
            <div class="flex flex-wrap gap-2">
                <button @click="openAddModal()" 
                        class="inline-flex items-center px-4 py-2 bg-teal-500 hover:bg-teal-600 text-white text-sm font-medium rounded-md transition-colors shadow-sm">
                    <i class="fas fa-plus mr-2"></i>
                    Assign Transport
                </button>
            </div>
        </div>
    </div>

    <!-- Data Table -->
    @php
        $tableColumns = [
            [
                'key' => 'sr_no',
                'label' => 'SR NO',
                'render' => function($row, $index, $data) use ($assignments) {
                    return ($assignments->currentPage() - 1) * $assignments->perPage() + $index + 1;
                }
            ],
            [
                'key' => 'student_name',
                'label' => 'STUDENT NAME',
                'render' => function($row) {
                    if (!$row->student) return 'N/A';
                    return trim($row->student->first_name . ' ' . $row->student->middle_name . ' ' . $row->student->last_name) ?: 'N/A';
                }
            ],
            [
                'key' => 'admission_no',
                'label' => 'ADMISSION NO',
                'render' => function($row) {
                    return $row->student->admission_no ?? 'N/A';
                }
            ],
            [
                'key' => 'class',
                'label' => 'CLASS',
                'render' => function($row) {
                    return $row->student->class->name ?? 'N/A';
                }
            ],
            [
                'key' => 'vehicle_no',
                'label' => 'VEHICLE',
                'render' => function($row) {
                    return $row->vehicle->vehicle_no ?? 'N/A';
                }
            ],
            [
                'key' => 'route_name',
                'label' => 'ROUTE',
                'render' => function($row) {
                    return $row->route->route_name ?? 'N/A';
                }
            ],
            [
                'key' => 'bus_stop_name',
                'label' => 'BUS STOP',
                'render' => function($row) {
                    return ($row->busStop->bus_stop_no ?? '') . ' - ' . ($row->busStop->bus_stop_name ?? 'N/A');
                }
            ],
            [
                'key' => 'fee_per_month',
                'label' => 'FEE/MONTH',
                'render' => function($row) {
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
                'onclick' => function($row) {
                    return "openEditModal(".json_encode([
                        'id' => $row->id,
                        'student_id' => $row->student_id,
                        'route_id' => $row->route_id,
                        'bus_stop_id' => $row->bus_stop_id,
                        'vehicle_id' => $row->vehicle_id,
                        'fee_per_month' => $row->fee_per_month,
                    ]).")";
                }
            ],
            [
                'type' => 'button',
                'icon' => 'fas fa-trash',
                'class' => 'text-red-600 hover:text-red-900',
                'title' => 'Delete',
                'onclick' => function($row) {
                    $name = trim($row->student->first_name . ' ' . $row->student->last_name);
                    return "confirmDelete('".route('receptionist.transport-assignments.destroy', $row->id)."', '{$name}')";
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

    <x-data-table
        :columns="$tableColumns"
        :data="$assignments"
        :actions="$tableActions"
        :searchable="true"
        :filterable="true"
        :filters="$filters"
    >
        Transport Assignments
    </x-data-table>

    {{-- Add/Edit Transport Assignment Modal --}}
    <x-modal name="assignment-modal" maxWidth="4xl" alpineTitle="editMode ? 'Modify Transport Assignment' : 'Assign Transport Facility'">
        <form @submit.prevent="save" method="POST" class="p-0 relative">
            @csrf
            <template x-if="editMode">
                <input type="hidden" name="_method" value="PUT">
            </template>

            {{-- Global Error Announcement --}}
            <template x-if="Object.keys(errors).length > 0">
                <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded-r-xl mx-6 mt-6">
                    <div class="flex items-center gap-2 mb-2">
                        <i class="fas fa-exclamation-circle text-red-500"></i>
                        <span class="text-xs font-black text-red-700 uppercase tracking-widest">Validation Exceptions</span>
                    </div>
                    <ul class="list-disc list-inside space-y-1">
                        <template x-for="(messages, field) in errors" :key="field">
                            <template x-for="message in messages" :key="message">
                                <li class="text-[10px] text-red-600 font-bold uppercase" x-text="message"></li>
                            </template>
                        </template>
                    </ul>
                </div>
            </template>

            <div class="p-8 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Student Selection -->
                    <div>
                        <label class="block text-xs font-black text-gray-500 dark:text-gray-400 mb-2 uppercase tracking-widest">
                            Target Student <span class="text-red-500">*</span>
                        </label>
                        <select name="student_id" x-model="formData.student_id" id="student_id"
                                @change="delete errors.student_id"
                                class="w-full px-4 py-3 bg-white border border-gray-200 rounded-xl focus:outline-none focus:ring-4 transition-all dark:bg-gray-800 dark:text-white"
                                :class="errors.student_id ? 'border-red-500 ring-red-500/10' : 'focus:ring-teal-500/10 focus:border-teal-500'">
                            <option value="">Select Student</option>
                            @foreach($students as $student)
                                <option value="{{ $student->id }}">
                                    {{ trim($student->first_name . ' ' . $student->middle_name . ' ' . $student->last_name) }} ({{ $student->admission_no }})
                                </option>
                            @endforeach
                        </select>
                        <template x-if="errors.student_id">
                            <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.student_id[0]"></p>
                        </template>
                    </div>

                    <!-- Route Selection -->
                    <div>
                        <label class="block text-xs font-black text-gray-500 dark:text-gray-400 mb-2 uppercase tracking-widest">
                            Primary Route <span class="text-red-500">*</span>
                        </label>
                        <select name="route_id" x-model="formData.route_id" id="route_id"
                                @change="delete errors.route_id; loadBusStops()"
                                class="w-full px-4 py-3 bg-white border border-gray-200 rounded-xl focus:outline-none focus:ring-4 transition-all dark:bg-gray-800 dark:text-white"
                                :class="errors.route_id ? 'border-red-500 ring-red-500/10' : 'focus:ring-teal-500/10 focus:border-teal-500'">
                            <option value="">Select Route</option>
                            @foreach($routes as $route)
                                <option value="{{ $route->id }}">{{ $route->route_name }}</option>
                            @endforeach
                        </select>
                        <template x-if="errors.route_id">
                            <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.route_id[0]"></p>
                        </template>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Bus Stop Selection -->
                    <div>
                        <label class="block text-xs font-black text-gray-500 dark:text-gray-400 mb-2 uppercase tracking-widest">
                            Network Node (Stop) <span class="text-red-500">*</span>
                        </label>
                        <select name="bus_stop_id" x-model="formData.bus_stop_id" id="bus_stop_id"
                                @change="delete errors.bus_stop_id; updateFee()"
                                class="w-full px-4 py-3 bg-white border border-gray-200 rounded-xl focus:outline-none focus:ring-4 transition-all dark:bg-gray-800 dark:text-white"
                                :class="errors.bus_stop_id ? 'border-red-500 ring-red-500/10' : 'focus:ring-teal-500/10 focus:border-teal-500'">
                            <option value="">Select Bus Stop</option>
                        </select>
                        <template x-if="errors.bus_stop_id">
                            <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.bus_stop_id[0]"></p>
                        </template>
                    </div>

                    <!-- Vehicle Selection -->
                    <div>
                        <label class="block text-xs font-black text-gray-500 dark:text-gray-400 mb-2 uppercase tracking-widest">
                            Assigned Vehicle
                        </label>
                        <select name="vehicle_id" x-model="formData.vehicle_id" id="vehicle_id"
                                @change="delete errors.vehicle_id"
                                class="w-full px-4 py-3 bg-white border border-gray-200 rounded-xl focus:outline-none focus:ring-4 transition-all dark:bg-gray-800 dark:text-white"
                                :class="errors.vehicle_id ? 'border-red-500 ring-red-500/10' : 'focus:ring-teal-500/10 focus:border-teal-500'">
                            <option value="">Select Vehicle</option>
                            @foreach($vehicles as $vehicle)
                                <option value="{{ $vehicle->id }}">{{ $vehicle->vehicle_no }} ({{ $vehicle->vehicle_type }})</option>
                            @endforeach
                        </select>
                        <template x-if="errors.vehicle_id">
                            <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.vehicle_id[0]"></p>
                        </template>
                    </div>
                </div>

                <!-- Fee Per Month -->
                <div class="bg-teal-50 dark:bg-teal-900/20 p-6 rounded-2xl border border-teal-100 dark:border-teal-800">
                    <label class="block text-xs font-black text-teal-800 dark:text-teal-400 mb-2 uppercase tracking-widest">
                        Computed Monthly Tariff (Incurred Fee) <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-teal-600 font-black">₹</span>
                        <input type="number" name="fee_per_month" x-model="formData.fee_per_month"
                               step="0.01" readonly
                               class="w-full pl-10 pr-4 py-4 bg-white border border-teal-200 rounded-xl focus:outline-none dark:bg-gray-900 dark:text-white font-black text-lg cursor-not-allowed shadow-inner"
                               :class="errors.fee_per_month ? 'border-red-500 ring-red-500/10' : 'focus:ring-teal-500/10 border-teal-200'">
                    </div>
                    <p class="text-[10px] text-teal-600 mt-2 font-bold uppercase tracking-widest">Computed based on network node selection</p>
                    <template x-if="errors.fee_per_month">
                        <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.fee_per_month[0]"></p>
                    </template>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="px-8 py-6 bg-gray-50 border-t border-gray-100 flex items-center justify-end gap-3 rounded-b-xl">
                <button type="button" @click="closeModal()" :disabled="submitting"
                        class="px-6 py-2.5 bg-white border border-gray-200 text-gray-700 rounded-xl hover:bg-gray-50 transition-all font-bold text-sm">
                    Discard
                </button>
                <button type="submit" :disabled="submitting"
                        class="px-8 py-2.5 bg-gradient-to-r from-teal-500 to-emerald-600 text-white rounded-xl hover:from-teal-600 hover:to-emerald-700 transition-all font-black text-sm shadow-lg shadow-teal-100 flex items-center gap-2">
                    <template x-if="submitting">
                        <span class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                    </template>
                    <span x-text="submitting ? 'Propagating...' : (editMode ? 'Update Assignment' : 'Assign Facility')"></span>
                </button>
            </div>
        </form>
    </x-modal>

    <x-confirm-modal 
        title="Strike Transport Record?" 
        message="This will terminate the student's transport facility access. This record will be archived."
        confirm-text="Strike Record"
        confirm-color="red"
    />
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
        
        async init() {
            this.$nextTick(() => {
                if (typeof $ !== 'undefined') {
                    // Initialize Select2 Sync
                    $(document).on('change', '#student_id', (e) => {
                        this.formData.student_id = e.target.value;
                        if (this.errors.student_id) delete this.errors.student_id;
                    });
                    $(document).on('change', '#route_id', (e) => {
                        this.formData.route_id = e.target.value;
                        if (this.errors.route_id) delete this.errors.route_id;
                        this.loadBusStops();
                    });
                    $(document).on('change', '#bus_stop_id', (e) => {
                        this.formData.bus_stop_id = e.target.value;
                        if (this.errors.bus_stop_id) delete this.errors.bus_stop_id;
                        this.updateFee();
                    });
                    $(document).on('change', '#vehicle_id', (e) => {
                        this.formData.vehicle_id = e.target.value;
                        if (this.errors.vehicle_id) delete this.errors.vehicle_id;
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
                    $('#student_id, #route_id, #bus_stop_id, #vehicle_id').val('').trigger('change.select2');
                }
            });
        },
        
        openEditModal(assignment) {
            this.editMode = true;
            this.assignmentId = assignment.id;
            this.errors = {};
            this.formData = {
                student_id: assignment.student_id || '',
                route_id: assignment.route_id || '',
                bus_stop_id: assignment.bus_stop_id || '',
                vehicle_id: assignment.vehicle_id || '',
                fee_per_month: assignment.fee_per_month || '',
            };
            
            // Initial load of stops for this route
            if (this.formData.route_id) {
                this.loadBusStops(true);
            }
            
            this.$dispatch('open-modal', 'assignment-modal');
            
            this.$nextTick(() => {
                if (typeof $ !== 'undefined') {
                    $('#student_id').val(assignment.student_id).trigger('change.select2');
                    $('#route_id').val(assignment.route_id).trigger('change.select2');
                    $('#vehicle_id').val(assignment.vehicle_id).trigger('change.select2');
                    
                    // Specific timing for bus stop because it's dynamic
                    setTimeout(() => {
                        $('#bus_stop_id').val(assignment.bus_stop_id).trigger('change.select2');
                    }, 300);
                }
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
                    if (typeof $ !== 'undefined') $('#vehicle_id').val(route.vehicle_id).trigger('change.select2');
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

                if (typeof $ !== 'undefined') $(select).trigger('change.select2');
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
                    if (typeof $ !== 'undefined') $('#vehicle_id').val(selectedStop.vehicle_id).trigger('change.select2');
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
