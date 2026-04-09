@extends('layouts.admin')

@section('title', 'Global Users')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">All Users</h1>
            <p class="text-gray-600 mt-1">Manage users across all schools</p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-lg shadow p-4">
        <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs font-medium text-gray-500 mb-1">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Name, email, phone..."
                       class="w-full rounded-lg border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div class="min-w-[180px]">
                <label class="block text-xs font-medium text-gray-500 mb-1">School</label>
                <select name="school_id" class="w-full rounded-lg border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500">
                    <option value="">All Schools</option>
                    @foreach($schools as $school)
                        <option value="{{ $school->id }}" {{ request('school_id') == $school->id ? 'selected' : '' }}>{{ $school->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="min-w-[140px]">
                <label class="block text-xs font-medium text-gray-500 mb-1">Role</label>
                <select name="role" class="w-full rounded-lg border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500">
                    <option value="">All Roles</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->slug }}" {{ request('role') == $role->slug ? 'selected' : '' }}>{{ $role->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="min-w-[120px]">
                <label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
                <select name="status" class="w-full rounded-lg border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500">
                    <option value="">All</option>
                    <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inactive</option>
                    <option value="2" {{ request('status') === '2' ? 'selected' : '' }}>Suspended</option>
                    <option value="3" {{ request('status') === '3' ? 'selected' : '' }}>Pending</option>
                </select>
            </div>
            <div>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700 transition">Filter</button>
                <a href="{{ route('admin.users.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg text-sm hover:bg-gray-300 transition ml-1">Reset</a>
            </div>
        </form>
    </div>

    {{-- Users Table --}}
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Role</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">School</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Last Login</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($users as $user)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center">
                                    <span class="text-xs font-bold text-blue-600">{{ strtoupper(substr($user->name, 0, 2)) }}</span>
                                </div>
                                <div class="ml-3">
                                    <span class="text-sm font-medium text-gray-900">{{ $user->name }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ $user->email }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-purple-100 text-purple-800">{{ $user->role->name ?? 'N/A' }}</span>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ $user->school->name ?? '—' }}</td>
                        <td class="px-4 py-3">
                            @php
                                $statusColors = [
                                    1 => 'bg-green-100 text-green-800',
                                    0 => 'bg-gray-100 text-gray-800',
                                    2 => 'bg-red-100 text-red-800',
                                    3 => 'bg-yellow-100 text-yellow-800',
                                ];
                            @endphp
                            <span class="px-2 py-1 text-xs font-medium rounded-full {{ $statusColors[$user->status?->value] ?? 'bg-gray-100 text-gray-800' }}">
                                {{ $user->status_label }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-500">
                            {{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Never' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-gray-500">No users found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($users->hasPages())
            <div class="px-4 py-3 border-t border-gray-200">
                {{ $users->withQueryString()->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
