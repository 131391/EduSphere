@extends('layouts.admin')

@section('title', 'Global Users')

@section('content')
    <div x-data="{ searchOpen: true }">
        <!-- Header Section -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 mb-6 border border-blue-100/50">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl font-bold text-gray-800 dark:text-white flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center text-blue-600">
                            <i class="fas fa-users-cog text-xs"></i>
                        </div>
                        Global User Registry
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Managing {{ number_format($stats['total']) }}
                        users across the entire EduSphere network.</p>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" @click="searchOpen = !searchOpen"
                        class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 text-sm font-semibold rounded-xl hover:bg-gray-50 dark:hover:bg-gray-600 transition-all shadow-sm">
                        <i class="fas fa-filter mr-2 opacity-50"></i>
                        Advanced Filters
                    </button>
                </div>
            </div>
        </div>

        <!-- Stats Section -->
        <div class="grid grid-cols-1 md:grid-cols-4 lg:grid-cols-5 gap-6 mb-8">
            <div
                class="bg-white p-5 rounded-2xl shadow-sm border border-blue-50 flex items-center gap-4 group hover:shadow-md transition-all duration-300">
                <div
                    class="w-12 h-12 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                    <i class="fas fa-users text-xl"></i>
                </div>
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Users</p>
                    <p class="text-2xl font-black text-gray-800">{{ number_format($stats['total']) }}</p>
                </div>
            </div>
            <div
                class="bg-white p-5 rounded-2xl shadow-sm border border-blue-50 flex items-center gap-4 group hover:shadow-md transition-all duration-300">
                <div
                    class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                    <i class="fas fa-check-circle text-xl"></i>
                </div>
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Active</p>
                    <p class="text-2xl font-black text-emerald-600">{{ number_format($stats['active']) }}</p>
                </div>
            </div>
            <div
                class="bg-white p-5 rounded-2xl shadow-sm border border-blue-50 flex items-center gap-4 group hover:shadow-md transition-all duration-300">
                <div
                    class="w-12 h-12 bg-gray-50 text-gray-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                    <i class="fas fa-pause-circle text-xl"></i>
                </div>
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Inactive</p>
                    <p class="text-2xl font-black text-gray-600">{{ number_format($stats['inactive']) }}</p>
                </div>
            </div>
            <div
                class="bg-white p-5 rounded-2xl shadow-sm border border-blue-50 flex items-center gap-4 group hover:shadow-md transition-all duration-300">
                <div
                    class="w-12 h-12 bg-rose-50 text-rose-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                    <i class="fas fa-ban text-xl"></i>
                </div>
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Suspended</p>
                    <p class="text-2xl font-black text-rose-600">{{ number_format($stats['suspended']) }}</p>
                </div>
            </div>
            <div
                class="bg-white p-5 rounded-2xl shadow-sm border border-blue-50 flex items-center gap-4 group hover:shadow-md transition-all duration-300 md:col-span-1 lg:col-span-1">
                <div
                    class="w-12 h-12 bg-amber-50 text-amber-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                    <i class="fas fa-clock text-xl"></i>
                </div>
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Pending</p>
                    <p class="text-2xl font-black text-amber-600">{{ number_format($stats['pending']) }}</p>
                </div>
            </div>
        </div>

        <!-- Filters Section -->
        <div x-show="searchOpen" x-collapse
            class="bg-white dark:bg-gray-800 p-4 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 mb-6">
            <form action="{{ route('admin.users.index') }}" method="GET"
                class="grid grid-cols-1 md:grid-cols-4 lg:grid-cols-6 gap-4">
                <div class="lg:col-span-2">
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-wider mb-1">Search
                        Identifier</label>
                    <div class="relative">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Name, email, phone..."
                            class="w-full h-10 pl-9 bg-gray-50 dark:bg-gray-700 border-gray-200 dark:border-gray-600 rounded-lg text-sm text-gray-700 dark:text-gray-200 focus:ring-blue-500 focus:border-blue-500 transition-all">
                        <div class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                            <i class="fas fa-search text-xs"></i>
                        </div>
                    </div>
                </div>
                <div>
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-wider mb-1">School
                        Affiliation</label>
                    <select name="school_id"
                        class="w-full h-10 px-3 bg-gray-50 dark:bg-gray-700 border-gray-200 dark:border-gray-600 rounded-lg text-sm text-gray-700 dark:text-gray-200 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">All Schools</option>
                        @foreach($schools as $school)
                            <option value="{{ $school->id }}" {{ request('school_id') == $school->id ? 'selected' : '' }}>
                                {{ $school->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-wider mb-1">System
                        Role</label>
                    <select name="role"
                        class="w-full h-10 px-3 bg-gray-50 dark:bg-gray-700 border-gray-200 dark:border-gray-600 rounded-lg text-sm text-gray-700 dark:text-gray-200 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">All Roles</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->slug }}" {{ request('role') == $role->slug ? 'selected' : '' }}>
                                {{ $role->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-wider mb-1">Account
                        Status</label>
                    <select name="status"
                        class="w-full h-10 px-3 bg-gray-50 dark:bg-gray-700 border-gray-200 dark:border-gray-600 rounded-lg text-sm text-gray-700 dark:text-gray-200 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">All Statuses</option>
                        <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inactive</option>
                        <option value="2" {{ request('status') === '2' ? 'selected' : '' }}>Suspended</option>
                        <option value="3" {{ request('status') === '3' ? 'selected' : '' }}>Pending</option>
                    </select>
                </div>
                <div class="flex items-end gap-2 text-right">
                    <button type="submit"
                        class="flex-1 h-10 flex items-center justify-center gap-2 bg-gray-800 dark:bg-gray-700 hover:bg-black dark:hover:bg-gray-600 text-white font-bold text-xs uppercase tracking-widest rounded-lg transition-all duration-300">
                        <i class="fas fa-filter text-[10px] opacity-50"></i>
                        Apply
                    </button>
                    @if(request()->hasAny(['search', 'school_id', 'role', 'status']))
                        <a href="{{ route('admin.users.index') }}"
                            class="h-10 px-4 flex items-center justify-center bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400 rounded-lg hover:bg-gray-200 transition-colors">
                            <i class="fas fa-times text-xs"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        @php
            $tableColumns = [
                [
                    'key' => 'user',
                    'label' => 'User Identity',
                    'sortable' => false,
                    'render' => function ($row) {
                        $initials = strtoupper(substr($row->name, 0, 2));
                        return '
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 rounded-xl bg-blue-50 border border-blue-100 flex items-center justify-center text-blue-600 font-black text-xs shadow-sm ring-2 ring-white">' . $initials . '</div>
                                        <div>
                                            <div class="text-sm font-bold text-gray-800">' . e($row->name) . '</div>
                                            <div class="text-[10px] font-bold text-gray-400 tracking-wider">' . e($row->email) . '</div>
                                        </div>
                                    </div>';
                    }
                ],
                [
                    'key' => 'role',
                    'label' => 'System Role',
                    'sortable' => false,
                    'render' => function ($row) {
                        return '<span class="px-2.5 py-1 text-[10px] font-black uppercase tracking-widest rounded-lg bg-indigo-50 text-indigo-700 border border-indigo-100">' . e($row->role->name ?? 'N/A') . '</span>';
                    }
                ],
                [
                    'key' => 'school',
                    'label' => 'Assigned School',
                    'sortable' => false,
                    'render' => function ($row) {
                        return '
                                    <div class="flex items-center gap-2">
                                        <div class="w-1.5 h-1.5 rounded-full bg-blue-400"></div>
                                        <span class="text-xs font-semibold text-gray-600">' . e($row->school->name ?? 'EduSphere Global') . '</span>
                                    </div>';
                    }
                ],
                [
                    'key' => 'status',
                    'label' => 'Account Status',
                    'sortable' => true,
                    'render' => function ($row) {
                        $status = $row->status;
                        $config = match ($status?->value) {
                            1 => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'border' => 'border-emerald-100', 'icon' => 'fa-check-circle'],
                            2 => ['bg' => 'bg-rose-50', 'text' => 'text-rose-700', 'border' => 'border-rose-100', 'icon' => 'fa-ban'],
                            3 => ['bg' => 'bg-amber-50', 'text' => 'text-amber-700', 'border' => 'border-amber-100', 'icon' => 'fa-clock'],
                            default => ['bg' => 'bg-gray-50', 'text' => 'text-gray-700', 'border' => 'border-gray-100', 'icon' => 'fa-pause-circle'],
                        };

                        return '
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-2xl ' . $config['bg'] . ' ' . $config['text'] . ' ' . $config['border'] . ' border text-[10px] font-black uppercase tracking-widest shadow-sm">
                                        <i class="fas ' . $config['icon'] . ' text-[8px]"></i>
                                        ' . $row->status_label . '
                                    </span>';
                    }
                ],
                [
                    'key' => 'last_login',
                    'label' => 'Recent Activity',
                    'sortable' => true,
                    'render' => function ($row) {
                        $time = $row->last_login_at ? $row->last_login_at->diffForHumans() : 'Never';
                        return '<div class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">' . e($time) . '</div>';
                    }
                ],
            ];

            $tableActions = [
                /* Admin details or edit would go here if routes exist */
            ];
        @endphp

        <div class="mt-6">
            <x-data-table :columns="$tableColumns" :data="$users" :actions="$tableActions"
                empty-message="No global users found matching your criteria." empty-icon="fas fa-users-slash">
                User Management Directory
            </x-data-table>
        </div>
    </div>
@endsection