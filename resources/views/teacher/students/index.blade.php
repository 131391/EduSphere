@extends('layouts.teacher')

@section('title', 'My Students')
@section('page-title', 'My Students')

@section('content')
<div class="space-y-6">

    <!-- Filters -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
        <form method="GET" action="{{ route('teacher.students.index') }}" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1 uppercase tracking-wide">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Name or admission no..."
                       class="w-full px-3 py-2 border border-gray-200 dark:border-gray-600 rounded-lg text-sm bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-indigo-500">
            </div>
            <div class="flex-1 min-w-[160px]">
                <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1 uppercase tracking-wide">Class</label>
                <select name="class_id" class="w-full px-3 py-2 border border-gray-200 dark:border-gray-600 rounded-lg text-sm bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-indigo-500">
                    <option value="">All Classes</option>
                    @foreach($classes as $class)
                    <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                        {{ $class->name }}
                    </option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors">
                <i class="fas fa-search mr-1.5"></i>Search
            </button>
            @if(request()->hasAny(['search','class_id']))
            <a href="{{ route('teacher.students.index') }}" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 text-sm font-medium rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                Clear
            </a>
            @endif
        </form>
    </div>

    <!-- Students Table -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
            <h3 class="text-base font-semibold text-gray-800 dark:text-white">Students</h3>
            <span class="text-sm text-gray-400">{{ $students->total() }} total</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-700/50">
                    <tr>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Student</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Admission No.</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Class / Section</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Contact</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($students as $student)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                        <td class="px-6 py-3">
                            <div class="flex items-center gap-3">
                                @if($student->photo)
                                <img src="{{ asset('storage/' . $student->photo) }}" class="w-8 h-8 rounded-full object-cover" alt="">
                                @else
                                <div class="w-8 h-8 rounded-full bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center text-xs font-bold text-indigo-600 dark:text-indigo-400">
                                    {{ strtoupper(substr($student->first_name, 0, 1)) }}
                                </div>
                                @endif
                                <div>
                                    <p class="font-medium text-gray-800 dark:text-gray-200">{{ $student->full_name }}</p>
                                    <p class="text-xs text-gray-400">Roll: {{ $student->roll_no ?? '—' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-400 font-mono text-xs">{{ $student->admission_no }}</td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-400">
                            {{ optional($student->class)->name }} {{ optional($student->section)->name }}
                        </td>
                        <td class="px-4 py-3 text-gray-500 dark:text-gray-400 text-xs">{{ $student->phone ?? '—' }}</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('teacher.students.show', $student->id) }}"
                               class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/20 hover:bg-indigo-100 dark:hover:bg-indigo-900/40 rounded-lg transition-colors">
                                View
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-16 text-center">
                            <i class="fas fa-users text-4xl text-gray-300 dark:text-gray-600 mb-3 block"></i>
                            <p class="text-gray-500 dark:text-gray-400">No students found.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($students->hasPages())
        <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700">
            {{ $students->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
