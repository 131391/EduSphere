@extends('layouts.receptionist')

@section('title', 'Change Password')

@section('content')
<div class="max-w-2xl">
    <a href="{{ route('receptionist.profile.show') }}" class="inline-flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors mb-4">
        <i class="fas fa-arrow-left"></i> Back to Profile
    </a>

    <form method="POST" action="{{ route('receptionist.profile.password.update') }}"
          class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
        @csrf

        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
            <h3 class="text-base font-semibold text-gray-800 dark:text-white flex items-center gap-2">
                <i class="fas fa-key text-blue-500"></i>
                Change Password
            </h3>
            <p class="text-xs text-gray-500 mt-1">Choose a strong password with at least 8 characters, mixed case letters, and numbers.</p>
        </div>

        <div class="p-6 space-y-4">
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Current Password <span class="text-rose-500">*</span></label>
                <input type="password" name="current_password" required autocomplete="current-password"
                       class="w-full h-10 px-3 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                @error('current_password')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">New Password <span class="text-rose-500">*</span></label>
                <input type="password" name="password" required autocomplete="new-password"
                       class="w-full h-10 px-3 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                @error('password')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Confirm New Password <span class="text-rose-500">*</span></label>
                <input type="password" name="password_confirmation" required autocomplete="new-password"
                       class="w-full h-10 px-3 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
        </div>

        <div class="px-6 py-4 bg-gray-50/50 dark:bg-gray-700/30 border-t border-gray-100 dark:border-gray-700 flex justify-end">
            <button type="submit" class="px-6 h-10 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl transition-colors flex items-center gap-2">
                <i class="fas fa-shield-alt text-xs"></i> Update Password
            </button>
        </div>
    </form>
</div>
@endsection
