@extends('layouts.admin')

@section('title', 'System Settings')

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Page Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-extrabold text-gray-900 dark:text-white tracking-tight">System Settings</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Configure platform-wide parameters and global defaults</p>
    </div>


    <form action="{{ route('admin.settings.update-system') }}" method="POST">
        @csrf
        
        <div class="space-y-8">
            @foreach($settings as $group => $groupSettings)
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-2xl border border-gray-100 dark:border-gray-700 overflow-hidden text-sm">
                    <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900/50 border-b border-gray-100 dark:border-gray-700">
                        <h3 class="text-sm font-black text-gray-400 uppercase tracking-widest">{{ $group }} CONFIGURATION</h3>
                    </div>
                    <div class="p-6 space-y-6">
                        @foreach($groupSettings as $setting)
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-start">
                                <label for="{{ $setting->key }}" class="block text-sm font-bold text-gray-700 dark:text-gray-300 pt-2">
                                    {{ ucwords(str_replace('_', ' ', $setting->key)) }}
                                </label>
                                <div class="md:col-span-2">
                                    @if($setting->key === 'maintenance_mode')
                                        <select name="{{ $setting->key }}" id="{{ $setting->key }}" class="w-full px-4 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none dark:text-white">
                                            <option value="0" {{ $setting->value == '0' ? 'selected' : '' }}>Disabled (System Live)</option>
                                            <option value="1" {{ $setting->value == '1' ? 'selected' : '' }}>Enabled (Maintenance Mode)</option>
                                        </select>
                                    @else
                                        <input type="text" name="{{ $setting->key }}" id="{{ $setting->key }}" value="{{ $setting->value }}" class="w-full px-4 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none dark:text-white">
                                    @endif
                                    <p class="mt-1 text-[10px] text-gray-400 font-medium">System key: <code class="bg-gray-100 dark:bg-gray-700 px-1 rounded">{{ $setting->key }}</code></p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach

            <div class="flex justify-end">
                <button type="submit" class="px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow-lg shadow-blue-500/30 transition-all hover:scale-[1.02] active:scale-[0.98]">
                    Save Global Configuration
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
