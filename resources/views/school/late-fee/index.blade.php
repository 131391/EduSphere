@extends('layouts.school')

@section('title', 'Late Fee Management')

@section('content')
<div class="container mx-auto px-4 py-6" x-data="{}">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Late Fee Management</h1>
        <button @click="$dispatch('open-modal', 'update-late-fee-modal')" class="bg-[#2e7d32] hover:bg-[#1b5e20] text-white font-bold py-2 px-4 rounded flex items-center transition-colors">
            Update Late Fee
        </button>
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
                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">FINE DATE</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">LATE FEE</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">CREATE DATE</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">ACTION</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($lateFees as $index => $feeItem)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $lateFees->firstItem() + $index }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                        {{ $feeItem->fine_date }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 font-semibold">
                        {{ number_format($feeItem->late_fee_amount, 2) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                        {{ $feeItem->created_at->format('M d, Y, h:i a') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <div class="flex space-x-3">
                            <button @click="$dispatch('open-modal', {name: 'update-late-fee-modal', fee: {{ $feeItem }}})" class="text-indigo-600 hover:text-indigo-900 transition-colors">
                                <i class="fas fa-edit"></i>
                            </button>
                            <form id="delete-late-fee-{{ $feeItem->id }}" action="{{ route('school.late-fee.destroy', $feeItem->id) }}" method="POST" class="inline-block">
                                @csrf
                                @method('DELETE')
                                <button type="button" @click="$dispatch('confirm-delete', { formId: 'delete-late-fee-{{ $feeItem->id }}', message: 'Are you sure you want to delete this late fee configuration?' })" class="text-red-500 hover:text-red-700 transition-colors">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center py-10">
                        <div class="flex flex-col items-center">
                            <i class="fas fa-info-circle text-gray-300 text-4xl mb-2"></i>
                            <span>No late fee configurations found.</span>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
            {{ $lateFees->links() }}
        </div>
    </div>
</div>

<!-- Update Modal -->
    fee: { id: '{{ old('fee_id') }}', late_fee_amount: '{{ old('late_fee_amount') }}', fine_date: '{{ old('fine_date') }}' },
    get action() {
        return this.fee.id ? '{{ route('school.late-fee.update', ':id') }}'.replace(':id', this.fee.id) : '{{ route('school.late-fee.store') }}';
    },
    init() {
        @if($errors->any())
            this.$nextTick(() => {
                this.$dispatch('open-modal', 'update-late-fee-modal');
            });
        @endif
    }
} @open-modal.window="if ($event.detail.name === 'update-late-fee-modal') { fee = { ...$event.detail.fee }; } else if ($event.detail === 'update-late-fee-modal') { fee = { id: null, late_fee_amount: '', fine_date: '' }; }">
    <x-modal name="update-late-fee-modal" title="Late Fee Configuration" maxWidth="xl">
        <form :action="action" method="POST" class="p-6 space-y-6">
            @csrf
            <template x-if="fee.id">
                @method('PUT')
            </template>
            
            <div class="space-y-4">
                <div class="flex items-center">
                    <label for="late_fee_amount" class="w-32 text-sm font-bold text-gray-700">Late Fee</label>
                    <div class="flex-1">
                        <input type="number" name="late_fee_amount" id="late_fee_amount" step="0.01" min="0" x-model="fee.late_fee_amount" placeholder="Enter Late Fee" class="w-full px-4 py-2 border @error('late_fee_amount') border-red-500 @else border-gray-300 @enderror rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all">
                        @error('late_fee_amount')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                
                <div class="flex items-center">
                    <label for="fine_date" class="w-32 text-sm font-bold text-gray-700">Select Late Fine Date</label>
                    <div class="flex-1">
                        <select name="fine_date" id="fine_date" class="w-full px-4 py-2 border @error('fine_date') border-red-500 @else border-gray-300 @enderror rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all" x-model="fee.fine_date">
                            <option value="">Select Late Fine Date</option>
                            @for($i = 1; $i <= 31; $i++)
                            <option value="{{ $i }}">{{ $i }}</option>
                            @endfor
                        </select>
                        @error('fine_date')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="flex justify-end space-x-3 pt-4 border-t">
                <input type="hidden" name="fee_id" x-model="fee.id">
                <button type="button" @click="$dispatch('close-modal', 'update-late-fee-modal')" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-6 rounded transition-colors">
                    Close
                </button>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded shadow-md transition-all active:scale-95" x-text="fee.id ? 'Update' : 'Save'">
                </button>
            </div>
        </form>
    </x-modal>
</div>
@endsection
