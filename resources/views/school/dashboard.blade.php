@extends('layouts.school')

@section('title', 'School Dashboard')

@section('content')
@php
    $school = app('currentSchool') ?? Auth::user()->school ?? \App\Models\School::where('status', 'active')->first();
    if (!$school) {
        abort(404, 'School not found');
    }
    $academicYear = \App\Models\AcademicYear::where('school_id', $school->id)->where('is_current', true)->first();
    
    // Calculate stats
    $totalCollection = \App\Models\Fee::where('school_id', $school->id)
        ->where('payment_status', 'paid')
        ->sum('paid_amount');
    
    $todayCollection = \App\Models\Fee::where('school_id', $school->id)
        ->where('payment_status', 'paid')
        ->whereDate('payment_date', today())
        ->sum('paid_amount');
    
    $totalAdmission = \App\Models\Student::where('school_id', $school->id)->count();
    $todayAdmission = \App\Models\Student::where('school_id', $school->id)
        ->whereDate('admission_date', today())
        ->count();
    
    $totalEnquiry = \App\Models\Registration::where('school_id', $school->id)->count();
    $todayEnquiry = \App\Models\Registration::where('school_id', $school->id)
        ->whereDate('registration_date', today())
        ->count();
    
    $runningClasses = \App\Models\ClassModel::where('school_id', $school->id)
        ->where('is_available', true)
        ->count();
    
    $totalSections = \App\Models\Section::where('school_id', $school->id)->count();
@endphp

