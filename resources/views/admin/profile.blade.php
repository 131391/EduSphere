@extends('layouts.admin')

@section('title', 'Profile')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">My Profile</h1>
            <p class="text-gray-600 mt-1">View and manage your profile information</p>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center space-x-6 mb-6">
            <div class="w-24 h-24 bg-blue-600 rounded-full flex items-center justify-center text-white">
                <i class="fas fa-user text-4xl"></i>
            </div>
            <div>
                <h2 class="text-2xl font-bold text-gray-800">{{ Auth::user()->name ?? 'Admin' }}</h2>
                <p class="text-gray-600">{{ Auth::user()->email ?? '' }}</p>
                <p class="text-sm text-gray-500 mt-1">{{ Auth::user()->roles->first()->name ?? 'Super Admin' }}</p>
            </div>
        </div>

        <div class="border-t border-gray-200 pt-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Profile Information</h3>
            <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <dt class="text-sm font-medium text-gray-500">Name</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ Auth::user()->name ?? 'N/A' }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Email</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ Auth::user()->email ?? 'N/A' }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Role</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ Auth::user()->roles->first()->name ?? 'Super Admin' }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Member Since</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ Auth::user()->created_at ? Auth::user()->created_at->format('M d, Y') : 'N/A' }}</dd>
                </div>
            </dl>
        </div>
    </div>
</div>
@endsection

