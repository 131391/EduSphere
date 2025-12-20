@extends('layouts.school')

@section('title', 'Receipt Note')

@section('content')
<div class="w-full">
    <div class="flex items-center justify-between mb-6">
        <a href="{{ url()->previous() }}" class="text-blue-600 hover:text-blue-800 font-medium flex items-center">
            <i class="fas fa-angle-double-left mr-2"></i> Back
        </a>
    </div>

    <div class="bg-yellow-400 text-white px-4 py-2 rounded-t-lg font-bold text-lg">
        Update Note
    </div>

    @if(session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 relative mb-6">
        <span class="block sm:inline">{{ session('success') }}</span>
    </div>
    @endif

    <div class="bg-white shadow-lg rounded-b-lg p-6 border border-gray-200">
        <form action="{{ route('school.settings.receipt-note.update') }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Registration Fee Receipt Note -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-start">
                <label class="block text-sm font-medium text-gray-700 md:text-right pt-2">Registration Fee Receipt Note</label>
                <div class="md:col-span-3">
                    <textarea name="registration_receipt_note" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('registration_receipt_note', $settings['registration_receipt_note'] ?? '') }}</textarea>
                </div>
            </div>

            <!-- Admission Fee Receipt Note -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-start">
                <label class="block text-sm font-medium text-gray-700 md:text-right pt-2">Admission Fee Receipt Note</label>
                <div class="md:col-span-3">
                    <textarea name="admission_receipt_note" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('admission_receipt_note', $settings['admission_receipt_note'] ?? '') }}</textarea>
                </div>
            </div>

            <!-- Fee Receipt Note -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-start">
                <label class="block text-sm font-medium text-gray-700 md:text-right pt-2">Fee Receipt Note</label>
                <div class="md:col-span-3">
                    <textarea name="fee_receipt_note" rows="2" class="w-full px-3 py-2 border border-blue-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 ring-2 ring-blue-100">{{ old('fee_receipt_note', $settings['fee_receipt_note'] ?? '') }}</textarea>
                </div>
            </div>

            <div class="flex justify-end pt-4">
                <button type="submit" class="bg-[#009688] text-white px-6 py-2 rounded shadow hover:bg-[#00796b] focus:outline-none focus:ring-2 focus:ring-[#009688] focus:ring-offset-2 uppercase font-medium text-sm">
                    Update
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
