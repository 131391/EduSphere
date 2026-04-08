@extends('layouts.school')

@section('title', 'Fee Management')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <h1 class="text-2xl font-bold text-gray-900">Fee Management</h1>
    <div class="flex space-x-3">
        <a href="{{ route('school.fees.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 flex items-center">
            <i class="fas fa-plus mr-2"></i>
            ADD
        </a>
        <button class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 flex items-center">
            <i class="fas fa-plus mr-2"></i>
            Misc ADD
        </button>
    </div>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SR NO</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">STUDENT</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">FEE HEAD</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">PERIOD</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">AMOUNT</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">STATUS</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-right">ACTION</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($fees as $index => $fee)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $fees->firstItem() + $index }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        <div class="font-medium text-gray-900">{{ $fee->student->full_name }}</div>
                        <div class="text-xs text-gray-500">{{ $fee->student->admission_no }} | {{ $fee->class->name ?? 'N/A' }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $fee->feeName->name ?? 'N/A' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $fee->fee_period }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">₹ {{ number_format($fee->payable_amount, 2) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        @if($fee->payment_status->value === 3)
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Paid</span>
                        @elseif($fee->payment_status->value === 1)
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                        @else
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Overdue</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium">
                        <a href="{{ route('school.fees.show', $fee) }}" class="text-blue-600 hover:text-blue-900 mr-3"><i class="fas fa-eye"></i></a>
                        <button class="text-red-600 hover:text-red-900" onclick="confirm('Are you sure?') ? document.getElementById('delete-form-{{ $fee->id }}').submit() : ''">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-8 text-center text-gray-500">No fee records found. Start by generating fees for a class.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($fees->hasPages())
    <div class="px-6 py-4 border-t border-gray-200">
        {{ $fees->links() }}
    </div>
    @endif
</div>
@endsection

