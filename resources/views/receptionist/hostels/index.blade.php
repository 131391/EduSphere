@extends('layouts.receptionist')

@section('title', 'Hostel Registry - Receptionist')
@section('page-title', 'Hostel Registry')
@section('page-description', 'Manage and monitor school hostel facilities')

@section('content')
<div class="space-y-6" x-data="hostelManagement" x-init="init()">
    {{-- Hostel Statistics --}}
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-8">
        <template x-for="(stat, index) in stats" :key="index">
            <div class="bg-white/80 backdrop-blur-md rounded-2xl shadow-sm border border-gray-100 p-5 transition-all hover:shadow-md group">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[10px] text-gray-400 uppercase font-black tracking-widest mb-1" x-text="stat.label"></p>
                        <p class="text-2xl font-black text-gray-800" x-text="stat.value"></p>
                    </div>
                    <div :class="`p-3 rounded-2xl transition-colors group-hover:scale-110 duration-300 ${stat.colorClass}`">
                        <i :class="`fas ${stat.icon} text-lg`"></i>
                    </div>
                </div>
            </div>
        </template>
    </div>

    {{-- Page Header with Actions --}}
    <div class="bg-white/40 backdrop-blur-xl rounded-2xl p-6 border border-white/20 shadow-sm mb-8">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6">
            <div class="flex items-center gap-4">
                <div class="bg-gradient-to-br from-indigo-500 to-purple-600 p-3 rounded-2xl shadow-lg shadow-indigo-100">
                    <i class="fas fa-building text-white text-xl"></i>
                </div>
                <div>
                    <h2 class="text-2xl font-black text-gray-800 tracking-tight">Hostel Master</h2>
                    <p class="text-sm text-gray-500 font-medium">Configure and manage primary hostel blocks</p>
                </div>
            </div>
            <div class="flex flex-wrap gap-3">
                <button @click="openAddModal()" 
                        class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white text-sm font-black rounded-xl transition-all shadow-lg shadow-indigo-100 group">
                    <i class="fas fa-plus mr-2 group-hover:rotate-90 transition-transform duration-300"></i>
                    Establish New Hostel
                </button>
                <a href="{{ route('receptionist.hostels.export') }}" 
                   class="inline-flex items-center px-6 py-3 bg-white border border-gray-100 text-gray-700 text-sm font-bold rounded-xl hover:bg-gray-50 transition-all shadow-sm">
                    <i class="fas fa-file-excel mr-2 text-emerald-500"></i>
                    Registry Export
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
                'render' => function($row) {
                    return '<div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-indigo-50 flex items-center justify-center">
                                    <i class="fas fa-bed text-indigo-500 text-xs"></i>
                                </div>
                                <span class="font-bold text-gray-800">' . $row->hostel_name . '</span>
                            </div>';
                }
            ],
            [
                'key' => 'hostel_incharge',
                'label' => 'INCHARGE',
                'sortable' => true,
                'render' => function($row) {
                    return '<div class="flex items-center gap-2 text-gray-600 font-medium">
                                <i class="fas fa-user-tie text-[10px] text-gray-400"></i>
                                <span>' . ($row->hostel_incharge ?: '<span class="text-gray-300 italic">Not Assigned</span>') . '</span>
                            </div>';
                }
            ],
            [
                'key' => 'capability',
                'label' => 'CAPACITY',
                'sortable' => true,
                'render' => function($row) {
                    return '<span class="px-3 py-1 bg-emerald-50 text-emerald-700 rounded-full text-xs font-black">' . ($row->capability ?: 0) . ' Beds</span>';
                }
            ],
            [
                'key' => 'hostel_create_date',
                'label' => 'ESTABLISHED',
                'sortable' => true,
                'render' => function($row) {
                    return '<div class="text-gray-500 text-xs font-bold">' . 
                           ($row->hostel_create_date ? $row->hostel_create_date->format('d M, Y') : 'N/A') . 
                           '</div>';
                }
            ],
        ];

        $tableActions = [
            [
                'type' => 'button',
                'onclick' => function($row) {
                    $hostelData = [
                        'id' => $row->id,
                        'hostel_name' => $row->hostel_name,
                        'hostel_incharge' => $row->hostel_incharge,
                        'capability' => $row->capability,
                        'hostel_create_date' => $row->hostel_create_date ? $row->hostel_create_date->format('Y-m-d') : '',
                    ];
                    return "openEditModal(".json_encode($hostelData).")";
                },
                'icon' => 'fas fa-edit',
                'class' => 'text-indigo-600 hover:text-indigo-900',
                'title' => 'Edit',
            ],
            [
                'type' => 'button',
                'onclick' => function($row) {
                    return "confirmDelete('".route('receptionist.hostels.destroy', $row->id)."', '{$row->hostel_name}')";
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
            :data="$hostels"
            :searchable="true"
            :actions="$tableActions"
            empty-message="No hostels found in the school registry"
            empty-icon="fas fa-bed"
        />
    </div>

    {{-- Add/Edit Hostel Modal --}}
    <!-- Add/Edit Hostel Modal -->
    <x-modal name="hostel-modal" alpineTitle="editMode ? 'Modify Hostel Specifications' : 'Establish New Hostel Node'" maxWidth="xl">
        <form @submit.prevent="save" method="POST" novalidate>
            @csrf
            <template x-if="editMode">
                <input type="hidden" name="_method" value="PUT">
            </template>

            <!-- Form Body - Academic Year Standard -->
            <div class="space-y-6">
                <!-- Hostel Identity Block -->
                <div class="space-y-2 mb-6">
                    <label class="modal-label-premium">Hostel Name <span class="text-red-600 font-bold">*</span></label>
                    <div class="relative group">
                        <input type="text" name="hostel_name" x-model="formData.hostel_name" @input="clearError('hostel_name')" placeholder="e.g., Aravali Boys Hostel"
                            class="modal-input-premium pr-10" :class="{'border-red-500 ring-red-500/10': errors.hostel_name}">
                        <div class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-300 pointer-events-none group-focus-within:scale-110 transition-transform">
                            <i class="fas fa-building text-[10px]"></i>
                        </div>
                    </div>
                    <template x-if="errors.hostel_name">
                        <p class="modal-error-message" x-text="errors.hostel_name[0]"></p>
                    </template>
                </div>

                <!-- Incharge Assignment -->
                <div class="space-y-2 mb-6">
                    <label class="modal-label-premium">Assigned Incharge / Warden</label>
                    <div class="relative group">
                        <input type="text" name="hostel_incharge" x-model="formData.hostel_incharge" @input="clearError('hostel_incharge')" placeholder="Assigned Warden Name"
                            class="modal-input-premium pr-10" :class="{'border-red-500 ring-red-500/10': errors.hostel_incharge}">
                        <div class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-300 pointer-events-none">
                            <i class="fas fa-user-tie text-[10px]"></i>
                        </div>
                    </div>
                </div>

                <!-- Configuration Grid -->
                <div class="grid grid-cols-2 gap-6 mb-6">
                    <div class="space-y-2">
                        <label class="modal-label-premium">Total Capacity (Beds)</label>
                        <div class="relative group">
                            <input type="number" name="capability" x-model="formData.capability" @input="clearError('capability')" placeholder="0"
                                class="modal-input-premium pr-10 font-bold" :class="{'border-red-500 ring-red-500/10': errors.capability}">
                            <div class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-300 pointer-events-none">
                                <i class="fas fa-bed text-[10px]"></i>
                            </div>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <label class="modal-label-premium">Establishment Date</label>
                        <div class="relative group">
                            <input type="date" name="hostel_create_date" x-model="formData.hostel_create_date" @input="clearError('hostel_create_date')"
                                class="modal-input-premium pr-10" :class="{'border-red-500 ring-red-500/10': errors.hostel_create_date}">
                        </div>
                    </div>
                </div>

                <!-- Guidance Notification Card -->
                <div class="mb-8 flex items-start gap-4 bg-[#f0f5ff] border border-[#e5edff] p-5 rounded-2xl shadow-sm">
                    <div class="w-11 h-11 bg-white rounded-xl shadow-sm flex items-center justify-center shrink-0">
                        <i class="fas fa-info-circle text-indigo-600 text-sm"></i>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-[13px] font-bold text-slate-900 leading-tight">Administrative Notice</span>
                        <p class="text-[10px] text-slate-500 font-bold uppercase mt-1 tracking-wide opacity-80 leading-relaxed">
                            Establishing a hostel creates a <span class="text-indigo-600 italic underline decoration-indigo-100">primary facility node</span>. Floors and rooms must be configured subsequently.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Modal Footer - Exact Match Academic Year -->
            <x-slot name="footer">
                <button type="button" @click="closeModal()" :disabled="submitting" class="btn-premium-cancel px-10">
                    Discard
                </button>
                <button type="submit" :disabled="submitting" class="btn-premium-primary min-w-[160px]">
                    <template x-if="submitting">
                        <span class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin mr-3 inline-block"></span>
                    </template>
                    <span x-text="submitting ? 'Propagating...' : (editMode ? 'Update Registry' : 'Establish Hostel')"></span>
                </button>
            </x-slot>
        </form>
    </x-modal>

    {{-- Custom Confirm Modal --}}
    <x-confirm-modal 
        title="Permanently Strike Record?" 
        message="This action will remove the hostel from the active registry. Active dependency floors must be cleared first."
        confirm-text="Strike Record"
        confirm-color="red"
    />
</div>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('hostelManagement', () => ({
        editMode: false,
        hostelId: null,
        formData: {
            hostel_name: '',
            hostel_incharge: '',
            capability: '',
            hostel_create_date: '',
        },
        stats: [
            { label: 'Total Hostel', value: '{{ $stats['total_hostel'] }}', icon: 'fa-building', colorClass: 'bg-indigo-100 text-indigo-600' },
            { label: 'Total Floor', value: '{{ $stats['total_floor'] }}', icon: 'fa-layer-group', colorClass: 'bg-blue-100 text-blue-600' },
            { label: 'Total Room', value: '{{ $stats['total_room'] }}', icon: 'fa-door-open', colorClass: 'bg-emerald-100 text-emerald-600' },
            { label: 'Total Bed', value: '{{ $stats['total_bed'] }}', icon: 'fa-bed', colorClass: 'bg-purple-100 text-purple-600' },
            { label: 'Hostelers', value: '{{ $stats['hosteler_students'] }}', icon: 'fa-user-graduate', colorClass: 'bg-pink-100 text-pink-600' },
        ],
        errors: {},
        submitting: false,
        
        init() {
            // Initial state sync
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
                ? `/receptionist/hostels/${this.hostelId}` 
                : '{{ route('receptionist.hostels.store') }}';
            
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

        async deleteHostel(url) {
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
            this.hostelId = null;
            this.errors = {};
            this.formData = {
                hostel_name: '',
                hostel_incharge: '',
                capability: '',
                hostel_create_date: '',
            };
            this.$dispatch('open-modal', 'hostel-modal');
        },
        
        openEditModal(hostel) {
            this.editMode = true;
            this.hostelId = hostel.id;
            this.errors = {};
            this.formData = {
                hostel_name: hostel.hostel_name || '',
                hostel_incharge: hostel.hostel_incharge || '',
                capability: hostel.capability || '',
                hostel_create_date: hostel.hostel_create_date || '',
            };
            this.$dispatch('open-modal', 'hostel-modal');
        },
        
        closeModal() {
            this.$dispatch('close-modal', 'hostel-modal');
            this.errors = {};
        }
    }));
});

// Global helpers
function openEditModal(hostel) {
    const el = document.querySelector('[x-data*="hostelManagement"]');
    if (el) Alpine.$data(el).openEditModal(hostel);
}

function confirmDelete(url, name) {
    window.dispatchEvent(new CustomEvent('open-confirm-modal', {
        detail: {
            message: `Are you sure you want to decommission the registry entry: "${name}"?`,
            onConfirm: () => {
                const el = document.querySelector('[x-data*="hostelManagement"]');
                if (el) Alpine.$data(el).deleteHostel(url);
            }
        }
    }));
}
</script>
@endpush
@endsection
