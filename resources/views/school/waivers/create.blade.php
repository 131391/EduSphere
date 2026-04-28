@extends('layouts.school')

@section('title', 'Apply Fee Waiver')

@section('content')
<div class="w-full space-y-6">
    <div class="flex items-center space-x-4">
        <a href="{{ route('school.waivers.index') }}" class="p-2 bg-white border border-gray-200 rounded-lg text-gray-400 hover:text-blue-600 hover:border-blue-200 transition shadow-sm">
            <i class="fas fa-chevron-left"></i>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Apply Fee Waiver</h1>
            <p class="text-sm text-gray-600">Grant fee concessions to a specific student</p>
        </div>
    </div>

    <form action="{{ route('school.waivers.store') }}" method="POST" class="space-y-6">
        @csrf

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-50 bg-gray-50/50">
                <h3 class="text-base font-bold text-gray-900">Waiver Target</h3>
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label for="student_id" class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-1">Select Student</label>
                    <select name="student_id" id="student_id" required class="w-full rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500 @error('student_id') border-red-500 @enderror">
                        <option value="">Select Student</option>
                        @foreach($students as $student)
                            <option value="{{ $student->id }}" {{ old('student_id') == $student->id ? 'selected' : '' }}>
                                {{ $student->full_name }} ({{ $student->admission_no }})
                            </option>
                        @endforeach
                    </select>
                    @error(\'student_id\')<p class="modal-error-message">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="academic_year_id" class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-1">Academic Year</label>
                    <select name="academic_year_id" id="academic_year_id" required class="w-full rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500">
                        @foreach($academicYears as $year)
                            <option value="{{ $year->id }}" {{ old('academic_year_id') == $year->id ? 'selected' : '' }}>{{ $year->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="fee_period" class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-1">Fee Period</label>
                    <input type="text" name="fee_period" id="fee_period" value="{{ old('fee_period', 'Monthly') }}" required placeholder="e.g. Monthly, Term 1"
                           class="w-full rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500">
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-50 bg-gray-50/50">
                <h3 class="text-base font-bold text-gray-900">Calculation Details</h3>
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6" x-data="{ actual: 0, percent: 0, amount: 0 }">
                <div>
                    <label for="actual_fee" class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-1">Actual Fee (per period)</label>
                    <input type="number" step="0.01" name="actual_fee" id="actual_fee" x-model.number="actual" required
                           class="w-full rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div>
                    <label for="upto_months" class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-1">Duration (Months)</label>
                    <input type="number" name="upto_months" id="upto_months" value="{{ old('upto_months', 12) }}"
                           class="w-full rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div class="md:col-span-2 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-xl border border-blue-100 dark:border-blue-800 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="waiver_percentage" class="block text-xs font-bold text-blue-600 uppercase tracking-widest mb-1">Waiver Percentage (%)</label>
                        <input type="number" step="0.01" name="waiver_percentage" id="waiver_percentage" x-model.number="percent" @input="amount = (actual * percent / 100).toFixed(2)"
                               class="w-full rounded-lg border-blue-300 focus:ring-blue-500 focus:border-blue-500 bg-white shadow-inner">
                    </div>
                    <div>
                        <label for="waiver_amount" class="block text-xs font-bold text-blue-600 uppercase tracking-widest mb-1">OR Fixed Waiver Amount (₹)</label>
                        <input type="number" step="0.01" name="waiver_amount" id="waiver_amount" x-model.number="amount" @input="percent = actual > 0 ? (amount / actual * 100).toFixed(2) : 0"
                               class="w-full rounded-lg border-blue-300 focus:ring-blue-500 focus:border-blue-500 bg-white shadow-inner">
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-50 bg-gray-50/50">
                <h3 class="text-base font-bold text-gray-900">Justification</h3>
            </div>
            <div class="p-6">
                <label for="reason" class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-1">Reason for Waiver</label>
                <textarea name="reason" id="reason" rows="3" required placeholder="Describe why this student is eligible..."
                          class="w-full rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500">{{ old('reason') }}</textarea>
                @error(\'reason\')<p class="modal-error-message">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="flex items-center justify-end space-x-4">
            <a href="{{ route('school.waivers.index') }}" class="px-6 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition font-medium">
                Cancel
            </a>
            <button type="submit" class="px-10 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-bold shadow-md shadow-blue-200">
                Apply Waiver
            </button>
        </div>
    </form>
</div>
@endsection
