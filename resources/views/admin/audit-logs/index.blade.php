@extends('layouts.admin')

@section('title', 'Audit Logs')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Audit Logs</h1>
            <p class="text-gray-600 mt-1">System activity and change history</p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-lg shadow p-4">
        <form method="GET" action="{{ route('admin.audit-logs.index') }}" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs font-medium text-gray-500 mb-1">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Action, model..."
                       class="w-full rounded-lg border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div class="min-w-[150px]">
                <label class="block text-xs font-medium text-gray-500 mb-1">From Date</label>
                <input type="date" name="from_date" value="{{ request('from_date') }}"
                       class="w-full rounded-lg border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div class="min-w-[150px]">
                <label class="block text-xs font-medium text-gray-500 mb-1">To Date</label>
                <input type="date" name="to_date" value="{{ request('to_date') }}"
                       class="w-full rounded-lg border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700 transition">Filter</button>
                <a href="{{ route('admin.audit-logs.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg text-sm hover:bg-gray-300 transition ml-1">Reset</a>
            </div>
        </form>
    </div>

    {{-- Logs Table --}}
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Time</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Model</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Changes</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($logs as $log)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm text-gray-500 whitespace-nowrap">
                            <span title="{{ $log->created_at->format('M d, Y H:i:s') }}">{{ $log->created_at->diffForHumans() }}</span>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-900 whitespace-nowrap">
                            {{ $log->causer?->name ?? 'System' }}
                        </td>
                        <td class="px-4 py-3">
                            @php
                                $actionColors = [
                                    'created' => 'bg-green-100 text-green-800',
                                    'updated' => 'bg-blue-100 text-blue-800',
                                    'deleted' => 'bg-red-100 text-red-800',
                                ];
                            @endphp
                            <span class="px-2 py-1 text-xs font-medium rounded-full {{ $actionColors[$log->description] ?? 'bg-gray-100 text-gray-800' }}">
                                {{ ucfirst($log->description) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600">
                            {{ class_basename($log->subject_type ?? 'N/A') }} #{{ $log->subject_id ?? '' }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-500">
                            @if($log->properties && $log->properties->has('attributes'))
                                <details class="cursor-pointer">
                                    <summary class="text-blue-600 hover:text-blue-800 text-xs">View changes</summary>
                                    <div class="mt-1 text-xs bg-gray-50 p-2 rounded max-w-xs overflow-auto">
                                        @if($log->properties->has('old'))
                                            <div class="mb-1"><strong>Before:</strong> {{ json_encode($log->properties['old'], JSON_PRETTY_PRINT) }}</div>
                                        @endif
                                        <div><strong>After:</strong> {{ json_encode($log->properties['attributes'], JSON_PRETTY_PRINT) }}</div>
                                    </div>
                                </details>
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-gray-500">No activity logs found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($logs->hasPages())
            <div class="px-4 py-3 border-t border-gray-200">
                {{ $logs->withQueryString()->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
