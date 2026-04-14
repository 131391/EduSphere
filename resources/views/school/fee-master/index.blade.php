@extends('layouts.school')

@section('title', 'Fee Master Configuration')

@section('content')
<div x-data="feeMasterManagement()">
    <!-- Header Section -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 mb-6 border border-emerald-100/50">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-gray-800 dark:text-white flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-emerald-100 flex items-center justify-center text-emerald-600">
                        <i class="fas fa-university text-xs"></i>
                    </div>
                    Fee Master Configuration
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Configure complex fee structures for different classes and installments</p>
            </div>
            <div class="flex gap-2">
                <button @click="openBulkModal()" 
                        class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-cyan-600 to-emerald-600 hover:from-cyan-700 hover:to-emerald-700 text-white text-sm font-semibold rounded-xl transition-all shadow-md hover:shadow-lg active:scale-95">
                    <i class="fas fa-layer-group mr-2"></i>
                    Bulk Assignment
                </button>
                <button @click="openMiscModal()" 
                        class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white text-sm font-semibold rounded-xl transition-all shadow-md hover:shadow-lg active:scale-95">
                    <i class="fas fa-plus mr-2"></i>
                    Add Misc Fee
                </button>
            </div>
        </div>
    </div>

    @php
        $tableColumns = [
            [
                'key' => 'class_name',
                'label' => 'CLASS / BATCH',
                'sortable' => true,
                'render' => function($row) {
                    return '
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-emerald-50 flex items-center justify-center text-emerald-600 border border-emerald-100 shadow-sm">
                            <i class="fas fa-school text-[10px]"></i>
                        </div>
                        <span class="font-bold text-gray-700">' . e($row->class->name) . '</span>
                    </div>';
                }
            ],
            [
                'key' => 'fee_name',
                'label' => 'FEE LABEL',
                'sortable' => true,
                'render' => function($row) {
                    return '<div class="text-gray-600 font-medium">' . e($row->feeName->name) . '</div>';
                }
            ],
            [
                'key' => 'fee_type',
                'label' => 'INSTALLMENT / TYPE',
                'sortable' => true,
                'render' => function($row) {
                    return '<span class="px-2 py-1 bg-gray-100 text-gray-600 text-[10px] font-bold rounded-lg uppercase tracking-tight border border-gray-200">' . e($row->feeType->name) . '</span>';
                }
            ],
            [
                'key' => 'amount',
                'label' => 'AMOUNT',
                'sortable' => true,
                'render' => function($row) {
                    return '<div class="text-emerald-700 font-bold bg-emerald-50 px-3 py-1 rounded-lg inline-block border border-emerald-100">
                                <span class="text-xs mr-0.5 font-medium">₹</span>' . number_format($row->amount, 2) . '
                            </div>';
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
                        'class_name' => $row->class->name,
                        'fee_name' => $row->feeName->name,
                        'fee_type' => $row->feeType->name,
                        'amount' => $row->amount,
                    ]);
                    return "window.dispatchEvent(new CustomEvent('open-edit-fee-master', { detail: $data }))";
                },
                'title' => 'Edit',
            ],
            [
                'type' => 'button',
                'icon' => 'fas fa-trash',
                'class' => 'text-red-600 hover:text-red-900 bg-red-50 hover:bg-red-100 p-2 rounded-lg transition-colors',
                'onclick' => function($row) {
                    return "window.dispatchEvent(new CustomEvent('open-delete-fee-master', { detail: { id: " . $row->id . ", name: '" . addslashes($row->feeName->name) . " for " . addslashes($row->class->name) . "' } }))";
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
            empty-message="No fee configurations set in Master"
            empty-icon="fas fa-university"
        >
            Fee Master List
        </x-data-table>
    </div>

    <!-- Bulk Assignment Modal -->
    <x-modal name="bulk-fee-master-modal" alpineTitle="'Bulk Fee Master Assignment'" maxWidth="2xl">
        <form id="bulk-fee-form" @submit.prevent="submitBulkForm" method="POST">
            @csrf
            <div class="space-y-6">
                <div class="grid grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="modal-label-premium">Target Class <span class="text-red-600 font-bold">*</span></label>
                        <div class="relative group">
                            <select x-model="bulkData.class_id" @change="clearError('class_id')" class="modal-input-premium appearance-none pr-10 hover:border-indigo-400 transition-colors" :class="{'border-red-500 ring-red-500/10': errors.class_id}">
                                <option value="">Select Class...</option>
                                @foreach($classes as $class)
                                    <option value="{{ $class->id }}">{{ $class->name }}</option>
                                @endforeach
                            </select>
                            <div class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none transition-colors group-focus-within:text-emerald-500">
                                <i class="fas fa-school text-sm"></i>
                            </div>
                        </div>
                        <template x-if="errors.class_id">
                            <p class="modal-error-message" x-text="errors.class_id[0]"></p>
                        </template>
                    </div>
                    <div class="space-y-2">
                        <label class="modal-label-premium">Installment Type <span class="text-red-600 font-bold">*</span></label>
                        <div class="relative group">
                            <select x-model="bulkData.fee_type_id" @change="clearError('fee_type_id')" class="modal-input-premium appearance-none pr-10 hover:border-indigo-400 transition-colors" :class="{'border-red-500 ring-red-500/10': errors.fee_type_id}">
                                <option value="">Select Type...</option>
                                @foreach($feeTypes as $feeType)
                                    <option value="{{ $feeType->id }}">{{ $feeType->name }}</option>
                                @endforeach
                            </select>
                            <div class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none transition-colors group-focus-within:text-emerald-500">
                                <i class="fas fa-layer-group text-sm"></i>
                            </div>
                        </div>
                        <template x-if="errors.fee_type_id">
                            <p class="modal-error-message" x-text="errors.fee_type_id[0]"></p>
                        </template>
                    </div>
                </div>

                <div class="bg-slate-50 border border-slate-100 rounded-2xl overflow-hidden shadow-inner">
                    <div class="bg-indigo-50/50 px-6 py-3 border-b border-slate-200 flex items-center justify-between">
                        <span class="text-[10px] font-black text-indigo-400 uppercase tracking-widest">Fee Component</span>
                        <span class="text-[10px] font-black text-indigo-400 uppercase tracking-widest pr-16">Amount (₹)</span>
                    </div>
                    <div class="max-h-[320px] overflow-y-auto divide-y divide-slate-100 custom-scrollbar">
                        @foreach($feeNames as $feeName)
                        <div class="flex items-center px-6 py-3 hover:bg-white transition-colors group">
                            <div class="flex-1">
                                <span class="text-sm font-bold text-slate-700 group-hover:text-indigo-600 transition-colors">{{ $feeName->name }}</span>
                            </div>
                            <div class="w-40">
                                <div class="relative">
                                    <input 
                                        type="number" 
                                        x-model="bulkData.amounts[{{ $feeName->id }}]" 
                                        step="0.01" 
                                        min="0" 
                                        placeholder="0.00" 
                                        class="modal-input-premium !py-2 !px-3 !bg-white font-bold text-right text-slate-800"
                                    <div class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-300 font-bold text-xs transition-colors group-focus-within:text-emerald-500">₹</div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <x-slot name="footer">
                <button type="button" @click="closeModal('bulk-fee-master-modal')" class="btn-premium-cancel px-10">Cancel</button>
                <button type="submit" form="bulk-fee-form" :disabled="submitting" class="btn-premium-primary min-w-[180px] bg-indigo-600 hover:bg-indigo-700 shadow-indigo-200">
                    <template x-if="submitting"><span class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin mr-3 inline-block"></span></template>
                    <span x-text="submitting ? 'Processing...' : 'Assign Fees'"></span>
                </button>
            </x-slot>
        </form>
    </x-modal>

    <!-- Single Edit Modal -->
    <x-modal name="edit-fee-master-modal" alpineTitle="'Edit Fee Configuration'" maxWidth="2xl">
        <form id="edit-fee-form" @submit.prevent="submitEditForm" method="POST">
            @csrf
            <template x-if="editMode"><input type="hidden" name="_method" value="PUT"></template>
            <div class="space-y-6">
                <div class="p-5 bg-indigo-50/50 border border-indigo-100 rounded-2xl space-y-1.5 shadow-sm">
                    <div class="flex justify-between text-[10px] font-black text-indigo-400 uppercase tracking-widest">
                        <span x-text="editData.class_name"></span>
                        <span x-text="editData.fee_type"></span>
                    </div>
                    <div class="text-base font-black text-indigo-900" x-text="editData.fee_name"></div>
                </div>

                <div class="space-y-2">
                    <label class="modal-label-premium">Updated Fee Amount <span class="text-red-600 font-bold">*</span></label>
                    <div class="relative">
                        <input 
                            type="number" 
                            x-model="editData.amount" 
                            step="0.01" 
                            min="0"
                            class="modal-input-premium !pr-10 font-bold text-slate-800"
                        >
                        <div class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-300 font-bold text-sm transition-colors group-focus-within:text-emerald-500">₹</div>
                    </div>
                </div>
            </div>

            <x-slot name="footer">
                <button type="button" @click="closeModal('edit-fee-master-modal')" class="btn-premium-cancel px-10">Cancel</button>
                <button type="submit" form="edit-fee-form" :disabled="submitting" class="btn-premium-primary min-w-[180px] bg-indigo-600 hover:bg-indigo-700 shadow-indigo-200">
                    <template x-if="submitting"><span class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin mr-3 inline-block"></span></template>
                    <span x-text="submitting ? 'Updating...' : 'Save Rate Changes'"></span>
                </button>
            </x-slot>
        </form>
    </x-modal>

    <!-- Misc Modal -->
    <x-modal name="misc-fee-master-modal" alpineTitle="'Add Single Configuration'" maxWidth="2xl">
        <form id="misc-fee-form" @submit.prevent="submitMiscForm" method="POST">
            @csrf
            <div class="space-y-6">
                <div class="space-y-2">
                    <label class="modal-label-premium">Select Class <span class="text-red-600 font-bold">*</span></label>
                    <div class="relative group">
                        <select x-model="miscData.class_id" @change="clearError('class_id')" class="modal-input-premium appearance-none pr-10" :class="{'border-red-500 ring-red-500/10': errors.class_id}">
                            <option value="">Select Class...</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}">{{ $class->name }}</option>
                            @endforeach
                        </select>
                        <div class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none transition-colors group-focus-within:text-emerald-500">
                            <i class="fas fa-school text-sm"></i>
                        </div>
                    </div>
                    <template x-if="errors.class_id">
                        <p class="modal-error-message" x-text="errors.class_id[0]"></p>
                    </template>
                </div>

                <div class="grid grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="modal-label-premium">Fee Category <span class="text-red-600 font-bold">*</span></label>
                        <div class="relative group">
                            <select x-model="miscData.fee_name_id" @change="errors = {}" class="modal-input-premium appearance-none pr-10" :class="{'border-red-500 ring-red-500/10': errors.amounts}">
                                <option value="">Select Category...</option>
                                @foreach($feeNames as $fn)
                                    <option value="{{ $fn->id }}">{{ $fn->name }}</option>
                                @endforeach
                            </select>
                            <div class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none transition-colors group-focus-within:text-emerald-500">
                                <i class="fas fa-tag text-sm"></i>
                            </div>
                        </div>
                        <template x-if="errors.amounts">
                            <p class="modal-error-message" x-text="errors.amounts[0]"></p>
                        </template>
                    </div>
                    <div class="space-y-2">
                        <label class="modal-label-premium">Installment <span class="text-red-600 font-bold">*</span></label>
                        <div class="relative group">
                            <select x-model="miscData.fee_type_id" @change="clearError('fee_type_id')" class="modal-input-premium appearance-none pr-10" :class="{'border-red-500 ring-red-500/10': errors.fee_type_id}">
                                <option value="">Select Type...</option>
                                @foreach($feeTypes as $ft)
                                    <option value="{{ $ft->id }}">{{ $ft->name }}</option>
                                @endforeach
                            </select>
                            <div class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none transition-colors group-focus-within:text-emerald-500">
                                <i class="fas fa-layer-group text-sm"></i>
                            </div>
                        </div>
                        <template x-if="errors.fee_type_id">
                            <p class="modal-error-message" x-text="errors.fee_type_id[0]"></p>
                        </template>
                    </div>
                </div>

                <div class="space-y-2 pb-2">
                    <label class="modal-label-premium">Configuration Amount <span class="text-red-600 font-bold">*</span></label>
                    <div class="relative group">
                        <input type="number" x-model="miscData.amount" @input="errors = {}" placeholder="0.00" class="modal-input-premium pr-10 font-black text-slate-800" :class="{'border-red-500 ring-red-500/10': Object.keys(errors).some(k => k.startsWith('amounts.'))}">
                        <div class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-300 font-bold text-sm transition-colors group-focus-within:text-emerald-500">₹</div>
                    </div>
                    <template x-if="Object.keys(errors).some(k => k.startsWith('amounts.'))">
                        <p class="modal-error-message" x-text="errors[Object.keys(errors).find(k => k.startsWith('amounts.'))][0]"></p>
                    </template>
                </div>
            </div>

            <x-slot name="footer">
                <button type="button" @click="closeModal('misc-fee-master-modal')" class="btn-premium-cancel px-10">Cancel</button>
                <button type="submit" form="misc-fee-form" :disabled="submitting" class="btn-premium-primary min-w-[180px] bg-indigo-600 hover:bg-indigo-700 shadow-indigo-200">
                    <template x-if="submitting"><span class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin mr-3 inline-block"></span></template>
                    <span x-text="submitting ? 'Processing...' : 'Save Configuration'"></span>
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
    Alpine.data('feeMasterManagement', () => ({
        submitting: false,
        editMode: false,
        errors: {},
        bulkData: { class_id: '', fee_type_id: '', amounts: {} },
        miscData: { class_id: '', fee_type_id: '', fee_name_id: '', amount: '' },
        editData: { id: '', class_name: '', fee_name: '', fee_type: '', amount: '' },

        init() {
            window.addEventListener('open-edit-fee-master', (e) => this.openEditModal(e.detail));
            window.addEventListener('open-delete-fee-master', (e) => this.confirmDelete(e.detail));

            this.$nextTick(() => {
                if (typeof $ !== 'undefined') {
                    // Sync Bulk Modal Selects
                    $('select[x-model="bulkData.class_id"]').on('change', (e) => {
                        this.bulkData.class_id = e.target.value;
                        this.clearError('class_id');
                    });
                    $('select[x-model="bulkData.fee_type_id"]').on('change', (e) => {
                        this.bulkData.fee_type_id = e.target.value;
                        this.clearError('fee_type_id');
                    });

                    // Sync Misc Modal Selects
                    $('select[x-model="miscData.class_id"]').on('change', (e) => {
                        this.miscData.class_id = e.target.value;
                        this.clearError('class_id');
                    });
                    $('select[x-model="miscData.fee_name_id"]').on('change', (e) => {
                        this.miscData.fee_name_id = e.target.value;
                        this.errors = {};
                    });
                    $('select[x-model="miscData.fee_type_id"]').on('change', (e) => {
                        this.miscData.fee_type_id = e.target.value;
                        this.clearError('fee_type_id');
                    });
                }
            });
        },

        async submitBulkForm() {
            if (this.submitting) return;
            this.submitting = true;
            this.errors = {};
            try {
                const response = await fetch('{{ route('school.fee-master.store') }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify(this.bulkData)
                });
                const result = await response.json();
                if (response.ok) {
                    if (window.Toast) window.Toast.fire({ icon: 'success', title: result.message });
                    setTimeout(() => window.location.reload(), 800);
                } else if (response.status === 422) {
                    this.errors = result.errors || {};
                } else { throw new Error(result.message || 'Operation failed'); }
            } catch (e) {
                if (window.Toast) window.Toast.fire({ icon: 'error', title: e.message });
            } finally { this.submitting = false; }
        },

        async submitEditForm() {
            if (this.submitting) return;
            this.submitting = true;
            this.errors = {};
            try {
                const response = await fetch(`/school/fee-master/${this.editData.id}`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ ...this.editData, _method: 'PUT' })
                });
                const result = await response.json();
                if (response.ok) {
                    if (window.Toast) window.Toast.fire({ icon: 'success', title: result.message });
                    setTimeout(() => window.location.reload(), 800);
                } else if (response.status === 422) {
                    this.errors = result.errors || {};
                } else { throw new Error(result.message || 'Update failed'); }
            } catch (e) {
                if (window.Toast) window.Toast.fire({ icon: 'error', title: e.message });
            } finally { this.submitting = false; }
        },

        async submitMiscForm() {
            if (this.submitting) return;
            // Map misc data to the bulk format for the existing controller logic
            const payload = {
                class_id: this.miscData.class_id,
                fee_type_id: this.miscData.fee_type_id,
                amounts: { [this.miscData.fee_name_id]: this.miscData.amount }
            };
            this.submitting = true;
            this.errors = {};
            try {
                const response = await fetch('{{ route('school.fee-master.store') }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify(payload)
                });
                const result = await response.json();
                if (response.ok) {
                    if (window.Toast) window.Toast.fire({ icon: 'success', title: result.message });
                    setTimeout(() => window.location.reload(), 800);
                } else if (response.status === 422) {
                    this.errors = result.errors || {};
                } else { throw new Error(result.message || 'Failed to save'); }
            } catch (e) {
                if (window.Toast) window.Toast.fire({ icon: 'error', title: e.message });
            } finally { this.submitting = false; }
        },

        clearError(field) {
            if (this.errors[field]) {
                delete this.errors[field];
            }
        },

        openBulkModal() { this.errors = {}; this.bulkData = { class_id: '', fee_type_id: '', amounts: {} }; this.$dispatch('open-modal', 'bulk-fee-master-modal'); },
        openMiscModal() { this.errors = {}; this.miscData = { class_id: '', fee_type_id: '', fee_name_id: '', amount: '' }; this.$dispatch('open-modal', 'misc-fee-master-modal'); },
        openEditModal(fee) { this.errors = {}; this.editMode = true; this.editData = { ...fee }; this.$dispatch('open-modal', 'edit-fee-master-modal'); },
        
        async confirmDelete(fee) {
            window.dispatchEvent(new CustomEvent('open-confirm-modal', {
                detail: {
                    title: 'Delete Fee Configuration',
                    message: `Are you sure you want to delete the fee configuration for "${fee.name}"? This action cannot be undone and may affect billing reports.`,
                    callback: async () => {
                        try {
                            const response = await fetch(`/school/fee-master/${fee.id}`, {
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

        closeModal(name) { this.$dispatch('close-modal', name); }
    }));
});
</script>
<style>
.custom-scrollbar::-webkit-scrollbar { width: 5px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #cbd5e1; }
</style>
@endpush
@endsection
