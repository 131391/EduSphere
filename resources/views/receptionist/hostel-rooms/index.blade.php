@extends('layouts.receptionist')

@section('title', 'Hostel Room Management - Receptionist')
@section('page-title', 'Hostel Room Management')
@section('page-description', 'Manage hostel rooms')

@section('content')
<div class="space-y-6" x-data="hostelRoomManagement" x-init="init()">
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
                <h2 class="text-xl font-bold text-gray-800">Hostel Room List</h2>
            </div>
            <div class="flex flex-wrap gap-2">
                <button @click="openAddModal()" 
                        class="inline-flex items-center px-4 py-2 bg-teal-500 hover:bg-teal-600 text-white text-sm font-medium rounded-md transition-colors">
                    <i class="fas fa-plus mr-2"></i>
                    Add Room
                </button>
                <a href="{{ route('receptionist.hostel-rooms.export') }}" 
                   class="inline-flex items-center px-4 py-2 bg-green-500 hover:bg-green-600 text-white text-sm font-medium rounded-md transition-colors">
                    <i class="fas fa-file-excel mr-2"></i>
                    Export to Excel
                </a>
            </div>
        </div>
    </div>

    {{-- Hostel Rooms Table --}}
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
                'render' => function($row) {
                    return $row->floor->floor_name ?? 'N/A';
                }
            ],
            [
                'key' => 'room_name',
                'label' => 'ROOM',
                'sortable' => true,
            ],
            [
                'key' => 'ac',
                'label' => 'AC',
                'sortable' => true,
                'render' => function($row) {
                    return $row->ac->label();
                }
            ],
            [
                'key' => 'cooler',
                'label' => 'COOLER',
                'sortable' => true,
                'render' => function($row) {
                    return $row->cooler->label();
                }
            ],
            [
                'key' => 'fan',
                'label' => 'FAN',
                'sortable' => true,
                'render' => function($row) {
                    return $row->fan->label();
                }
            ],
            [
                'key' => 'room_create_date',
                'label' => 'ROOM CREATE DATE',
                'sortable' => true,
                'render' => function($row) {
                    return $row->room_create_date ? $row->room_create_date->format('d/m/Y') : 'N/A';
                }
            ],
        ];

        $tableActions = [
            [
                'type' => 'button',
                'onclick' => function($row) {
                    return "openEditModal(JSON.parse(atob(this.getAttribute('data-room'))))";
                },
                'data-room' => function($row) {
                    $roomData = [
                        'id' => $row->id,
                        'hostel_id' => $row->hostel_id,
                        'hostel_floor_id' => $row->hostel_floor_id,
                        'room_name' => $row->room_name,
                        'ac' => $row->ac->value,
                        'cooler' => $row->cooler->value,
                        'fan' => $row->fan->value,
                        'room_create_date' => $row->room_create_date ? $row->room_create_date->format('Y-m-d') : '',
                    ];
                    return base64_encode(json_encode($roomData));
                },
                'icon' => 'fas fa-edit',
                'class' => 'text-blue-600 hover:text-blue-900',
                'title' => 'Edit',
            ],
            [
                'type' => 'form',
                'url' => function($row) {
                    return route('receptionist.hostel-rooms.destroy', $row->id);
                },
                'method' => 'DELETE',
                'confirm' => 'Are you sure you want to delete this room?',
                'icon' => 'fas fa-trash',
                'class' => 'text-red-600 hover:text-red-900',
                'title' => 'Delete',
            ],
        ];
    @endphp

    <x-data-table 
        :columns="$tableColumns"
        :data="$rooms"
        :searchable="true"
        :actions="$tableActions"
        empty-message="No rooms found"
        empty-icon="fas fa-door-open"
    >
        Hostel Room List
    </x-data-table>

    {{-- Add/Edit Hostel Room Modal --}}
    <x-modal name="hostel-room-modal" alpineTitle="editMode ? 'Edit Hostel Room' : 'Add New Hostel Room'" maxWidth="3xl">
        <form :action="editMode ? `/receptionist/hostel-rooms/${roomId}` : '{{ route('receptionist.hostel-rooms.store') }}'" 
              method="POST" class="p-6">
            @csrf
            <template x-if="editMode">
                @method('PUT')
            </template>

            <div class="bg-teal-50 dark:bg-teal-900 p-4 rounded-lg mb-6">
                <h4 class="font-bold text-gray-800 dark:text-white mb-4">Room Information</h4>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                            Hostel Name <span class="text-red-500">*</span>
                        </label>
                        <select name="hostel_id" 
                                id="hostel_id"
                                x-model="formData.hostel_id"
                                @change="loadFloors()"
                                class="w-full px-4 py-2 border {{ $errors->has('hostel_id') ? 'border-red-500' : 'border-gray-300 dark:border-gray-600' }} rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                            <option value="">Select Hostel</option>
                            @foreach($hostels as $hostel)
                                <option value="{{ $hostel->id }}">
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
                            Select Floor <span class="text-red-500">*</span>
                        </label>
                        <select name="hostel_floor_id" 
                                id="hostel_floor_id"
                                x-model="formData.hostel_floor_id"
                                x-ref="floorSelect"
                                class="w-full px-4 py-2 border {{ $errors->has('hostel_floor_id') ? 'border-red-500' : 'border-gray-300 dark:border-gray-600' }} rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                            <option value="">Select Floor</option>
                        </select>
                        @error('hostel_floor_id')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 mt-4">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                            Room Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               name="room_name" 
                               x-model="formData.room_name"
                               value="{{ old('room_name') }}"
                               placeholder="Enter room name"
                               class="w-full px-4 py-2 border {{ $errors->has('room_name') ? 'border-red-500' : 'border-gray-300 dark:border-gray-600' }} rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                        @error('room_name')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                            Ac
                        </label>
                        <select name="ac" 
                                x-model="formData.ac"
                                class="w-full px-4 py-2 border {{ $errors->has('ac') ? 'border-red-500' : 'border-gray-300 dark:border-gray-600' }} rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                            <option value="">Select Ac</option>
                            @foreach(\App\Enums\YesNo::options() as $value => $label)
                                <option value="{{ $value }}">
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @error('ac')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 mt-4">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                            Cooler
                        </label>
                        <select name="cooler" 
                                x-model="formData.cooler"
                                class="w-full px-4 py-2 border {{ $errors->has('cooler') ? 'border-red-500' : 'border-gray-300 dark:border-gray-600' }} rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                            <option value="">Select Cooler</option>
                            @foreach(\App\Enums\YesNo::options() as $value => $label)
                                <option value="{{ $value }}">
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @error('cooler')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                            Fan
                        </label>
                        <select name="fan" 
                                x-model="formData.fan"
                                class="w-full px-4 py-2 border {{ $errors->has('fan') ? 'border-red-500' : 'border-gray-300 dark:border-gray-600' }} rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                            <option value="">Select Fan</option>
                            @foreach(\App\Enums\YesNo::options() as $value => $label)
                                <option value="{{ $value }}">
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @error('fan')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 mt-4">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                            Room Create Date
                        </label>
                        <div class="relative">
                            <input type="date" 
                                   name="room_create_date" 
                                   x-model="formData.room_create_date"
                                   value="{{ old('room_create_date') }}"
                                   class="w-full px-4 py-2 border {{ $errors->has('room_create_date') ? 'border-red-500' : 'border-gray-300 dark:border-gray-600' }} rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                <i class="fas fa-calendar-alt text-gray-400"></i>
                            </div>
                        </div>
                        @error('room_create_date')
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
    Alpine.data('hostelRoomManagement', () => ({
        showModal: false,
        editMode: false,
        roomId: null,
        formData: {
            hostel_id: '',
            hostel_floor_id: '',
            room_name: '',
            ac: '',
            cooler: '',
            fan: '',
            room_create_date: '',
        },
        floors: [],
        
        async init() {
            // Check if there are validation errors and reopen modal with old data
            @if($errors->any())
                this.editMode = {{ old('_method') === 'PUT' ? 'true' : 'false' }};
                this.roomId = '{{ old('room_id') }}';
                const oldHostelId = '{{ old('hostel_id') }}';
                const oldFloorId = '{{ old('hostel_floor_id') }}';
                
                this.formData = {
                    hostel_id: oldHostelId,
                    hostel_floor_id: '', // Clear floor initially, will be set after floors load
                    room_name: '{{ old('room_name') }}',
                    ac: '{{ old('ac') }}',
                    cooler: '{{ old('cooler') }}',
                    fan: '{{ old('fan') }}',
                    room_create_date: '{{ old('room_create_date') }}',
                };
                
                // Load floors if hostel is selected, then restore floor if it's valid
                if (oldHostelId) {
                    await this.loadFloors(true, oldFloorId);
                }
                
                this.$nextTick(() => {
                    this.$dispatch('open-modal', 'hostel-room-modal');
                });
            @endif
        },
        
        openAddModal() {
            this.editMode = false;
            this.roomId = null;
            this.resetForm();
            this.$dispatch('open-modal', 'hostel-room-modal');
        },
        
        async openEditModal(room) {
            this.editMode = true;
            this.roomId = room.id;
            const roomHostelId = String(room.hostel_id || '');
            const roomFloorId = String(room.hostel_floor_id || '');
            
            // Convert enum values to strings to match option values
            this.formData = {
                hostel_id: roomHostelId,
                hostel_floor_id: roomFloorId,
                room_name: room.room_name || '',
                ac: room.ac !== undefined && room.ac !== null ? String(room.ac) : '',
                cooler: room.cooler !== undefined && room.cooler !== null ? String(room.cooler) : '',
                fan: room.fan !== undefined && room.fan !== null ? String(room.fan) : '',
                room_create_date: room.room_create_date || '',
            };
            
            // Load floors for the selected hostel, preserving the floor value
            if (roomHostelId) {
                await this.loadFloors(true, roomFloorId);
            }
            
            this.$dispatch('open-modal', 'hostel-room-modal');
            
            // Ensure select values are set after modal opens
            this.$nextTick(() => {
                setTimeout(() => {
                    // Set hostel select
                    const hostelSelect = document.getElementById('hostel_id');
                    if (hostelSelect && this.formData.hostel_id) {
                        hostelSelect.value = this.formData.hostel_id;
                        // Trigger change to ensure Alpine.js updates
                        hostelSelect.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                    
                    // Set floor select
                    const floorSelect = document.getElementById('hostel_floor_id');
                    if (floorSelect && this.formData.hostel_floor_id) {
                        floorSelect.value = this.formData.hostel_floor_id;
                        floorSelect.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                    
                    // Set AC, Cooler, Fan selects
                    const acSelect = document.querySelector('select[name="ac"]');
                    const coolerSelect = document.querySelector('select[name="cooler"]');
                    const fanSelect = document.querySelector('select[name="fan"]');
                    
                    if (acSelect && this.formData.ac) {
                        acSelect.value = this.formData.ac;
                        acSelect.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                    if (coolerSelect && this.formData.cooler) {
                        coolerSelect.value = this.formData.cooler;
                        coolerSelect.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                    if (fanSelect && this.formData.fan) {
                        fanSelect.value = this.formData.fan;
                        fanSelect.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                }, 300);
            });
        },
        
        async loadFloors(preserveValue = false, valueToPreserve = null) {
            const hostelId = this.formData.hostel_id;
            
            if (!hostelId) {
                this.floors = [];
                if (!preserveValue) {
                    this.formData.hostel_floor_id = '';
                }
                this.updateFloorOptions(preserveValue, valueToPreserve);
                return;
            }

            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
                const response = await fetch('{{ route('receptionist.hostel-rooms.get-floors') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        hostel_id: hostelId,
                    }),
                });

                if (!response.ok) {
                    throw new Error('Failed to load floors');
                }

                const data = await response.json();
                
                if (data.success && Array.isArray(data.floors)) {
                    this.floors = data.floors;
                    this.updateFloorOptions(preserveValue, valueToPreserve || this.formData.hostel_floor_id);
                    // Only clear floor_id if not preserving value and not in edit mode
                    if (!preserveValue && !this.editMode) {
                        this.formData.hostel_floor_id = '';
                    }
                } else {
                    this.floors = [];
                    this.updateFloorOptions(preserveValue, valueToPreserve);
                }
            } catch (error) {
                alert('Error loading floors: ' + error.message);
                this.floors = [];
                this.updateFloorOptions(preserveValue, valueToPreserve);
            }
        },

        updateFloorOptions(preserveValue = false, valueToPreserve = null) {
            this.$nextTick(() => {
                const select = this.$refs.floorSelect || document.getElementById('hostel_floor_id');
                if (!select) return;

                // Clear existing options except the first one (placeholder)
                while (select.options.length > 1) {
                    select.remove(1);
                }

                // Add new options
                if (Array.isArray(this.floors) && this.floors.length > 0) {
                    this.floors.forEach((floor) => {
                        const option = document.createElement('option');
                        option.value = floor.id;
                        option.textContent = floor.floor_name;
                        select.appendChild(option);
                    });
                }

                // Restore selected value if preserving or in edit mode
                // Only restore if the value exists in the loaded floors (belongs to selected hostel)
                if (preserveValue && valueToPreserve) {
                    // Check if the value exists in the new options (i.e., belongs to the selected hostel)
                    const valueExists = Array.from(select.options).some(opt => opt.value == valueToPreserve);
                    if (valueExists) {
                        select.value = valueToPreserve;
                        this.formData.hostel_floor_id = valueToPreserve;
                    } else {
                        // Floor doesn't belong to selected hostel, clear it
                        select.value = '';
                        this.formData.hostel_floor_id = '';
                    }
                } else if (this.editMode && this.formData.hostel_floor_id) {
                    const valueExists = Array.from(select.options).some(opt => opt.value == this.formData.hostel_floor_id);
                    if (valueExists) {
                        select.value = this.formData.hostel_floor_id;
                    } else {
                        // Floor doesn't belong to selected hostel, clear it
                        select.value = '';
                        this.formData.hostel_floor_id = '';
                    }
                } else if (!preserveValue) {
                    select.value = '';
                    this.formData.hostel_floor_id = '';
                }
            });
        },
        
        resetForm() {
            this.formData = {
                hostel_id: '',
                hostel_floor_id: '',
                room_name: '',
                ac: '',
                cooler: '',
                fan: '',
                room_create_date: '',
            };
            this.floors = [];
        },
        
        closeModal() {
            this.$dispatch('close-modal', 'hostel-room-modal');
            this.resetForm();
            this.editMode = false;
            this.roomId = null;
        }
    }));
});

// Global function to open edit modal (called from table action buttons)
function openEditModal(room) {
    const component = Alpine.$data(document.querySelector('[x-data*="hostelRoomManagement"]'));
    if (component) {
        component.openEditModal(room);
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
    const modal = document.querySelector('[x-data*="hostelRoomManagement"]');
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
    $(document).on('change', 'select[name="hostel_id"], select[name="hostel_floor_id"], select[name="ac"], select[name="cooler"], select[name="fan"]', function() {
        clearFieldError(this);
    });

    // Handle hostel select change to load floors
    $(document).on('change', 'select[name="hostel_id"]', function() {
        clearFieldError(this);
        const component = Alpine.$data(document.querySelector('[x-data*="hostelRoomManagement"]'));
        if (component) {
            component.formData.hostel_id = $(this).val();
            component.loadFloors(false);
        }
    });
});
</script>
@endpush
@endsection

