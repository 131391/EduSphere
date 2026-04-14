@extends('layouts.school')

@section('title', 'Registration Fee Settings')

@section('content')
<div x-data="registrationFeeManager()">
    <!-- Header Section -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 mb-6 border border-emerald-100/50">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-gray-800 dark:text-white flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-emerald-100 flex items-center justify-center text-emerald-600">
                        <i class="fas fa-user-plus text-xs"></i>
                    </div>
                    Registration Fee Settings
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Configure mandatory registration fees for new admissions by class</p>
            </div>
            <button @click="openAddModal()" 
                    class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white text-sm font-semibold rounded-xl transition-all shadow-md hover:shadow-lg active:scale-95">
                <i class="fas fa-plus mr-2"></i>
                Add Registration Fee
            </button>
        </div>
    </div>

    @php
        $tableColumns = [
            [
                'key' => 'class_name',
                'label' => 'FOR CLASS',
                'sortable' => true,
                'render' => function($row) {
                    return '
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-emerald-50 flex items-center justify-center text-emerald-600 border border-emerald-100">
                            <i class="fas fa-graduation-cap text-[10px]"></i>
                        </div>
                        <span class="font-bold text-gray-700">' . e($row->class->name ?? 'N/A') . '</span>
                    </div>';
                }
            ],
            [
                'key' => 'amount',
                'label' => 'REGISTRATION AMOUNT',
                'sortable' => true,
                'render' => function($row) {
                    return '<div class="text-emerald-700 font-bold bg-emerald-50 px-3 py-1 rounded-lg inline-block border border-emerald-100">
                                <span class="text-xs mr-0.5 font-medium">₹</span>' . number_format($row->amount, 2) . '
                            </div>';
                }
            ],
            [
                'key' => 'created_at',
                'label' => 'DATE CONFIGURED',
                'sortable' => true,
                'render' => function($row) {
                    return '<div class="text-gray-500 text-sm">' . $row->created_at->format('M d, Y') . '</div>';
                }
            ],
        ];

        $tableActions = [
            [
                'type' => 'button',
                'icon' => 'fas fa-edit',
                'class' => 'text-emerald-600 hover:text-emerald-900 bg-emerald-50 hover:bg-emerald-100 p-2 rounded-lg transition-colors',
                'onclick' => function($row) {
                    $data = json_encode([
                        'id' => $row->id,
                        'class_id' => $row->class_id,
                        'class_name' => $row->class->name ?? 'N/A',
                        'amount' => $row->amount,
                    ]);
                    return "window.dispatchEvent(new CustomEvent('open-edit-registration-fee', { detail: $data }))";
                },
                'title' => 'Edit',
            ],
            [
                'type' => 'button',
                'icon' => 'fas fa-trash',
                'class' => 'text-red-600 hover:text-red-900 bg-red-50 hover:bg-red-100 p-2 rounded-lg transition-colors',
                'onclick' => function($row) {
                    return "window.dispatchEvent(new CustomEvent('open-delete-registration-fee', { detail: { id: " . $row->id . ", name: '" . addslashes($row->class->name ?? 'N/A') . "' } }))";
                },
                'title' => 'Delete',
            ],
        ];
    @endphp

    <div>
        <x-data-table 
            :columns="$tableColumns"
            :data="$fees"
            :actions="$tableActions"
            empty-message="No registration fees configured"
            empty-icon="fas fa-clipboard-check"
        >
            Registration Fee Rates
        </x-data-table>
    </div>

    <!-- Modal -->
    <x-modal name="registration-fee-modal" alpineTitle="editMode ? 'Edit Registration Rate' : 'Configure New Registration Rate'" maxWidth="md">
        <form @submit.prevent="submitForm" method="POST" class="p-0" novalidate>
            @csrf
            <template x-if="editMode">
                <input type="hidden" name="_method" value="PUT">
            </template>

            <div class="space-y-6">
                <!-- Class Selection -->
                <div class="space-y-2">
                    <label class="modal-label-premium">Target Class <span class="text-red-600 font-bold">*</span></label>
                    <div class="relative group">
                        <template x-if="!editMode">
                            <select 
                                name="class_id" 
                                x-model="formData.class_id"
                                @change="if(errors.class_id) delete errors.class_id"
                                class="modal-input-premium appearance-none pr-10"
                                :class="{'border-red-500 ring-red-500/10': errors.class_id}"
                            >
                                <option value="">Select a Class</option>
                                @foreach($classes as $class)
                                    <option value="{{ $class->id }}">{{ $class->name }}</option>
                                @endforeach
                            </select>
                        </template>
                        
                        <template x-if="editMode">
                            <div class="modal-input-premium bg-slate-50 border-slate-200 text-slate-500 font-bold flex items-center cursor-not-allowed pr-10">
                                <span x-text="formData.class_name"></span>
                            </div>
                        </template>

                        <div class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none transition-colors group-focus-within:text-emerald-500">
                            <i class="fas fa-school text-sm"></i>
                        </div>
                    </div>
                    <template x-if="errors.class_id">
                        <p class="modal-error-message" x-text="errors.class_id[0]"></p>
                    </template>
                </div>

                <!-- Amount -->
                <div class="space-y-2">
                    <label class="modal-label-premium">Fee Amount <span class="text-red-600 font-bold">*</span></label>
                    <div class="relative group">
                        <input 
                            type="number" 
                            name="amount" 
                            step="0.01"
                            x-model="formData.amount"
                            @input="if(errors.amount) delete errors.amount"
                            placeholder="0.00"
                            class="modal-input-premium pr-10"
                            :class="{'border-red-500 ring-red-500/10': errors.amount}"
                        >
                        <div class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-300 font-bold text-sm transition-colors group-focus-within:text-emerald-500">₹</div>
                    </div>
                    <template x-if="errors.amount">
                        <p class="modal-error-message" x-text="errors.amount[0]"></p>
                    </template>
                </div>
            </div>

            <x-slot name="footer">
                <button type="button" @click="closeModal()" class="btn-premium-cancel px-10">
                    Cancel
                </button>
                <button type="submit" :disabled="submitting" class="btn-premium-primary min-w-[160px] bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 shadow-emerald-100">
                    <template x-if="submitting">
                        <span class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin mr-3 inline-block"></span>
                    </template>
                    <span x-text="editMode ? 'Update Changes' : 'Set Fee Rate'"></span>
                </button>
            </x-slot>
        </form>
    </x-modal>
