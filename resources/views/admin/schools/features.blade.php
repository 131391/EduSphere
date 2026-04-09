@extends('layouts.admin')

@section('title', 'School Features')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Feature Management</h1>
            <p class="text-gray-600 mt-1">Manage available modules for {{ $school->name }}</p>
        </div>
        <a href="{{ route('admin.schools.index') }}" class="text-sm font-medium text-blue-600 hover:text-blue-500">
            &larr; Back to Schools
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded mb-6">
            <p class="text-green-700">{{ session('success') }}</p>
        </div>
    @endif

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <form action="{{ route('admin.schools.update-features', $school->id) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">Core Modules</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                    @foreach($availableFeatures as $key => $label)
                        <label class="relative flex items-start p-4 border rounded-xl cursor-pointer hover:bg-gray-50 transition-colors {{ in_array($key, $features) ? 'bg-blue-50 border-blue-200' : 'border-gray-200' }}">
                            <div class="flex items-center h-5">
                                <input type="checkbox" name="features[]" value="{{ $key }}" 
                                       {{ in_array($key, $features) ? 'checked' : '' }}
                                       class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded">
                            </div>
                            <div class="ml-3 text-sm">
                                <span class="font-medium text-gray-900">{{ $label }}</span>
                            </div>
                        </label>
                    @endforeach
                </div>

                <h3 class="text-lg font-semibold text-gray-800 mt-10 mb-4 border-b pb-2">Premium Add-ons</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                    @foreach($premiumFeatures as $key => $label)
                        <label class="relative flex items-start p-4 border rounded-xl cursor-pointer hover:bg-gray-50 transition-colors {{ in_array($key, $features) ? 'bg-orange-50 border-orange-200' : 'border-gray-200' }}">
                            <div class="flex items-center h-5">
                                <input type="checkbox" name="features[]" value="{{ $key }}" 
                                       {{ in_array($key, $features) ? 'checked' : '' }}
                                       class="focus:ring-orange-500 h-4 w-4 text-orange-600 border-gray-300 rounded">
                            </div>
                            <div class="ml-3 text-sm">
                                <span class="font-medium text-gray-900">{{ $label }}</span>
                                <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-orange-100 text-orange-800">
                                    PRO
                                </span>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="px-6 py-4 bg-gray-50 text-right border-t border-gray-200">
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium">
                    Save Feature Configuration
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
