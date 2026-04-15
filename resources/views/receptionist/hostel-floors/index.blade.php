@extends('layouts.receptionist')

@section('title', 'Floor Configuration - Receptionist')
@section('page-title', 'Floor Configuration')
@section('page-description', 'Manage structural levels within hostel blocks')

@section('content')
<div class="space-y-6" x-data="hostelFloorManagement" x-init="init()">
    {{-- Statistics Overview --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border-t-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Floor Levels</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ $stats['total_floor'] }}</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-layer-group text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border-t-4 border-emerald-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Inventory Capacity</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ $stats['total_room'] }}</p>
                </div>
                <div class="w-12 h-12 bg-emerald-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-door-open text-emerald-600 text-xl"></i>
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
                        <i class="fas fa-layer-group text-xs"></i>
                    </div>
                    Floor Distribution
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Manage vertical residential zoning and floor specs.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <button @click="$dispatch('open-add-hostel-floor')"
                    class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-teal-600 to-emerald-600 hover:from-teal-700 hover:to-emerald-700 text-white text-sm font-semibold rounded-xl transition-all shadow-md hover:shadow-lg active:scale-95">
                    <i class="fas fa-plus mr-2"></i>
                    Configure Level
                </button>
                <a href="{{ route('receptionist.hostel-floors.export') }}"
                    class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-slate-700 to-slate-900 hover:from-black hover:to-slate-800 text-white text-sm font-semibold rounded-xl transition-all shadow-md hover:shadow-lg active:scale-95">
                    <i class="fas fa-file-excel mr-2 text-xs"></i>
                    Export Layout
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
                    return "window.dispatchEvent(new CustomEvent('open-edit-hostel-floor', { detail: ".json_encode($floorData)." }))";
                },
                'icon' => 'fas fa-edit',
                'class' => 'text-indigo-600 hover:text-indigo-900',
                'title' => 'Edit',
            ],
            [
                'type' => 'button',
                'onclick' => function($row) {
                    $deleteData = [
                        'url' => route('receptionist.hostel-floors.destroy', $row->id),
                        'name' => $row->floor_name
                    ];
                    return "window.dispatchEvent(new CustomEvent('open-delete-hostel-floor', { detail: ".json_encode($deleteData)." }))";
                },
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
        empty-message="No floor levels configured"
        empty-icon="fas fa-layer-group"
    >
        Floor List
    </x-data-table>

    {{-- Add/Edit Floor Modal --}}
    <!-- Add/Edit Floor Modal -->
    <x-modal name="hostel-floor-modal" alpineTitle="editMode ? 'Modify Floor Specifications' : 'Establish New Floor Level'" maxWidth="2xl">
        <form @submit.prevent="save" id="floorForm" method="POST" class="space-y-6" novalidate>
            @csrf
            <template x-if="editMode">
                <input type="hidden" name="_method" value="PUT">
            </template>

            <div class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Hostel Selection -->
                    <div class="space-y-2">
                        <label class="modal-label-premium">Select Hostel Block <span class="text-red-600 font-bold">*</span></label>
                        <select name="hostel_id" x-model="formData.hostel_id" @change="clearError('hostel_id')"
                                class="modal-input-premium"
                                :class="errors.hostel_id ? 'border-red-500 ring-red-500/10' : ''">
                            <option value="">Choose Hostel Block</option>
                            @foreach($hostels as $hostel)
                                <option value="{{ $hostel->id }}">{{ $hostel->hostel_name }}</option>
                            @endforeach
                        </select>
                        <template x-if="errors.hostel_id">
                            <p class="modal-error-message" x-text="errors.hostel_id[0]"></p>
                        </template>
                    </div>

                    <!-- Floor Designation -->
                    <div class="space-y-2">
                        <label class="modal-label-premium">Floor Designation <span class="text-red-600 font-bold">*</span></label>
                        <input type="text" name="floor_name" x-model="formData.floor_name" @input="clearError('floor_name')" placeholder="e.g., Ground Floor, Sector A"
                            class="modal-input-premium" :class="errors.floor_name ? 'border-red-500 ring-red-500/10' : ''">
                        <template x-if="errors.floor_name">
                            <p class="modal-error-message" x-text="errors.floor_name[0]"></p>
                        </template>
                    </div>

                    <div class="space-y-2">
                        <label class="modal-label-premium">Room Capacity</label>
                        <input type="number" name="total_room" x-model="formData.total_room" @input="clearError('total_room')" placeholder="0"
                            class="modal-input-premium font-bold">
                    </div>
                    <div class="space-y-2">
                        <label class="modal-label-premium">Configuration Date</label>
                        <input type="date" name="floor_create_date" x-model="formData.floor_create_date" @input="clearError('floor_create_date')"
                            class="modal-input-premium">
                    </div>
                </div>

                <!-- Guidance Notification Card -->
                <div class="flex items-start gap-4 bg-[#f0f5ff] border border-[#e5edff] p-5 rounded-2xl shadow-sm mb-8">
                    <div class="w-11 h-11 bg-white rounded-xl shadow-sm flex items-center justify-center shrink-0">
                        <i class="fas fa-info-circle text-indigo-600 text-sm"></i>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-sm font-bold text-slate-900 leading-tight">Structural Notice</span>
                        <p class="text-[10px] text-slate-500 font-bold uppercase mt-1 tracking-wide opacity-80 leading-relaxed">
                            Floor designation helps in <span class="text-indigo-600 font-bold underline decoration-indigo-200">spatial mapping</span> of rooms. Ensure unique names within a hostel block.
                        </p>
                    </div>
                </div>
            </div>
        </form>

        <x-slot name="footer">
            <button type="button" @click="closeModal()" :disabled="submitting" class="btn-premium-cancel px-10">
                Cancel
            </button>
            <button type="submit" form="floorForm" :disabled="submitting" class="btn-premium-primary min-w-[160px]">
                <template x-if="submitting">
                    <span class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin mr-3 inline-block"></span>
                </template>
                <span x-text="submitting ? 'Propagating...' : (editMode ? 'Update Changes' : 'Confirm Floor')"></span>
            </button>
        </x-slot>
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
            window.addEventListener('open-add-hostel-floor', () => this.openAddModal());
            window.addEventListener('open-edit-hostel-floor', (e) => this.openEditModal(e.detail));
            window.addEventListener('open-delete-hostel-floor', (e) => this.confirmDelete(e.detail));

            this.$nextTick(() => {
                if (typeof $ !== 'undefined') {
                    $('select[name="hostel_id"]').on('change', (e) => {
                        this.formData.hostel_id = e.target.value;
                        this.clearError('hostel_id');
                    });
                }
            });
        },

        clearError(field) {
            if (this.errors[field]) {
                delete this.errors[field];
            }
        },

        async save() {
            this.submitting = true;
            this.errors = {};

            const url = this.editMode 
                ? `{{ route('receptionist.hostel-floors.update', '___ID___') }}`.replace('___ID___', this.floorId)
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

        confirmDelete(detail) {
            window.dispatchEvent(new CustomEvent('open-confirm-modal', {
                detail: {
                    message: `Are you sure you want to decommission the floor node: "${detail.name}"?`,
                    onConfirm: () => this.deleteFloor(detail.url)
                }
            }));
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
                floor_create_date: '{{ date('Y-m-d') }}',
            };
            this.$dispatch('open-modal', 'hostel-floor-modal');

            this.$nextTick(() => {
                if (typeof $ !== 'undefined') {
                    $('select[name="hostel_id"]').val('').trigger('change');
                }
            });
        },
        
        openEditModal(floor) {
            this.editMode = true;
            this.floorId = floor.id;
            this.errors = {};
            this.formData = {
                hostel_id: floor.hostel_id ? String(floor.hostel_id) : '',
                floor_name: floor.floor_name || '',
                total_room: floor.total_room || '',
                floor_create_date: floor.floor_create_date || '',
            };
            this.$dispatch('open-modal', 'hostel-floor-modal');

            this.$nextTick(() => {
                setTimeout(() => {
                    if (typeof $ !== 'undefined') {
                        $('select[name="hostel_id"]').val(this.formData.hostel_id).trigger('change');
                    }
                }, 150);
            });
        },
        
        closeModal() {
            this.$dispatch('close-modal', 'hostel-floor-modal');
            this.errors = {};
        }
    }));
});
</script>
@endpush
@endsection

