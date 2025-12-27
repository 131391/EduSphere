@extends('layouts.admin')

@section('title', 'Admin Dashboard')

@section('content')
<!-- Page Header -->
<div class="mb-4 sm:mb-6">
    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Admin Dashboard</h1>
</div>

<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6 mb-6 sm:mb-8">
    <div class="bg-white overflow-hidden shadow rounded-lg border border-gray-200">
        <div class="p-4 sm:p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-blue-500 rounded-lg p-3">
                    <i class="fas fa-school text-white text-xl"></i>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Total Schools</dt>
                        <dd class="text-xl sm:text-2xl font-bold text-gray-900 mt-1">{{ \App\Models\School::count() }}</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white overflow-hidden shadow rounded-lg border border-gray-200">
        <div class="p-4 sm:p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-green-500 rounded-lg p-3">
                    <i class="fas fa-check-circle text-white text-xl"></i>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Active Schools</dt>
                        <dd class="text-xl sm:text-2xl font-bold text-gray-900 mt-1">{{ \App\Models\School::where('status', 'active')->count() }}</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white overflow-hidden shadow rounded-lg border border-gray-200">
        <div class="p-4 sm:p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-yellow-500 rounded-lg p-3">
                    <i class="fas fa-users text-white text-xl"></i>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Total Users</dt>
                        <dd class="text-xl sm:text-2xl font-bold text-gray-900 mt-1">{{ \App\Models\User::count() }}</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="bg-white shadow rounded-lg border border-gray-200 p-4 sm:p-6">
    <h2 class="text-lg sm:text-xl font-semibold text-gray-900 mb-4">Quick Actions</h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
        <a href="{{ route('admin.schools.index') }}" class="block p-4 border border-gray-200 rounded-lg hover:bg-gray-50 hover:border-blue-500 transition-colors">
            <h3 class="font-medium text-gray-900">Manage Schools</h3>
            <p class="text-sm text-gray-500 mt-1">View and manage all schools</p>
        </a>
        <a href="{{ route('admin.schools.create') }}" class="block p-4 border border-gray-200 rounded-lg hover:bg-gray-50 hover:border-blue-500 transition-colors">
            <h3 class="font-medium text-gray-900">Add New School</h3>
            <p class="text-sm text-gray-500 mt-1">Register a new school</p>
        </a>
    </div>
</div>
@endsection

