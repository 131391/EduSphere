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
                    $encoded = base64_encode(json_encode([
                        'id' => $row->id,
                        'class_name' => $row->class->name,
                        'fee_name' => $row->feeName->name,
                        'fee_type' => $row->feeType->name,
                        'amount' => $row->amount,
                    ]));
                    return "window.dispatchEvent(new CustomEvent('open-edit-fee-master', { detail: JSON.parse(atob('$encoded')) }))";
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

    <div x-on:open-edit-fee-master.window="openEditModal($event.detail)" 
         x-on:open-delete-fee-master.window="confirmDelete($event.detail)">
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
    <x-modal name="bulk-fee-master-modal" title="Bulk Fee Assignment" maxWidth="4xl">
        <form @submit.prevent="submitBulkForm" method="POST" class="p-0">
            @csrf
            <div class="px-8 py-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <div>
                        <label class="block text-sm font-semibold text-gray-800 mb-1.5 ml-1">Select Class <span class="text-red-500">*</span></label>
                        <select x-model="bulkData.class_id" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all font-medium text-gray-700">
                            <option value="">Select Class Name</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}">{{ $class->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-800 mb-1.5 ml-1">Installment Type <span class="text-red-500">*</span></label>
                        <select x-model="bulkData.fee_type_id" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all font-medium text-gray-700">
                            <option value="">Select Fee Type</option>
                            @foreach($feeTypes as $feeType)
                                <option value="{{ $feeType->id }}">{{ $feeType->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="border border-gray-100 rounded-2xl overflow-hidden shadow-sm">
                    <div class="bg-gray-50/80 px-6 py-3 border-b border-gray-100 flex items-center justify-between">
                        <span class="text-xs font-bold text-gray-400 uppercase tracking-widest">Fee Particulars</span>
                        <span class="text-xs font-bold text-gray-400 uppercase tracking-widest pr-20">Amount (₹)</span>
                    </div>
                    <div class="max-h-[350px] overflow-y-auto divide-y divide-gray-50 custom-scrollbar">
                        @foreach($feeNames as $feeName)
                        <div class="flex items-center px-6 py-4 hover:bg-emerald-50/30 transition-colors group">
                            <div class="flex-1">
                                <span class="text-sm font-bold text-gray-700 group-hover:text-emerald-700 transition-colors">{{ $feeName->name }}</span>
                            </div>
                            <div class="w-48">
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none text-gray-300">
                                        <span class="text-xs">₹</span>
                                    </div>
                                    <input 
                                        type="number" 
                                        x-model="bulkData.amounts[{{ $feeName->id }}]" 
                                        step="0.01" 
                                        min="0" 
                                        placeholder="0.00" 
                                        class="w-full pl-7 pr-3 py-2 bg-white border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all text-sm font-semibold text-gray-700"
                                    >
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="px-8 py-6 bg-gray-50/50 flex items-center justify-end gap-3 rounded-b-lg border-t border-gray-100">
                <button type="button" @click="closeModal('bulk-fee-master-modal')" class="px-5 py-2.5 text-sm font-bold text-gray-500 hover:text-gray-700 hover:bg-gray-100/50 rounded-xl">Cancel</button>
                <button type="submit" :disabled="submitting" class="px-8 py-2.5 bg-gradient-to-r from-emerald-600 to-teal-600 text-white text-sm font-bold rounded-xl shadow-lg flex items-center gap-2">
                    <template x-if="submitting"><span class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span></template>
                    <span x-text="submitting ? 'Applying...' : 'Apply Configurations'"></span>
                </button>
            </div>
        </form>
    </x-modal>

    <!-- Single Edit Modal -->
    <x-modal name="edit-fee-master-modal" title="Edit Fee Rate" maxWidth="md">
        <form @submit.prevent="submitEditForm" method="POST" class="p-0">
            @csrf
            <template x-if="editMode"><input type="hidden" name="_method" value="PUT"></template>
            <div class="px-8 py-8 space-y-5">
                <div class="bg-emerald-50 py-3 px-4 rounded-xl border border-emerald-100 space-y-1">
                    <div class="flex justify-between text-[10px] font-bold text-emerald-400 uppercase tracking-widest">
                        <span x-text="editData.class_name"></span>
                        <span x-text="editData.fee_type"></span>
                    </div>
                    <div class="text-sm font-bold text-emerald-800" x-text="editData.fee_name"></div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-800 mb-1.5 ml-1">Updated Amount <span class="text-red-500">*</span></label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                            <span class="text-sm font-bold">₹</span>
                        </div>
                        <input 
                            type="number" 
                            x-model="editData.amount" 
                            step="0.01" 
                            min="0"
                            class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 focus:bg-white transition-all font-medium text-gray-700"
                        >
                    </div>
                </div>
            </div>

            <div class="px-8 py-6 bg-gray-50/50 flex items-center justify-end gap-3 rounded-b-lg border-t border-gray-100">
                <button type="button" @click="closeModal('edit-fee-master-modal')" class="px-5 py-2.5 text-sm font-bold text-gray-500 hover:text-gray-700 rounded-xl">Cancel</button>
                <button type="submit" :disabled="submitting" class="px-8 py-2.5 bg-emerald-600 text-white text-sm font-bold rounded-xl shadow-lg flex items-center gap-2">
                    <template x-if="submitting"><span class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span></template>
                    <span x-text="submitting ? 'Updating...' : 'Save Changes'"></span>
                </button>
            </div>
        </form>
    </x-modal>

    <!-- Misc Modal (Legacy wrapper for same store logic) -->
    <x-modal name="misc-fee-master-modal" title="Add Single Configuration" maxWidth="md">
        <form @submit.prevent="submitMiscForm" method="POST" class="p-0">
            @csrf
            <div class="px-8 py-8 space-y-6">
                <div>
                    <label class="block text-sm font-semibold text-gray-800 mb-1.5 ml-1">Class</label>
                    <select x-model="miscData.class_id" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 transition-all font-medium text-gray-700">
                        <option value="">Select Class</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}">{{ $class->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-800 mb-1.5 ml-1">Fee Type</label>
                    <select x-model="miscData.fee_type_id" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 transition-all font-medium text-gray-700">
                        <option value="">Select Type</option>
                        @foreach($feeTypes as $ft)
                            <option value="{{ $ft->id }}">{{ $ft->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-800 mb-1.5 ml-1">Fee Name</label>
                    <select x-model="miscData.fee_name_id" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 transition-all font-medium text-gray-700">
                        <option value="">Select Name</option>
                        @foreach($feeNames as $fn)
                            <option value="{{ $fn->id }}">{{ $fn->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-800 mb-1.5 ml-1">Amount</label>
                    <input type="number" x-model="miscData.amount" placeholder="0.00" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 transition-all font-medium text-gray-700">
                </div>
            </div>
            <div class="px-8 py-6 flex items-center justify-end gap-3 bg-gray-50/50 rounded-b-lg">
                <button type="button" @click="closeModal('misc-fee-master-modal')" class="text-sm font-bold text-gray-500">Cancel</button>
                <button type="submit" class="px-8 py-2.5 bg-emerald-600 text-white text-sm font-bold rounded-xl shadow-lg">Save Config</button>
            </div>
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
        bulkData: { class_id: '', fee_type_id: '', amounts: {} },
        miscData: { class_id: '', fee_type_id: '', fee_name_id: '', amount: '' },
        editData: { id: '', class_name: '', fee_name: '', fee_type: '', amount: '' },

        async submitBulkForm() {
            this.submitting = true;
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
                } else { throw new Error(result.message || 'Operation failed'); }
            } catch (e) {
                if (window.Toast) window.Toast.fire({ icon: 'error', title: e.message });
            } finally { this.submitting = false; }
        },

        async submitEditForm() {
            this.submitting = true;
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
                } else { throw new Error(result.message || 'Update failed'); }
            } catch (e) {
                if (window.Toast) window.Toast.fire({ icon: 'error', title: e.message });
            } finally { this.submitting = false; }
        },

        async submitMiscForm() {
            // Map misc data to the bulk format for the existing controller logic
            const payload = {
                class_id: this.miscData.class_id,
                fee_type_id: this.miscData.fee_type_id,
                amounts: { [this.miscData.fee_name_id]: this.miscData.amount }
            };
            this.submitting = true;
            try {
                const response = await fetch('{{ route('school.fee-master.store') }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify(payload)
                });
                if (response.ok) window.location.reload();
            } catch (e) { alert('Failed to save'); }
            finally { this.submitting = false; }
        },

        openBulkModal() { this.bulkData = { class_id: '', fee_type_id: '', amounts: {} }; this.$dispatch('open-modal', 'bulk-fee-master-modal'); },
        openMiscModal() { this.miscData = { class_id: '', fee_type_id: '', fee_name_id: '', amount: '' }; this.$dispatch('open-modal', 'misc-fee-master-modal'); },
        openEditModal(fee) { this.editMode = true; this.editData = { ...fee }; this.$dispatch('open-modal', 'edit-fee-master-modal'); },
        
        async confirmDelete(fee) {
            if (window.confirm(`Delete fee config for "${fee.name}"?`)) {
                await fetch(`/school/fee-master/${fee.id}`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ _method: 'DELETE' })
                });
                window.location.reload();
            }
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
