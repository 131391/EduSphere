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

    @if(session('success'))
        <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded mb-6">
            <p class="text-green-700">{{ session('success') }}</p>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Profile Overview -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow p-6 h-full">
                <div class="flex flex-col items-center text-center">
                    <div class="w-32 h-32 bg-blue-600 rounded-full flex items-center justify-center text-white mb-4">
                        <i class="fas fa-user text-5xl"></i>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-800">{{ Auth::user()->name }}</h2>
                    <p class="text-gray-600 mb-2">{{ Auth::user()->email }}</p>
                    <span class="px-3 py-1 text-xs font-medium rounded-full bg-purple-100 text-purple-800">
                        {{ Auth::user()->role->name ?? 'Super Admin' }}
                    </span>
                    
                    <div class="mt-8 pt-8 border-t border-gray-100 w-full text-left">
                        <div class="mb-4">
                            <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Status</span>
                            <span class="text-sm font-medium text-green-600 flex items-center">
                                <span class="w-2 h-2 bg-green-500 rounded-full mr-2"></span> Active
                            </span>
                        </div>
                        <div class="mb-4">
                            <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Member Since</span>
                            <span class="text-sm font-medium text-gray-900">{{ Auth::user()->created_at ? Auth::user()->created_at->format('M d, Y') : 'N/A' }}</span>
                        </div>
                        <div>
                            <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Account Type</span>
                            <span class="text-sm font-medium text-gray-900">Platform Administrator</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Profile Form -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800">Account Settings</h3>
                </div>
                <div class="p-6">
                    <form action="{{ route('admin.update-profile') }}" method="POST" class="space-y-6">
                        @csrf
                        @method('PUT')
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                                <input type="text" name="name" id="name" value="{{ old('name', Auth::user()->name) }}" required
                                       class="w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('name') border-red-500 @enderror">
                                @error('name')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                                <input type="email" name="email" id="email" value="{{ old('email', Auth::user()->email) }}" required
                                       class="w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('email') border-red-500 @enderror">
                                @error('email')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                                <input type="text" name="phone" id="phone" value="{{ old('phone', Auth::user()->phone) }}"
                                       class="w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('phone') border-red-500 @enderror">
                                @error('phone')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="pt-4 flex items-center justify-between border-t border-gray-100 mt-6">
                            <p class="text-xs text-gray-500">Last updated {{ Auth::user()->updated_at ? Auth::user()->updated_at->diffForHumans() : 'never' }}</p>
                            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium">
                                Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6 mt-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="text-md font-semibold text-gray-800">Password & Security</h4>
                        <p class="text-sm text-gray-500">Keep your account secure with a strong password</p>
                    </div>
                    <a href="{{ route('admin.change-password') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition text-sm font-medium">
                        Change Password
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


