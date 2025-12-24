@extends('layouts.receptionist')

@section('title', 'Assign Transport Facility')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Assign Transport Facility</h1>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#assignmentModal" onclick="resetForm()">
            <i class="fas fa-plus"></i> Assign Transport
        </button>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Assigned Students</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalAssigned }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Active Routes</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $activeRoutes }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-route fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total Monthly Fees</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">₹{{ number_format($totalFees, 2) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-rupee-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Table Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Transport Assignments</h6>
        </div>
        <div class="card-body">
            <x-data-table
                :columns="[
                    ['label' => 'SR NO', 'field' => 'sr_no'],
                    ['label' => 'STUDENT NAME', 'field' => 'student_name'],
                    ['label' => 'ADMISSION NO', 'field' => 'admission_no'],
                    ['label' => 'CLASS', 'field' => 'class'],
                    ['label' => 'VEHICLE NO', 'field' => 'vehicle_no'],
                    ['label' => 'SELECT ROUTE NAME', 'field' => 'route_name'],
                    ['label' => 'SELECT BUS STOP', 'field' => 'bus_stop_no'],
                    ['label' => 'BUS STOP NAME', 'field' => 'bus_stop_name'],
                    ['label' => 'FEE PER MONTH', 'field' => 'fee_per_month'],
                    ['label' => 'ACTION', 'field' => 'action'],
                ]"
                :data="$assignments->map(function($assignment, $index) {
                    return [
                        'sr_no' => $index + 1,
                        'student_name' => $assignment->student->name ?? 'N/A',
                        'admission_no' => $assignment->student->admission_no ?? 'N/A',
                        'class' => $assignment->student->class->name ?? 'N/A',
                        'vehicle_no' => $assignment->vehicle->vehicle_number ?? 'N/A',
                        'route_name' => $assignment->route->name ?? 'N/A',
                        'bus_stop_no' => $assignment->busStop->stop_number ?? 'N/A',
                        'bus_stop_name' => $assignment->busStop->name ?? 'N/A',
                        'fee_per_month' => '₹' . number_format($assignment->fee_per_month, 2),
                        'action' => view('components.action-buttons', [
                            'editAction' => 'editAssignment(' . $assignment->id . ')',
                            'deleteAction' => [
                                'route' => route('receptionist.transport-assignments.destroy', $assignment->id),
                                'message' => 'Are you sure you want to delete this transport assignment?'
                            ]
                        ])->render(),
                    ];
                })"
            />
        </div>
    </div>
</div>

