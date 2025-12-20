@extends('layouts.school')

@section('title', 'Logo Update')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Logo Update</h1>
            <p class="text-gray-600 mt-1">Update school logo</p>
        </div>
    </div>

    @if(session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg relative mb-6">
        <span class="block sm:inline">{{ session('success') }}</span>
    </div>
    @endif

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <form action="{{ route('school.settings.logo.update') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-6">
            @csrf
            @method('PUT')

            <div class="flex flex-col items-center space-y-6">
                <!-- Current Logo -->
                <div class="w-48 h-48 bg-gray-100 rounded-lg flex items-center justify-center overflow-hidden border-2 border-dashed border-gray-300">
                    @if($school->logo)
                        <img src="{{ Storage::url($school->logo) }}" alt="School Logo" class="w-full h-full object-contain">
                    @else
                        <div class="text-gray-400 text-center">
                            <i class="fas fa-image text-4xl mb-2"></i>
                            <p class="text-sm">No Logo Uploaded</p>
                        </div>
                    @endif
                </div>

                <!-- Upload Input -->
                <div class="w-full">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Choose New Logo</label>
                    <input type="file" name="logo" accept="image/*" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    <p class="text-xs text-gray-500 mt-1">Supported formats: JPEG, PNG, GIF. Max size: 2MB.</p>
                    @error('logo') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="flex justify-end pt-6">
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    Update Logo
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
