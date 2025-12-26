@extends('layouts.receptionist')

@section('title', 'Student Attendance - Transport')

@section('content')
<div class="space-y-6" x-data="transportAttendanceManagement()" x-init="init()">
    <!-- Success Message -->
    @if(session('success'))
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg relative">
        <span class="block sm:inline">{{ session('success') }}</span>
        <button @click="show = false" class="absolute top-0 right-0 px-4 py-3">
            <i class="fas fa-times"></i>
        </button>
    </div>
    @endif

    {{-- Page Header --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 mb-6">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <h2 class="text-xl font-bold text-gray-800 dark:text-white">Student Attendance</h2>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('receptionist.transport-assignments.index') }}" 
                   class="inline-flex items-center px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white text-sm font-medium rounded-md transition-colors shadow-sm">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Back
                </a>
            </div>
        </div>
    </div>

    <!-- Attendance Form -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
        <form id="attendanceForm" method="POST" action="{{ route('receptionist.transport-attendance.store') }}">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <!-- Vehicle Selection -->
                <div>
                    <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                        Vehicle <span class="text-red-500">*</span>
                    </label>
                    <select 
                        name="vehicle_id" 
                        id="vehicle_id"
                        x-model="formData.vehicle_id"
                        @change="loadRoutes()"
                        x-ref="vehicleSelect"
                        class="w-full px-4 py-2 border @error('vehicle_id') border-red-500 @else border-gray-300 dark:border-gray-600 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white"
                        required
                    >
                        <option value="">Select Vehicle</option>
                        @foreach($vehicles as $vehicle)
                            <option value="{{ $vehicle->id }}">
                                {{ $vehicle->vehicle_no }} ({{ $vehicle->registration_no }})
                            </option>
                        @endforeach
                    </select>
                    @error('vehicle_id')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Route Selection -->
                <div>
                    <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                        Route <span class="text-red-500">*</span>
                    </label>
                    <select 
                        name="route_id" 
                        id="route_id"
                        x-model="formData.route_id"
                        @change="loadStudents()"
                        x-ref="routeSelect"
                        class="w-full px-4 py-2 border @error('route_id') border-red-500 @else border-gray-300 dark:border-gray-600 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white"
                        required
                    >
                        <option value="">Select Route</option>
                    </select>
                    @error('route_id')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Attendance Type Selection -->
                <div>
                    <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                        Attendance Type <span class="text-red-500">*</span>
                    </label>
                    <select 
                        name="attendance_type" 
                        id="attendance_type"
                        x-model="formData.attendance_type"
                        class="w-full px-4 py-2 border @error('attendance_type') border-red-500 @else border-gray-300 dark:border-gray-600 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white"
                        required
                    >
                        <option value="">Select Attendance</option>
                        @foreach($attendanceTypes as $type)
                            <option value="{{ $type->value }}">{{ $type->label() }}</option>
                        @endforeach
                    </select>
                    @error('attendance_type')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Attendance Date -->
                <div>
                    <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                        Date <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="date" 
                        name="attendance_date" 
                        id="attendance_date"
                        x-model="formData.attendance_date"
                        value="{{ old('attendance_date', date('Y-m-d')) }}"
                        max="{{ date('Y-m-d') }}"
                        class="w-full px-4 py-2 border @error('attendance_date') border-red-500 @else border-gray-300 dark:border-gray-600 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white"
                        required
                    >
                    @error('attendance_date')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end mb-6">
                <button 
                    type="submit"
                    :disabled="!canSubmit"
                    class="px-6 py-2 bg-green-500 hover:bg-green-600 text-white font-medium rounded-lg transition-colors shadow-sm disabled:bg-gray-400 disabled:cursor-not-allowed"
                >
                    <i class="fas fa-check mr-2"></i>
                    Submit
                </button>
            </div>

            <!-- Students List -->
            <div x-show="students.length > 0" class="mt-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white">
                        Students (<span x-text="students.length"></span>)
                    </h3>
                    <div class="flex gap-2">
                        <button 
                            type="button"
                            @click="checkAll()"
                            class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white text-sm font-medium rounded-lg transition-colors"
                        >
                            <i class="fas fa-check-square mr-2"></i>
                            Check All
                        </button>
                        <button 
                            type="button"
                            @click="uncheckAll()"
                            class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white text-sm font-medium rounded-lg transition-colors"
                        >
                            <i class="fas fa-square mr-2"></i>
                            Uncheck All
                        </button>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    <input 
                                        type="checkbox" 
                                        @change="toggleAll($event.target.checked)"
                                        :checked="allChecked"
                                        class="rounded border-gray-300 text-teal-600 focus:ring-teal-500"
                                    >
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">SR NO</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">ADMISSION NO</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">STUDENT NAME</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">BUS STOP NAME</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">ATTENDANCE</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <template x-for="(student, index) in students" :key="student.id">
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <input 
                                            type="checkbox" 
                                            :id="`student_${student.id}`"
                                            :value="student.id.toString()"
                                            x-model="checkedStudents"
                                            class="rounded border-gray-300 text-teal-600 focus:ring-teal-500"
                                        >
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-white" x-text="index + 1"></td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-white" x-text="student.admission_no"></td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-white" x-text="student.name"></td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-white" x-text="student.bus_stop_name"></td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <span 
                                            class="px-2 py-1 text-xs font-semibold rounded-full"
                                            :class="checkedStudents.includes(student.id.toString()) ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                                            x-text="checkedStudents.includes(student.id.toString()) ? 'Present' : 'Absent'"
                                        ></span>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                <!-- Hidden inputs for all students and checked students -->
                <template x-for="student in students" :key="student.id">
                    <input type="hidden" :name="`students[]`" :value="student.id">
                </template>
                <template x-for="studentId in checkedStudents" :key="studentId">
                    <input type="hidden" :name="`checked_students[]`" :value="studentId">
                </template>
            </div>

            <!-- Empty State -->
            <div x-show="students.length === 0 && formData.route_id" class="text-center py-12">
                <i class="fas fa-users text-gray-400 text-6xl mb-4"></i>
                <p class="text-gray-500 dark:text-gray-400">No students found for the selected route.</p>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function transportAttendanceManagement() {
    return {
        formData: {
            vehicle_id: '',
            route_id: '',
            attendance_type: '',
            attendance_date: '{{ date('Y-m-d') }}',
        },
        routes: [],
        students: [],
        checkedStudents: [],
        loading: false,

        get canSubmit() {
            return !!(this.formData.vehicle_id && this.formData.route_id && this.formData.attendance_type && this.formData.attendance_date && this.students.length > 0);
        },

        async init() {
            // Wait for Select2 to be auto-initialized by global script, then attach handlers
            this.$nextTick(() => {
                setTimeout(() => {
                    if (typeof $ !== 'undefined') {
                        // Handle vehicle select Select2 events
                        const vehicleSelect = this.$refs.vehicleSelect || document.getElementById('vehicle_id');
                        if (vehicleSelect) {
                            const $vehicleSelect = $(vehicleSelect);
                            // Handle Select2 change events for vehicle
                            $vehicleSelect.off('select2:select.select2:change').on('select2:select select2:change', (e) => {
                                const value = e.target.value || $vehicleSelect.val();
                                this.formData.vehicle_id = value;
                                this.$nextTick(() => {
                                    this.loadRoutes();
                                });
                            });
                        }
                        
                        // Handle attendance type select Select2 events
                        const attendanceTypeSelect = document.getElementById('attendance_type');
                        if (attendanceTypeSelect) {
                            const $attendanceTypeSelect = $(attendanceTypeSelect);
                            $attendanceTypeSelect.off('select2:select.select2:change').on('select2:select select2:change', (e) => {
                                const value = e.target.value || $attendanceTypeSelect.val();
                                this.formData.attendance_type = value;
                            });
                        }
                        
                        const routeSelect = this.$refs.routeSelect || document.getElementById('route_id');
                        if (routeSelect) {
                            const $routeSelect = $(routeSelect);
                            
                            // If Select2 is already initialized, just attach handlers
                            if ($routeSelect.hasClass('select2-hidden-accessible')) {
                                // Remove existing handlers to avoid duplicates
                                $routeSelect.off('select2:select.select2:change.change');
                            } else {
                                // Initialize Select2 if not already initialized
                                $routeSelect.select2({
                                    placeholder: 'Select Route',
                                    allowClear: false,
                                    width: '100%'
                                });
                            }
                            
                            // Handle Select2 change events
                            $routeSelect.on('select2:select select2:change', (e) => {
                                const value = e.target.value || $routeSelect.val();
                                this.formData.route_id = value;
                                this.$nextTick(() => {
                                    this.loadStudents();
                                });
                            });
                            
                            // Also handle native change event as fallback
                            $routeSelect.on('change', (e) => {
                                const value = e.target.value || $routeSelect.val();
                                this.formData.route_id = value;
                                this.$nextTick(() => {
                                    this.loadStudents();
                                });
                            });
                        }
                    }
                }, 300);
            });

            // Set old values if validation failed
            @if(old('vehicle_id'))
                this.formData.vehicle_id = '{{ old('vehicle_id') }}';
                await this.loadRoutes();
                
                @if(old('route_id'))
                    // Wait a bit for routes to populate, then set route and load students
                    setTimeout(() => {
                        this.formData.route_id = '{{ old('route_id') }}';
                        if (typeof $ !== 'undefined') {
                            const routeSelect = this.$refs.routeSelect || document.getElementById('route_id');
                            if (routeSelect) {
                                $(routeSelect).val(this.formData.route_id).trigger('change');
                            }
                        }
                        this.loadStudents();
                    }, 500);
                @endif
            @endif

            @if(old('attendance_type'))
                this.formData.attendance_type = '{{ old('attendance_type') }}';
            @endif

            @if(old('attendance_date'))
                this.formData.attendance_date = '{{ old('attendance_date') }}';
            @endif
        },

        async loadRoutes() {
            if (!this.formData.vehicle_id) {
                this.routes = [];
                this.formData.route_id = '';
                this.students = [];
                this.checkedStudents = [];
                this.updateRouteOptions();
                return;
            }

            this.loading = true;
            const url = '{{ route('receptionist.transport-attendance.get-routes') }}';
            
            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
                
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        vehicle_id: this.formData.vehicle_id,
                    }),
                });

                if (!response.ok) {
                    const errorText = await response.text();
                    let errorData;
                    try {
                        errorData = JSON.parse(errorText);
                    } catch (e) {
                        errorData = { error: errorText || 'Failed to load routes' };
                    }
                    throw new Error(errorData.error || errorData.message || 'Failed to load routes');
                }

                const data = await response.json();
                
                if (data.success && Array.isArray(data.routes)) {
                    this.routes = data.routes;
                    this.updateRouteOptions();
                    // Only clear route_id if routes were loaded successfully
                    if (this.routes.length > 0) {
                        this.formData.route_id = '';
                    }
                    this.students = [];
                    this.checkedStudents = [];
                } else {
                    this.routes = [];
                    this.updateRouteOptions();
                }
            } catch (error) {
                alert('Error loading routes: ' + error.message);
                this.routes = [];
                this.updateRouteOptions();
            } finally {
                this.loading = false;
            }
        },

        updateRouteOptions() {
            // Use setTimeout to ensure DOM is ready
            setTimeout(() => {
                let select = this.$refs.routeSelect;
                
                // Fallback to getElementById if ref doesn't work
                if (!select) {
                    select = document.getElementById('route_id');
                }
                
                if (!select) {
                    return;
                }

                // Check if jQuery and Select2 are available
                if (typeof $ === 'undefined') {
                    // Fallback to native DOM manipulation
                    while (select.options.length > 1) {
                        select.remove(1);
                    }
                    if (Array.isArray(this.routes) && this.routes.length > 0) {
                        this.routes.forEach(route => {
                            const option = document.createElement('option');
                            option.value = route.id;
                            option.textContent = route.route_name;
                            select.appendChild(option);
                        });
                    }
                    return;
                }

                const $select = $(select);
                const isSelect2 = $select.hasClass('select2-hidden-accessible');
                
                // Store current value
                const currentValue = this.formData.route_id;

                // Destroy Select2 if it exists
                if (isSelect2) {
                    try {
                        $select.select2('destroy');
                    } catch (e) {
                        // Silently handle error
                    }
                }

                // Clear existing options except the first one (placeholder)
                while (select.options.length > 1) {
                    select.remove(1);
                }

                // Add new options
                if (Array.isArray(this.routes) && this.routes.length > 0) {
                    this.routes.forEach((route) => {
                        const option = document.createElement('option');
                        option.value = route.id;
                        option.textContent = route.route_name;
                        select.appendChild(option);
                    });
                }

                // Always reinitialize Select2 (even if it wasn't initialized before)
                try {
                    $select.select2({
                        placeholder: 'Select Route',
                        allowClear: false,
                        width: '100%'
                    });
                    
                    // Re-attach change handler
                    $select.off('select2:select select2:change').on('select2:select select2:change', (e) => {
                        const value = e.target.value || $select.val();
                        this.formData.route_id = value;
                        this.$nextTick(() => {
                            this.loadStudents();
                        });
                    });
                    
                    // Restore value if it still exists in the new options
                    if (currentValue) {
                        const optionExists = $select.find(`option[value="${currentValue}"]`).length > 0;
                        if (optionExists) {
                            $select.val(currentValue).trigger('change');
                            this.formData.route_id = currentValue;
                        } else {
                            $select.val('').trigger('change');
                            this.formData.route_id = '';
                        }
                    } else {
                        $select.val('').trigger('change');
                    }
                    
                    // Sync current Select2 value with Alpine.js
                    const currentSelectValue = $select.val();
                    if (currentSelectValue && currentSelectValue !== this.formData.route_id) {
                        this.formData.route_id = currentSelectValue;
                    }
                } catch (e) {
                    // Silently handle error
                }
            }, 150);
        },

        async loadStudents() {
            if (!this.formData.vehicle_id || !this.formData.route_id) {
                this.students = [];
                this.checkedStudents = [];
                return;
            }

            this.loading = true;
            try {
                const response = await fetch('{{ route('receptionist.transport-attendance.get-students') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({
                        vehicle_id: this.formData.vehicle_id,
                        route_id: this.formData.route_id,
                    }),
                });

                const data = await response.json();
                
                if (data.success) {
                    this.students = data.students;
                    // By default, check all students
                    this.checkedStudents = this.students.map(s => s.id.toString());
                } else {
                    alert(data.message || 'Failed to load students');
                    this.students = [];
                    this.checkedStudents = [];
                }
            } catch (error) {
                alert('Error loading students. Please try again.');
                this.students = [];
                this.checkedStudents = [];
            } finally {
                this.loading = false;
            }
        },

        checkAll() {
            this.checkedStudents = this.students.map(s => s.id.toString());
        },

        uncheckAll() {
            this.checkedStudents = [];
        },

        toggleAll(checked) {
            if (checked) {
                this.checkAll();
            } else {
                this.uncheckAll();
            }
        },

        get allChecked() {
            return this.students.length > 0 && this.checkedStudents.length === this.students.length;
        },
    };
}
</script>
@endpush
@endsection