<!-- Assignment Modal -->
<div class="modal fade" id="assignmentModal" tabindex="-1" aria-labelledby="assignmentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="assignmentForm" method="POST">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">
                <input type="hidden" name="assignment_id" id="assignmentId">

                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="assignmentModalLabel">Assign Transport Facility</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="row">
                        <!-- Student Selection -->
                        <div class="col-md-6 mb-3">
                            <label for="student_id" class="form-label">Student <span class="text-red-500">*</span></label>
                            <select class="form-select" id="student_id" name="student_id" required>
                                <option value="">Select Student</option>
                                @foreach($students as $student)
                                    <option value="{{ $student->id }}" 
                                            data-admission="{{ $student->admission_no }}"
                                            data-class="{{ $student->class->name ?? 'N/A' }}">
                                        {{ $student->name }} ({{ $student->admission_no }})
                                    </option>
                                @endforeach
                            </select>
                            @error('student_id')
                                <div class="text-red-500 small">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Route Selection -->
                        <div class="col-md-6 mb-3">
                            <label for="route_id" class="form-label">Route <span class="text-red-500">*</span></label>
                            <select class="form-select" id="route_id" name="route_id" required>
                                <option value="">Select Route</option>
                                @foreach($routes as $route)
                                    <option value="{{ $route->id }}">{{ $route->name }}</option>
                                @endforeach
                            </select>
                            @error('route_id')
                                <div class="text-red-500 small">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Bus Stop Selection (Filtered by Route) -->
                        <div class="col-md-6 mb-3">
                            <label for="bus_stop_id" class="form-label">Bus Stop <span class="text-red-500">*</span></label>
                            <select class="form-select" id="bus_stop_id" name="bus_stop_id" required disabled>
                                <option value="">Select Route First</option>
                            </select>
                            @error('bus_stop_id')
                                <div class="text-red-500 small">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Vehicle Selection -->
                        <div class="col-md-6 mb-3">
                            <label for="vehicle_id" class="form-label">Vehicle (Optional)</label>
                            <select class="form-select" id="vehicle_id" name="vehicle_id">
                                <option value="">Select Vehicle</option>
                                @foreach($vehicles as $vehicle)
                                    <option value="{{ $vehicle->id }}">{{ $vehicle->vehicle_number }} - {{ $vehicle->vehicle_type }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Fee Per Month (Auto-filled, Read-only) -->
                        <div class="col-md-6 mb-3">
                            <label for="fee_per_month" class="form-label">Fee Per Month (₹) <span class="text-red-500">*</span></label>
                            <input type="number" class="form-control bg-light" id="fee_per_month" name="fee_per_month" step="0.01" readonly required>
                            @error('fee_per_month')
                                <div class="text-red-500 small">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Assignment</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Bus stops data for filtering
    const busStops = @json($busStops);
    let editingAssignmentId = null;

    // Route change handler - filter bus stops
    $('#route_id').on('change', function() {
        const routeId = $(this).val();
        const busStopSelect = $('#bus_stop_id');
        
        // Clear and disable bus stop dropdown
        busStopSelect.empty().append('<option value="">Select Bus Stop</option>');
        $('#fee_per_month').val('');
        
        if (routeId) {
            // Filter bus stops by route
            const filteredStops = busStops.filter(stop => stop.route_id == routeId);
            
            if (filteredStops.length > 0) {
                filteredStops.forEach(stop => {
                    busStopSelect.append(
                        `<option value="${stop.id}" data-fee="${stop.charge_per_month}">
                            ${stop.stop_number} - ${stop.name}
                        </option>`
                    );
                });
                busStopSelect.prop('disabled', false);
            } else {
                busStopSelect.append('<option value="">No bus stops available for this route</option>');
                busStopSelect.prop('disabled', true);
            }
        } else {
            busStopSelect.prop('disabled', true);
        }
        
        // Reinitialize Select2
        busStopSelect.select2({
            placeholder: 'Select Bus Stop',
            allowClear: true,
            width: '100%',
            dropdownParent: $('#assignmentModal')
        });
    });

    // Bus stop change handler - auto-fill fee
    $('#bus_stop_id').on('change', function() {
        const selectedOption = $(this).find('option:selected');
        const fee = selectedOption.data('fee');
        
        if (fee) {
            $('#fee_per_month').val(parseFloat(fee).toFixed(2));
        } else {
            $('#fee_per_month').val('');
        }
    });

    // Reset form
    function resetForm() {
        editingAssignmentId = null;
        $('#assignmentForm')[0].reset();
        $('#formMethod').val('POST');
        $('#assignmentForm').attr('action', '{{ route("receptionist.transport-assignments.store") }}');
        $('#assignmentModalLabel').text('Assign Transport Facility');
        $('#bus_stop_id').empty().append('<option value="">Select Route First</option>').prop('disabled', true);
        $('#fee_per_month').val('');
        
        // Reset Select2
        $('#student_id').val(null).trigger('change');
        $('#route_id').val(null).trigger('change');
        $('#vehicle_id').val(null).trigger('change');
    }

    // Edit assignment
    function editAssignment(id) {
        editingAssignmentId = id;
        
        // Fetch assignment data
        fetch(`/receptionist/transport-assignments/${id}/edit`)
            .then(response => response.json())
            .then(data => {
                $('#formMethod').val('PUT');
                $('#assignmentForm').attr('action', `/receptionist/transport-assignments/${id}`);
                $('#assignmentModalLabel').text('Edit Transport Assignment');
                
                // Set form values
                $('#student_id').val(data.student_id).trigger('change');
                $('#route_id').val(data.route_id).trigger('change');
                
                // Wait for bus stops to load, then set bus stop
                setTimeout(() => {
                    $('#bus_stop_id').val(data.bus_stop_id).trigger('change');
                }, 300);
                
                $('#vehicle_id').val(data.vehicle_id).trigger('change');
                $('#fee_per_month').val(data.fee_per_month);
                
                // Show modal
                $('#assignmentModal').modal('show');
            })
            .catch(error => {
                console.error('Error fetching assignment:', error);
                alert('Error loading assignment data');
            });
    }

    // Initialize Select2 on modal shown
    $('#assignmentModal').on('shown.bs.modal', function() {
        $('#student_id, #route_id, #vehicle_id').select2({
            placeholder: function() {
                return $(this).data('placeholder') || 'Select an option';
            },
            allowClear: true,
            width: '100%',
            dropdownParent: $('#assignmentModal')
        });
    });
</script>
@endpush
@endsection
