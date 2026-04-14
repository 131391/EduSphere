@extends('layouts.school')

@section('title', 'Fee Waivers')

@section('content')
<div x-data="waiverManagement">
    <!-- Header Section -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 mb-6 border border-emerald-100/50">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-gray-800 dark:text-white flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-emerald-100 flex items-center justify-center text-emerald-600">
                        <i class="fas fa-hand-holding-heart text-xs"></i>
                    </div>
                    Fee Waivers & Concessions
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Manage and track student fee reductions and scholarship adjustments</p>
            </div>
            <button @click="openAddModal" 
                    class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white text-sm font-semibold rounded-xl transition-all shadow-md hover:shadow-lg active:scale-95">
                <i class="fas fa-plus mr-2"></i>
                Apply Waiver
            </button>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="bg-white dark:bg-gray-800 p-4 rounded-xl shadow-sm border border-gray-100 mb-6">
        <form action="{{ route('school.waivers.index') }}" method="GET" class="flex flex-wrap items-end gap-4">
            <div class="flex-1 min-w-[280px]">
                <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1.5 ml-1">Search Student</label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400 group-focus-within:text-emerald-500 transition-colors">
                        <i class="fas fa-search text-xs"></i>
                    </div>
                    <select name="student_id" class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all text-sm font-medium text-gray-700 appearance-none">
                        <option value="">All Students</option>
                        @foreach($students as $student)
                            <option value="{{ $student->id }}" {{ request('student_id') == $student->id ? 'selected' : '' }}>
                                {{ $student->full_name }} ({{ $student->admission_no }})
                            </option>
                        @endforeach
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-gray-400">
                        <i class="fas fa-chevron-down text-[10px]"></i>
                    </div>
                </div>
            </div>
            <button type="submit" class="px-8 py-2.5 bg-emerald-600 text-white rounded-xl hover:bg-emerald-700 transition-all font-bold text-sm shadow-md active:scale-95">
                Apply Filters
            </button>
        </form>
    </div>

    @php
        $tableColumns = [
            [
                'key' => 'student',
                'label' => 'STUDENT DETAILS',
                'render' => function($row) {
                    return '
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-full bg-emerald-50 flex items-center justify-center text-emerald-600 border border-emerald-100 font-bold text-xs ring-2 ring-emerald-50 ring-offset-1">
                            ' . substr($row->student->full_name, 0, 1) . '
                        </div>
                        <div>
                            <div class="text-sm font-bold text-gray-700 uppercase tracking-tight">' . e($row->student->full_name) . '</div>
                            <div class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">' . e($row->student->admission_no) . '</div>
                        </div>
                    </div>';
                }
            ],
            [
                'key' => 'waiver_details',
                'label' => 'CONCESSION',
                'render' => function($row) {
                    $percentLabel = $row->waiver_percentage ? '<span class="text-[9px] px-2 py-0.5 bg-emerald-100 text-emerald-700 rounded-md ml-2 font-bold">' . $row->waiver_percentage . '%</span>' : '';
                    return '<div>
                                <div class="flex items-center">
                                    <span class="text-sm font-bold text-emerald-600 tracking-tight">₹ ' . number_format($row->waiver_amount, 2) . '</span>
                                    ' . $percentLabel . '
                                </div>
                                <div class="text-[10px] text-gray-400 font-medium italic mt-0.5">Off base fee of ₹' . number_format($row->actual_fee, 2) . '</div>
                            </div>';
                }
            ],
            [
                'key' => 'period',
                'label' => 'PERIOD / SESSION',
                'render' => function($row) {
                    return '<div>
                                <div class="text-xs font-bold text-gray-700">' . e($row->academicYear->name) . '</div>
                                <div class="text-[10px] font-bold text-teal-600 uppercase tracking-widest mt-0.5">' . e($row->fee_period) . '</div>
                            </div>';
                }
            ],
            [
                'key' => 'reason',
                'label' => 'JUSTIFICATION',
                'render' => function($row) {
                    return '<div class="text-xs text-gray-500 italic max-w-xs truncate font-medium" title="' . e($row->reason) . '">' . e($row->reason) . '</div>';
                }
            ],
        ];

        $tableActions = [
            [
                'type' => 'button',
                'icon' => 'fas fa-trash',
                'class' => 'text-red-500 hover:text-red-700 bg-red-50 p-2 rounded-lg transition-colors',
                'onclick' => function($row) {
                    $name = addslashes($row->student->full_name);
                    return "window.dispatchEvent(new CustomEvent('open-delete-waiver', { detail: { id: " . $row->id . ", name: 'Waiver for {$name}' } }))";
                },
                'title' => 'Remove Waiver',
            ],
        ];
    @endphp

    <div>
        <x-data-table 
            :columns="$tableColumns"
            :data="$waivers"
            :actions="$tableActions"
            empty-message="No active fee waivers found"
            empty-icon="fas fa-hand-holding-heart"
        >
            Waivers List
        </x-data-table>
    </div>

    <!-- Apply Waiver Modal -->
    <x-modal name="waiver-modal" alpineTitle="'Apply Student Fee Waiver'" maxWidth="2xl">
        <form @submit.prevent="submitForm()" method="POST" novalidate>
            @csrf
            
            <div class="space-y-6">
                <!-- Target Selection -->
                <div class="space-y-2">
                    <label class="modal-label-premium">Target Student <span class="text-red-600 font-bold">*</span></label>
                    <div class="relative group">
                        <select x-model="formData.student_id" @change="clearError('student_id')" class="modal-input-premium appearance-none pr-10" :class="{'border-red-500 ring-red-500/10': errors.student_id}">
                            <option value="">Choose a student...</option>
                            @foreach($students as $student)
                                <option value="{{ $student->id }}">{{ $student->full_name }} ({{ $student->admission_no }})</option>
                            @endforeach
                        </select>
                        <div class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none transition-colors group-focus-within:text-emerald-500">
                            <i class="fas fa-user-graduate text-sm"></i>
                        </div>
                    </div>
                    <template x-if="errors.student_id">
                        <p class="modal-error-message" x-text="errors.student_id[0]"></p>
                    </template>
                </div>

                <div class="grid grid-cols-2 gap-6">
                    <!-- Academic Year -->
                    <div class="space-y-2">
                        <label class="modal-label-premium">Academic Session</label>
                        <div class="relative group">
                            <select x-model="formData.academic_year_id" class="modal-input-premium appearance-none pr-10 hover:border-indigo-400 transition-colors">
                                @foreach($academicYears as $year)
                                    <option value="{{ $year->id }}">{{ $year->name }}</option>
                                @endforeach
                            </select>
                            <div class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none transition-colors group-focus-within:text-emerald-500">
                                <i class="fas fa-calendar-check text-sm"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Fee Period -->
                    <div class="space-y-2">
                        <label class="modal-label-premium">Billing Period</label>
                        <div class="relative group">
                            <input type="text" x-model="formData.fee_period" placeholder="e.g., Monthly" class="modal-input-premium pr-10">
                            <div class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none transition-colors group-focus-within:text-emerald-500">
                                <i class="fas fa-clock text-sm"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Calculation Details -->
                <div class="p-6 bg-slate-50 border border-slate-100 rounded-2xl space-y-6 shadow-inner">
                    <div class="grid grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest pl-1">Standard Fee Amount</label>
                            <div class="relative group">
                                <input type="number" step="0.01" x-model.number="formData.actual_fee" @input="recalculate('actual')" class="modal-input-premium !bg-white !shadow-none pr-10 font-bold text-slate-800">
                                <div class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-300 font-bold text-xs">₹</div>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest pl-1">Tenure (Months)</label>
                            <div class="relative group">
                                <input type="number" x-model="formData.upto_months" class="modal-input-premium !bg-white !shadow-none font-bold text-slate-800">
                                <div class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-300 transition-colors group-focus-within:text-emerald-500">
                                    <i class="fas fa-info-circle text-xs"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="pt-6 border-t border-slate-200 grid grid-cols-1 gap-6">
                        <div class="flex items-center gap-6">
                            <div class="flex-1 space-y-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest pl-1">Reduction (Percentage)</label>
                                <div class="relative group">
                                    <input type="number" step="0.01" x-model.number="formData.waiver_percentage" @input="recalculate('percent')" class="modal-input-premium !bg-white !shadow-none font-black text-indigo-600">
                                    <div class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-300 font-bold text-xs">%</div>
                                </div>
                            </div>
                            <div class="pt-6 font-black text-slate-300 italic text-xs uppercase tracking-tighter">OR</div>
                            <div class="flex-1 space-y-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest pl-1">Reduction (Fixed Amount)</label>
                                <div class="relative group">
                                    <input type="number" step="0.01" x-model.number="formData.waiver_amount" @input="recalculate('amount')" class="modal-input-premium !bg-white !shadow-none pr-10 font-black text-emerald-600">
                                    <div class="absolute right-3.5 top-1/2 -translate-y-1/2 text-emerald-300 font-bold text-xs">₹</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Justification -->
                <div class="space-y-2">
                    <label class="modal-label-premium">Concession Justification <span class="text-red-600 font-bold">*</span></label>
                    <textarea x-model="formData.reason" @input="clearError('reason')" rows="3" placeholder="Define the eligibility criteria or scholarship details..." class="modal-input-premium resize-none !h-auto" :class="{'border-red-500 ring-red-500/10': errors.reason}"></textarea>
                    <template x-if="errors.reason">
                        <p class="modal-error-message" x-text="errors.reason[0]"></p>
                    </template>
                </div>
            </div>

            <x-slot name="footer">
                <button type="button" @click="closeModal()" class="btn-premium-cancel px-10">
                    Cancel
                </button>
                <button type="button" @click="submitForm()" :disabled="submitting" class="btn-premium-primary min-w-[180px] bg-indigo-600 hover:bg-indigo-700 shadow-indigo-200">
                    <template x-if="submitting">
                        <span class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin mr-3 inline-block"></span>
                    </template>
                    <span x-text="submitting ? 'Processing...' : 'Issue Waiver'"></span>
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
    Alpine.data('waiverManagement', () => ({
        submitting: false,
        errors: {},
        formData: {
            student_id: '',
            academic_year_id: '{{ $academicYear->id ?? '' }}',
            fee_period: 'Monthly',
            actual_fee: 0,
            upto_months: 12,
            waiver_percentage: 0,
            waiver_amount: 0,
            reason: ''
        },

        init() {
            window.addEventListener('open-delete-waiver', (e) => this.confirmDelete(e.detail));
            
            // Auto-clear errors on value change
            this.$watch('formData.student_id', () => this.clearError('student_id'));
            this.$watch('formData.reason', () => this.clearError('reason'));
        },

        async submitForm() {
            if (this.submitting) return;
            this.submitting = true;
            this.errors = {};
            
            try {
                const response = await fetch('{{ route('school.waivers.store') }}', {
                    method: 'POST',
                    headers: { 
                        'Content-Type': 'application/json', 
                        'Accept': 'application/json', 
                        'X-CSRF-TOKEN': '{{ csrf_token() }}' 
                    },
                    body: JSON.stringify(this.formData)
                });
                
                const result = await response.json();
                
                if (response.ok) {
                    if (window.Toast) window.Toast.fire({ icon: 'success', title: result.message });
                    setTimeout(() => window.location.reload(), 800);
                } else if (response.status === 422) {
                    this.errors = result.errors || {};
                } else { 
                    throw new Error(result.message || 'Failed to apply waiver'); 
                }
            } catch (e) {
                if (window.Toast) window.Toast.fire({ icon: 'error', title: e.message });
            } finally { 
                this.submitting = false; 
            }
        },

        clearError(field) {
            if (this.errors && this.errors[field]) {
                delete this.errors[field];
                this.errors = { ...this.errors };
            }
        },

        recalculate(source) {
            const actual = parseFloat(this.formData.actual_fee) || 0;
            if (source === 'percent' || source === 'actual') {
                const percent = parseFloat(this.formData.waiver_percentage) || 0;
                this.formData.waiver_amount = parseFloat((actual * percent / 100).toFixed(2));
            } else if (source === 'amount') {
                const amount = parseFloat(this.formData.waiver_amount) || 0;
                this.formData.waiver_percentage = actual > 0 ? parseFloat((amount / actual * 100).toFixed(2)) : 0;
            }
        },

        openAddModal() {
            this.errors = {};
            this.formData = {
                student_id: '',
                academic_year_id: '{{ $academicYear->id ?? '' }}',
                fee_period: 'Monthly',
                actual_fee: 0,
                upto_months: 12,
                waiver_percentage: 0,
                waiver_amount: 0,
                reason: ''
            };
            this.$dispatch('open-modal', 'waiver-modal');
        },

        async confirmDelete(waiver) {
            window.dispatchEvent(new CustomEvent('open-confirm-modal', {
                detail: {
                    title: 'Delete Fee Waiver',
                    message: `Are you sure you want to remove the waiver for "${waiver.name}"? This action cannot be undone.`,
                    callback: async () => {
                        try {
                            const response = await fetch(`/school/waivers/${waiver.id}`, {
                                method: 'POST',
                                headers: { 
                                    'Content-Type': 'application/json', 
                                    'Accept': 'application/json', 
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}' 
                                },
                                body: JSON.stringify({ _method: 'DELETE' })
                            });
                            
                            if (response.ok) {
                                const result = await response.json();
                                if (window.Toast) window.Toast.fire({ icon: 'success', title: result.message });
                                setTimeout(() => window.location.reload(), 800);
                            } else {
                                const result = await response.json();
                                if (window.Toast) {
                                    window.Toast.fire({
                                        icon: 'error',
                                        title: result.message || 'Deletion failed'
                                    });
                                }
                            }
                        } catch (e) { 
                            console.error('Delete Error:', e);
                        }
                    }
                }
            }));
        },

        closeModal() { 
            this.$dispatch('close-modal', 'waiver-modal'); 
        }
    }));
});
</script>
@endpush
@endsection
