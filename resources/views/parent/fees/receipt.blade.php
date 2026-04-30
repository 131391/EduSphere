@extends('layouts.parent')

@section('title', 'Fee Receipt - ' . $receiptNo)

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 no-print">
        <div class="flex items-center gap-4">
            <a href="{{ route('parent.fees.show', $payments->first()->fee_id) }}" class="w-10 h-10 rounded-xl bg-white border border-gray-200 flex items-center justify-center text-gray-400 hover:text-emerald-600 hover:border-emerald-200 transition-all shadow-sm group">
                <i class="fas fa-chevron-left group-hover:-translate-x-0.5 transition-transform"></i>
            </a>
            <div>
                <h1 class="text-2xl font-black text-gray-800 dark:text-white tracking-tight">Fee Receipt</h1>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Receipt #{{ $receiptNo }}</p>
            </div>
        </div>

        <button onclick="window.print()" class="px-6 py-2.5 bg-gray-900 text-white text-sm font-bold rounded-xl hover:bg-black transition-all shadow-lg flex items-center gap-2">
            <i class="fas fa-print"></i> Print Receipt
        </button>
    </div>

    <div class="max-w-4xl mx-auto">
        <div class="bg-white dark:bg-gray-800 rounded-[2rem] shadow-2xl shadow-gray-200/50 border border-gray-100 dark:border-gray-700 overflow-hidden print:shadow-none print:border-gray-200" id="receipt-document">
            <div class="h-2 bg-gradient-to-r from-emerald-500 via-teal-500 to-indigo-500"></div>

            <div class="p-8 sm:p-12">
                <div class="flex flex-col md:flex-row justify-between gap-10 border-b border-gray-100 dark:border-gray-700 pb-10 mb-10">
                    <div class="flex items-start gap-6">
                        <div class="w-20 h-20 bg-emerald-600 rounded-3xl flex items-center justify-center text-white text-3xl font-black shadow-lg shadow-emerald-100 shrink-0 overflow-hidden">
                            @if($school->logo)
                                <img src="{{ asset('storage/' . $school->logo) }}" alt="Logo" class="w-full h-full object-cover">
                            @else
                                {{ substr($school->name, 0, 1) }}
                            @endif
                        </div>
                        <div>
                            <h2 class="text-2xl font-black text-gray-900 dark:text-white leading-tight">{{ $school->name }}</h2>
                            <p class="text-sm text-gray-500 dark:text-gray-400 font-medium max-w-sm mt-1">{{ $school->address }}</p>
                            <div class="flex flex-wrap items-center gap-4 mt-3">
                                @if($school->phone)
                                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest flex items-center">
                                        <i class="fas fa-phone-alt mr-1.5 text-emerald-500"></i> {{ $school->phone }}
                                    </span>
                                @endif
                                @if($school->email)
                                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest flex items-center">
                                        <i class="fas fa-globe mr-1.5 text-emerald-500"></i> {{ $school->email }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="text-right flex flex-col justify-between">
                        <div>
                            <div class="text-[10px] font-black text-emerald-600 uppercase tracking-[0.3em] mb-1">Parent Copy</div>
                            <h3 class="text-4xl font-black text-gray-800 dark:text-gray-100 tracking-tighter">#{{ $receiptNo }}</h3>
                        </div>
                        <div class="mt-4">
                            <div class="text-[10px] font-bold text-gray-400 uppercase mb-0.5 tracking-tighter">Issue Date</div>
                            <div class="text-sm font-black text-gray-700 dark:text-gray-200">{{ optional($payments->first()->payment_date)->format('d M, Y') ?? 'N/A' }}</div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-12">
                    <div class="bg-gray-50 dark:bg-gray-900/30 rounded-2xl p-6 border border-gray-100 dark:border-gray-700">
                        <div class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-4">Student Details</div>
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-full bg-white dark:bg-gray-800 flex items-center justify-center text-xs font-black text-emerald-600 border border-gray-100 dark:border-gray-700 shadow-sm">
                                {{ strtoupper(substr($student->first_name ?? 'S', 0, 1)) }}
                            </div>
                            <div>
                                <div class="text-lg font-black text-gray-800 dark:text-white leading-none">{{ $student->full_name }}</div>
                                <div class="text-[10px] font-bold text-emerald-600 uppercase tracking-tight mt-1">
                                    Admission ID: {{ $student->admission_no ?: 'N/A' }}
                                </div>
                            </div>
                        </div>
                        <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700 space-y-2 text-xs font-bold text-gray-700 dark:text-gray-200">
                            <div class="flex justify-between gap-4">
                                <span class="text-gray-400 uppercase">Class</span>
                                <span>{{ optional($student->class)->name ?? 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between gap-4">
                                <span class="text-gray-400 uppercase">Section</span>
                                <span>{{ optional($student->section)->name ?? 'N/A' }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50 dark:bg-gray-900/30 rounded-2xl p-6 border border-gray-100 dark:border-gray-700 space-y-4">
                        <div class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 text-right">Transaction Details</div>
                        <div class="flex justify-between items-center gap-4">
                            <span class="text-[10px] font-bold text-gray-400 uppercase">Method</span>
                            <span class="px-2 py-0.5 bg-emerald-100 text-emerald-700 text-[10px] font-black rounded uppercase">
                                {{ optional($payments->first()->paymentMethod)->name ?? 'N/A' }}
                            </span>
                        </div>
                        <div class="flex justify-between items-center gap-4">
                            <span class="text-[10px] font-bold text-gray-400 uppercase">Reference #</span>
                            <span class="text-xs font-bold text-gray-700 dark:text-gray-200 font-mono tracking-tighter">{{ $payments->first()->transaction_id ?: 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between items-center gap-4">
                            <span class="text-[10px] font-bold text-gray-400 uppercase">Academic Session</span>
                            <span class="text-xs font-bold text-gray-700 dark:text-gray-200">{{ optional($payments->first()->academicYear)->name ?? 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between items-center gap-4">
                            <span class="text-[10px] font-bold text-gray-400 uppercase">Received By</span>
                            <span class="text-xs font-bold text-gray-700 dark:text-gray-200">{{ optional($payments->first()->creator)->name ?? 'School Office' }}</span>
                        </div>
                    </div>
                </div>

                <div class="overflow-hidden border border-gray-100 dark:border-gray-700 rounded-3xl mb-10 shadow-sm">
                    <table class="w-full text-left">
                        <thead class="bg-emerald-600 text-white">
                            <tr>
                                <th class="px-6 sm:px-8 py-5 text-[10px] font-black uppercase tracking-widest">Fee Description / Period</th>
                                <th class="px-6 sm:px-8 py-5 text-[10px] font-black uppercase tracking-widest text-right">Amount (₹)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 dark:divide-gray-700">
                            @foreach($payments as $payment)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/20 transition-colors">
                                    <td class="px-6 sm:px-8 py-6">
                                        <div class="text-sm font-black text-gray-700 dark:text-gray-200">
                                            {{ optional(optional($payment->fee)->feeName)->name ?? 'Fee Payment' }}
                                        </div>
                                        <div class="text-[10px] font-bold text-teal-600 uppercase mt-0.5 tracking-tighter italic">
                                            {{ optional($payment->fee)->fee_period ?: 'General Payment' }}
                                        </div>
                                    </td>
                                    <td class="px-6 sm:px-8 py-6 text-right">
                                        <span class="text-sm font-black text-gray-800 dark:text-white">{{ number_format((float) $payment->amount, 2) }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-indigo-50/50 dark:bg-indigo-900/10">
                            <tr>
                                <td class="px-6 sm:px-8 py-8">
                                    <div class="text-[10px] font-black text-indigo-400 uppercase tracking-widest mb-1">Remarks</div>
                                    <div class="text-xs text-indigo-700 dark:text-indigo-300 font-medium italic">
                                        {{ $payments->first()->remarks ?: 'Payment received successfully.' }}
                                    </div>
                                </td>
                                <td class="px-6 sm:px-8 py-8 text-right">
                                    <div class="text-xs font-bold text-indigo-400 mb-1">TOTAL PAID</div>
                                    <div class="text-4xl font-black text-indigo-800 dark:text-indigo-200 tracking-tighter">
                                        <span class="text-lg font-bold mr-1">₹</span>{{ number_format((float) $payments->sum('amount'), 2) }}
                                    </div>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="pt-8 border-t border-dashed border-gray-200 dark:border-gray-700 text-center">
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-[0.3em]">Computer Generated Receipt</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">This receipt is valid without a physical signature.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    body { background: white !important; -webkit-print-color-adjust: exact; }
    .no-print { display: none !important; }
    #receipt-document { border: none !important; box-shadow: none !important; width: 100% !important; max-width: 100% !important; margin: 0 !important; border-radius: 0 !important; }
}
</style>
@endsection
