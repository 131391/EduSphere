@extends('layouts.school')

@section('title', 'Edit Student - ' . $student->full_name)

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center space-x-4">
        <a href="{{ route('school.students.show', $student->id) }}" class="p-2 bg-white border border-gray-200 rounded-lg text-gray-400 hover:text-blue-600 hover:border-blue-200 transition shadow-sm">
            <i class="fas fa-chevron-left"></i>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Edit Student Record</h1>
            <p class="text-sm text-gray-600">Updating information for {{ $student->admission_no }}</p>
        </div>
    </div>

    <form action="{{ route('school.students.update', $student->id) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-50 bg-gray-50/50">
                <h3 class="text-base font-bold text-gray-900">Basic Information</h3>
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="first_name" class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-1">First Name</label>
                    <input type="text" name="first_name" id="first_name" value="{{ old('first_name', $student->first_name) }}" required
                           class="w-full rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500 @error('first_name') border-red-500 @enderror">
                    @error('first_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="last_name" class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-1">Last Name</label>
                    <input type="text" name="last_name" id="last_name" value="{{ old('last_name', $student->last_name) }}" required
                           class="w-full rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500 @error('last_name') border-red-500 @enderror">
                    @error('last_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="email" class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-1">Email Address</label>
                    <input type="email" name="email" id="email" value="{{ old('email', $student->email) }}"
                           class="w-full rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500 @error('email') border-red-500 @enderror">
                    @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="phone" class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-1">Phone Number</label>
                    <input type="text" name="phone" id="phone" value="{{ old('phone', $student->phone) }}"
                           class="w-full rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500 @error('phone') border-red-500 @enderror">
                    @error('phone') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-50 bg-gray-50/50">
                <h3 class="text-base font-bold text-gray-900">Academic & Status</h3>
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="class_id" class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-1">Class</label>
                    <select name="class_id" id="class_id" required class="w-full rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500">
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}" {{ old('class_id', $student->class_id) == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="section_id" class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-1">Section</label>
                    <select name="section_id" id="section_id" required class="w-full rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500">
                        @foreach($sections as $section)
                            <option value="{{ $section->id }}" {{ old('section_id', $student->section_id) == $section->id ? 'selected' : '' }}>{{ $section->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="status" class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-1">Status</label>
                    <select name="status" id="status" required class="w-full rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500">
                        <option value="active" {{ old('status', $student->status->value ?? $student->status) == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status', $student->status->value ?? $student->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        <option value="withdrawn" {{ old('status', $student->status->value ?? $student->status) == 'withdrawn' ? 'selected' : '' }}>Withdrawn</option>
                        <option value="graduated" {{ old('status', $student->status->value ?? $student->status) == 'graduated' ? 'selected' : '' }}>Graduated</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-50 bg-gray-50/50">
                <h3 class="text-base font-bold text-gray-900">Address</h3>
            </div>
            <div class="p-6">
                <label for="address" class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-1">Current Address</label>
                <textarea name="address" id="address" rows="3" 
                          class="w-full rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500">{{ old('address', $student->address) }}</textarea>
                @error('address') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="flex items-center justify-end space-x-4">
            <a href="{{ route('school.students.show', $student->id) }}" class="px-6 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition font-medium">
                Cancel
            </a>
            <button type="submit" class="px-10 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-bold shadow-md shadow-blue-200">
                Update Student
            </button>
        </div>
    </form>
</div>
@endsection
