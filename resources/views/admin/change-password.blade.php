@extends('layouts.admin')

@section('title', 'Change Password')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Change Password</h1>
            <p class="text-gray-600 mt-1">Update your account password</p>
        </div>
    </div>


    @if($errors->any())
        <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded">
            <ul class="text-red-700 text-sm list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white rounded-lg shadow p-6 max-w-lg">
        <form method="POST" action="{{ route('admin.update-password') }}" class="space-y-5">
            @csrf
            <div>
                <label for="current_password" class="block text-sm font-medium text-gray-700 mb-1">Current Password</label>
                <input type="password" name="current_password" id="current_password" required
                       class="w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500">
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                <input type="password" name="password" id="password" required
                       class="w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500">
                <p class="text-xs text-gray-500 mt-1">Minimum 8 characters, with upper/lowercase and numbers</p>
            </div>

            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
                <input type="password" name="password_confirmation" id="password_confirmation" required
                       class="w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500">
            </div>

            <div class="pt-2">
                <button type="submit" class="px-6 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium">
                    Update Password
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
