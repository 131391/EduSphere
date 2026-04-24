@extends('layouts.school')

@section('title', 'Financial Reports')

@section('content')
<div class="px-4 py-8 mx-auto max-w-7xl sm:px-6 lg:px-8">
    <div class="mb-8 sm:flex sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Financial Reports</h1>
            <p class="mt-2 text-sm text-gray-600">Export daily collection logs and fee defaulter lists to Excel.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
        <!-- Daily Collection Report -->
        <div class="overflow-hidden bg-white shadow sm:rounded-lg">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 p-3 bg-emerald-100 rounded-md">
                        <i class="fas fa-file-excel text-emerald-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-medium leading-6 text-gray-900">Daily Collection Report</h3>
                        <p class="mt-1 text-sm text-gray-500">Export a detailed list of all fee payments received on a specific date.</p>
                    </div>
                </div>
                <form action="{{ route('school.reports.fees.daily-collection') }}" method="GET" class="mt-6">
                    <div class="flex items-end gap-4">
                        <div class="flex-grow">
                            <label for="collection_date" class="block text-sm font-medium text-gray-700">Target Date</label>
                            <input type="date" id="collection_date" name="date" value="{{ date('Y-m-d') }}" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm" required>
                        </div>
                        <button type="submit" class="inline-flex justify-center px-4 py-2 text-sm font-medium text-white transition-colors bg-emerald-600 border border-transparent rounded-md shadow-sm hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500">
                            <i class="mr-2 fas fa-download"></i> Export
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Defaulters List -->
        <div class="overflow-hidden bg-white shadow sm:rounded-lg">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 p-3 bg-red-100 rounded-md">
                        <i class="fas fa-file-invoice-dollar text-red-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-medium leading-6 text-gray-900">Defaulters List</h3>
                        <p class="mt-1 text-sm text-gray-500">Export a list of all students with pending fees past their due date.</p>
                    </div>
                </div>
                <form action="{{ route('school.reports.fees.defaulters') }}" method="GET" class="mt-6">
                    <div class="flex items-end gap-4">
                        <div class="flex-grow">
                            <label for="defaulter_date" class="block text-sm font-medium text-gray-700">As Of Date</label>
                            <input type="date" id="defaulter_date" name="date" value="{{ date('Y-m-d') }}" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm" required>
                        </div>
                        <button type="submit" class="inline-flex justify-center px-4 py-2 text-sm font-medium text-white transition-colors bg-red-600 border border-transparent rounded-md shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                            <i class="mr-2 fas fa-download"></i> Export
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
