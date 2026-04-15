@php
    use App\Enums\YesNo;
@endphp
@extends('layouts.receptionist')

@section('title', 'Room Inventory - Receptionist')
@section('page-title', 'Room Inventory')
@section('page-description', 'Manage residential units and amenities across hostel blocks')

@section('content')
<div class="space-y-6" x-data="hostelRoomManagement" x-init="init()">
    {{-- Statistics Overview --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border-t-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Managed Units</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ $stats['total_room'] }}</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-door-open text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border-t-4 border-emerald-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Climate Controlled</p>
                    <p class="text-3xl font-bold text-emerald-600 dark:text-emerald-400 mt-2">{{ $stats['ac_rooms'] }}</p>
                </div>
                <div class="w-12 h-12 bg-emerald-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-snowflake text-emerald-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border-t-4 border-purple-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Occupancy Nodes</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ $stats['total_beds'] }}</p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-bed text-purple-600 text-xl"></i>
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
                        <i class="fas fa-door-open text-xs"></i>
                    </div>
                    Room Inventory
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Configure residential specifications and unit amenities.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <button @click="$dispatch('open-add-hostel-room')"
                    class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-teal-600 to-emerald-600 hover:from-teal-700 hover:to-emerald-700 text-white text-sm font-semibold rounded-xl transition-all shadow-md hover:shadow-lg active:scale-95">
                    <i class="fas fa-plus mr-2"></i>
                    Initialize Unit
                </button>
                <a href="{{ route('receptionist.hostel-rooms.export') }}"
                    class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-slate-700 to-slate-900 hover:from-black hover:to-slate-800 text-white text-sm font-semibold rounded-xl transition-all shadow-md hover:shadow-lg active:scale-95">
                    <i class="fas fa-file-excel mr-2 text-xs"></i>
                    Export Records
                </a>
            </div>
        </div>
    </div>

    {{-- Rooms Table --}}
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
                'key' => 'hostel',
                'label' => 'HOSTEL BLOCK',
                'sortable' => false,
                'render' => function($row) {
                    return $row->hostel->hostel_name ?? 'N/A';
                }
            ],
            [
                'key' => 'floor',
                'label' => 'FLOOR LEVEL',
                'sortable' => false,
                'render' => function($row) {
                    return $row->floor->floor_name ?? 'N/A';
                }
            ],
            [
                'key' => 'room_name',
                'label' => 'ROOM NAME',
                'sortable' => true,
                'render' => function($row) {
                    return '<span class="font-bold text-gray-800">' . $row->room_name . '</span>';
                }
            ],
            [
                'key' => 'amenities',
                'label' => 'CLIMATE',
                'sortable' => false,
                'render' => function($row) {
                    if ($row->ac->value === YesNo::Yes->value) {
                        return '<span class="px-2 py-0.5 bg-emerald-100 text-emerald-700 rounded text-[10px] font-bold uppercase tracking-tight">AC</span>';
                    }
                    return '<span class="px-2 py-0.5 bg-gray-100 text-gray-500 rounded text-[10px] font-bold uppercase tracking-tight">NON-AC</span>';
                }
            ],
            [
                'key' => 'capacity',
                'label' => 'OCCUPANCY',
                'sortable' => false,
                'render' => function($row) {
                    $count = $row->bedAssignments()->count();
                    return '<span class="font-bold ' . ($count > 0 ? 'text-indigo-600' : 'text-gray-400') . '">' . $count . ' Students</span>';
                }
            ],
            [
                'key' => 'room_create_date',
                'label' => 'INITIALIZED',
                'sortable' => true,
                'render' => function($row) {
                    return $row->room_create_date ? $row->room_create_date->format('d M, Y') : 'N/A';
                }
            ],
        ];

        $tableActions = [
            [
                'type' => 'button',
                'onclick' => function($row) {
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
                    return "window.dispatchEvent(new CustomEvent('open-edit-hostel-room', { detail: ".json_encode($roomData)." }))";
                },
                'icon' => 'fas fa-edit',
                'class' => 'text-indigo-600 hover:text-indigo-900',
                'title' => 'Edit',
            ],
            [
                'type' => 'button',
                'onclick' => function($row) {
                    $deleteData = [
                        'url' => route('receptionist.hostel-rooms.destroy', $row->id),
                        'name' => $row->room_name
                    ];
                    return "window.dispatchEvent(new CustomEvent('open-delete-hostel-room', { detail: ".json_encode($deleteData)." }))";
                },
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
        empty-message="No residential units configured"
        empty-icon="fas fa-door-open"
    >
        Room List
    </x-data-table>

    {{-- Add/Edit Room Modal --}}
    <x-modal name="hostel-room-modal" alpineTitle="editMode ? 'Modify Room Configuration' : 'Initialize New Residential Unit'" maxWidth="2xl">
        <form @submit.prevent="save" id="roomForm" method="POST" class="space-y-6" novalidate>
            @csrf
            <template x-if="editMode">
                <input type="hidden" name="_method" value="PUT">
            </template>

            <div class="space-y-6">
                <!-- Block & Level Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="modal-label-premium">Hostel Block <span class="text-red-500 font-bold">*</span></label>
                        <select name="hostel_id" x-model="formData.hostel_id" @change="loadFloors(); clearError('hostel_id')"
                                class="modal-input-premium" :class="errors.hostel_id ? 'border-red-500 ring-red-500/10' : ''">
                            <option value="">Choose Hostel Block</option>
                            @foreach($hostels as $hostel)
                                <option value="{{ $hostel->id }}">{{ $hostel->hostel_name }}</option>
                            @endforeach
                        </select>
                        <template x-if="errors.hostel_id">
                            <p class="modal-error-message" x-text="errors.hostel_id[0]"></p>
                        </template>
                    </div>

                    <div class="space-y-2">
                        <label class="modal-label-premium">Floor Level <span class="text-red-500 font-bold">*</span></label>
                        <select name="hostel_floor_id" x-model="formData.hostel_floor_id" :disabled="!formData.hostel_id"
                                @change="clearError('hostel_floor_id')"
                                class="modal-input-premium" :class="errors.hostel_floor_id ? 'border-red-500 ring-red-500/10' : ''">
                            <option value="">Select Floor</option>
                            <template x-for="floor in floors" :key="floor.id">
                                <option :value="floor.id" x-text="floor.floor_name"></option>
                            </template>
                        </select>
                        <template x-if="errors.hostel_floor_id">
                            <p class="modal-error-message" x-text="errors.hostel_floor_id[0]"></p>
                        </template>
                    </div>
                </div>

                <!-- Room Designation -->
                <div class="space-y-2">
                    <label class="modal-label-premium">Room Identifier <span class="text-red-500 font-bold">*</span></label>
                    <input type="text" name="room_name" x-model="formData.room_name" placeholder="e.g., Room 101, Deluxe Suite"
                           @input="clearError('room_name')" class="modal-input-premium" :class="errors.room_name ? 'border-red-500 ring-red-500/10' : ''">
                    <template x-if="errors.room_name">
                        <p class="modal-error-message" x-text="errors.room_name[0]"></p>
                    </template>
                </div>

                <!-- Amenities Cluster -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="modal-label-premium text-emerald-600">Air Conditioning</label>
                        <select name="ac" x-model="formData.ac" class="modal-input-premium">
                            @foreach(YesNo::options() as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="modal-label-premium text-indigo-600">Cooling Unit</label>
                        <select name="cooler" x-model="formData.cooler" class="modal-input-premium">
                            @foreach(YesNo::options() as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="modal-label-premium text-amber-600">Fan Node</label>
                        <select name="fan" x-model="formData.fan" class="modal-input-premium">
                            @foreach(YesNo::options() as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="modal-label-premium">Configuration Date</label>
                        <input type="date" name="room_create_date" x-model="formData.room_create_date"
                               class="modal-input-premium">
                    </div>
                </div>

                <!-- Guidance Notification Card sits between sections -->
                <div class="flex items-start gap-4 bg-[#f0f5ff] border border-[#e5edff] p-5 rounded-2xl shadow-sm mb-8">
                    <div class="w-11 h-11 bg-white rounded-xl shadow-sm flex items-center justify-center shrink-0">
                        <i class="fas fa-info-circle text-indigo-600 text-sm"></i>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-sm font-bold text-slate-900 leading-tight">Occupancy Notice</span>
                        <p class="text-[10px] text-slate-500 font-bold uppercase mt-1 tracking-wide opacity-80 leading-relaxed">
                            Room units serve as <span class="text-indigo-600 font-bold underline decoration-indigo-200">occupancy nodes</span>. Select amenities below to accurately reflect the room's facilities.
                        </p>
                    </div>
                </div>
            </div>
        </form>
        
        <x-slot name="footer">
            <button type="button" @click="closeModal()" :disabled="submitting"
                    class="btn-premium-cancel px-10">
                Cancel
            </button>
            <button type="submit" form="roomForm" :disabled="submitting"
                    class="btn-premium-primary min-w-[160px]">
                <template x-if="submitting">
                    <span class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin mr-3 inline-block"></span>
                </template>
                <span x-text="submitting ? 'Propagating...' : (editMode ? 'Update Changes' : 'Confirm Room')"></span>
            </button>
        </x-slot>
    </x-modal>

    {{-- Custom Confirm Modal --}}
    <x-confirm-modal 
        title="Dismantle Room Node?" 
        message="This will remove the residential unit from inventory. Active bed assignments must be struck first."
        confirm-text="Strike Record"
        confirm-color="red"
    />
</div>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('hostelRoomManagement', () => ({
        editMode: false,
        roomId: null,
        formData: {
            hostel_id: '',
            hostel_floor_id: '',
            room_name: '',
            ac: '{{ YesNo::No->value }}',
            cooler: '{{ YesNo::No->value }}',
            fan: '{{ YesNo::Yes->value }}',
            room_create_date: '',
        },
        floors: [],
        errors: {},
        submitting: false,
        
        init() {
            window.addEventListener('open-add-hostel-room', () => this.openAddModal());
            window.addEventListener('open-edit-hostel-room', (e) => this.openEditModal(e.detail));
            window.addEventListener('open-delete-hostel-room', (e) => this.confirmDelete(e.detail));

            this.$nextTick(() => {
                if (typeof $ !== 'undefined') {
                    $('select[name="hostel_id"], select[name="hostel_floor_id"], select[name="ac"], select[name="cooler"], select[name="fan"]').on('change', (e) => {
                        const field = e.target.getAttribute('name');
                        if (field && this.formData.hasOwnProperty(field)) {
                            this.formData[field] = e.target.value;
                            if (field === 'hostel_id') this.loadFloors();
                            this.clearError(field);
                        }
                    });
                }
            });
        },

        clearError(field) {
            if (this.errors[field]) {
                delete this.errors[field];
            }
        },

        async loadFloors(targetFloorId = null) {
            if (!this.formData.hostel_id) {
                this.floors = [];
                this.formData.hostel_floor_id = '';
                return;
            }

            try {
                const response = await fetch('{{ route('receptionist.hostel-rooms.get-floors') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        hostel_id: this.formData.hostel_id
                    })
                });

                const data = await response.json();
                if (data.success) {
                    this.floors = data.floors;
                    if (targetFloorId) {
                        this.formData.hostel_floor_id = String(targetFloorId);
                        this.$nextTick(() => {
                            if (typeof $ !== 'undefined') {
                                $('select[name="hostel_floor_id"]').val(this.formData.hostel_floor_id).trigger('change');
                            }
                        });
                    } else if (!this.editMode) {
                        this.formData.hostel_floor_id = '';
                    }
                }
            } catch (error) {
                console.error('Cascading Floor Refresh Failed:', error);
            }
        },

        async save() {
            this.submitting = true;
            this.errors = {};

            const url = this.editMode 
                ? `{{ route('receptionist.hostel-rooms.update', '___ID___') }}`.replace('___ID___', this.roomId)
                : '{{ route('receptionist.hostel-rooms.store') }}';
            
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
                            title: result.message || 'Registry updated successfully'
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

        confirmDelete(detail) {
            window.dispatchEvent(new CustomEvent('open-confirm-modal', {
                detail: {
                    message: `Are you sure you want to decommission the residential node: "${detail.name}"?`,
                    onConfirm: () => this.deleteRoom(detail.url)
                }
            }));
        },

        async deleteRoom(url) {
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
                        window.Toast.fire({ icon: 'success', title: result.message });
                    }
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    throw new Error(result.message || 'Strike operation failed');
                }
            } catch (error) {
                if (window.Toast) {
                    window.Toast.fire({ icon: 'error', title: error.message });
                }
            }
        },
        
        openAddModal() {
            this.editMode = false;
            this.roomId = null;
            this.errors = {};
            this.formData = {
                hostel_id: '',
                hostel_floor_id: '',
                room_name: '',
                ac: '{{ YesNo::No->value }}',
                cooler: '{{ YesNo::No->value }}',
                fan: '{{ YesNo::Yes->value }}',
                room_create_date: '{{ date('Y-m-d') }}',
            };
            this.floors = [];
            this.$dispatch('open-modal', 'hostel-room-modal');

            this.$nextTick(() => {
                if (typeof $ !== 'undefined') {
                    ['hostel_id', 'hostel_floor_id', 'ac', 'cooler', 'fan'].forEach(field => {
                        $(`select[name="${field}"]`).val(this.formData[field]).trigger('change');
                    });
                }
            });
        },
        
        async openEditModal(room) {
            this.editMode = true;
            this.roomId = room.id;
            this.errors = {};
            this.formData = {
                hostel_id: room.hostel_id ? String(room.hostel_id) : '',
                hostel_floor_id: room.hostel_floor_id ? String(room.hostel_floor_id) : '',
                room_name: room.room_name || '',
                ac: String(room.ac),
                cooler: String(room.cooler),
                fan: String(room.fan),
                room_create_date: room.room_create_date || '',
            };

            // Wait for floors to load before showing modal to ensure correct floor is selected
            await this.loadFloors(String(room.hostel_floor_id));
            this.$dispatch('open-modal', 'hostel-room-modal');

            // Sync other Select2 fields
            this.$nextTick(() => {
                setTimeout(() => {
                    if (typeof $ !== 'undefined') {
                        const fields = ['hostel_id', 'ac', 'cooler', 'fan'];
                        fields.forEach(field => {
                            $(`select[name="${field}"]`).val(this.formData[field]).trigger('change');
                        });
                    }
                }, 150);
            });
        },
        
        closeModal() {
            this.$dispatch('close-modal', 'hostel-room-modal');
            this.errors = {};
        }
    }));
});
</script>
@endpush
@endsection


