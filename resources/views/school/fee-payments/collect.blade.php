@extends('layouts.school')

@section('title', 'Process Fee Collection - ' . $student->full_name)

@section('content')
<div x-data="feeCollectionManager()">
    <!-- Header/Breadcrumb -->
    <div class="mb-6 flex flex-col sm:flex-row items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <a href="{{ route('school.fee-payments.index') }}" class="w-10 h-10 rounded-xl bg-white border border-gray-200 flex items-center justify-center text-gray-400 hover:text-emerald-600 hover:border-emerald-200 transition-all shadow-sm group">
                <i class="fas fa-chevron-left group-hover:-translate-x-0.5 transition-transform"></i>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-800 tracking-tight">Process Fee Payment</h1>
                <nav class="flex text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-0.5">
                    <span>Financial</span>
                    <span class="mx-2 text-gray-300">/</span>
                    <span class="text-emerald-600">Collection Portal</span>
                </nav>
            </div>
        </div>
        
        <div class="bg-white px-5 py-3 rounded-2xl border border-gray-100 shadow-sm flex items-center gap-4">
            <div class="flex flex-col items-end">
                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-tighter">Current Balance</span>
                <span class="text-lg font-black text-gray-800">
                    <span class="text-xs font-bold mr-0.5">₹</span>{{ number_format($pendingFees->sum('due_amount'), 2) }}
                </span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600 border border-emerald-100">
                <i class="fas fa-wallet text-sm"></i>
            </div>
        </div>
    </div>

    <!-- Student Quick Profile -->
    <div class="bg-gradient-to-r from-emerald-600 to-teal-700 rounded-2xl p-6 mb-8 shadow-lg shadow-emerald-100 flex items-center gap-6 relative overflow-hidden">
        <div class="absolute top-0 right-0 p-8 opacity-10 pointer-events-none">
            <i class="fas fa-user-graduate text-9xl"></i>
        </div>
        <div class="w-16 h-16 rounded-2xl bg-white/20 backdrop-blur-md flex items-center justify-center text-white border border-white/30 text-2xl font-black shadow-inner">
            {{ substr($student->first_name, 0, 1) }}
        </div>
        <div>
            <h2 class="text-2xl font-bold text-white tracking-tight">{{ $student->full_name }}</h2>
            <div class="flex gap-4 mt-1">
                <span class="inline-flex items-center text-[11px] font-bold text-emerald-50/80 uppercase tracking-widest bg-white/10 px-3 py-1 rounded-full backdrop-blur-sm">
                    <i class="fas fa-barcode mr-1.5 opacity-60"></i> ADM: {{ $student->admission_no }}
                </span>
                <span class="inline-flex items-center text-[11px] font-bold text-emerald-50/80 uppercase tracking-widest bg-white/10 px-3 py-1 rounded-full backdrop-blur-sm">
                    <i class="fas fa-graduation-cap mr-1.5 opacity-60"></i> {{ $student->class->name ?? 'N/A' }}
                </span>
            </div>
        </div>
    </div>

    <form @submit.prevent="submitPayment" method="POST" id="payment-form" class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        @csrf
        <input type="hidden" name="academic_year_id" value="{{ $academicYear->id ?? '' }}">

        <!-- Fees Selection Area -->
        <div class="lg:col-span-8 space-y-6">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-50 flex items-center justify-between bg-gray-50/30">
                    <h3 class="text-sm font-bold text-gray-500 uppercase tracking-widest">Fee Heads Breakdown</h3>
                    <div class="flex items-center gap-2">
                        <span class="text-[10px] font-bold text-gray-400 uppercase">Select All</span>
                        <input type="checkbox" @change="toggleAll" x-model="allSelected" class="rounded w-4 h-4 text-emerald-600 focus:ring-emerald-500/20 border-gray-200">
                    </div>
                </div>
                
                <div class="divide-y divide-gray-50">
                    @forelse($pendingFees as $fee)
                    <div class="p-6 flex flex-col md:flex-row md:items-center gap-6 group hover:bg-emerald-50/20 transition-all" 
                         :class="{'bg-emerald-50/50': selections['{{ $fee->id }}']}">
                        <div class="flex items-center gap-4 flex-1">
                            <input type="checkbox" x-model="selections['{{ $fee->id }}']" @change="recalculateTotal()" 
                                   class="rounded-lg w-5 h-5 text-emerald-600 focus:ring-emerald-500/20 border-gray-200 transition-all">
                            
                            <div class="w-10 h-10 rounded-xl bg-gray-100 flex items-center justify-center text-gray-400 group-hover:bg-emerald-100 group-hover:text-emerald-600 transition-all border border-transparent group-hover:border-emerald-100">
                                <i class="fas fa-file-invoice-dollar text-sm"></i>
                            </div>
                            
                            <div>
                                <div class="text-sm font-bold text-gray-700">{{ $fee->feeName->name ?? 'Registration Fee' }}</div>
                                <div class="flex items-center gap-3 mt-0.5">
                                    <span class="text-[10px] font-bold text-teal-600 uppercase">{{ $fee->feeType->name ?? 'One-Time' }}</span>
                                    <span class="w-1 h-1 rounded-full bg-gray-200"></span>
                                    <span class="text-[10px] font-medium text-gray-400">{{ $fee->fee_period }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-8 justify-between md:justify-end">
                            <div class="text-right">
                                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-tighter block mb-0.5">Outstanding</span>
                                <span class="text-sm font-black text-gray-800">₹ {{ number_format($fee->due_amount, 2) }}</span>
                            </div>

                            <div class="w-36 relative">
                                <input 
                                    type="number" 
                                    step="0.01" 
                                    x-model="amounts['{{ $fee->id }}']" 
                                    @input="recalculateTotal()"
                                    :disabled="!selections['{{ $fee->id }}']"
                                    max="{{ $fee->due_amount }}"
                                    class="w-full pr-7 pl-3 py-2.5 bg-gray-50 border border-gray-100 rounded-xl focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 focus:bg-white transition-all text-sm font-bold text-gray-700 disabled:opacity-30 disabled:bg-gray-100/50"
                                >
                                <div class="absolute inset-y-0 right-3 flex items-center pointer-events-none text-gray-300 font-bold text-xs">₹</div>
                            </div>
                        </div>
                    </div>
                    @empty
                        <div class="p-12 text-center">
                            <i class="fas fa-check-circle text-4xl text-emerald-100 mb-4"></i>
                            <p class="text-sm font-bold text-gray-400">Perfect! No pending fees for this year.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Checkout Sidebar -->
        <div class="lg:col-span-4 sticky top-6">
            <div class="bg-white rounded-3xl shadow-xl shadow-gray-200/50 border border-gray-100 p-8 space-y-8">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 rounded-2xl bg-indigo-600 flex items-center justify-center text-white shadow-lg shadow-indigo-100">
                        <i class="fas fa-shopping-cart text-sm"></i>
                    </div>
                    <h3 class="text-lg font-black text-gray-800 tracking-tight">Summary</h3>
                </div>

                <div class="space-y-4">
                    <div class="flex justify-between items-end pb-4 border-b border-dashed border-gray-100">
                        <span class="text-xs font-bold text-gray-400 uppercase tracking-widest">Collection Total</span>
                        <div class="text-right">
                            <span class="text-2xl font-black text-indigo-700" x-text="'₹ ' + total.toLocaleString('en-IN', {minimumFractionDigits: 2})"></span>
                        </div>
                    </div>

                    <div class="space-y-5 pt-4">
                        <div>
                            <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1.5 ml-1">Payment Date</label>
                            <input type="date" x-model="payment_date" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-100 rounded-xl focus:border-indigo-500 transition-all font-bold text-gray-700">
                        </div>

                        <div>
                            <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1.5 ml-1">Payment Channel</label>
                            <select x-model="payment_method_id" required class="no-select2 w-full px-4 py-2.5 bg-gray-50 border border-gray-100 rounded-xl focus:border-indigo-500 transition-all font-bold text-gray-700 appearance-none">
                                @foreach($paymentMethods as $method)
                                    <option value="{{ $method->id }}">{{ $method->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div x-show="payment_method_id != 1">
                            <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1.5 ml-1">Ref ID / Cheque #</label>
                            <input type="text" x-model="transaction_id" placeholder="Optional reference" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-100 rounded-xl focus:border-indigo-500 transition-all font-bold text-gray-700">
                        </div>

                        <div>
                            <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1.5 ml-1">Staff Remarks</label>
                            <textarea x-model="remarks" rows="2" class="w-full px-4 py-3 bg-gray-50 border border-gray-100 rounded-xl focus:border-indigo-500 transition-all font-medium text-gray-700 resize-none"></textarea>
                        </div>
                    </div>

                    <button 
                        type="submit" 
                        :disabled="total <= 0 || submitting"
                        class="w-full bg-gradient-to-r from-indigo-600 to-blue-700 hover:from-indigo-700 hover:to-blue-800 text-white font-black py-4 rounded-2xl shadow-xl shadow-indigo-100 transition-all active:scale-[0.98] disabled:opacity-30 disabled:grayscale mt-4 flex items-center justify-center gap-3 tracking-wider uppercase text-sm"
                    >
                        <template x-if="submitting"><span class="w-5 h-5 border-2 border-white/30 border-t-white rounded-full animate-spin"></span></template>
                        <i x-show="!submitting" class="fas fa-check-circle"></i>
                        <span x-text="submitting ? 'Processing...' : 'Complete Payment'"></span>
                    </button>
                    
                    <p class="text-[10px] text-center text-gray-400 font-bold uppercase tracking-tight opacity-60">Receipt will be generated automatically</p>
                </div>
            </div>
        </div>
    </form>

    <!-- Success Feedback Overlay (Internal Modal) -->
    <template x-if="showSuccessOverlay">
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/40 backdrop-blur-sm">
            <div class="bg-white rounded-3xl p-10 max-w-sm w-full text-center shadow-2xl animate-bounce-in">
                <div class="w-20 h-20 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-check text-4xl"></i>
                </div>
                <h3 class="text-2xl font-black text-gray-800 mb-2">Payment Recorded!</h3>
                <p class="text-gray-500 mb-8 text-sm">The fee collection has been processed and recorded successfully.</p>
                <div class="flex flex-col gap-3">
                    <a :href="receiptUrl" target="_blank" class="w-full bg-emerald-600 text-white font-bold py-3 rounded-xl shadow-lg ring-4 ring-emerald-500/10">
                        <i class="fas fa-print mr-2"></i> Print Receipt
                    </a>
                    <button @click="window.location.href='{{ route('school.fee-payments.index') }}'" class="w-full bg-gray-100 text-gray-600 font-bold py-3 rounded-xl hover:bg-gray-200">
                        Return to Search
                    </button>
                </div>
            </div>
        </div>
    </template>
</div>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('feeCollectionManager', () => ({
        submitting: false,
        allSelected: false,
        total: 0,
        selections: {},
        amounts: {},
        payment_date: '{{ date('Y-m-d') }}',
        payment_method_id: '{{ $paymentMethods->first()->id ?? '' }}',
        transaction_id: '',
        remarks: '',
        showSuccessOverlay: false,
        receiptUrl: '#',

        init() {
            @foreach($pendingFees as $fee)
                this.selections['{{ $fee->id }}'] = false;
                this.amounts['{{ $fee->id }}'] = {{ $fee->due_amount }};
            @endforeach
        },

        toggleAll() {
            Object.keys(this.selections).forEach(id => {
                this.selections[id] = this.allSelected;
            });
            this.recalculateTotal();
        },

        recalculateTotal() {
            let runningTotal = 0;
            Object.keys(this.selections).forEach(id => {
                if (this.selections[id]) {
                    runningTotal += parseFloat(this.amounts[id] || 0);
                }
            });
            this.total = runningTotal;
            this.allSelected = Object.values(this.selections).every(v => v);
        },

        async submitPayment() {
            const selectedItems = Object.keys(this.selections)
                .filter(id => this.selections[id])
                .map(id => ({
                    fee_id: id,
                    amount: this.amounts[id]
                }));

            if (!selectedItems.length) return;

            this.submitting = true;
            try {
                const response = await fetch('{{ route('school.fee-payments.store', $student) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        academic_year_id: '{{ $academicYear->id ?? '' }}',
                        payment_date: this.payment_date,
                        payment_method_id: this.payment_method_id,
                        transaction_id: this.transaction_id,
                        remarks: this.remarks,
                        payments: selectedItems
                    })
                });

                const result = await response.json();
                
                if (response.ok) {
                    this.receiptUrl = result.redirect;
                    this.showSuccessOverlay = true;
                } else {
                    throw new Error(result.message || 'Payment processing failed');
                }
            } catch (err) {
                if (window.Toast) {
                    window.Toast.fire({ icon: 'error', title: err.message });
                } else {
                    alert(err.message);
                }
            } finally {
                this.submitting = false;
            }
        }
    }));
});
</script>

<style>
@keyframes bounce-in {
    0% { transform: scale(0.9); opacity: 0; }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); opacity: 1; }
}
.animate-bounce-in {
    animation: bounce-in 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
}
</style>
@endpush
@endsection
