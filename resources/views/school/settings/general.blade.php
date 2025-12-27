@extends('layouts.school')

@section('title', 'General Settings')

@section('content')
<div class="w-full">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">General Settings</h1>
            <p class="text-gray-600 mt-1">Manage fees, fines, and notes</p>
        </div>
    </div>

    @if(session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg relative mb-6">
        <span class="block sm:inline">{{ session('success') }}</span>
    </div>
    @endif

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <form action="{{ route('school.settings.general.update') }}" method="POST" class="p-6 space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Registration Fee -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Registration Fee</label>
                    <div class="relative rounded-md shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 sm:text-sm">₹</span>
                        </div>
                        <input type="number" name="registration_fee" value="{{ old('registration_fee', $settings['registration_fee'] ?? '') }}" class="focus:ring-blue-500 focus:border-blue-500 block w-full pl-7 pr-12 sm:text-sm border-gray-300 rounded-md" placeholder="0.00">
                    </div>
                </div>

                <!-- Admission Fee -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Admission Fee</label>
                    <div class="relative rounded-md shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 sm:text-sm">₹</span>
                        </div>
                        <input type="number" name="admission_fee" value="{{ old('admission_fee', $settings['admission_fee'] ?? '') }}" class="focus:ring-blue-500 focus:border-blue-500 block w-full pl-7 pr-12 sm:text-sm border-gray-300 rounded-md" placeholder="0.00">
                    </div>
                </div>

                <!-- Late Return Library Book Fine -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Late Return Library Book Fine (Per Day)</label>
                    <div class="relative rounded-md shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 sm:text-sm">₹</span>
                        </div>
                        <input type="number" name="late_return_library_book_fine" value="{{ old('late_return_library_book_fine', $settings['late_return_library_book_fine'] ?? '') }}" class="focus:ring-blue-500 focus:border-blue-500 block w-full pl-7 pr-12 sm:text-sm border-gray-300 rounded-md" placeholder="0.00">
                    </div>
                </div>

                <!-- Admission Fee Applicable -->
                <div class="flex items-center h-full pt-6">
                    <label class="inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="admission_fee_applicable" value="1" {{ !empty($settings['admission_fee_applicable']) ? 'checked' : '' }} class="form-checkbox h-5 w-5 text-blue-600 rounded border-gray-300 focus:ring-blue-500">
                        <span class="ml-2 text-gray-700">Admission Fee Applicable</span>
                    </label>
                </div>

                <!-- Receipt Note -->
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Receipt Note</label>
                    <textarea name="receipt_note" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Note to appear on receipts...">{{ old('receipt_note', $settings['receipt_note'] ?? '') }}</textarea>
                </div>
            </div>

            <div class="flex justify-end pt-6">
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    Save Settings
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
