@extends('layouts.admin')

@section('title', 'Global Users')

@section('content')
    <div x-data="{ searchOpen: true }" class="space-y-6">
        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <!-- Total Users -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border-t-4 border-blue-500 transition-all duration-300 hover:shadow-md">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Users</p>
                        <h3 class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ number_format($stats['total']) }}</h3>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/20 rounded-full flex items-center justify-center transition-transform duration-300 hover:scale-110">
                        <i class="fas fa-users text-blue-600 dark:text-blue-400 text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- Active Users -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border-t-4 border-emerald-500 transition-all duration-300 hover:shadow-md">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Active</p>
                        <h3 class="text-3xl font-bold text-emerald-600 mt-2">{{ number_format($stats['active']) }}</h3>
                    </div>
                    <div class="w-12 h-12 bg-emerald-100 dark:bg-emerald-900/20 rounded-full flex items-center justify-center transition-transform duration-300 hover:scale-110">
                        <i class="fas fa-check-circle text-emerald-600 dark:text-emerald-400 text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- Inactive Users -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border-t-4 border-gray-400 transition-all duration-300 hover:shadow-md">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Inactive</p>
                        <h3 class="text-3xl font-bold text-gray-500 dark:text-white mt-2">{{ number_format($stats['inactive']) }}</h3>
                    </div>
                    <div class="w-12 h-12 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center transition-transform duration-300 hover:scale-110">
                        <i class="fas fa-pause-circle text-gray-600 dark:text-gray-400 text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- Suspended Users -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border-t-4 border-rose-500 transition-all duration-300 hover:shadow-md">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Suspended</p>
                        <h3 class="text-3xl font-bold text-rose-600 mt-2">{{ number_format($stats['suspended']) }}</h3>
                    </div>
                    <div class="w-12 h-12 bg-rose-100 dark:bg-rose-900/20 rounded-full flex items-center justify-center transition-transform duration-300 hover:scale-110">
                        <i class="fas fa-ban text-rose-600 dark:text-rose-400 text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- Pending Users -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border-t-4 border-amber-500 transition-all duration-300 hover:shadow-md">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Pending</p>
                        <h3 class="text-3xl font-bold text-amber-600 mt-2">{{ number_format($stats['pending']) }}</h3>
                    </div>
                    <div class="w-12 h-12 bg-amber-100 dark:bg-amber-900/20 rounded-full flex items-center justify-center transition-transform duration-300 hover:scale-110">
                        <i class="fas fa-clock text-amber-600 dark:text-amber-400 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Header Section -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 border border-blue-100/50 dark:border-gray-700">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center text-blue-600">
                        <i class="fas fa-users-cog text-xs"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-gray-800 dark:text-white">Global User Registry</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Managing {{ number_format($stats['total']) }} users across the EduSphere network</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" @click="searchOpen = !searchOpen"
                        class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 text-gray-700 dark:text-gray-200 text-xs font-semibold rounded-xl hover:bg-gray-50 dark:hover:bg-gray-600 transition-all shadow-sm">
                        <i class="fas fa-filter mr-2 text-blue-500"></i>
                        Advanced Filters
                    </button>
                </div>
            </div>
        </div>

        <!-- Filters Section -->
        <div x-show="searchOpen" x-collapse x-cloak
            class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700">
            <form action="{{ route('admin.users.index') }}" method="GET"
                class="grid grid-cols-1 md:grid-cols-4 lg:grid-cols-6 gap-6">
                <div class="lg:col-span-2">
                    <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1.5">Search Identifier</label>
                    <div class="relative group">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Name, email, phone..."
                            class="w-full h-11 pl-10 bg-gray-50 dark:bg-gray-700 border-gray-200 dark:border-gray-600 rounded-xl text-sm text-gray-700 dark:text-gray-200 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none">
                        <div class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-blue-500 transition-colors">
                            <i class="fas fa-search text-xs"></i>
                        </div>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1.5">School Affiliation</label>
                    <select name="school_id"
                        class="w-full h-11 px-4 bg-gray-50 dark:bg-gray-700 border-gray-200 dark:border-gray-600 rounded-xl text-sm text-gray-700 dark:text-gray-200 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none">
                        <option value="">All Schools</option>
                        @foreach($schools as $school)
                            <option value="{{ $school->id }}" {{ request('school_id') == $school->id ? 'selected' : '' }}>
                                {{ $school->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1.5">System Role</label>
                    <select name="role"
                        class="w-full h-11 px-4 bg-gray-50 dark:bg-gray-700 border-gray-200 dark:border-gray-600 rounded-xl text-sm text-gray-700 dark:text-gray-200 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none">
                        <option value="">All Roles</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->slug }}" {{ request('role') == $role->slug ? 'selected' : '' }}>
                                {{ $role->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1.5">Account Status</label>
                    <select name="status"
                        class="w-full h-11 px-4 bg-gray-50 dark:bg-gray-700 border-gray-200 dark:border-gray-600 rounded-xl text-sm text-gray-700 dark:text-gray-200 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none">
                        <option value="">All Statuses</option>
                        <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inactive</option>
                        <option value="2" {{ request('status') === '2' ? 'selected' : '' }}>Suspended</option>
                        <option value="3" {{ request('status') === '3' ? 'selected' : '' }}>Pending</option>
                    </select>
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit"
                        class="flex-1 h-11 flex items-center justify-center gap-2 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-bold text-xs uppercase tracking-widest rounded-xl transition-all duration-300 shadow-md hover:shadow-lg active:scale-95">
                        <i class="fas fa-filter text-[10px]"></i>
                        Apply Filters
                    </button>
                    @if(request()->hasAny(['search', 'school_id', 'role', 'status']))
                        <a href="{{ route('admin.users.index') }}"
                            class="h-11 px-4 flex items-center justify-center bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400 rounded-xl hover:bg-gray-200 transition-all shadow-sm">
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
                                        <div class="w-10 h-10 rounded-xl bg-blue-50 border border-blue-100 flex items-center justify-center text-blue-600 font-bold text-xs shadow-sm ring-2 ring-white">' . $initials . '</div>
                                        <div>
                                            <div class="text-sm font-bold text-gray-800">' . e($row->name) . '</div>
                                            <div class="text-xs font-semibold text-gray-400">' . e($row->email) . '</div>
                                        </div>
                                    </div>';
                    }
                ],
                [
                    'key' => 'role',
                    'label' => 'System Role',
                    'sortable' => false,
                    'render' => function ($row) {
                        return '<span class="px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider rounded-lg bg-indigo-50 text-indigo-700 border border-indigo-100">' . e($row->role->name ?? 'N/A') . '</span>';
                    }
                ],
                [
                    'key' => 'school',
                    'label' => 'Assigned School',
                    'sortable' => false,
                    'render' => function ($row) {
                        return '
                                    <div class="flex items-center gap-2">
                                        <div class="w-1.5 h-1.5 rounded-full bg-blue-400 shadow-sm shadow-blue-200"></div>
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
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full ' . $config['bg'] . ' ' . $config['text'] . ' ' . $config['border'] . ' border text-[10px] font-bold uppercase tracking-wider shadow-sm">
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
                        return '<div class="flex items-center gap-2">
                                    <i class="far fa-clock text-gray-400 text-[10px]"></i>
                                    <div class="text-[10px] font-semibold text-gray-500">' . e($time) . '</div>
                                </div>';
                    }
                ],
            ];

            $tableActions = [];
        @endphp

        <div>
            <x-data-table :columns="$tableColumns" :data="$users" :actions="$tableActions"
                empty-message="No global users found matching your criteria." empty-icon="fas fa-users-slash">
                User Management Directory
            </x-data-table>
        </div>
    </div>
@endsection