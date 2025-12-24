@extends('layouts.school')

@section('title', 'Set Session')

@section('content')
<div class="w-full">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Set Session</h1>
            <p class="text-gray-600 mt-1">Configure the current academic session for the school</p>
        </div>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6 rounded-r-lg shadow-sm">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-check-circle text-green-500"></i>
            </div>
            <div class="ml-3">
                <p class="text-sm text-green-700">{{ session('success') }}</p>
            </div>
        </div>
    </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100 bg-gray-50">
            <h3 class="text-lg font-semibold text-gray-800">Session Configuration</h3>
            <p class="text-sm text-gray-500 mt-1">Select the active academic year for all operations</p>
        </div>
        
        <form action="{{ route('school.settings.session.update') }}" method="POST" class="p-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Current Academic Year</label>
                    <div class="relative">
                        <select name="current_session_id" class="w-full pl-4 pr-10 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 appearance-none bg-white transition-colors" required>
                            <option value="">Select Session</option>
                            @foreach($academicYears as $year)
                                <option value="{{ $year->id }}" {{ $currentSessionId == $year->id ? 'selected' : '' }}>
                                    {{ $year->name }} ({{ $year->start_date->format('Y') }} - {{ $year->end_date->format('Y') }})
                                </option>
                            @endforeach
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-500">
                            <i class="fas fa-chevron-down text-xs"></i>
                        </div>
                    </div>
                    @error('current_session_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    <p class="text-xs text-gray-500 mt-2">
                        <i class="fas fa-info-circle mr-1"></i>
                        Changing the session will affect all data displayed in the dashboard.
                    </p>
                </div>
            </div>

            <div class="mt-8 pt-6 border-t border-gray-100 flex justify-end">
                <button type="submit" class="bg-blue-600 text-white px-6 py-2.5 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all shadow-sm hover:shadow flex items-center">
                    <i class="fas fa-save mr-2"></i>
                    Update Session
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
