@extends('layouts.school')

@section('title', 'Fee Receipt - ' . $receipt_no)

@section('content')
<div class="mb-6 flex items-center justify-between no-print">
    <h1 class="text-2xl font-bold text-gray-900">Fee Receipt</h1>
    <div class="flex space-x-3">
        <button onclick="window.print()" class="bg-gray-800 text-white px-4 py-2 rounded-lg hover:bg-black">
            <i class="fas fa-print mr-2"></i> Print Receipt
        </button>
        <a href="{{ route('fee-payments.index') }}" class="bg-gray-100 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-200">
            Back to Payments
        </a>
    </div>
</div>

<div class="bg-white rounded-lg shadow-lg max-w-4xl mx-auto p-8 border" id="receipt">
    <!-- Receipt Header -->
    <div class="flex justify-between items-start mb-8 border-b pb-6">
        <div class="flex items-center">
            @if($school->logo)
                <img src="{{ asset('storage/' . $school->logo) }}" alt="Logo" class="h-16 w-16 mr-4 rounded">
            @else
                <div class="h-16 w-16 bg-blue-600 rounded flex items-center justify-center text-white font-bold text-2xl mr-4 uppercase">
                    {{ substr($school->name, 0, 1) }}
                </div>
            @endif
            <div>
                <h2 class="text-2xl font-bold text-gray-900">{{ $school->name }}</h2>
                <p class="text-sm text-gray-600">{{ $school->address }}</p>
                <p class="text-sm text-gray-600">Ph: {{ $school->phone }} | Email: {{ $school->email }}</p>
            </div>
        </div>
        <div class="text-right">
            <h3 class="text-xl font-black text-blue-600 uppercase tracking-widest">FEES RECEIPT</h3>
            <p class="mt-2 text-sm text-gray-500">No: <span class="font-bold text-gray-800">{{ $receipt_no }}</span></p>
            <p class="text-sm text-gray-500">Date: <span class="font-bold text-gray-800">{{ $payments->first()->payment_date->format('d M, Y') }}</span></p>
        </div>
    </div>

    <!-- Student Details -->
    <div class="grid grid-cols-2 gap-8 mb-8 bg-gray-50 p-4 rounded-lg">
        <div>
            <h4 class="text-xs uppercase font-bold text-gray-400 mb-2">Student Information</h4>
            <table class="w-full text-sm">
                <tr>
                    <td class="text-gray-600 py-1">Admission No:</td>
                    <td class="font-bold text-gray-900">{{ $student->admission_no }}</td>
                </tr>
                <tr>
                    <td class="text-gray-600 py-1">Name:</td>
                    <td class="font-bold text-gray-900">{{ $student->full_name }}</td>
                </tr>
                <tr>
                    <td class="text-gray-600 py-1">Class/Section:</td>
                    <td class="font-bold text-gray-900">{{ $student->class->name ?? 'N/A' }} - {{ $student->section->name ?? 'N/A' }}</td>
                </tr>
            </table>
        </div>
        <div>
            <h4 class="text-xs uppercase font-bold text-gray-400 mb-2">Payment Meta</h4>
            <table class="w-full text-sm">
                <tr>
                    <td class="text-gray-600 py-1">Payment Method:</td>
                    <td class="font-bold text-gray-900">{{ $payments->first()->paymentMethod->name }}</td>
                </tr>
                <tr>
                    <td class="text-gray-600 py-1">Transaction ID:</td>
                    <td class="font-bold text-gray-900">{{ $payments->first()->transaction_id ?: 'N/A' }}</td>
                </tr>
                <tr>
                    <td class="text-gray-600 py-1">Academic Year:</td>
                    <td class="font-bold text-gray-900">{{ $payments->first()->academicYear->name }}</td>
                </tr>
            </table>
        </div>
    </div>

    <!-- Payment Breakdown -->
    <table class="min-w-full mb-8 border border-gray-100 rounded-lg overflow-hidden">
        <thead class="bg-gray-100">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase">Fee Description</th>
                <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase">Period</th>
                <th class="px-6 py-3 text-right text-xs font-bold text-gray-600 uppercase">Amount Paid</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @foreach($payments as $payment)
            <tr>
                <td class="px-6 py-4 text-sm text-gray-900 font-medium">
                    {{ $payment->fee->feeName->name ?? 'Miscellaneous' }}
                </td>
                <td class="px-6 py-4 text-sm text-gray-500">
                    {{ $payment->fee->fee_period }}
                </td>
                <td class="px-6 py-4 text-sm text-gray-900 text-right font-bold">
                    ₹ {{ number_format($payment->amount, 2) }}
                </td>
            </tr>
            @endforeach
        </tbody>
        <tfoot class="bg-gray-50 border-t">
            <tr>
                <td colspan="2" class="px-6 py-4 text-right text-sm font-bold text-gray-600 uppercase">Total Amount Received</td>
                <td class="px-6 py-4 text-right text-xl font-black text-gray-900">
                    ₹ {{ number_format($payments->sum('amount'), 2) }}
                </td>
            </tr>
        </tfoot>
    </table>

    <div class="mb-12">
        <p class="text-sm text-gray-700 italic">
            <span class="font-bold">Remarks:</span> {{ $payments->first()->remarks ?: 'No remarks provided.' }}
        </p>
    </div>

    <!-- Signature Areas -->
    <div class="flex justify-between mt-12 pt-8 border-t border-dashed">
        <div class="text-center">
            <div class="w-48 border-b border-gray-300 mb-2"></div>
            <p class="text-xs text-gray-500 uppercase font-bold text-center">Student/Parent Sign</p>
        </div>
        <div class="text-center">
            <div class="font-bold text-sm text-gray-900 mb-10">{{ $payments->first()->creator->name ?? 'Office Admin' }}</div>
            <div class="w-48 border-b border-gray-300 mb-2"></div>
            <p class="text-xs text-gray-500 uppercase font-bold">Authorized Signatory</p>
        </div>
    </div>

    <!-- Footer Note -->
    <div class="mt-12 text-center border-t pt-4">
        <p class="text-xs text-gray-400 capitalize">Computer generated receipt. No signature required unless manually verified.</p>
        <p class="text-xs text-gray-400 font-medium mt-1">Thank you for your timely payment!</p>
    </div>
</div>

<style>
@media print {
    body { background: white; margin: 0; padding: 0; }
    .no-print { display: none; }
    #receipt { border: none; box-shadow: none; width: 100%; max-width: 100%; padding: 0; margin: 0; }
    .bg-gray-50 { background-color: #f9fafb !important; -webkit-print-color-adjust: exact; }
    .bg-blue-600 { background-color: #2563eb !important; -webkit-print-color-adjust: exact; }
}
</style>
@endsection
