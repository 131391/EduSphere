@extends('layouts.student')

@section('title', 'Timetable')
@section('page-title', 'Timetable')

@section('content')
<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-8 text-center">
    <i class="fas fa-clock text-5xl text-indigo-300 dark:text-indigo-600 mb-4"></i>
    <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300 mb-2">Timetable</h3>
    @if($student->class)
    <p class="text-gray-500 dark:text-gray-400 text-sm">
        Class: <span class="font-medium text-gray-700 dark:text-gray-300">{{ $student->class->name }}</span>
        @if($student->section)
        &middot; Section: <span class="font-medium text-gray-700 dark:text-gray-300">{{ $student->section->name }}</span>
        @endif
    </p>
    @endif
    <p class="text-gray-400 dark:text-gray-500 text-sm mt-4">
        Your timetable will appear here once it has been published by the school.
    </p>
</div>
@endsection