<!-- Stats Cards -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-6 sm:mb-8">
    <!-- Total Collection -->
    <div class="bg-white rounded-lg shadow border-t-4 border-green-500">
        <div class="p-4 sm:p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs sm:text-sm font-medium text-gray-500 uppercase">Total Collection</p>
                    <p class="text-xl sm:text-2xl font-bold text-gray-900 mt-2">₹ {{ number_format($totalCollection, 2) }}</p>
                    <p class="text-xs text-gray-500 mt-1">Today Collection - ₹ {{ number_format($todayCollection, 2) }}</p>
                </div>
                <div class="bg-green-100 p-2 sm:p-3 rounded-lg">
                    <i class="fas fa-rupee-sign text-green-600 text-xl sm:text-2xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Admission -->
    <div class="bg-white rounded-lg shadow border-t-4 border-blue-500">
        <div class="p-4 sm:p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs sm:text-sm font-medium text-gray-500 uppercase">Total Admission</p>
                    <p class="text-xl sm:text-2xl font-bold text-gray-900 mt-2">{{ $totalAdmission }}</p>
                    <p class="text-xs text-gray-500 mt-1">Today Admission - {{ $todayAdmission }}</p>
                </div>
                <div class="bg-blue-100 p-2 sm:p-3 rounded-lg">
                    <i class="fas fa-user-graduate text-blue-600 text-xl sm:text-2xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Enquiry -->
    <div class="bg-white rounded-lg shadow border-t-4 border-orange-500">
        <div class="p-4 sm:p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs sm:text-sm font-medium text-gray-500 uppercase">Total Enquiry</p>
                    <p class="text-xl sm:text-2xl font-bold text-gray-900 mt-2">{{ $totalEnquiry }}</p>
                    <p class="text-xs text-gray-500 mt-1">Today Enquiry - {{ $todayEnquiry }}</p>
                </div>
                <div class="bg-orange-100 p-2 sm:p-3 rounded-lg">
                    <i class="fas fa-question-circle text-orange-600 text-xl sm:text-2xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Running Classes -->
    <div class="bg-white rounded-lg shadow border-t-4 border-red-500">
        <div class="p-4 sm:p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs sm:text-sm font-medium text-gray-500 uppercase">Running Classes</p>
                    <p class="text-xl sm:text-2xl font-bold text-gray-900 mt-2">{{ $runningClasses }}</p>
                    <p class="text-xs text-gray-500 mt-1">Total Section - {{ $totalSections }}</p>
                </div>
                <div class="bg-red-100 p-2 sm:p-3 rounded-lg">
                    <i class="fas fa-chalkboard-teacher text-red-600 text-xl sm:text-2xl"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts and Recent Activity Row -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
    <!-- Collection Category Wise Chart -->
    <div class="bg-white rounded-lg shadow p-4 sm:p-6">
        <h3 class="text-base sm:text-lg font-semibold text-gray-900 mb-4">Collection Category Wise</h3>
        <div class="flex items-center justify-center h-48 sm:h-64">
            <div class="text-center">
                <div class="relative w-40 h-40 sm:w-48 sm:h-48 mx-auto">
                    <!-- Simple Donut Chart Representation -->
                    <svg class="transform -rotate-90" viewBox="0 0 100 100">
                        <circle cx="50" cy="50" r="40" fill="none" stroke="#e5e7eb" stroke-width="8"/>
                        <circle cx="50" cy="50" r="40" fill="none" stroke="#10b981" stroke-width="8" 
                                stroke-dasharray="{{ ($totalCollection / ($totalCollection + 100000)) * 251.2 }} 251.2"/>
                    </svg>
                </div>
                <div class="mt-4 space-y-2">
                    <div class="flex items-center justify-center">
                        <div class="w-4 h-4 bg-green-500 rounded mr-2"></div>
                        <span class="text-xs sm:text-sm">Total Collection</span>
                    </div>
                    <div class="flex items-center justify-center">
                        <div class="w-4 h-4 bg-red-600 rounded mr-2"></div>
                        <span class="text-xs sm:text-sm">Total Outstanding Amount</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="bg-white rounded-lg shadow p-4 sm:p-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4 gap-3">
            <h3 class="text-base sm:text-lg font-semibold text-gray-900">Recent Activity</h3>
            <div class="relative">
                <select class="appearance-none bg-white border border-gray-300 rounded-lg px-3 sm:px-4 py-2 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 w-full sm:w-auto">
                    <option>Report</option>
                    <option>Fee Deposit</option>
                    <option>Student Enquiry Report</option>
                    <option>Students Admission</option>
                </select>
            </div>
        </div>

        <!-- Tabs -->
        <div class="border-b border-gray-200 mb-4 overflow-x-auto">
            <nav class="flex space-x-2 sm:space-x-4 min-w-max">
                <button class="px-3 sm:px-4 py-2 border-b-2 border-blue-500 text-blue-600 font-medium text-xs sm:text-sm whitespace-nowrap">Fee Deposit</button>
                <button class="px-3 sm:px-4 py-2 text-gray-500 hover:text-gray-700 text-xs sm:text-sm whitespace-nowrap">Student Enquiry Report</button>
                <button class="px-3 sm:px-4 py-2 text-gray-500 hover:text-gray-700 text-xs sm:text-sm whitespace-nowrap">Students Admission</button>
            </nav>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto -mx-4 sm:mx-0">
            <div class="inline-block min-w-full align-middle">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 sm:px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase whitespace-nowrap">BILL NO</th>
                            <th class="px-3 sm:px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase whitespace-nowrap">ADMISSION NO</th>
                            <th class="px-3 sm:px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase whitespace-nowrap">PAYABLE AMOUNT</th>
                            <th class="px-3 sm:px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase whitespace-nowrap">PAID AMOUNT</th>
                            <th class="px-3 sm:px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase whitespace-nowrap">DUE AMOUNT</th>
                            <th class="px-3 sm:px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase whitespace-nowrap">PAYMENT MODE</th>
                            <th class="px-3 sm:px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase whitespace-nowrap">FEE DEPOSIT DATE</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse(\App\Models\Fee::where('school_id', $school->id)->latest()->take(5)->get() as $fee)
                        <tr>
                            <td class="px-3 sm:px-4 py-3 text-xs sm:text-sm text-gray-900 whitespace-nowrap">{{ $fee->bill_no }}</td>
                            <td class="px-3 sm:px-4 py-3 text-xs sm:text-sm text-gray-900 whitespace-nowrap">{{ $fee->student->admission_no ?? 'N/A' }}</td>
                            <td class="px-3 sm:px-4 py-3 text-xs sm:text-sm text-gray-900 whitespace-nowrap">₹ {{ number_format($fee->payable_amount, 2) }}</td>
                            <td class="px-3 sm:px-4 py-3 text-xs sm:text-sm text-gray-900 whitespace-nowrap">₹ {{ number_format($fee->paid_amount, 2) }}</td>
                            <td class="px-3 sm:px-4 py-3 text-xs sm:text-sm text-gray-900 whitespace-nowrap">₹ {{ number_format($fee->due_amount, 2) }}</td>
                            <td class="px-3 sm:px-4 py-3 text-xs sm:text-sm whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-medium rounded-full {{ $fee->payment_mode === 'online' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">
                                    {{ strtoupper($fee->payment_mode ?? 'N/A') }}
                                </span>
                            </td>
                            <td class="px-3 sm:px-4 py-3 text-xs sm:text-sm text-gray-500 whitespace-nowrap">{{ $fee->payment_date ? $fee->payment_date->format('M. d, Y, g:i a') : 'N/A' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-3 sm:px-4 py-8 text-center text-gray-500 text-xs sm:text-sm">No recent fee deposits</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

