@extends('layouts.school')

@section('title', 'Process Fee Collection - ' . $student->full_name)

@section('content')
<div
    x-data="feeCollectionManager()"
    class="space-y-6"
>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <x-stat-card
            label="Pending Heads"
            :value="$pendingFees->count()"
            icon="fas fa-file-invoice-dollar"
            color="amber"
        />
        <x-stat-card
            label="Outstanding Balance"
            :value="'₹' . number_format($pendingFees->sum('due_amount'), 2)"
            icon="fas fa-wallet"
            color="rose"
            alpine-text="'₹' + totalOutstanding.toLocaleString('en-IN', { minimumFractionDigits: 2 })"
        />
        <x-stat-card
            label="Selected Collection"
            :value="'₹0.00'"
            icon="fas fa-cash-register"
            color="emerald"
            alpine-text="'₹' + total.toLocaleString('en-IN', { minimumFractionDigits: 2 })"
        />
    </div>

    <x-page-header
        title="Process Fee Payment"
        description="Review pending dues, choose the applicable fee heads, and record the payment using the same collection workflow as the rest of the finance module."
        icon="fas fa-money-bill-transfer"
    >
        <a
            href="{{ route('school.fee-payments.index') }}"
            class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-gray-600 dark:text-gray-200 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition"
        >
            <i class="fas fa-arrow-left text-xs"></i>
            Back to Collection Portal
        </a>
    </x-page-header>

    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="p-6 lg:p-7 flex flex-col lg:flex-row lg:items-center gap-6">
            <div class="w-16 h-16 rounded-2xl bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 flex items-center justify-center text-2xl font-bold flex-shrink-0">
                {{ substr($student->first_name, 0, 1) }}
            </div>

            <div class="flex-1 min-w-0">
                <div class="flex flex-col xl:flex-row xl:items-start xl:justify-between gap-4">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.2em] text-emerald-600 dark:text-emerald-400">Student Profile</p>
                        <h2 class="mt-1 text-2xl font-bold text-gray-900 dark:text-white tracking-tight">{{ $student->full_name }}</h2>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Continue collection for the current academic year and generate the receipt automatically after payment.
                        </p>
                    </div>

                    <div class="flex items-center gap-3 px-4 py-3 rounded-xl bg-gray-50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700">
                        <div class="w-10 h-10 rounded-xl bg-rose-100 dark:bg-rose-900/30 text-rose-600 dark:text-rose-300 flex items-center justify-center">
                            <i class="fas fa-scale-balanced"></i>
                        </div>
                        <div>
                            <p class="text-[11px] font-bold uppercase tracking-widest text-gray-400 dark:text-gray-500">Current Balance</p>
                            <p class="text-lg font-bold text-gray-900 dark:text-white">₹ {{ number_format($pendingFees->sum('due_amount'), 2) }}</p>
                        </div>
                    </div>
                </div>

                <div class="mt-5 flex flex-wrap gap-2.5">
                    <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 border border-indigo-100 dark:border-indigo-800 text-xs font-bold uppercase tracking-wider">
                        <i class="fas fa-barcode text-[10px]"></i>
                        Adm: {{ $student->admission_no }}
                    </span>
                    <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 border border-emerald-100 dark:border-emerald-800 text-xs font-bold uppercase tracking-wider">
                        <i class="fas fa-graduation-cap text-[10px]"></i>
                        {{ $student->class->name ?? 'Class N/A' }}
                    </span>
                    @if($student->section?->name)
                        <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300 border border-amber-100 dark:border-amber-800 text-xs font-bold uppercase tracking-wider">
                            <i class="fas fa-layer-group text-[10px]"></i>
                            {{ $student->section->name }} Section
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <form @submit.prevent="submitPayment" method="POST" id="payment-form" class="grid grid-cols-1 xl:grid-cols-12 gap-6 items-start">
        @csrf
        <input type="hidden" name="academic_year_id" value="{{ $academicYear->id ?? '' }}">

        <div class="xl:col-span-8 space-y-6">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/60 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <h3 class="text-base font-semibold text-gray-900 dark:text-white">Fee Heads Breakdown</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Choose the dues to collect and adjust individual amounts when needed.</p>
                    </div>

                    @if($pendingFees->isNotEmpty())
                        <label class="inline-flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 cursor-pointer select-none">
                            <input
                                type="checkbox"
                                @change="toggleAll"
                                x-model="allSelected"
                                class="rounded w-4 h-4 text-emerald-600 border-gray-300 dark:border-gray-600 focus:ring-emerald-500 dark:bg-gray-800"
                            >
                            Select all
                        </label>
                    @endif
                </div>

                @if($pendingFees->isNotEmpty())
                    <div class="p-4 sm:p-6 space-y-4">
                        @foreach($pendingFees as $fee)
                            <div
                                class="rounded-2xl border transition-all duration-200 p-4 sm:p-5"
                                :class="selections['{{ $fee->id }}']
                                    ? 'bg-emerald-50 dark:bg-emerald-900/20 border-emerald-200 dark:border-emerald-800 shadow-sm'
                                    : 'bg-gray-50 dark:bg-gray-900/40 border-gray-200 dark:border-gray-700 hover:border-emerald-200 dark:hover:border-emerald-800'"
                            >
                                <div class="flex flex-col lg:flex-row lg:items-center gap-4 lg:gap-6">
                                    <div class="flex items-start gap-4 flex-1">
                                        <input
                                            type="checkbox"
                                            x-model="selections['{{ $fee->id }}']"
                                            @change="recalculateTotal()"
                                            class="mt-1 rounded w-5 h-5 text-emerald-600 border-gray-300 dark:border-gray-600 focus:ring-emerald-500 dark:bg-gray-800"
                                        >

                                        <div
                                            class="w-11 h-11 rounded-xl border flex items-center justify-center transition-colors"
                                            :class="selections['{{ $fee->id }}']
                                                ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800'
                                                : 'bg-white dark:bg-gray-800 text-gray-400 dark:text-gray-500 border-gray-200 dark:border-gray-700'"
                                        >
                                            <i class="fas fa-file-invoice-dollar"></i>
                                        </div>

                                        <div class="min-w-0 flex-1">
                                            <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-3">
                                                <h4 class="text-sm sm:text-base font-semibold text-gray-900 dark:text-white">
                                                    {{ $fee->feeName->name ?? 'Registration Fee' }}
                                                </h4>
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 border border-indigo-100 dark:border-indigo-800 text-[10px] font-bold uppercase tracking-widest w-fit">
                                                    {{ $fee->feeType->name ?? 'One-Time' }}
                                                </span>
                                            </div>

                                            <div class="mt-2 flex flex-wrap gap-2">
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-white dark:bg-gray-800 text-gray-500 dark:text-gray-400 border border-gray-200 dark:border-gray-700 text-[10px] font-bold uppercase tracking-wider">
                                                    Period: {{ $fee->fee_period }}
                                                </span>
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-rose-50 dark:bg-rose-900/30 text-rose-700 dark:text-rose-300 border border-rose-100 dark:border-rose-800 text-[10px] font-bold uppercase tracking-wider">
                                                    Due: ₹ {{ number_format($fee->due_amount, 2) }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 lg:w-[24rem]">
                                        <div class="rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 px-4 py-3">
                                            <p class="text-[11px] font-bold uppercase tracking-widest text-gray-400 dark:text-gray-500">Outstanding</p>
                                            <p class="mt-1 text-lg font-bold text-gray-900 dark:text-white">₹ {{ number_format($fee->due_amount, 2) }}</p>
                                        </div>

                                        <div>
                                            <label class="block text-[11px] font-bold uppercase tracking-widest text-gray-400 dark:text-gray-500 mb-1.5">Collect Amount</label>
                                            <div class="relative">
                                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-sm font-bold text-gray-400 dark:text-gray-500">₹</span>
                                                <input
                                                    type="number"
                                                    step="0.01"
                                                    x-model="amounts['{{ $fee->id }}']"
                                                    @input="recalculateTotal()"
                                                    :disabled="!selections['{{ $fee->id }}']"
                                                    max="{{ $fee->due_amount }}"
                                                    class="w-full pl-8 pr-4 py-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm font-semibold text-gray-800 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 disabled:opacity-50 disabled:cursor-not-allowed disabled:bg-gray-100 dark:disabled:bg-gray-900/40 transition"
                                                >
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="px-6 py-14 text-center">
                        <div class="w-16 h-16 mx-auto rounded-full bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-300 flex items-center justify-center mb-4">
                            <i class="fas fa-check-circle text-2xl"></i>
                        </div>
                        <h4 class="text-lg font-semibold text-gray-900 dark:text-white">No pending dues for this student</h4>
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">This academic year is already settled, so there is nothing left to collect right now.</p>
                    </div>
                @endif
            </div>
        </div>

        <div class="xl:col-span-4 xl:sticky xl:top-6">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/60">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-indigo-100 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-300 flex items-center justify-center">
                            <i class="fas fa-cart-shopping"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Payment Summary</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Finalize the collection details before submitting.</p>
                        </div>
                    </div>
                </div>

                <div class="p-6 space-y-6">
                    <div class="rounded-2xl bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-100 dark:border-indigo-800 p-4">
                        <div class="flex items-end justify-between gap-4">
                            <div>
                                <p class="text-[11px] font-bold uppercase tracking-widest text-indigo-500 dark:text-indigo-300">Collection Total</p>
                                <p class="mt-2 text-3xl font-bold text-indigo-700 dark:text-indigo-300" x-text="'₹ ' + total.toLocaleString('en-IN', { minimumFractionDigits: 2 })"></p>
                            </div>
                            <div class="text-right">
                                <p class="text-[11px] font-bold uppercase tracking-widest text-gray-400 dark:text-gray-500">Selected Heads</p>
                                <p class="mt-2 text-xl font-bold text-gray-900 dark:text-white" x-text="selectedCount"></p>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-5">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                                Payment Date <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="date"
                                x-model="payment_date"
                                required
                                class="w-full px-4 py-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition"
                            >
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                                Payment Channel <span class="text-red-500">*</span>
                            </label>
                            <select
                                x-model="payment_method_id"
                                required
                                class="no-select2 w-full px-4 py-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition"
                            >
                                @foreach($paymentMethods as $method)
                                    <option value="{{ $method->id }}">{{ $method->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div x-show="payment_method_id != 1">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Ref ID / Cheque Number</label>
                            <input
                                type="text"
                                x-model="transaction_id"
                                placeholder="Optional reference"
                                class="w-full px-4 py-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm text-gray-700 dark:text-gray-200 placeholder:text-gray-400 dark:placeholder:text-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition"
                            >
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Staff Remarks</label>
                            <textarea
                                x-model="remarks"
                                rows="3"
                                class="w-full px-4 py-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm text-gray-700 dark:text-gray-200 placeholder:text-gray-400 dark:placeholder:text-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition resize-none"
                            ></textarea>
                        </div>
                    </div>

                    <div class="rounded-xl bg-gray-50 dark:bg-gray-900/40 border border-gray-200 dark:border-gray-700 p-4 text-sm text-gray-500 dark:text-gray-400">
                        Receipt will be generated automatically after a successful payment submission.
                    </div>

                    <button
                        type="submit"
                        :disabled="total <= 0 || submitting"
                        class="w-full inline-flex items-center justify-center gap-2 px-6 py-3.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-xl shadow-sm transition active:scale-[0.99] disabled:opacity-50 disabled:cursor-not-allowed disabled:active:scale-100"
                    >
                        <template x-if="submitting">
                            <span class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                        </template>
                        <i x-show="!submitting" class="fas fa-check-circle text-xs"></i>
                        <span x-text="submitting ? 'Processing Payment...' : 'Complete Payment'"></span>
                    </button>
                </div>
            </div>
        </div>
    </form>

    <template x-if="showSuccessOverlay">
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/70 backdrop-blur-sm">
            <div class="bg-white dark:bg-gray-800 rounded-3xl p-8 max-w-md w-full text-center shadow-2xl border border-gray-100 dark:border-gray-700 animate-bounce-in">
                <div class="w-20 h-20 bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-300 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-check text-4xl"></i>
                </div>
                <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Payment Recorded</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-8">
                    The collection has been saved successfully and the receipt is ready to view.
                </p>
                <div class="flex flex-col gap-3">
                    <a
                        :href="receiptUrl"
                        target="_blank"
                        class="w-full inline-flex items-center justify-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-3 rounded-xl transition"
                    >
                        <i class="fas fa-print text-sm"></i>
                        Print Receipt
                    </a>
                    <button
                        @click="window.location.href='{{ route('school.fee-payments.index') }}'"
                        class="w-full bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 font-semibold py-3 rounded-xl hover:bg-gray-200 dark:hover:bg-gray-600 transition"
                    >
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
        totalOutstanding: {{ $pendingFees->sum('due_amount') }},
        selections: {},
        amounts: {},
        payment_date: '{{ date('Y-m-d') }}',
        payment_method_id: '{{ $paymentMethods->first()->id ?? '' }}',
        transaction_id: '',
        remarks: '',
        showSuccessOverlay: false,
        receiptUrl: '#',

        get selectedCount() {
            return Object.values(this.selections).filter(Boolean).length;
        },

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
            this.allSelected = Object.keys(this.selections).length > 0 && Object.values(this.selections).every(v => v);
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
                    throw new Error(window.resolveApiMessage(result, ''));
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
