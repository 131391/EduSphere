@extends('layouts.receptionist')

@section('title', 'Dashboard - Receptionist')
@section('page-title', 'Dashboard')
@section('page-description', 'Overview of front desk activities')

@section('content')
<div class="space-y-6">
    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Collection -->
        <div class="bg-white rounded-lg shadow-sm border-t-4 border-green-500 overflow-hidden hover:shadow-md transition-shadow">
            <div class="p-6">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <p class="text-xs text-gray-500 uppercase font-semibold mb-2">Total Collection</p>
                        <h3 class="text-2xl font-bold text-gray-800 mb-1">₹ {{ number_format($stats['total_collection'], 2) }}</h3>
                        <p class="text-xs text-gray-400">Today Collection = ₹ {{ number_format($stats['today_collection'], 2) }}</p>
                    </div>
                    <div class="flex-shrink-0 w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-rupee-sign text-green-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Admission -->
        <div class="bg-white rounded-lg shadow-sm border-t-4 border-blue-500 overflow-hidden hover:shadow-md transition-shadow">
            <div class="p-6">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <p class="text-xs text-gray-500 uppercase font-semibold mb-2">Total Admission</p>
                        <h3 class="text-2xl font-bold text-gray-800 mb-1">{{ $stats['total_admission'] }}</h3>
                        <p class="text-xs text-gray-400">Today Admission = {{ $stats['today_admission'] }}</p>
                    </div>
                    <div class="flex-shrink-0 w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-user-graduate text-blue-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Enquiry -->
        <div class="bg-white rounded-lg shadow-sm border-t-4 border-orange-500 overflow-hidden hover:shadow-md transition-shadow">
            <div class="p-6">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <p class="text-xs text-gray-500 uppercase font-semibold mb-2">Total Enquiry</p>
                        <h3 class="text-2xl font-bold text-gray-800 mb-1">{{ $stats['total_enquiry'] }}</h3>
                        <p class="text-xs text-gray-400">Today Enquiry = {{ $stats['today_enquiry'] }}</p>
                    </div>
                    <div class="flex-shrink-0 w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-question-circle text-orange-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Running Classes -->
        <div class="bg-white rounded-lg shadow-sm border-t-4 border-red-500 overflow-hidden hover:shadow-md transition-shadow">
            <div class="p-6">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <p class="text-xs text-gray-500 uppercase font-semibold mb-2">Running Classes</p>
                        <h3 class="text-2xl font-bold text-gray-800 mb-1">{{ $stats['running_classes'] }}</h3>
                        <p class="text-xs text-gray-400">Total Section = {{ $stats['total_sections'] }}</p>
                    </div>
                    <div class="flex-shrink-0 w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-chalkboard-teacher text-red-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Visitor Statistics -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6">
        <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-4">Visitor Statistics</h3>
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
            <div class="text-center p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                <div class="flex items-center justify-center mb-2">
                    <i class="fas fa-users text-2xl text-teal-500"></i>
                </div>
                <p class="text-2xl font-bold text-gray-800 dark:text-white">{{ $visitorStats['total'] }}</p>
                <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">Total Visitor</p>
            </div>
            <div class="text-center p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                <div class="flex items-center justify-center mb-2">
                    <i class="fas fa-video text-2xl text-blue-500"></i>
                </div>
                <p class="text-2xl font-bold text-gray-800 dark:text-white">{{ $visitorStats['online'] }}</p>
                <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">Online Visitor</p>
            </div>
            <div class="text-center p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                <div class="flex items-center justify-center mb-2">
                    <i class="fas fa-building text-2xl text-green-500"></i>
                </div>
                <p class="text-2xl font-bold text-gray-800 dark:text-white">{{ $visitorStats['offline'] }}</p>
                <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">Offline/Office Meeting</p>
            </div>
            <div class="text-center p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                <div class="flex items-center justify-center mb-2">
                    <i class="fas fa-laptop text-2xl text-yellow-500"></i>
                </div>
                <p class="text-2xl font-bold text-gray-800 dark:text-white">{{ $visitorStats['office'] }}</p>
                <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">Online Meeting</p>
            </div>
            <div class="text-center p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                <div class="flex items-center justify-center mb-2">
                    <i class="fas fa-times-circle text-2xl text-red-500"></i>
                </div>
                <p class="text-2xl font-bold text-gray-800 dark:text-white">{{ $visitorStats['cancelled'] }}</p>
                <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">Cancelled Visitor</p>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-gray-800 dark:text-white">Recent Visitors</h3>
            <a href="{{ route('receptionist.visitors.index') }}" class="text-blue-600 hover:text-blue-700 text-sm font-semibold">
                View All <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Visitor No</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Mobile</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Purpose</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Date</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($recentVisitors as $visitor)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">{{ $visitor->visitor_no }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">{{ $visitor->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">{{ $visitor->mobile }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">{{ $visitor->visit_purpose ?? 'N/A' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $statusColors = [
                                    'scheduled' => 'bg-blue-100 text-blue-800',
                                    'checked_in' => 'bg-green-100 text-green-800',
                                    'completed' => 'bg-gray-100 text-gray-800',
                                    'cancelled' => 'bg-red-100 text-red-800',
                                ];
                            @endphp
                            <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $statusColors[$visitor->status] ?? 'bg-gray-100 text-gray-800' }}">
                                {{ ucfirst($visitor->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">{{ $visitor->created_at->format('d M, Y') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                            <i class="fas fa-users text-4xl mb-2"></i>
                            <p>No recent visitors</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
