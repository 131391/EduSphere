@extends('layouts.receptionist')

@section('title', 'Hostel Floor Management - Receptionist')
@section('page-title', 'Hostel Floor Management')
@section('page-description', 'Manage hostel floors')

@section('content')
<div class="space-y-6" x-data="hostelFloorManagement" x-init="init()">
    {{-- Success/Error Messages --}}
    @if(session('success'))
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg relative">
        <span class="block sm:inline">{{ session('success') }}</span>
        <button @click="show = false" class="absolute top-0 right-0 px-4 py-3">
            <i class="fas fa-times"></i>
        </button>
    </div>
    @endif

    @if(session('error'))
    <div x-data="{ show: true }" x-show="show" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg relative">
        <span class="block sm:inline">{{ session('error') }}</span>
        <button @click="show = false" class="absolute top-0 right-0 px-4 py-3">
            <i class="fas fa-times"></i>
        </button>
    </div>
    @endif

    {{-- Page Header with Actions --}}
    <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div class="flex items-center space-x-4">
                <a href="{{ route('receptionist.hostels.index') }}" 
                   class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-md transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Back
                </a>
                <h2 class="text-xl font-bold text-gray-800">Hostel Floor List</h2>
            </div>
            <div class="flex flex-wrap gap-2">
                <button @click="openAddModal()" 
                        class="inline-flex items-center px-4 py-2 bg-teal-500 hover:bg-teal-600 text-white text-sm font-medium rounded-md transition-colors">
                    <i class="fas fa-plus mr-2"></i>
                    Add Floor
                </button>
                <a href="{{ route('receptionist.hostel-floors.export') }}" 
                   class="inline-flex items-center px-4 py-2 bg-green-500 hover:bg-green-600 text-white text-sm font-medium rounded-md transition-colors">
                    <i class="fas fa-file-excel mr-2"></i>
                    Export to Excel
                </a>
            </div>
        </div>
    </div>

    {{-- Hostel Floors Table --}}
    @php
        $tableColumns = [
            [
                'key' => 'sr_no',
                'label' => 'SR NO',
                'sortable' => false,
                'render' => function($row, $index, $data) {
                    return ($data->currentPage() - 1) * $data->perPage() + $index + 1;
                }
            ],
            [
                'key' => 'hostel_name',
                'label' => 'HOSTEL NAME',
                'sortable' => true,
                'render' => function($row) {
                    return $row->hostel->hostel_name ?? 'N/A';
                }
            ],
            [
                'key' => 'floor_name',
                'label' => 'HOSTEL FLOOR',
                'sortable' => true,
            ],
            [
                'key' => 'total_room',
                'label' => 'TOTAL ROOM',
                'sortable' => true,
            ],
            [
                'key' => 'floor_create_date',
                'label' => 'FLOOR CREATE DATE',
                'sortable' => true,
                'render' => function($row) {
                    return $row->floor_create_date ? $row->floor_create_date->format('d/m/Y') : 'N/A';
                }
            ],
        ];

        $tableActions = [
            [
                'type' => 'button',
                'onclick' => function($row) {
                    return "openEditModal(JSON.parse(atob(this.getAttribute('data-floor'))))";
                },
                'data-floor' => function($row) {
                    $floorData = [
                        'id' => $row->id,
                        'hostel_id' => $row->hostel_id,
                        'floor_name' => $row->floor_name,
                        'total_room' => $row->total_room,
                        'floor_create_date' => $row->floor_create_date ? $row->floor_create_date->format('Y-m-d') : '',
                    ];
                    return base64_encode(json_encode($floorData));
                },
                'icon' => 'fas fa-edit',
                'class' => 'text-blue-600 hover:text-blue-900',
                'title' => 'Edit',
            ],
            [
                'type' => 'form',
                'url' => function($row) {
                    return route('receptionist.hostel-floors.destroy', $row->id);
                },
                'method' => 'DELETE',
                'confirm' => 'Are you sure you want to delete this floor?',
                'icon' => 'fas fa-trash',
                'class' => 'text-red-600 hover:text-red-900',
                'title' => 'Delete',
            ],
        ];
    @endphp

    <x-data-table 
        :columns="$tableColumns"
        :data="$floors"
        :searchable="true"
        :actions="$tableActions"
        empty-message="No floors found"
        empty-icon="fas fa-layer-group"
    >
        Hostel Floor List
    </x-data-table>

    {{-- Add/Edit Hostel Floor Modal --}}
    <x-modal name="hostel-floor-modal" alpineTitle="editMode ? 'Edit Hostel Floor' : 'Add New Hostel Floor'" maxWidth="3xl">
        <form :action="editMode ? `/receptionist/hostel-floors/${floorId}` : '{{ route('receptionist.hostel-floors.store') }}'" 
              method="POST" class="p-6">
            @csrf
            <template x-if="editMode">
                @method('PUT')
            </template>

            <div class="bg-teal-50 dark:bg-teal-900 p-4 rounded-lg mb-6">
                <h4 class="font-bold text-gray-800 dark:text-white mb-4">Hostel Floor Information</h4>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                            Hostel Name <span class="text-red-500">*</span>
                        </label>
                        <select name="hostel_id" 
                                x-model="formData.hostel_id"
                                class="w-full px-4 py-2 border {{ $errors->has('hostel_id') ? 'border-red-500' : 'border-gray-300 dark:border-gray-600' }} rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                            <option value="">Select Hostel</option>
                            @foreach($hostels as $hostel)
                                <option value="{{ $hostel->id }}" {{ old('hostel_id') == $hostel->id ? 'selected' : '' }}>
                                    {{ $hostel->hostel_name }}
                                </option>
                            @endforeach
                        </select>
                        @error('hostel_id')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                            Floor Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               name="floor_name" 
                               x-model="formData.floor_name"
                               value="{{ old('floor_name') }}"
                               placeholder="Enter Hostel Floor Name"
                               class="w-full px-4 py-2 border {{ $errors->has('floor_name') ? 'border-red-500' : 'border-gray-300 dark:border-gray-600' }} rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                        @error('floor_name')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 mt-4">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                            Total Room
                        </label>
                        <input type="number" 
                               name="total_room" 
                               x-model="formData.total_room"
                               value="{{ old('total_room') }}"
                               placeholder="Enter rooms"
                               min="0"
                               class="w-full px-4 py-2 border {{ $errors->has('total_room') ? 'border-red-500' : 'border-gray-300 dark:border-gray-600' }} rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                        @error('total_room')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                            Floor Create Date
                        </label>
                        <div class="relative">
                            <input type="date" 
                                   name="floor_create_date" 
                                   x-model="formData.floor_create_date"
                                   value="{{ old('floor_create_date') }}"
                                   class="w-full px-4 py-2 border {{ $errors->has('floor_create_date') ? 'border-red-500' : 'border-gray-300 dark:border-gray-600' }} rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                <i class="fas fa-calendar-alt text-gray-400"></i>
                            </div>
                        </div>
                        @error('floor_create_date')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Modal Footer --}}
            <div class="flex items-center justify-center gap-4">
                <button type="button" @click="closeModal()"
                        class="px-8 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 transition-colors font-semibold">
                    Close
                </button>
                <button type="submit"
                        class="px-8 py-2 bg-teal-500 text-white rounded-lg hover:bg-teal-600 transition-colors font-semibold shadow-md">
                    Submit
                </button>
            </div>
        </form>
    </x-modal>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('hostelFloorManagement', () => ({
        showModal: false,
        editMode: false,
        floorId: null,
        formData: {
            hostel_id: '',
            floor_name: '',
            total_room: '',
            floor_create_date: '',
        },
        
        init() {
            // Check if there are validation errors and reopen modal with old data
            @if($errors->any())
                this.editMode = {{ old('_method') === 'PUT' ? 'true' : 'false' }};
                this.floorId = '{{ old('floor_id') }}';
                this.formData = {
                    hostel_id: '{{ old('hostel_id') }}',
                    floor_name: '{{ old('floor_name') }}',
                    total_room: '{{ old('total_room') }}',
                    floor_create_date: '{{ old('floor_create_date') }}',
                };
                this.$nextTick(() => {
                    this.$dispatch('open-modal', 'hostel-floor-modal');
                });
            @endif
        },
        
        openAddModal() {
            this.editMode = false;
            this.floorId = null;
            this.resetForm();
            this.$dispatch('open-modal', 'hostel-floor-modal');
        },
        
        openEditModal(floor) {
            this.editMode = true;
            this.floorId = floor.id;
            this.formData = {
                hostel_id: floor.hostel_id || '',
                floor_name: floor.floor_name || '',
                total_room: floor.total_room || '',
                floor_create_date: floor.floor_create_date || '',
            };
            this.$dispatch('open-modal', 'hostel-floor-modal');
        },
        
        resetForm() {
            this.formData = {
                hostel_id: '',
                floor_name: '',
                total_room: '',
                floor_create_date: '',
            };
        },
        
        closeModal() {
            this.$dispatch('close-modal', 'hostel-floor-modal');
            this.resetForm();
            this.editMode = false;
            this.floorId = null;
        }
    }));
});

