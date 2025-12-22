@extends('layouts.school')

@section('content')
<div class="p-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Edit Student Registration</h1>
            <p class="text-gray-600 dark:text-gray-400">Update the details for registration: {{ $studentRegistration->registration_no }}</p>
        </div>
        <a href="{{ route('school.student-registrations.index') }}" class="text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-white transition-colors flex items-center gap-2">
            <i class="fas fa-arrow-left"></i>
            <span>Back to List</span>
        </a>
    </div>

    @if ($errors->any())
        <div class="mb-6 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
            <ul class="list-disc list-inside text-sm text-red-600 dark:text-red-400">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('school.student-registrations.update', $studentRegistration->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        
        {{-- Admission Status (Only in Edit) --}}
        <div class="mb-6 bg-white dark:bg-gray-800 p-6 rounded-lg border border-gray-200 dark:border-gray-700">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Admission Status <span class="text-red-500">*</span>
            </label>
            <select name="admission_status" required class="w-full md:w-1/3 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                <option value="Pending" {{ old('admission_status', $studentRegistration->admission_status) == 'Pending' ? 'selected' : '' }}>Pending</option>
                <option value="Admitted" {{ old('admission_status', $studentRegistration->admission_status) == 'Admitted' ? 'selected' : '' }}>Admitted</option>
                <option value="Cancelled" {{ old('admission_status', $studentRegistration->admission_status) == 'Cancelled' ? 'selected' : '' }}>Cancelled</option>
            </select>
        </div>

        @include('school.student-registrations.partials.form')
    </form>
</div>
@endsection
