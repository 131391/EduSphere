@extends('layouts.teacher')

@section('title', $title ?? 'Teacher Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<div class="space-y-6">
    <!-- Welcome Banner -->
    <div class="bg-gradient-to-r from-[#1a237e] to-indigo-600 rounded-2xl shadow-lg p-8 text-white relative overflow-hidden">
        <div class="absolute inset-0 bg-black/10"></div>
        <div class="relative z-10">
            <h2 class="text-3xl font-bold mb-2">Welcome back, {{ Auth::user()->name ?? 'Teacher' }}! 🎓</h2>
            <p class="text-indigo-100 text-lg">Here's what's happening in your classes today.</p>
        </div>
        <!-- Decorative icon -->
        <i class="fas fa-chalkboard-teacher absolute -right-4 -bottom-8 text-white/10" style="font-size: 12rem;"></i>
    </div>

    <!-- Quick Stats Overview -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Attendance Stats -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 flex flex-col justify-between hover:shadow-md transition-shadow">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Today's Attendance</p>
                    <h3 class="text-3xl font-bold text-gray-900 dark:text-white mt-2">--</h3>
                </div>
                <div class="w-12 h-12 bg-green-50 text-green-600 rounded-xl flex items-center justify-center text-xl">
                    <i class="fas fa-calendar-check"></i>
                </div>
            </div>
            <div class="mt-4">
                <a href="{{ route('teacher.attendance.index') }}" class="text-sm text-indigo-600 hover:text-indigo-700 font-medium flex items-center">
                    Mark Attendance <i class="fas fa-arrow-right ml-1 text-xs"></i>
                </a>
            </div>
        </div>

        <!-- Student Stats -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 flex flex-col justify-between hover:shadow-md transition-shadow">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">My Students</p>
                    <h3 class="text-3xl font-bold text-gray-900 dark:text-white mt-2">--</h3>
                </div>
                <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center text-xl">
                    <i class="fas fa-users"></i>
                </div>
            </div>
            <div class="mt-4">
                <a href="{{ route('teacher.students.index') }}" class="text-sm text-indigo-600 hover:text-indigo-700 font-medium flex items-center">
                    View Roster <i class="fas fa-arrow-right ml-1 text-xs"></i>
                </a>
            </div>
        </div>
        
        <!-- Pending Tasks -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 flex flex-col justify-between hover:shadow-md transition-shadow">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Pending Tasks</p>
                    <h3 class="text-3xl font-bold text-gray-900 dark:text-white mt-2">0</h3>
                </div>
                <div class="w-12 h-12 bg-orange-50 text-orange-600 rounded-xl flex items-center justify-center text-xl">
                    <i class="fas fa-tasks"></i>
                </div>
            </div>
            <div class="mt-4">
                <span class="text-sm text-gray-500">You're all caught up!</span>
            </div>
        </div>

        <!-- System Alerts -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 flex flex-col justify-between hover:shadow-md transition-shadow">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Notifications</p>
                    <h3 class="text-3xl font-bold text-gray-900 dark:text-white mt-2">0</h3>
                </div>
                <div class="w-12 h-12 bg-purple-50 text-purple-600 rounded-xl flex items-center justify-center text-xl">
                    <i class="fas fa-bell"></i>
                </div>
            </div>
            <div class="mt-4">
                <span class="text-sm text-gray-500">No new alerts.</span>
            </div>
        </div>
    </div>
</div>
@endsection
