@extends('layouts.receptionist')

@section('title', 'Floor Configuration - Receptionist')
@section('page-title', 'Floor Configuration')
@section('page-description', 'Manage structural levels within hostel blocks')

@section('content')
<div class="space-y-6" x-data="hostelFloorManagement" x-init="init()">
    {{-- Statistics Overview --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
        <div class="bg-white/80 backdrop-blur-md rounded-2xl shadow-sm border border-gray-100 p-6 flex items-center justify-between group">
            <div>
                <p class="text-[10px] text-gray-400 uppercase font-black tracking-widest mb-1">Total Floor Levels</p>
                <p class="text-3xl font-black text-gray-800">{{ $stats['total_floor'] }}</p>
            </div>
            <div class="bg-indigo-100 p-4 rounded-2xl text-indigo-600 group-hover:scale-110 transition-transform duration-300">
                <i class="fas fa-layer-group text-2xl"></i>
            </div>
        </div>

        <div class="bg-white/80 backdrop-blur-md rounded-2xl shadow-sm border border-gray-100 p-6 flex items-center justify-between group">
            <div>
                <p class="text-[10px] text-gray-400 uppercase font-black tracking-widest mb-1">Total Room Capacity</p>
                <p class="text-3xl font-black text-gray-800">{{ $stats['total_room'] }}</p>
            </div>
            <div class="bg-emerald-100 p-4 rounded-2xl text-emerald-600 group-hover:scale-110 transition-transform duration-300">
                <i class="fas fa-door-open text-2xl"></i>
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
                    <h2 class="text-2xl font-black text-gray-800 tracking-tight">Floor Distribution</h2>
                    <p class="text-sm text-gray-500 font-medium">Define and organize hostel floor specifications</p>
                </div>
            </div>
            <div class="flex flex-wrap gap-3">
                <button @click="openAddModal()" 
                        class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white text-sm font-black rounded-xl transition-all shadow-lg shadow-indigo-100 group">
                    <i class="fas fa-plus mr-2 group-hover:rotate-90 transition-transform duration-300"></i>
                    Add Floor Level
                </button>
                <a href="{{ route('receptionist.hostel-floors.export') }}" 
                   class="inline-flex items-center px-6 py-3 bg-white border border-gray-100 text-gray-700 text-sm font-bold rounded-xl hover:bg-gray-50 transition-all shadow-sm">
                    <i class="fas fa-file-excel mr-2 text-emerald-500"></i>
                    Floor Export
                </a>
            </div>
        </div>
    </div>

    {{-- Floors Table --}}
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
                'label' => 'HOSTEL BLOCK',
                'sortable' => false,
                'render' => function($row) {
                    return '<div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-indigo-50 flex items-center justify-center">
                                    <i class="fas fa-building text-indigo-500 text-xs"></i>
                                </div>
                                <span class="font-bold text-gray-800">' . ($row->hostel ? $row->hostel->hostel_name : 'N/A') . '</span>
                            </div>';
                }
            ],
            [
                'key' => 'floor_name',
                'label' => 'FLOOR LEVEL',
                'sortable' => true,
                'render' => function($row) {
                    return '<span class="font-black text-gray-700">' . $row->floor_name . '</span>';
                }
            ],
            [
                'key' => 'total_room',
                'label' => 'ROOMS',
                'sortable' => true,
                'render' => function($row) {
                    return '<span class="px-3 py-1 bg-emerald-50 text-emerald-700 rounded-full text-xs font-black">' . ($row->total_room ?: 0) . ' Rooms</span>';
                }
            ],
            [
                'key' => 'floor_create_date',
                'label' => 'CONFIGURED ON',
                'sortable' => true,
                'render' => function($row) {
                    return '<div class="text-gray-500 text-xs font-bold">' . 
                           ($row->floor_create_date ? $row->floor_create_date->format('d M, Y') : 'N/A') . 
                           '</div>';
                }
            ],
        ];

        $tableActions = [
            [
                'type' => 'button',
                'onclick' => function($row) {
                    $floorData = [
                        'id' => $row->id,
                        'hostel_id' => $row->hostel_id,
                        'floor_name' => $row->floor_name,
                        'total_room' => $row->total_room,
                        'floor_create_date' => $row->floor_create_date ? $row->floor_create_date->format('Y-m-d') : '',
                    ];
                    return "openEditModal(".json_encode($floorData).")";
                },
                'icon' => 'fas fa-edit',
                'class' => 'text-indigo-600 hover:text-indigo-900',
                'title' => 'Edit',
            ],
            [
                'type' => 'button',
                'onclick' => function($row) {
                    return "confirmDelete('".route('receptionist.hostel-floors.destroy', $row->id)."', '{$row->floor_name}')";
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
            :data="$floors"
            :searchable="true"
            :actions="$tableActions"
            empty-message="No floor levels configured"
            empty-icon="fas fa-layer-group"
        />
    </div>

    {{-- Add/Edit Floor Modal --}}
    <x-modal name="hostel-floor-modal" alpineTitle="editMode ? 'Modify Floor Specifications' : 'Establish New Floor Level'" maxWidth="xl">
        <form @submit.prevent="save" method="POST" class="p-0 relative">
            @csrf
            <template x-if="editMode">
                <input type="hidden" name="_method" value="PUT">
            </template>

            {{-- Global Error Announcement --}}
            <template x-if="Object.keys(errors).length > 0">
                <div class="mb-4 p-4 bg-red-50 border-l-4 border-red-500 rounded-r-xl mx-6 mt-6">
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
                <div>
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Select Hostel Block <span class="text-red-500">*</span></label>
                    <select name="hostel_id" x-model="formData.hostel_id"
                            @change="delete errors.hostel_id"
                            class="w-full px-5 py-3.5 bg-gray-50/50 border border-gray-100 rounded-2xl focus:outline-none focus:ring-4 transition-all focus:ring-indigo-500/5 focus:border-indigo-500 focus:bg-white"
                            :class="errors.hostel_id ? 'border-red-300 ring-red-500/5 bg-red-50/20' : ''">
                        <option value="">Choose Hostel Block</option>
                        @foreach($hostels as $hostel)
                            <option value="{{ $hostel->id }}">{{ $hostel->hostel_name }}</option>
                        @endforeach
                    </select>
                    <template x-if="errors.hostel_id">
                        <p class="text-red-500 text-[10px] font-black mt-2 uppercase tracking-tight" x-text="errors.hostel_id[0]"></p>
                    </template>
                </div>

                <div>
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Floor Designation <span class="text-red-500">*</span></label>
                    <input type="text" name="floor_name" x-model="formData.floor_name"
                           placeholder="e.g., Ground Floor, Sector A"
                           @input="delete errors.floor_name"
                           class="w-full px-5 py-3.5 bg-gray-50/50 border border-gray-100 rounded-2xl focus:outline-none focus:ring-4 transition-all focus:ring-indigo-500/5 focus:border-indigo-500 focus:bg-white"
                           :class="errors.floor_name ? 'border-red-300 ring-red-500/5 bg-red-50/20' : ''">
                    <template x-if="errors.floor_name">
                        <p class="text-red-500 text-[10px] font-black mt-2 uppercase tracking-tight" x-text="errors.floor_name[0]"></p>
                    </template>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Room Capacity</label>
                        <input type="number" name="total_room" x-model="formData.total_room"
                               placeholder="0" min="0"
                               @input="delete errors.total_room"
                               class="w-full px-5 py-3.5 bg-gray-50/50 border border-gray-100 rounded-2xl focus:outline-none focus:ring-4 transition-all focus:ring-indigo-500/5 focus:border-indigo-500 focus:bg-white">
                        <template x-if="errors.total_room">
                            <p class="text-red-500 text-[10px] font-black mt-2 uppercase tracking-tight" x-text="errors.total_room[0]"></p>
                        </template>
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Config Date</label>
                        <input type="date" name="floor_create_date" x-model="formData.floor_create_date"
                               @input="delete errors.floor_create_date"
                               class="w-full px-5 py-3.5 bg-gray-50/50 border border-gray-100 rounded-2xl focus:outline-none focus:ring-4 transition-all focus:ring-indigo-500/5 focus:border-indigo-500 focus:bg-white">
                        <template x-if="errors.floor_create_date">
                            <p class="text-red-500 text-[10px] font-black mt-2 uppercase tracking-tight" x-text="errors.floor_create_date[0]"></p>
                        </template>
                    </div>
                </div>
            </div>

            {{-- Modal Footer --}}
            <div class="px-8 py-6 bg-gray-50/50 border-t border-gray-100 flex items-center justify-end gap-3 rounded-b-3xl">
                <button type="button" @click="closeModal()" :disabled="submitting"
                        class="px-6 py-3 bg-white border border-gray-200 text-gray-600 rounded-xl hover:bg-gray-50 transition-all font-bold text-sm disabled:opacity-50">
                    Discard
                </button>
                <button type="submit" :disabled="submitting"
                        class="px-8 py-3 bg-gradient-to-r from-indigo-600 to-indigo-700 text-white rounded-xl hover:from-indigo-700 hover:to-indigo-800 transition-all font-black text-sm shadow-xl shadow-indigo-100 flex items-center gap-2">
                    <template x-if="submitting">
                        <span class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                    </template>
                    <span x-text="submitting ? 'Propagating...' : (editMode ? 'Update Floor' : 'Confirm Floor')"></span>
                </button>
            </div>
        </form>
    </x-modal>

    {{-- Custom Confirm Modal --}}
    <x-confirm-modal 
        title="Dismantle Floor Record?" 
        message="This will remove the floor from the registry. Active room nodes must be struck first."
        confirm-text="Strike Record"
        confirm-color="red"
    />
</div>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('hostelFloorManagement', () => ({
        editMode: false,
        floorId: null,
        formData: {
            hostel_id: '',
            floor_name: '',
            total_room: '',
            floor_create_date: '',
        },
        errors: {},
        submitting: false,
        
        init() {
            // Initializing logic
        },

        async save() {
            this.submitting = true;
            this.errors = {};

            const url = this.editMode 
                ? `/receptionist/hostel-floors/${this.floorId}` 
                : '{{ route('receptionist.hostel-floors.store') }}';
            
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

        async deleteFloor(url) {
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
            this.floorId = null;
            this.errors = {};
            this.formData = {
                hostel_id: '',
                floor_name: '',
                total_room: '',
                floor_create_date: '',
            };
            this.$dispatch('open-modal', 'hostel-floor-modal');
        },
        
        openEditModal(floor) {
            this.editMode = true;
            this.floorId = floor.id;
            this.errors = {};
            this.formData = {
                hostel_id: floor.hostel_id || '',
                floor_name: floor.floor_name || '',
                total_room: floor.total_room || '',
                floor_create_date: floor.floor_create_date || '',
            };
            this.$dispatch('open-modal', 'hostel-floor-modal');
        },
        
        closeModal() {
            this.$dispatch('close-modal', 'hostel-floor-modal');
            this.errors = {};
        }
    }));
});

// Global helpers
function openEditModal(floor) {
    const el = document.querySelector('[x-data*="hostelFloorManagement"]');
    if (el) Alpine.$data(el).openEditModal(floor);
}

function confirmDelete(url, floorName) {
    window.dispatchEvent(new CustomEvent('open-confirm-modal', {
        detail: {
            message: `Are you sure you want to decommission the floor node: "${floorName}"?`,
            onConfirm: () => {
                const el = document.querySelector('[x-data*="hostelFloorManagement"]');
                if (el) Alpine.$data(el).deleteFloor(url);
            }
        }
    }));
}
</script>
@endpush
@endsection

