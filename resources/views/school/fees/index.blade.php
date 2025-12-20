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
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">CLASS NAME</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">FEE</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">FEE NAME</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">FEE TYPE</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ACTION</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @php
                    $school = app('currentSchool');
                    $fees = \App\Models\Fee::where('school_id', $school->id)
                        ->with(['student.class', 'feeType'])
                        ->orderBy('created_at', 'desc')
                        ->paginate(20);
                @endphp
                @forelse($fees as $index => $fee)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $fees->firstItem() + $index }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $fee->student->class->name ?? 'N/A' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">₹ {{ number_format($fee->payable_amount, 2) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $fee->fee_period }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $fee->feeType->name ?? 'Monthly' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <button class="text-red-600 hover:text-red-800">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-8 text-center text-gray-500">No fees found</td>
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