// Global function to open edit modal (called from table action buttons)
function openEditModal(floor) {
    const component = Alpine.$data(document.querySelector('[x-data*="hostelFloorManagement"]'));
    if (component) {
        component.openEditModal(floor);
    }
}

// Global script to hide validation errors when user starts typing or selecting
document.addEventListener('DOMContentLoaded', function() {
    // Function to clear field error
    const clearFieldError = function(field) {
        // Remove red border
        field.classList.remove('border-red-500');
        
        // Find and remove error message (could be next sibling or in parent)
        let errorElement = field.nextElementSibling;
        if (errorElement && errorElement.classList.contains('text-red-500')) {
            errorElement.remove();
        }
        
        // Also check in parent div
        const parentDiv = field.closest('div');
        if (parentDiv) {
            const errorInParent = parentDiv.querySelector('p.text-red-500');
            if (errorInParent) {
                errorInParent.remove();
            }
        }
    };
    
    // Add event listeners to all inputs and selects in the modal
    const modal = document.querySelector('[x-data*="hostelFloorManagement"]');
    if (modal) {
        // Handle regular inputs
        modal.addEventListener('input', function(e) {
            if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') {
                clearFieldError(e.target);
            }
        });
        
        // Handle native selects - use change event
        modal.addEventListener('change', function(e) {
            if (e.target.tagName === 'SELECT') {
                clearFieldError(e.target);
            }
        });
        
        // Also listen for input events on selects (some browsers fire input on select change)
        modal.addEventListener('input', function(e) {
            if (e.target.tagName === 'SELECT') {
                clearFieldError(e.target);
            }
        });
        
        // Use jQuery for better event handling if available
        if (typeof $ !== 'undefined') {
            $(modal).on('change', 'select', function() {
                clearFieldError(this);
            });
        }
    }
    
    // Also handle globally for any select changes
    $(document).on('change', 'select[name="hostel_id"]', function() {
        clearFieldError(this);
    });
});
</script>
@endpush
@endsection
