@extends('layouts.school')

@section('title', 'Admission Fee')

@section('content')
<div class="container mx-auto px-4 py-6" x-data>
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Admission Fee</h1>
        <button @click="$dispatch('open-modal', 'add-admission-fee-modal')" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded flex items-center">
            <i class="fas fa-plus mr-2"></i> ADD
        </button>
    </div>

    @if(session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
        <span class="block sm:inline">{{ session('success') }}</span>
    </div>
    @endif

    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <input type="checkbox" class="form-checkbox h-4 w-4 text-blue-600">
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        SR NO
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        CLASS
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        FEE
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        DATE
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        ACTION
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($fees as $index => $fee)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <input type="checkbox" class="form-checkbox h-4 w-4 text-blue-600">
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $fees->firstItem() + $index }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                        {{ $fee->class->name }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ number_format($fee->amount, 2) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $fee->created_at->format('F j, Y, g:i a') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <div class="flex space-x-2">
                            <button @click="$dispatch('open-modal', {name: 'edit-admission-fee-modal', fee: {{ $fee }}})" class="text-indigo-600 hover:text-indigo-900">
                                <i class="fas fa-edit"></i>
                            </button>
                            <form id="delete-adm-fee-{{ $fee->id }}" action="{{ route('school.settings.admission-fee.destroy', $fee->id) }}" method="POST" class="inline-block">
                                @csrf
                                @method('DELETE')
                                <button type="button" @click="$dispatch('confirm-delete', { formId: 'delete-adm-fee-{{ $fee->id }}', message: 'Are you sure you want to delete this admission fee?' })" class="text-red-600 hover:text-red-900">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                        No admission fees found.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $fees->links() }}
        </div>
    </div>
</div>

<!-- Add Modal -->
<x-modal name="add-admission-fee-modal" title="Admission Fee">
    <form action="{{ route('school.settings.admission-fee.store') }}" method="POST" class="space-y-4">
        @csrf
        <div>
            <label for="class_id" class="block text-sm font-medium text-gray-700 mb-1">Class</label>
            <select name="class_id" id="class_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                <option value="">Select Class</option>
                @foreach($classes as $class)
                <option value="{{ $class->id }}">{{ $class->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="amount" class="block text-sm font-medium text-gray-700 mb-1">Fee</label>
            <input type="number" name="amount" id="amount" step="0.01" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Enter Fee" required>
        </div>
        <div class="flex justify-end space-x-3 pt-4">
            <button type="button" @click="show = false" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded">
                Close
            </button>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Submit
            </button>
        </div>
    </form>
</x-modal>

<!-- Edit Modal -->
<div x-data="{ fee: null }" @open-modal.window="if ($event.detail.name === 'edit-admission-fee-modal') { fee = $event.detail.fee; $dispatch('open-actual-edit-modal'); }">
    <x-modal name="edit-admission-fee-modal-actual" title="Edit Admission Fee" focusable>
        <form x-bind:action="'{{ route('school.settings.admission-fee.update', '') }}/' + fee.id" method="POST" class="space-y-4" x-show="fee">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Class</label>
                <input type="text" x-bind:value="fee?.class?.name" class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100 cursor-not-allowed focus:outline-none" disabled>
            </div>
            <div>
                <label for="edit_amount" class="block text-sm font-medium text-gray-700 mb-1">Fee</label>
                <input type="number" name="amount" id="edit_amount" step="0.01" min="0" x-bind:value="fee?.amount" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>
            <div class="flex justify-end space-x-3 pt-4">
                <button type="button" @click="show = false" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded">
                    Close
                </button>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Update
                </button>
            </div>
        </form>
    </x-modal>
    
    <!-- Hidden trigger to open the actual modal after setting data -->
    <div @open-actual-edit-modal.window="$dispatch('open-modal', 'edit-admission-fee-modal-actual')"></div>
</div>
@endsection
