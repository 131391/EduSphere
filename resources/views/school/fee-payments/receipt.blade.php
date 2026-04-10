@extends('layouts.school')

@section('title', 'Official Fee Receipt - ' . $receipt_no)

@section('content')
<div class="mb-8 flex flex-col sm:flex-row items-center justify-between gap-4 no-print">
    <div class="flex items-center gap-4">
        <a href="{{ route('school.fee-payments.index') }}" class="w-10 h-10 rounded-xl bg-white border border-gray-200 flex items-center justify-center text-gray-400 hover:text-emerald-600 hover:border-emerald-200 transition-all shadow-sm group">
            <i class="fas fa-chevron-left group-hover:-translate-x-0.5 transition-transform"></i>
        </a>
        <div>
            <h1 class="text-2xl font-black text-gray-800 tracking-tight">Fee Receipt</h1>
            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Transaction #{{ $receipt_no }}</p>
        </div>
    </div>
    <div class="flex gap-3">
        <button onclick="window.print()" class="px-6 py-2.5 bg-gray-900 text-white text-sm font-bold rounded-xl hover:bg-black transition-all shadow-lg flex items-center gap-2">
            <i class="fas fa-print"></i> Print Document
        </button>
        <button class="px-6 py-2.5 bg-white border border-gray-200 text-gray-600 text-sm font-bold rounded-xl hover:bg-gray-50 transition-all shadow-sm flex items-center gap-2">
            <i class="fas fa-file-pdf"></i> Download PDF
        </button>
    </div>
</div>

