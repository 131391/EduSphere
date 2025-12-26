@extends('layouts.receptionist')

@section('title', 'Assign Transport Facility')

@section('content')
<div class="space-y-6" x-data="transportAssignmentManagement()" x-init="init()">
    <!-- Success Message -->
    @if(session('success'))
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg relative">
        <span class="block sm:inline">{{ session('success') }}</span>
        <button @click="show = false" class="absolute top-0 right-0 px-4 py-3">
            <i class="fas fa-times"></i>
        </button>
    </div>
    @endif

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
                'key' => 'academic_year',
                'label' => 'ACADEMIC YEAR',
                'render' => function($row) {
                    if (!$row->academicYear) return 'N/A';
                    return $row->academicYear->name ?? ($row->academicYear->start_date->format('Y') . '-' . $row->academicYear->end_date->format('y'));
                }
            ],
            [
                'key' => 'vehicle_no',
                'label' => 'VEHICLE NO',
                'render' => function($row) {
                    return $row->vehicle->vehicle_no ?? 'N/A';
                }
            ],
            [
                'key' => 'route_name',
                'label' => 'ROUTE NAME',
                'render' => function($row) {
                    return $row->route->route_name ?? 'N/A';
                }
            ],
            [
                'key' => 'bus_stop_no',
                'label' => 'BUS STOP NO',
                'render' => function($row) {
                    return $row->busStop->bus_stop_no ?? 'N/A';
                }
            ],
            [
                'key' => 'bus_stop_name',
                'label' => 'BUS STOP NAME',
                'render' => function($row) {
                    return $row->busStop->bus_stop_name ?? 'N/A';
                }
            ],
            [
                'key' => 'fee_per_month',
                'label' => 'FEE PER MONTH',
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
                    return "openEditModal(JSON.parse(atob(this.getAttribute('data-assignment'))))";
                },
                'data-assignment' => function($row) {
                    return base64_encode(json_encode([
                        'id' => $row->id,
                        'student_id' => $row->student_id,
                        'route_id' => $row->route_id,
                        'bus_stop_id' => $row->bus_stop_id,
                        'vehicle_id' => $row->vehicle_id,
                        'fee_per_month' => $row->fee_per_month,
                    ]));
                }
            ],
            [
                'type' => 'form',
                'url' => fn($row) => route('receptionist.transport-assignments.destroy', $row->id),
                'method' => 'DELETE',
                'icon' => 'fas fa-trash',
                'class' => 'text-red-600 hover:text-red-900',
                'title' => 'Delete',
                'confirm' => 'Are you sure you want to delete this transport assignment?'
            ],
        ];

        // Prepare filters
        $filters = [
            [
                'name' => 'class_id',
                'label' => 'Select Class',
                'options' => $classes->pluck('name', 'id')->toArray()
            ],
            [
                'name' => 'vehicle_id',
                'label' => 'Select Vehicle',
                'options' => $vehicles->pluck('vehicle_no', 'id')->toArray()
            ],
            [
                'name' => 'route_id',
                'label' => 'Select Route Name',
                'options' => $routes->pluck('route_name', 'id')->toArray()
            ],
            [
                'name' => 'bus_stop_id',
                'label' => 'Select Bus Stop',
                'options' => $busStops->pluck('bus_stop_name', 'id')->toArray()
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

<!-- Add/Edit Transport Assignment Modal -->
<x-modal name="assignment-modal" maxWidth="4xl" alpineTitle="editMode ? 'Edit Transport Assignment' : 'Assign Transport Facility'">
        <form :action="editMode ? `/receptionist/transport-assignments/${assignmentId}` : '{{ route('receptionist.transport-assignments.store') }}'" 
              method="POST" class="p-6">
            @csrf
            <template x-if="editMode">
                @method('PUT')
            </template>
            <input type="hidden" name="assignment_id" :value="assignmentId" x-show="editMode">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Student Selection -->
            <div>
                <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                    Student <span class="text-red-500">*</span>
                </label>
                <select name="student_id" 
                        x-model="formData.student_id"
                        class="w-full px-4 py-2 border @error('student_id') border-red-500 @else border-gray-300 dark:border-gray-600 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                    <option value="">Select Student</option>
                    @foreach($students as $student)
                        <option value="{{ $student->id }}" 
                                data-admission="{{ $student->admission_no }}"
                                data-class="{{ $student->class->name ?? 'N/A' }}">
                            {{ trim($student->first_name . ' ' . $student->middle_name . ' ' . $student->last_name) }} ({{ $student->admission_no }})
                        </option>
                    @endforeach
                </select>
                @error('student_id')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Route Selection -->
            <div>
                <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                    Route <span class="text-red-500">*</span>
                </label>
                <select name="route_id" 
                        x-model="formData.route_id"
                        @change="loadBusStops()"
                        x-ref="routeSelect"
                        class="w-full px-4 py-2 border @error('route_id') border-red-500 @else border-gray-300 dark:border-gray-600 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                    <option value="">Select Route</option>
                    @foreach($routes as $route)
                        <option value="{{ $route->id }}">{{ $route->route_name }}</option>
                    @endforeach
                </select>
                @error('route_id')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Bus Stop Selection (Filtered by Route) -->
            <div>
                <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                    Bus Stop <span class="text-red-500">*</span>
                </label>
                <select name="bus_stop_id" 
                        x-model="formData.bus_stop_id"
                        @change="updateFee()"
                        x-ref="busStopSelect"
                        class="w-full px-4 py-2 border @error('bus_stop_id') border-red-500 @else border-gray-300 dark:border-gray-600 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                    <option value="">Select Bus Stop</option>
                </select>
                @error('bus_stop_id')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Vehicle Selection -->
            <div>
                <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                    Vehicle (Optional)
                </label>
                <select name="vehicle_id" 
                        x-model="formData.vehicle_id"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                    <option value="">Select Vehicle</option>
                    @foreach($vehicles as $vehicle)
                        <option value="{{ $vehicle->id }}">{{ $vehicle->vehicle_no }} - {{ $vehicle->vehicle_type }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Fee Per Month (Auto-filled, Read-only) -->
            <div>
                <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                    Fee Per Month (₹) <span class="text-red-500">*</span>
                </label>
                <input type="number" 
                       name="fee_per_month"
                       x-model="formData.fee_per_month"
                       step="0.01" 
                       readonly 
                       required
                       class="w-full px-4 py-2 border @error('fee_per_month') border-red-500 @else border-gray-300 dark:border-gray-600 @enderror rounded-lg bg-gray-100 dark:bg-gray-800 dark:text-white cursor-not-allowed">
                @error('fee_per_month')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Form Footer with Buttons -->
            <div class="mt-6 flex justify-end gap-3 pt-4 border-t border-gray-200 dark-border-gray-600">
                <button type="button" 
                        @click="closeModal()"
                        class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 rounded-md transition-colors">
                    Cancel
                </button>
                <button type="submit" 
                        class="px-4 py-2 bg-teal-500 hover:bg-teal-600 text-white rounded-md transition-colors">
                    <span x-text="editMode ? 'Update Assignment' : 'Save Assignment'"></span>
                </button>
            </div>
        </form>
</x-modal>

<!-- close main x-data wrapper -->
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
        
        init() {
            // Listen for modal open/close events
            window.addEventListener('open-modal', (event) => {
                if (event.detail === 'assignment-modal') {
                    const errorBanner = document.getElementById('error-banner');
                    if (errorBanner) {
                        errorBanner.style.display = 'none';
                        if (errorBanner.__x) {
                            errorBanner.__x.$data.show = false;
                        }
                    }
                    
                    // Setup Select2 change handlers for route and bus stop selects after modal opens
                    this.$nextTick(() => {
                        setTimeout(() => {
                            // Setup route select handler
                            let routeSelect = this.$refs.routeSelect || document.querySelector('select[name="route_id"]');
                            if (typeof $ !== 'undefined' && routeSelect) {
                                const $routeSelect = $(routeSelect);
                                // Remove existing handlers to avoid duplicates
                                $routeSelect.off('select2:select.select2:change.change');
                                // Add handler for Select2 events
                                $routeSelect.on('select2:select select2:change', (e) => {
                                    const newRouteId = e.target.value;
                                    const oldRouteId = this.formData.route_id;
                                    
                                    // Only clear bus stop if route actually changed (not initial load)
                                    if (oldRouteId && oldRouteId !== newRouteId) {
                                        this.formData.bus_stop_id = '';
                                        this.formData.fee_per_month = '';
                                    }
                                    
                                    this.formData.route_id = newRouteId;
                                    // Load bus stops for the new route
                                    this.loadBusStops();
                                });
                                // Also handle native change event as fallback
                                $routeSelect.on('change', (e) => {
                                    const newRouteId = e.target.value;
                                    const oldRouteId = this.formData.route_id;
                                    
                                    // Only clear bus stop if route actually changed (not initial load)
                                    if (oldRouteId && oldRouteId !== newRouteId) {
                                        this.formData.bus_stop_id = '';
                                        this.formData.fee_per_month = '';
                                    }
                                    
                                    this.formData.route_id = newRouteId;
                                    this.loadBusStops();
                                });
                            }
                            
                            // Setup bus stop select handler
                            let busStopSelect = this.$refs.busStopSelect || document.querySelector('select[name="bus_stop_id"]');
                            if (typeof $ !== 'undefined' && busStopSelect) {
                                const $busStopSelect = $(busStopSelect);
                                // Remove existing handlers to avoid duplicates
                                $busStopSelect.off('select2:select.select2:change.change');
                                // Add handler for Select2 events
                                $busStopSelect.on('select2:select select2:change', (e) => {
                                    this.formData.bus_stop_id = e.target.value;
                                    this.updateFee();
                                });
                                // Also handle native change event as fallback
                                $busStopSelect.on('change', (e) => {
                                    this.formData.bus_stop_id = e.target.value;
                                    this.updateFee();
                                });
                            }
                        }, 500);
                    });
                }
            });
            
            // Check if there are validation errors and reopen modal with old data
            @if($errors->any())
                this.editMode = {{ old('_method') === 'PUT' ? 'true' : 'false' }};
                this.assignmentId = '{{ old('assignment_id') }}';
                this.formData = {
                    student_id: '{{ old('student_id') }}',
                    route_id: '{{ old('route_id') }}',
                    bus_stop_id: '{{ old('bus_stop_id') }}',
                    vehicle_id: '{{ old('vehicle_id') }}',
                    fee_per_month: '{{ old('fee_per_month') }}',
                };
                
                // Load bus stops if route is selected
                if (this.formData.route_id) {
                    this.loadBusStops();
                }
                
                this.$nextTick(() => {
                    this.$dispatch('open-modal', 'assignment-modal');
                });
            @endif
        },
        
        openAddModal() {
            this.editMode = false;
            this.assignmentId = null;
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
            this.formData = {
                student_id: assignment.student_id || '',
                route_id: assignment.route_id || '',
                bus_stop_id: assignment.bus_stop_id || '',
                vehicle_id: assignment.vehicle_id || '',
                fee_per_month: assignment.fee_per_month || '',
            };
            
            // Store original values for later use
            const originalRouteId = this.formData.route_id;
            const originalBusStopId = this.formData.bus_stop_id;
            const originalFee = this.formData.fee_per_month;
            
            // Load bus stops for the selected route first
            if (this.formData.route_id) {
                this.loadBusStops();
            }
            
            this.$dispatch('open-modal', 'assignment-modal');
            
            // Wait for modal to open and Select2 to initialize, then set all values
            this.$nextTick(() => {
                setTimeout(() => {
                    if (typeof $ !== 'undefined') {
                        // Set student select
                        const $studentSelect = $('select[name="student_id"]');
                        if ($studentSelect.length && this.formData.student_id) {
                            $studentSelect.val(this.formData.student_id).trigger('change');
                        }
                        
                        // Set route select without triggering change (to avoid clearing bus stop)
                        const $routeSelect = $('select[name="route_id"]');
                        if ($routeSelect.length && this.formData.route_id) {
                            // Temporarily disable change handlers to prevent clearing bus stop
                            $routeSelect.off('select2:select.select2:change.change');
                            $routeSelect.val(this.formData.route_id).trigger('change.select2');
                            // Re-enable handlers after a short delay
                            setTimeout(() => {
                                $routeSelect.on('select2:select select2:change', (e) => {
                                    const newRouteId = e.target.value;
                                    const oldRouteId = this.formData.route_id;
                                    
                                    // Only clear bus stop if route actually changed (not initial load)
                                    if (oldRouteId && oldRouteId !== newRouteId) {
                                        this.formData.bus_stop_id = '';
                                        this.formData.fee_per_month = '';
                                    }
                                    
                                    this.formData.route_id = newRouteId;
                                    // Load bus stops for the new route
                                    this.loadBusStops();
                                });
                                $routeSelect.on('change', (e) => {
                                    const newRouteId = e.target.value;
                                    const oldRouteId = this.formData.route_id;
                                    
                                    // Only clear bus stop if route actually changed (not initial load)
                                    if (oldRouteId && oldRouteId !== newRouteId) {
                                        this.formData.bus_stop_id = '';
                                        this.formData.fee_per_month = '';
                                    }
                                    
                                    this.formData.route_id = newRouteId;
                                    this.loadBusStops();
                                });
                            }, 100);
                        }
                        
                        // Wait for bus stops to load, then set bus stop and fee
                        // Use a longer delay to ensure bus stops are fully loaded
                        setTimeout(() => {
                            // Ensure bus stops are loaded
                            if (originalRouteId && this.busStops.length === 0) {
                                this.loadBusStops();
                            }
                            
                            // Wait a bit more for Select2 to be ready
                            setTimeout(() => {
                                if (originalBusStopId) {
                                    const $busStopSelect = $('select[name="bus_stop_id"]');
                                    if ($busStopSelect.length) {
                                        // Check if the option exists in the select
                                        const optionExists = $busStopSelect.find(`option[value="${originalBusStopId}"]`).length > 0;
                                        
                                        if (optionExists) {
                                            // Set the bus stop value
                                            $busStopSelect.val(originalBusStopId).trigger('change');
                                            
                                            // Update formData to ensure Alpine reactivity
                                            this.formData.bus_stop_id = originalBusStopId;
                                            
                                            // Update fee - try from bus stop data first
                                            this.updateFee();
                                            
                                            // If fee wasn't updated from bus stop, use original fee
                                            if (!this.formData.fee_per_month && originalFee) {
                                                this.formData.fee_per_month = parseFloat(originalFee).toFixed(2);
                                            }
                                        } else {
                                            // Option doesn't exist yet, wait a bit more and try again
                                            setTimeout(() => {
                                                if (this.busStops.length > 0) {
                                                    $busStopSelect.val(originalBusStopId).trigger('change');
                                                    this.formData.bus_stop_id = originalBusStopId;
                                                    this.updateFee();
                                                    if (!this.formData.fee_per_month && originalFee) {
                                                        this.formData.fee_per_month = parseFloat(originalFee).toFixed(2);
                                                    }
                                                }
                                            }, 200);
                                        }
                                    }
                                }
                            }, 400);
                        }, 600);
                        
                        // Set vehicle select
                        const $vehicleSelect = $('select[name="vehicle_id"]');
                        if ($vehicleSelect.length && this.formData.vehicle_id) {
                            $vehicleSelect.val(this.formData.vehicle_id).trigger('change');
                        }
                    }
                }, 500);
            });
        },
        
        loadBusStops() {
            // Store the current bus stop ID and fee before clearing (for edit mode)
            const currentBusStopId = this.formData.bus_stop_id;
            const currentFee = this.formData.fee_per_month;
            
            if (!this.formData.route_id) {
                this.busStops = [];
                this.formData.bus_stop_id = '';
                this.formData.fee_per_month = '';
                this.routeVehicleId = null;
                this.formData.vehicle_id = '';
                
                // Clear select options and refresh Select2
                this.$nextTick(() => {
                    const select = this.$refs.busStopSelect || document.querySelector('select[name="bus_stop_id"]');
                    if (select && typeof $ !== 'undefined') {
                        const $select = $(select);
                        const isSelect2 = $select.hasClass('select2-hidden-accessible');
                        
                        // Destroy Select2 if initialized
                        if (isSelect2) {
                            $select.select2('destroy');
                        }
                        
                        // Clear options
                        while (select.options.length > 1) {
                            select.remove(1);
                        }
                        
                        // Reinitialize Select2
                        $select.select2({
                            placeholder: 'Select Bus Stop',
                            allowClear: false,
                            width: '100%'
                        });
                    }
                });
                return;
            }
            
            const routeId = Number(this.formData.route_id);
            
            // Filter bus stops by route (type-safe)
            const filtered = this.allBusStops.filter(stop => {
                const stopRouteId = Number(stop.route_id);
                return stopRouteId === routeId;
            });
            
            // Update busStops array (create new array to trigger reactivity)
            this.busStops = [...filtered];
            
            // Check if current bus stop is still valid for the new route
            const busStopStillValid = this.busStops.some(stop => Number(stop.id) === Number(currentBusStopId));
            if (!busStopStillValid && currentBusStopId) {
                // Only clear bus stop and fee if it's not valid for the new route AND we're not in edit mode
                // In edit mode, we want to preserve the values until we confirm they're invalid
                if (!this.editMode) {
                    this.formData.bus_stop_id = '';
                    this.formData.fee_per_month = '';
                }
            }
            
            // Manually update select options and refresh Select2
            // Use setTimeout to ensure modal is fully rendered
            setTimeout(() => {
                const select = this.$refs.busStopSelect || document.querySelector('select[name="bus_stop_id"]');
                
                if (!select) {
                    return;
                }
                
                if (typeof $ === 'undefined') {
                    return;
                }
                
                // Update options while preserving the current value to avoid flickering
                this.updateSelectOptions(select, currentBusStopId, busStopStillValid, currentFee);
            }, 200);

            // Store the route's default vehicle (if any)
            const route = this.allRoutes.find(r => Number(r.id) === routeId);
            this.routeVehicleId = route ? route.vehicle_id : null;
            // Preselect the route vehicle if present
            this.formData.vehicle_id = this.routeVehicleId || '';
            
            // Clear bus stop selection if current selection is not in the filtered list
            if (this.formData.bus_stop_id) {
                const exists = this.busStops.some(stop => Number(stop.id) === Number(this.formData.bus_stop_id));
                if (!exists) {
                    this.formData.bus_stop_id = '';
                    this.formData.fee_per_month = '';
                }
            }
        },
        
        updateSelectOptions(select, preserveValue = null, isValid = true, preserveFee = null) {
            const $select = $(select);
            const isSelect2 = $select.hasClass('select2-hidden-accessible');
            
            // Store current value before updating options
            const currentValue = preserveValue || this.formData.bus_stop_id || $select.val();
            
            // If Select2 is initialized and we're preserving a valid value, update more smoothly
            if (isSelect2 && currentValue && isValid) {
                try {
                    // Temporarily disable Select2 to update options without visual disruption
                    const wasSelected = $select.val() === currentValue;
                    
                    // Clear existing options except the first one
                    while (select.options.length > 1) {
                        select.remove(1);
                    }
                    
                    // Add filtered bus stops
                    this.busStops.forEach((stop) => {
                        const option = document.createElement('option');
                        option.value = stop.id;
                        option.textContent = `${stop.bus_stop_no || ''} - ${stop.bus_stop_name || ''}`;
                        option.setAttribute('data-fee', stop.charge_per_month || '');
                        select.appendChild(option);
                    });
                    
                    // Restore the value immediately before Select2 updates
                    select.value = currentValue;
                    
                    // Update Select2 display without destroying (just refresh the display)
                    $select.trigger('change.select2');
                    
                    // Ensure formData is in sync
                    if (wasSelected) {
                        this.formData.bus_stop_id = currentValue;
                        // Update fee if needed
                        if (preserveFee && !this.formData.fee_per_month) {
                            this.formData.fee_per_month = preserveFee;
                        } else {
                            this.updateFee();
                        }
                    }
                    
                    return; // Exit early, no need to destroy/reinitialize
                } catch (e) {
                    // If update fails, fall through to destroy/reinitialize method
                }
            }
            
            // Fallback: Destroy and reinitialize (for when Select2 isn't initialized or value is invalid)
            if (isSelect2) {
                try {
                    $select.select2('destroy');
                } catch (e) {
                    // Silently handle error
                }
            }
            
            // Clear existing options except the first one
            while (select.options.length > 1) {
                select.remove(1);
            }
            
            // Add filtered bus stops
            this.busStops.forEach((stop) => {
                const option = document.createElement('option');
                option.value = stop.id;
                option.textContent = `${stop.bus_stop_no || ''} - ${stop.bus_stop_name || ''}`;
                option.setAttribute('data-fee', stop.charge_per_month || '');
                select.appendChild(option);
            });
            
            // Reinitialize Select2
            try {
                $select.select2({
                    placeholder: 'Select Bus Stop',
                    allowClear: false,
                    width: '100%'
                });
                
                // Restore the value if it still exists in the new options
                if (currentValue && isValid) {
                    const optionExists = $select.find(`option[value="${currentValue}"]`).length > 0;
                    if (optionExists) {
                        $select.val(currentValue).trigger('change.select2');
                        this.formData.bus_stop_id = currentValue;
                        this.updateFee();
                        if (preserveFee && !this.formData.fee_per_month) {
                            this.formData.fee_per_month = preserveFee;
                        }
                    }
                } else if (!isValid) {
                    $select.val('').trigger('change.select2');
                }
            } catch (e) {
                // Silently handle error
            }
        },
        
        updateFee() {
            if (!this.formData.bus_stop_id) {
                this.formData.fee_per_month = '';
                // If no bus stop selected, fall back to route vehicle
                this.formData.vehicle_id = this.routeVehicleId || '';
                return;
            }
            
            // Try to find selected stop from busStops array
            let selectedStop = this.busStops.find(
                stop => Number(stop.id) === Number(this.formData.bus_stop_id)
            );
            
            // If not found in array, try to get from select element's data attribute
            if (!selectedStop && typeof $ !== 'undefined') {
                const busStopSelect = this.$refs.busStopSelect || document.querySelector('select[name="bus_stop_id"]');
                if (busStopSelect) {
                    const $select = $(busStopSelect);
                    const selectedOption = $select.find('option:selected');
                    if (selectedOption.length && selectedOption.val()) {
                        const fee = selectedOption.data('fee') || selectedOption.attr('data-fee');
                        if (fee) {
                            this.formData.fee_per_month = parseFloat(fee).toFixed(2);
                        }
                    }
                }
            }
            
            if (selectedStop) {
                if (selectedStop.charge_per_month != null) {
                    this.formData.fee_per_month = parseFloat(selectedStop.charge_per_month).toFixed(2);
                } else {
                    this.formData.fee_per_month = '';
                }

                // Set vehicle from the selected bus stop if available; otherwise keep route vehicle
                if (selectedStop.vehicle_id) {
                    this.formData.vehicle_id = selectedStop.vehicle_id;
                } else {
                    this.formData.vehicle_id = this.routeVehicleId || '';
                }
            }
        },
        
        closeModal() {
            this.$dispatch('close-modal', 'assignment-modal');
        },
    }));
});

// Global function to open edit modal (called from table action buttons)
function openEditModal(assignment) {
    const component = Alpine.$data(document.querySelector('[x-data*="transportAssignmentManagement"]'));
    if (component) {
        component.openEditModal(assignment);
    }
}

// Global script to hide validation errors when user starts typing or selecting
document.addEventListener('DOMContentLoaded', function() {
    // Clear errors on input/change
    $(document).on('input change', 'input, select, textarea', function() {
        const fieldName = $(this).attr('name');
        if (fieldName) {
            // Remove red border
            $(this).removeClass('border-red-500').addClass('border-gray-300');
            
            // Remove error message
            $(this).siblings('p.text-red-500').remove();
            $(this).closest('div').find('p.text-red-500').remove();
        }
    });
    
    // Hide error banner when modal opens
    $(document).on('shown.bs.modal', function() {
        $('#error-banner').hide();
    });
});
</script>
@endpush
@endsection