</div>

<!-- Confirmation Modal -->
<x-confirm-modal />

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('registrationFeeManager', () => ({
        editMode: false,
        feeId: null,
        submitting: false,
        errors: {},
        formData: {
            class_id: '',
            class_name: '',
            amount: ''
        },

        init() {
            window.addEventListener('open-edit-registration-fee', (e) => this.openEditModal(e.detail));
            window.addEventListener('open-delete-registration-fee', (e) => this.confirmDelete(e.detail));

            this.$nextTick(() => {
                if (typeof $ !== 'undefined') {
                    $('select[name="class_id"]').on('change', (e) => {
                        this.formData.class_id = e.target.value;
                        if (this.errors.class_id) delete this.errors.class_id;
                    });
                }
            });
        },

        async submitForm() {
            this.submitting = true;
            this.errors = {};
            
            const url = this.editMode 
                ? `{{ url('school/settings/registration-fee') }}/${this.feeId}` 
                : '{{ route('school.settings.registration-fee.store') }}';
            
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
                        _method: this.editMode ? 'PUT' : 'POST'
                    })
                });
                
                const result = await response.json();
                
                if (response.ok) {
                    if (window.Toast) {
                        window.Toast.fire({
                            icon: 'success',
                            title: result.message
                        });
                    }
                    setTimeout(() => window.location.reload(), 1000);
                } else if (response.status === 422) {
                    this.errors = result.errors || {};
                } else {
                    throw new Error(result.message || 'Something went wrong');
                }
            } catch (error) {
                if (window.Toast) {
                    window.Toast.fire({
                        icon: 'error',
                        title: error.message
                    });
                }
            } finally {
                this.submitting = false;
            }
        },

        openAddModal() {
            this.editMode = false;
            this.feeId = null;
            this.errors = {};
            this.formData = { class_id: '', class_name: '', amount: '' };
            this.$dispatch('open-modal', 'registration-fee-modal');
        },
        
        openEditModal(fee) {
            this.editMode = true;
            this.feeId = fee.id;
            this.errors = {};
            this.formData = {
                class_id: String(fee.class_id),
                class_name: fee.class_name,
                amount: fee.amount
            };
            this.$dispatch('open-modal', 'registration-fee-modal');
        },

        async confirmDelete(fee) {
            window.dispatchEvent(new CustomEvent('open-confirm-modal', {
                detail: {
                    title: 'Delete Registration Fee',
                    message: `Are you sure you want to delete the registration fee for "${fee.name}"? This action cannot be undone.`,
                    callback: async () => {
                        try {
                            const response = await fetch(`{{ url('school/settings/registration-fee') }}/${fee.id}`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify({ _method: 'DELETE' })
                            });
                            
                            if (response.ok) {
                                window.location.reload();
                            } else {
                                const result = await response.json();
                                if (window.Toast) {
                                    window.Toast.fire({
                                        icon: 'error',
                                        title: result.message || 'Delete failed'
                                    });
                                }
                            }
                        } catch (error) {
                            console.error('Delete Error:', error);
                        }
                    }
                }
            }));
        },

        closeModal() {
            this.$dispatch('close-modal', 'registration-fee-modal');
        }
    }));
});
</script>
@endpush
@endsection