<div class="max-w-4xl mx-auto relative animate-fade-in">
    <!-- Success Badge / Watermark (No-print) -->
    <div class="absolute -top-6 -right-6 z-10 no-print rotate-12">
        <div class="bg-emerald-500 text-white px-8 py-2 rounded-xl font-black tracking-widest shadow-xl ring-4 ring-emerald-500/20">
            PAID
        </div>
    </div>

    <!-- The Receipt Paper -->
    <div class="bg-white rounded-[2rem] shadow-2xl shadow-gray-200/50 border border-gray-100 overflow-hidden print:shadow-none print:border-gray-200" id="receipt-document">
        
        <!-- Document Header Decor -->
        <div class="h-2 bg-gradient-to-r from-emerald-500 via-teal-500 to-indigo-500"></div>

        <div class="p-12">
            <!-- School Brand Info -->
            <div class="flex flex-col md:flex-row justify-between gap-10 border-b border-gray-100 pb-10 mb-10">
                <div class="flex items-start gap-6">
                    <div class="w-20 h-20 bg-emerald-600 rounded-3xl flex items-center justify-center text-white text-3xl font-black shadow-lg shadow-emerald-100 shrink-0">
                        @if($school->logo)
                            <img src="{{ asset('storage/' . $school->logo) }}" alt="Logo" class="w-full h-full object-cover rounded-3xl">
                        @else
                            {{ substr($school->name, 0, 1) }}
                        @endif
                    </div>
                    <div>
                        <h2 class="text-2xl font-black text-gray-900 leading-tight">{{ $school->name }}</h2>
                        <p class="text-sm text-gray-500 font-medium max-w-sm mt-1">{{ $school->address }}</p>
                        <div class="flex items-center gap-4 mt-3">
                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest flex items-center">
                                <i class="fas fa-phone-alt mr-1.5 text-emerald-500"></i> {{ $school->phone }}
                            </span>
                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest flex items-center">
                                <i class="fas fa-globe mr-1.5 text-emerald-500"></i> {{ $school->email }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="text-right flex flex-col justify-between">
                    <div>
                        <div class="text-[10px] font-black text-emerald-600 uppercase tracking-[0.3em] mb-1">Official Voucher</div>
                        <h3 class="text-4xl font-black text-gray-800 tracking-tighter">#{{ $receipt_no }}</h3>
                    </div>
                    <div class="mt-4">
                        <div class="text-[10px] font-bold text-gray-400 uppercase mb-0.5 tracking-tighter">Issue Date</div>
                        <div class="text-sm font-black text-gray-700">{{ $payments->first()->payment_date->format('d M, Y') }}</div>
                    </div>
                </div>
            </div>

            <!-- Recipient Meta -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-12">
                <div class="bg-gray-50/50 rounded-2xl p-6 border border-gray-100">
                    <div class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-4">Paid By (Student)</div>
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-full bg-white flex items-center justify-center text-xs font-black text-emerald-600 border border-gray-100 shadow-sm">
                            {{ substr($student->first_name, 0, 1) }}
                        </div>
                        <div>
                            <div class="text-lg font-black text-gray-800 leading-none">{{ $student->full_name }}</div>
                            <div class="text-[10px] font-bold text-emerald-600 uppercase tracking-tight mt-1">
                                Admission ID: {{ $student->admission_no }}
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 pt-4 border-t border-gray-100 flex justify-between">
                        <div class="text-[10px] font-bold text-gray-400 uppercase">Class & Section</div>
                        <div class="text-xs font-bold text-gray-700">{{ $student->class->name ?? 'N/A' }} / {{ $student->section->name ?? 'N/A' }}</div>
                    </div>
                </div>

                <div class="bg-gray-50/50 rounded-2xl p-6 border border-gray-100 space-y-4">
                    <div class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 text-right">Transaction Details</div>
                    <div class="flex justify-between items-center">
                        <span class="text-[10px] font-bold text-gray-400 uppercase">Method</span>
                        <span class="px-2 py-0.5 bg-emerald-100 text-emerald-700 text-[10px] font-black rounded uppercase">{{ $payments->first()->paymentMethod->name }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-[10px] font-bold text-gray-400 uppercase">Reference #</span>
                        <span class="text-xs font-bold text-gray-700 font-mono tracking-tighter">{{ $payments->first()->transaction_id ?: 'CASH_VOUCHER' }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-[10px] font-bold text-gray-400 uppercase">Academic Session</span>
                        <span class="text-xs font-bold text-gray-700">{{ $payments->first()->academicYear->name }}</span>
                    </div>
                </div>
            </div>

            <!-- Particulars Table -->
            <div class="overflow-hidden border border-gray-100 rounded-3xl mb-12 shadow-sm">
                <table class="w-full text-left">
                    <thead class="bg-emerald-600 text-white">
                        <tr>
                            <th class="px-8 py-5 text-[10px] font-black uppercase tracking-widest">Fee Description / Period</th>
                            <th class="px-8 py-5 text-[10px] font-black uppercase tracking-widest text-right">Amount (₹)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($payments as $payment)
                        <tr class="group hover:bg-gray-50 transition-colors">
                            <td class="px-8 py-6">
                                <div class="text-sm font-black text-gray-700 group-hover:text-emerald-700 transition-colors">
                                    {{ $payment->fee->feeName->name ?? 'Miscellaneous Contribution' }}
                                </div>
                                <div class="text-[10px] font-bold text-teal-600 uppercase mt-0.5 tracking-tighter italic">
                                    {{ $payment->fee->fee_period }} Session
                                </div>
                            </td>
                            <td class="px-8 py-6 text-right">
                                <span class="text-sm font-black text-gray-800">
                                    {{ number_format($payment->amount, 2) }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-indigo-50/50">
                        <tr>
                            <td class="px-8 py-8">
                                <div class="text-[10px] font-black text-indigo-400 uppercase tracking-widest mb-1">Total Amount Discharged</div>
                                <div class="text-xs text-indigo-700 font-medium italic">Handled by: {{ $payments->first()->creator->name ?? 'Admin Desk' }}</div>
                            </td>
                            <td class="px-8 py-8 text-right">
                                <div class="text-xs font-bold text-indigo-400 mb-1">GRAND TOTAL</div>
                                <div class="text-4xl font-black text-indigo-800 tracking-tighter">
                                    <span class="text-lg font-bold mr-1">₹</span>{{ number_format($payments->sum('amount'), 2) }}
                                </div>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- Remarks & Notes -->
            <div class="grid grid-cols-1 md:grid-cols-12 gap-12 mb-12">
                <div class="md:col-span-12">
                    <div class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3">Admin Notes</div>
                    <div class="p-4 bg-gray-50 rounded-2xl border-l-[6px] border-emerald-500 text-sm text-gray-600 italic font-medium">
                        "{{ $payments->first()->remarks ?: 'The transaction was processed successfully with no additional notes.' }}"
                    </div>
                </div>
            </div>

            <!-- Footer Area / Signatures -->
            <div class="flex flex-col md:flex-row justify-between items-end gap-12 pt-12 border-t border-dashed border-gray-200">
                <div class="w-full md:w-auto text-center md:text-left">
                    <div class="bg-gray-100 inline-block p-4 rounded-2xl mb-4 grayscale opacity-30 no-print">
                        <i class="fas fa-qrcode text-5xl"></i>
                    </div>
                    <p class="text-[9px] font-bold text-gray-300 uppercase leading-loose max-w-xs px-2">
                        COMPUTED GENERATED DIGITAL VOUCHER. <br>
                        REQUIRES NO PHYSICAL SIGNATURE FOR VALIDATION. <br>
                        © {{ date('Y') }} {{ $school->name }} FINANCIAL SYSTEM.
                    </p>
                </div>
                
                <div class="w-full md:w-auto flex flex-col items-center">
                    <div class="mb-6 h-12 flex items-center justify-center italic text-emerald-800 font-black text-xl opacity-80 font-serif">
                        {{ $school->name }}
                    </div>
                    <div class="w-64 border-b-2 border-gray-900 mb-2"></div>
                    <div class="text-[10px] font-black text-gray-900 uppercase tracking-[0.2em]">Authorized Signatory</div>
                </div>
            </div>
        </div>

        <!-- Receipt Base -->
        <div class="bg-gray-900 p-4 text-center">
            <p class="text-[10px] font-bold text-emerald-500/50 uppercase tracking-[0.5em]">Thank you for your timely contribution</p>
        </div>
    </div>
</div>

<style>
@keyframes fade-in {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}
.animate-fade-in {
    animation: fade-in 0.6s cubic-bezier(0.165, 0.84, 0.44, 1) forwards;
}

@media print {
    body { background: white !important; -webkit-print-color-adjust: exact; }
    .no-print { display: none !important; }
    #receipt-document { border: none !important; box-shadow: none !important; width: 100% !important; max-width: 100% !important; margin: 0 !important; border-radius: 0 !important; }
    .animate-fade-in { animation: none !important; }
    .p-12 { padding: 2rem !important; }
}
</style>
@endsection
