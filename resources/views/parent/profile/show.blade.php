@extends('layouts.parent')

@section('title', 'My Profile')

@section('content')
<div class="space-y-6 max-w-5xl">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
        <div class="flex flex-col md:flex-row md:items-center gap-5">
            @if($parentProfile->photo)
                <img src="{{ asset('storage/' . $parentProfile->photo) }}"
                     class="w-24 h-24 rounded-2xl object-cover border-2 border-indigo-100"
                     alt="{{ $parentProfile->full_name }}">
            @else
                <div class="w-24 h-24 rounded-2xl bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center text-3xl font-bold text-indigo-600 dark:text-indigo-300">
                    {{ strtoupper(substr($parentProfile->first_name ?? 'P', 0, 1)) }}{{ strtoupper(substr($parentProfile->last_name ?? '', 0, 1)) }}
                </div>
            @endif

            <div class="flex-1">
                <h2 class="text-2xl font-bold text-gray-800 dark:text-white">{{ $parentProfile->full_name ?: ($user->name ?? 'Parent') }}</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    {{ $user->email }}
                    @if($parentProfile->phone)
                        &middot; {{ $parentProfile->phone }}
                    @endif
                </p>
                <div class="mt-3 flex flex-wrap gap-2">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700">
                        <i class="fas fa-user-friends text-[10px]"></i> Parent
                    </span>
                    @if($parentProfile->relation)
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700">
                            <i class="fas fa-people-arrows text-[10px]"></i> {{ $parentProfile->relation->label() }}
                        </span>
                    @endif
                    @if($parentProfile->occupation)
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700">
                            <i class="fas fa-briefcase text-[10px]"></i> {{ $parentProfile->occupation }}
                        </span>
                    @endif
                </div>
            </div>

            <div>
                <a href="{{ route('parent.profile.password') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 rounded-xl hover:bg-gray-200 dark:hover:bg-gray-600 text-sm font-semibold transition-colors">
                    <i class="fas fa-key"></i> Change Password
                </a>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('parent.profile.update') }}" enctype="multipart/form-data"
          class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
        @csrf

        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
            <h3 class="text-base font-semibold text-gray-800 dark:text-white flex items-center gap-2">
                <i class="fas fa-user-edit text-indigo-500"></i>
                Edit Profile
            </h3>
        </div>

        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">First Name <span class="text-rose-500">*</span></label>
                <input type="text" name="first_name" value="{{ old('first_name', $parentProfile->first_name) }}" required
                       class="w-full h-10 px-3 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-sm text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                @error('first_name')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Last Name</label>
                <input type="text" name="last_name" value="{{ old('last_name', $parentProfile->last_name) }}"
                       class="w-full h-10 px-3 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-sm text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                @error('last_name')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Email <span class="text-rose-500">*</span></label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                       class="w-full h-10 px-3 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-sm text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                @error('email')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Phone</label>
                <input type="tel" name="phone" value="{{ old('phone', $parentProfile->phone) }}"
                       class="w-full h-10 px-3 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-sm text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                @error('phone')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Occupation</label>
                <input type="text" name="occupation" value="{{ old('occupation', $parentProfile->occupation) }}"
                       class="w-full h-10 px-3 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-sm text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                @error('occupation')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Relationship</label>
                <input type="text" value="{{ $parentProfile->relation?->label() ?? 'Parent' }}" disabled
                       class="w-full h-10 px-3 bg-gray-100 dark:bg-gray-800/70 border border-gray-200 dark:border-gray-600 rounded-lg text-sm text-gray-500 dark:text-gray-300 cursor-not-allowed">
            </div>

            <div class="md:col-span-2">
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Address</label>
                <textarea name="address" rows="3"
                          class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-sm text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">{{ old('address', $parentProfile->address) }}</textarea>
                @error('address')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="md:col-span-2">
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Profile Photo</label>
                <input type="file" name="photo" accept="image/jpeg,image/png,image/webp"
                       class="block w-full text-sm text-gray-600 dark:text-gray-300 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                <p class="text-xs text-gray-400 mt-1">JPG, PNG, or WEBP, max 2 MB.</p>
                @error('photo')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="px-6 py-4 bg-gray-50/50 dark:bg-gray-700/30 border-t border-gray-100 dark:border-gray-700 flex justify-end">
            <button type="submit" class="px-6 h-10 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl transition-colors flex items-center gap-2">
                <i class="fas fa-save text-xs"></i> Save Changes
            </button>
        </div>
    </form>
</div>
@endsection
