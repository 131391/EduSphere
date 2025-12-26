@extends('layouts.receptionist')

@section('title', 'Hostel Management - Receptionist')
@section('page-title', 'Hostel Management')
@section('page-description', 'Manage school hostels')

@section('content')
<div class="space-y-6" x-data="hostelManagement" x-init="init()">
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

    {{-- Hostel Statistics --}}
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow-sm border-l-4 border-blue-500 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500 uppercase font-semibold mb-1">Total Hostel</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $stats['total_hostel'] }}</p>
                </div>
                <div class="bg-blue-100 p-3 rounded-full">
                    <i class="fas fa-bed text-blue-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border-l-4 border-green-500 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500 uppercase font-semibold mb-1">Total Floor</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $stats['total_floor'] }}</p>
                </div>
                <div class="bg-green-100 p-3 rounded-full">
                    <i class="fas fa-layer-group text-green-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border-l-4 border-yellow-500 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500 uppercase font-semibold mb-1">Total Room</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $stats['total_room'] }}</p>
                </div>
                <div class="bg-yellow-100 p-3 rounded-full">
                    <i class="fas fa-door-open text-yellow-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border-l-4 border-purple-500 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500 uppercase font-semibold mb-1">Total Bed</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $stats['total_bed'] }}</p>
                </div>
                <div class="bg-purple-100 p-3 rounded-full">
                    <i class="fas fa-bed text-purple-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border-l-4 border-teal-500 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500 uppercase font-semibold mb-1">Hosteler Students</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $stats['hosteler_students'] }}</p>
                </div>
                <div class="bg-teal-100 p-3 rounded-full">
                    <i class="fas fa-user-graduate text-teal-600"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Page Header with Actions --}}
    <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <h2 class="text-xl font-bold text-gray-800">Hostel List</h2>
            <div class="flex flex-wrap gap-2">
                <button @click="openAddModal()" 
                        class="inline-flex items-center px-4 py-2 bg-teal-500 hover:bg-teal-600 text-white text-sm font-medium rounded-md transition-colors">
                    <i class="fas fa-plus mr-2"></i>
                    Add Hostel
                </button>
                <a href="{{ route('receptionist.hostels.export') }}" 
                   class="inline-flex items-center px-4 py-2 bg-green-500 hover:bg-green-600 text-white text-sm font-medium rounded-md transition-colors">
                    <i class="fas fa-file-excel mr-2"></i>
                    Export to Excel
                </a>
            </div>
        </div>
    </div>

    {{-- Hostels Table --}}
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
            ],
            [
                'key' => 'hostel_incharge',
                'label' => 'HOSTEL INCHARGE',
                'sortable' => true,
            ],
            [
                'key' => 'capability',
                'label' => 'CAPABILITY',
                'sortable' => true,
            ],
            [
                'key' => 'hostel_create_date',
                'label' => 'HOSTEL CREATE DATE',
                'sortable' => true,
                'render' => function($row) {
                    return $row->hostel_create_date ? $row->hostel_create_date->format('d/m/Y') : 'N/A';
                }
            ],
        ];

        $tableActions = [
            [
                'type' => 'button',
                'onclick' => function($row) {
                    return "openEditModal(JSON.parse(atob(this.getAttribute('data-hostel'))))";
                },
                'data-hostel' => function($row) {
                    $hostelData = [
                        'id' => $row->id,
                        'hostel_name' => $row->hostel_name,
                        'hostel_incharge' => $row->hostel_incharge,
                        'capability' => $row->capability,
                        'hostel_create_date' => $row->hostel_create_date ? $row->hostel_create_date->format('Y-m-d') : '',
                    ];
                    return base64_encode(json_encode($hostelData));
                },
                'icon' => 'fas fa-edit',
                'class' => 'text-blue-600 hover:text-blue-900',
                'title' => 'Edit',
            ],
            [
                'type' => 'form',
                'url' => function($row) {
                    return route('receptionist.hostels.destroy', $row->id);
                },
                'method' => 'DELETE',
                'confirm' => 'Are you sure you want to delete this hostel?',
                'icon' => 'fas fa-trash',
                'class' => 'text-red-600 hover:text-red-900',
                'title' => 'Delete',
            ],
        ];
    @endphp

    <x-data-table 
        :columns="$tableColumns"
        :data="$hostels"
        :searchable="true"
        :actions="$tableActions"
        empty-message="No hostels found"
        empty-icon="fas fa-bed"
    >
        Hostel List
    </x-data-table>

    {{-- Add/Edit Hostel Modal --}}
    <x-modal name="hostel-modal" alpineTitle="editMode ? 'Edit Hostel' : 'Add New Hostel'" maxWidth="3xl">
        <form :action="editMode ? `/receptionist/hostels/${hostelId}` : '{{ route('receptionist.hostels.store') }}'" 
              method="POST" class="p-6">
            @csrf
            <template x-if="editMode">
                @method('PUT')
            </template>

            <div class="bg-teal-50 dark:bg-teal-900 p-4 rounded-lg mb-6">
                <h4 class="font-bold text-gray-800 dark:text-white mb-4">Hostel Information</h4>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                            Hostel Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               name="hostel_name" 
                               x-model="formData.hostel_name"
                               value="{{ old('hostel_name') }}"
                               placeholder="Enter Hostel Name"
                               class="w-full px-4 py-2 border {{ $errors->has('hostel_name') ? 'border-red-500' : 'border-gray-300 dark:border-gray-600' }} rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                        @error('hostel_name')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                            Hostel Incharge
                        </label>
                        <input type="text" 
                               name="hostel_incharge" 
                               x-model="formData.hostel_incharge"
                               value="{{ old('hostel_incharge') }}"
                               placeholder="Enter Hostel Incharge"
                               class="w-full px-4 py-2 border {{ $errors->has('hostel_incharge') ? 'border-red-500' : 'border-gray-300 dark:border-gray-600' }} rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                        @error('hostel_incharge')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 mt-4">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                            Capability
                        </label>
                        <input type="number" 
                               name="capability" 
                               x-model="formData.capability"
                               value="{{ old('capability') }}"
                               placeholder="Enter Hostel Capability"
                               min="1"
                               class="w-full px-4 py-2 border {{ $errors->has('capability') ? 'border-red-500' : 'border-gray-300 dark:border-gray-600' }} rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                        @error('capability')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                            Hostel Create Date
                        </label>
                        <div class="relative">
                            <input type="date" 
                                   name="hostel_create_date" 
                                   x-model="formData.hostel_create_date"
                                   value="{{ old('hostel_create_date') }}"
                                   class="w-full px-4 py-2 border {{ $errors->has('hostel_create_date') ? 'border-red-500' : 'border-gray-300 dark:border-gray-600' }} rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                <i class="fas fa-calendar-alt text-gray-400"></i>
                            </div>
                        </div>
                        @error('hostel_create_date')
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
    Alpine.data('hostelManagement', () => ({
        showModal: false,
        editMode: false,
        hostelId: null,
        formData: {
            hostel_name: '',
            hostel_incharge: '',
            capability: '',
            hostel_create_date: '',
        },
        
        init() {
            // Check if there are validation errors and reopen modal with old data
            @if($errors->any())
                this.editMode = {{ old('_method') === 'PUT' ? 'true' : 'false' }};
                this.hostelId = '{{ old('hostel_id') }}';
                this.formData = {
                    hostel_name: '{{ old('hostel_name') }}',
                    hostel_incharge: '{{ old('hostel_incharge') }}',
                    capability: '{{ old('capability') }}',
                    hostel_create_date: '{{ old('hostel_create_date') }}',
                };
                this.$nextTick(() => {
                    this.$dispatch('open-modal', 'hostel-modal');
                });
            @endif
        },
        
        openAddModal() {
            this.editMode = false;
            this.hostelId = null;
            this.resetForm();
            this.$dispatch('open-modal', 'hostel-modal');
        },
        
        openEditModal(hostel) {
            this.editMode = true;
            this.hostelId = hostel.id;
            this.formData = {
                hostel_name: hostel.hostel_name || '',
                hostel_incharge: hostel.hostel_incharge || '',
                capability: hostel.capability || '',
                hostel_create_date: hostel.hostel_create_date || '',
            };
            this.$dispatch('open-modal', 'hostel-modal');
        },
        
        resetForm() {
            this.formData = {
                hostel_name: '',
                hostel_incharge: '',
                capability: '',
                hostel_create_date: '',
            };
        },
        
        closeModal() {
            this.$dispatch('close-modal', 'hostel-modal');
            this.resetForm();
            this.editMode = false;
            this.hostelId = null;
        }
    }));
});

// Global function to open edit modal (called from table action buttons)
function openEditModal(hostel) {
    const component = Alpine.$data(document.querySelector('[x-data*="hostelManagement"]'));
    if (component) {
        component.openEditModal(hostel);
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
    const modal = document.querySelector('[x-data*="hostelManagement"]');
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
});
</script>
@endpush
@endsection

