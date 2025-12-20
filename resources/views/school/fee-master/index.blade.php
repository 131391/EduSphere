@extends('layouts.school')

@section('title', 'Fee Management')

@section('content')
<div class="container mx-auto px-4 py-6" x-data="{ showBulkModal: false }">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Fee Management</h1>
        <div class="flex space-x-2">
            <button @click="$dispatch('open-modal', 'bulk-fee-master-modal')" class="bg-[#0097a7] hover:bg-[#00838f] text-white font-bold py-2 px-4 rounded flex items-center transition-colors">
                <i class="fas fa-plus mr-2"></i> ADD
            </button>
            <button @click="$dispatch('open-modal', 'misc-fee-master-modal')" class="bg-[#2e7d32] hover:bg-[#1b5e20] text-white font-bold py-2 px-4 rounded flex items-center transition-colors">
                <i class="fas fa-plus mr-2"></i> Misc ADD
            </button>
        </div>
    </div>

    @if(session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
        <span class="block sm:inline">{{ session('success') }}</span>
    </div>
    @endif

    <div class="bg-white shadow-md rounded-lg overflow-hidden border border-gray-200">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">SR NO</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">CLASS NAME</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">FEE</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">FEE NAME</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">FEE TYPE</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">ACTION</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($fees as $index => $fee)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $fees->firstItem() + $index }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                        {{ $fee->class->name }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 font-semibold">
                        {{ number_format($fee->amount, 2) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                        {{ $fee->feeName->name }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                        {{ $fee->feeType->name }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <div class="flex space-x-3">
                            <button @click="$dispatch('open-modal', {name: 'edit-fee-master-modal', fee: {{ $fee->load(['class', 'feeName', 'feeType']) }}})" class="text-indigo-600 hover:text-indigo-900 transition-colors">
                                <i class="fas fa-edit"></i>
                            </button>
                            <form action="{{ route('school.fee-master.destroy', $fee->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this fee configuration?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-500 hover:text-red-700 transition-colors">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center py-10">
                        <div class="flex flex-col items-center">
                            <i class="fas fa-info-circle text-gray-300 text-4xl mb-2"></i>
                            <span>No fee configurations found.</span>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
            {{ $fees->links() }}
        </div>
    </div>
</div>

<!-- Bulk Add Modal -->
<x-modal name="bulk-fee-master-modal" title="Fee Management" maxWidth="4xl">
    <form action="{{ route('school.fee-master.store') }}" method="POST" class="p-6">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div>
                <label for="class_id" class="block text-sm font-bold text-gray-700 mb-2">Class</label>
                <select name="class_id" id="class_id" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all" required>
                    <option value="">Select Class Name</option>
                    @foreach($classes as $class)
                    <option value="{{ $class->id }}">{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="fee_type_id" class="block text-sm font-bold text-gray-700 mb-2">Fee Type</label>
                <select name="fee_type_id" id="fee_type_id" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all" required>
                    <option value="">Select Fee Type</option>
                    @foreach($feeTypes as $feeType)
                    <option value="{{ $feeType->id }}">{{ $feeType->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="border rounded-lg overflow-hidden mb-6">
            <div class="bg-gray-50 px-4 py-2 border-b flex font-bold text-gray-700 text-sm">
                <div class="flex-1">FEE NAME</div>
                <div class="w-48 text-center">AMOUNT</div>
            </div>
            <div class="max-h-[400px] overflow-y-auto divide-y divide-gray-100">
                @foreach($feeNames as $feeName)
                <div class="flex items-center px-4 py-3 hover:bg-gray-50 transition-colors">
                    <div class="flex-1 text-sm font-medium text-gray-700">{{ $feeName->name }}</div>
                    <div class="w-48">
                        <input type="number" name="amounts[{{ $feeName->id }}]" step="0.01" min="0" placeholder="Enter Amount" class="w-full px-3 py-1.5 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all text-sm">
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <div class="flex justify-end space-x-3 pt-4 border-t">
            <button type="button" @click="show = false" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-6 rounded transition-colors">
                Close
            </button>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded shadow-md transition-all active:scale-95">
                Submit
            </button>
        </div>
    </form>
</x-modal>

<!-- Misc Add Modal -->
<x-modal name="misc-fee-master-modal" title="Msic Fee Management" maxWidth="2xl">
    <form action="{{ route('school.fee-master.store') }}" method="POST" class="p-6 space-y-6">
        @csrf
        <div class="space-y-4">
            <div class="flex items-center">
                <label for="misc_class_id" class="w-32 text-sm font-bold text-gray-700">Class</label>
                <div class="flex-1">
                    <select name="class_id" id="misc_class_id" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all" required>
                        <option value="">Select Class Name</option>
                        @foreach($classes as $class)
                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            
            <div class="flex items-center">
                <label for="misc_fee_type_id" class="w-32 text-sm font-bold text-gray-700">Fee Type</label>
                <div class="flex-1">
                    <select name="fee_type_id" id="misc_fee_type_id" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all" required>
                        <option value="">Select Class Name</option>
                        @foreach($feeTypes as $feeType)
                        <option value="{{ $feeType->id }}">{{ $feeType->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="flex items-center" x-data="{ selectedFeeNameId: '' }">
                <label for="misc_fee_name_id" class="w-32 text-sm font-bold text-gray-700">Fee Name</label>
                <div class="flex-1">
                    <select x-model="selectedFeeNameId" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all" required>
                        <option value="">Select Class Name</option>
                        @foreach($feeNames as $feeName)
                        <option value="{{ $feeName->id }}">{{ $feeName->name }}</option>
                        @endforeach
                    </select>
                    <!-- Hidden input to match the bulk entry 'amounts[id]' structure -->
                    <template x-if="selectedFeeNameId">
                        <input type="hidden" x-bind:name="'amounts[' + selectedFeeNameId + ']'" x-bind:value="$refs.miscAmount.value">
                    </template>
                </div>
            </div>

            <div class="flex items-center">
                <label for="misc_amount" class="w-32 text-sm font-bold text-gray-700">Fee Amount</label>
                <div class="flex-1">
                    <input type="number" id="misc_amount" x-ref="miscAmount" step="0.01" min="0" placeholder="Enter Fee amount" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all" required>
                </div>
            </div>
        </div>

        <div class="flex justify-end space-x-3 pt-4 border-t">
            <button type="button" @click="show = false" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-6 rounded transition-colors">
                Close
            </button>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded shadow-md transition-all active:scale-95">
                Submit
            </button>
        </div>
    </form>
</x-modal>

<!-- Edit Modal -->
<div x-data="{ fee: null }" @open-modal.window="if ($event.detail.name === 'edit-fee-master-modal') { fee = $event.detail.fee; $dispatch('open-actual-edit-modal'); }">
    <x-modal name="edit-fee-master-modal-actual" title="Edit Fee Configuration" focusable>
        <form x-bind:action="'{{ route('school.fee-master.update', '') }}/' + fee.id" method="POST" class="p-6 space-y-5" x-show="fee">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 gap-4">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Class</label>
                    <div class="w-full px-3 py-2 border border-gray-200 rounded-md bg-gray-50 text-gray-600 text-sm" x-text="fee?.class?.name"></div>
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Fee Name</label>
                    <div class="w-full px-3 py-2 border border-gray-200 rounded-md bg-gray-50 text-gray-600 text-sm" x-text="fee?.fee_name?.name"></div>
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Fee Type</label>
                    <div class="w-full px-3 py-2 border border-gray-200 rounded-md bg-gray-50 text-gray-600 text-sm" x-text="fee?.fee_type?.name"></div>
                </div>
                <div>
                    <label for="edit_amount" class="block text-sm font-bold text-gray-700 mb-1">Amount</label>
                    <input type="number" name="amount" id="edit_amount" step="0.01" min="0" x-bind:value="fee?.amount" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all" required>
                </div>
            </div>
            <div class="flex justify-end space-x-3 pt-6 border-t">
                <button type="button" @click="show = false" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-6 rounded transition-colors">
                    Close
                </button>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded shadow-md transition-all active:scale-95">
                    Update
                </button>
            </div>
        </form>
    </x-modal>
    
    <!-- Hidden trigger to open the actual modal after setting data -->
    <div @open-actual-edit-modal.window="$dispatch('open-modal', 'edit-fee-master-modal-actual')"></div>
</div>
@endsection
