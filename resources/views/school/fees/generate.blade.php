@extends('layouts.school')

@section('title', 'Generate Fees')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <h1 class="text-2xl font-bold text-gray-900">Generate Bulk Fees</h1>
    <a href="{{ route('school.fees.index') }}" class="text-gray-600 hover:text-gray-900 flex items-center">
        <i class="fas fa-arrow-left mr-2"></i>
        Back to List
    </a>
</div>

<div class="bg-white rounded-lg shadow p-6">
    <form action="{{ route('school.fees.store') }}" method="POST">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Class Selection -->
            <div>
                <label for="class_id" class="block text-sm font-medium text-gray-700 mb-1">Select Class <span class="text-red-500">*</span></label>
                <select name="class_id" id="class_id" required class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">-- Choose Class --</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}" {{ old('class_id') == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                    @endforeach
                </select>
                @error('class_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Academic Year -->
            <div>
                <label for="academic_year_id" class="block text-sm font-medium text-gray-700 mb-1">Academic Year <span class="text-red-500">*</span></label>
                <select name="academic_year_id" id="academic_year_id" required class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @foreach($academicYears as $year)
                        <option value="{{ $year->id }}" {{ old('academic_year_id') == $year->id || $year->is_active ? 'selected' : '' }}>{{ $year->name }}</option>
                    @endforeach
                </select>
                @error('academic_year_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Fee Type -->
            <div>
                <label for="fee_type_id" class="block text-sm font-medium text-gray-700 mb-1">Fee Category (Type) <span class="text-red-500">*</span></label>
                <select name="fee_type_id" id="fee_type_id" required class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">-- Choose Type --</option>
                    @foreach($feeTypes as $type)
                        <option value="{{ $type->id }}" {{ old('fee_type_id') == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                    @endforeach
                </select>
                @error('fee_type_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Fee Period -->
            <div>
                <label for="fee_period" class="block text-sm font-medium text-gray-700 mb-1">Fee Period <span class="text-red-500">*</span></label>
                <input type="text" name="fee_period" id="fee_period" placeholder="e.g. April 2025" required value="{{ old('fee_period', date('F Y')) }}" 
                       class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <p class="text-xs text-gray-500 mt-1">This will appear on the student's bill.</p>
                @error('fee_period') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Due Date -->
            <div>
                <label for="due_date" class="block text-sm font-medium text-gray-700 mb-1">Due Date <span class="text-red-500">*</span></label>
                <input type="date" name="due_date" id="due_date" required value="{{ old('due_date', date('Y-m-d', strtotime('+10 days'))) }}" 
                       class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                @error('due_date') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <!-- Fee Names (Multi-select) -->
        <div class="mt-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Select Fee Heads <span class="text-red-500">*</span></label>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 p-4 border rounded-lg bg-gray-50">
                @foreach($feeNames as $name)
                <div class="flex items-center">
                    <input type="checkbox" name="fee_name_ids[]" value="{{ $name->id }}" id="fee_name_{{ $name->id }}" 
                           class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                           {{ is_array(old('fee_name_ids')) && in_array($name->id, old('fee_name_ids')) ? 'checked' : '' }}>
                    <label for="fee_name_{{ $name->id }}" class="ml-2 text-sm text-gray-700">{{ $name->name }}</label>
                </div>
                @endforeach
            </div>
            @error('fee_name_ids') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="mt-8 border-t pt-6 flex justify-end">
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 flex items-center">
                <i class="fas fa-magic mr-2"></i>
                Generate Now
            </button>
        </div>
    </form>
</div>
@endsection
