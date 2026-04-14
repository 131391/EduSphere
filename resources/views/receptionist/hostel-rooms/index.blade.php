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
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
        <div class="bg-white/80 backdrop-blur-md rounded-2xl shadow-sm border border-gray-100 p-6 flex items-center justify-between group">
            <div>
                <p class="text-[10px] text-gray-400 uppercase font-black tracking-widest mb-1">Total Room Units</p>
                <p class="text-3xl font-black text-gray-800">{{ $stats['total_room'] }}</p>
            </div>
            <div class="bg-indigo-100 p-4 rounded-2xl text-indigo-600 group-hover:scale-110 transition-transform duration-300">
                <i class="fas fa-door-open text-2xl"></i>
            </div>
        </div>

        <div class="bg-white/80 backdrop-blur-md rounded-2xl shadow-sm border border-gray-100 p-6 flex items-center justify-between group">
            <div>
                <p class="text-[10px] text-gray-400 uppercase font-black tracking-widest mb-1">Climate Controlled (AC)</p>
                <p class="text-3xl font-black text-gray-800">{{ $stats['ac_rooms'] }}</p>
            </div>
            <div class="bg-emerald-100 p-4 rounded-2xl text-emerald-600 group-hover:scale-110 transition-transform duration-300">
                <i class="fas fa-snowflake text-2xl"></i>
            </div>
        </div>

        <div class="bg-white/80 backdrop-blur-md rounded-2xl shadow-sm border border-gray-100 p-6 flex items-center justify-between group">
            <div>
                <p class="text-[10px] text-gray-400 uppercase font-black tracking-widest mb-1">Occupancy Nodes</p>
                <p class="text-3xl font-black text-gray-800">{{ $stats['total_beds'] }}</p>
            </div>
            <div class="bg-purple-100 p-4 rounded-2xl text-purple-600 group-hover:scale-110 transition-transform duration-300">
                <i class="fas fa-bed text-2xl"></i>
            </div>
        </div>
    </div>

    {{-- Page Header --}}
    <div class="bg-white/40 backdrop-blur-xl rounded-2xl p-6 border border-white/20 shadow-sm mb-8">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6">
            <div class="flex items-center gap-4">
                <a href="{{ route('receptionist.hostels.index') }}" 
                   class="w-10 h-10 bg-white border border-gray-100 rounded-xl flex items-center justify-center text-gray-400 hover:text-indigo-600 hover:border-indigo-100 transition-all shadow-sm">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <div>
                    <h2 class="text-2xl font-black text-gray-800 tracking-tight">Room Inventory</h2>
                    <p class="text-sm text-gray-500 font-medium">Configure residential specifications and amenities</p>
                </div>
            </div>
            <div class="flex flex-wrap gap-3">
                <button @click="openAddModal()" 
                        class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white text-sm font-black rounded-xl transition-all shadow-lg shadow-indigo-100 group">
                    <i class="fas fa-plus mr-2 group-hover:rotate-90 transition-transform duration-300"></i>
                    Initialize Room
                </button>
                <a href="{{ route('receptionist.hostel-rooms.export') }}" 
                   class="inline-flex items-center px-6 py-3 bg-white border border-gray-100 text-gray-700 text-sm font-bold rounded-xl hover:bg-gray-50 transition-all shadow-sm">
                    <i class="fas fa-file-excel mr-2 text-emerald-500"></i>
                    Inventory Export
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
                'key' => 'room_identity',
                'label' => 'ROOM IDENTITY',
                'sortable' => false,
                'render' => function($row) {
                    return '<div class="flex flex-col">
                                <span class="font-black text-gray-800">' . $row->room_name . '</span>
                                <span class="text-[10px] text-gray-400 uppercase font-black tracking-tighter">' . ($row->hostel ? $row->hostel->hostel_name : 'N/A') . ' • ' . ($row->floor ? $row->floor->floor_name : 'N/A') . '</span>
                            </div>';
                }
            ],
            [
                'key' => 'amenities',
                'label' => 'AMENITIES',
                'sortable' => false,
                'render' => function($row) {
                    $ac = $row->ac->value === YesNo::Yes->value ? '<span class="w-6 h-6 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center" title="AC Available"><i class="fas fa-snowflake text-[10px]"></i></span>' : '';
                    $cooler = $row->cooler->value === YesNo::Yes->value ? '<span class="w-6 h-6 rounded-lg bg-indigo-100 text-indigo-600 flex items-center justify-center" title="Cooler Available"><i class="fas fa-wind text-[10px]"></i></span>' : '';
                    $fan = $row->fan->value === YesNo::Yes->value ? '<span class="w-6 h-6 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center" title="Fan Available"><i class="fas fa-fan text-[10px]"></i></span>' : '';
                    
                    return '<div class="flex items-center gap-2">' . ($ac . $cooler . $fan ?: '<span class="text-gray-300 text-[10px] font-bold italic">Standard</span>') . '</div>';
                }
            ],
            [
                'key' => 'capacity',
                'label' => 'ASSIGNMENTS',
                'sortable' => false,
                'render' => function($row) {
                    return '<span class="px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-xs font-black">' . ($row->bedAssignments()->count() ?: 0) . ' Occupants</span>';
                }
            ],
            [
                'key' => 'room_create_date',
                'label' => 'INITIALIZED',
                'sortable' => true,
                'render' => function($row) {
                    return '<div class="text-gray-500 text-xs font-bold">' . 
                           ($row->room_create_date ? $row->room_create_date->format('d M, Y') : 'N/A') . 
                           '</div>';
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
                    return "openEditModal(".json_encode($roomData).")";
                },
                'icon' => 'fas fa-edit',
                'class' => 'text-indigo-600 hover:text-indigo-900',
                'title' => 'Edit',
            ],
            [
                'type' => 'button',
                'onclick' => function($row) {
                    return "confirmDelete('".route('receptionist.hostel-rooms.destroy', $row->id)."', '{$row->room_name}')";
                },
                'icon' => 'fas fa-trash',
                'class' => 'text-red-600 hover:text-red-900',
                'title' => 'Delete',
            ],
        ];
    @endphp

    <div class="bg-white/80 backdrop-blur-md rounded-3xl shadow-xl shadow-gray-100/50 border border-gray-100 overflow-hidden">
        <x-data-table 
            :columns="$tableColumns"
            :data="$rooms"
            :searchable="true"
            :actions="$tableActions"
            empty-message="No residential units configured"
            empty-icon="fas fa-door-open"
        />
    </div>

    {{-- Add/Edit Room Modal --}}
    <x-modal name="hostel-room-modal" alpineTitle="editMode ? 'Modify Room Configuration' : 'Initialize New Residential Unit'" maxWidth="2xl">
        <form @submit.prevent="save" method="POST" class="p-0 relative">
            @csrf
            <template x-if="editMode">
                <input type="hidden" name="_method" value="PUT">
            </template>

            <!-- Form Body - Academic Year Standard -->
            <div class="space-y-6">
                <!-- Block & Level Grid -->
                <div class="grid grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="modal-label-premium">Hostel Block <span class="text-red-500 font-bold">*</span></label>
                        <div class="relative group">
                            <select name="hostel_id" x-model="formData.hostel_id" @change="loadFloors(); clearError('hostel_id')"
                                    class="modal-input-premium appearance-none pr-10" :class="errors.hostel_id ? 'border-red-500 ring-red-500/10' : ''">
                                <option value="">Choose Hostel Block</option>
                                @foreach($hostels as $hostel)
                                    <option value="{{ $hostel->id }}">{{ $hostel->hostel_name }}</option>
                                @endforeach
                            </select>
                            <div class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-300 pointer-events-none group-focus-within:rotate-180 transition-transform duration-300">
                                <i class="fas fa-chevron-down text-[10px]"></i>
                            </div>
                        </div>
                        <template x-if="errors.hostel_id">
                            <p class="modal-error-message" x-text="errors.hostel_id[0]"></p>
                        </template>
                    </div>

                    <div class="space-y-2">
                        <label class="modal-label-premium">Floor Level <span class="text-red-500 font-bold">*</span></label>
                        <div class="relative group">
                            <select name="hostel_floor_id" x-model="formData.hostel_floor_id" :disabled="!formData.hostel_id"
                                    @change="clearError('hostel_floor_id')"
                                    class="modal-input-premium appearance-none pr-10" :class="errors.hostel_floor_id ? 'border-red-500 ring-red-500/10' : ''">
                                <option value="">Select Floor</option>
                                <template x-for="floor in floors" :key="floor.id">
                                    <option :value="floor.id" x-text="floor.floor_name"></option>
                                </template>
                            </select>
                            <div class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-300 pointer-events-none group-focus-within:rotate-180 transition-transform duration-300">
                                <i class="fas fa-chevron-down text-[10px]"></i>
                            </div>
                        </div>
                        <template x-if="errors.hostel_floor_id">
                            <p class="modal-error-message" x-text="errors.hostel_floor_id[0]"></p>
                        </template>
                    </div>
                </div>

                <!-- Room Designation -->
                <div class="space-y-2 mb-6">
                    <label class="modal-label-premium">Room Identifier <span class="text-red-500 font-bold">*</span></label>
                    <div class="relative group">
                        <input type="text" name="room_name" x-model="formData.room_name" placeholder="e.g., Room 101, Deluxe Suite"
                               @input="clearError('room_name')" class="modal-input-premium pr-10" :class="errors.room_name ? 'border-red-500 ring-red-500/10' : ''">
                        <div class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-300 pointer-events-none">
                            <i class="fas fa-door-open text-[10px]"></i>
                        </div>
                    </div>
                    <template x-if="errors.room_name">
                        <p class="modal-error-message" x-text="errors.room_name[0]"></p>
                    </template>
                </div>

                <!-- Guidance Notification Card sits between sections -->
                <div class="mb-8 flex items-start gap-4 bg-[#f0f5ff] border border-[#e5edff] p-5 rounded-2xl shadow-sm">
                    <div class="w-11 h-11 bg-white rounded-xl shadow-sm flex items-center justify-center shrink-0">
                        <i class="fas fa-info-circle text-indigo-600 text-sm"></i>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-[13px] font-bold text-slate-900 leading-tight">Occupancy Notice</span>
                        <p class="text-[10px] text-slate-500 font-bold uppercase mt-1 tracking-wide opacity-80 leading-relaxed">
                            Room units serve as <span class="text-indigo-600 italic underline decoration-indigo-100">occupancy nodes</span>. Select amenities below to accurately reflect the room's facilities.
                        </p>
                    </div>
                </div>

                <!-- Amenities Cluster -->
                <div class="grid grid-cols-2 gap-6 mb-6">
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
                </div>

                <div class="grid grid-cols-2 gap-6 mb-6">
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
            </div>

            {{-- Modal Footer Slot --}}
            <x-slot name="footer">
                <button type="button" @click="closeModal()" :disabled="submitting"
                        class="btn-premium-cancel">
                    Discard
                </button>
                <button type="submit" :disabled="submitting"
                        class="btn-premium-primary flex items-center gap-2">
                    <template x-if="submitting">
                        <span class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                    </template>
                    <span x-text="submitting ? 'Propagating...' : (editMode ? 'Update Unit' : 'Confirm Room')"></span>
                </button>
            </x-slot>
        </form>
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
                ? `/receptionist/hostel-rooms/${this.roomId}` 
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

// Global helpers
function openEditModal(room) {
    const el = document.querySelector('[x-data*="hostelRoomManagement"]');
    if (el) Alpine.$data(el).openEditModal(room);
}

function confirmDelete(url, roomName) {
    window.dispatchEvent(new CustomEvent('open-confirm-modal', {
        detail: {
            message: `Are you sure you want to decommission the residential node: "${roomName}"?`,
            onConfirm: () => {
                const el = document.querySelector('[x-data*="hostelRoomManagement"]');
                if (el) Alpine.$data(el).deleteRoom(url);
            }
        }
    }));
}
</script>
@endpush
@endsection


