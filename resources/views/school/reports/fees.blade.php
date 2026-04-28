@extends('layouts.school')

@section('title', 'Financial Reports')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <x-page-header
        title="Financial Reports"
        description="Export daily collection logs and fee defaulter lists to Excel."
        icon="fas fa-chart-bar">
    </x-page-header>

    <!-- Report Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <!-- Daily Collection Report -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="p-6 sm:p-8">
                <div class="flex items-start gap-4 mb-6">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-100 dark:bg-emerald-900/40 flex items-center justify-center text-emerald-600 dark:text-emerald-400 shrink-0">
                        <i class="fas fa-file-excel text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-gray-800 dark:text-white">Daily Collection Report</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Export a detailed list of all fee payments received on a specific date.</p>
                    </div>
                </div>

                <form action="{{ route('school.reports.fees.daily-collection') }}" method="GET">
                    <div class="space-y-2 mb-4">
                        <label class="modal-label-premium">Target Date</label>
                        <input type="date" name="date" value="{{ date('Y-m-d') }}"
                            class="modal-input-premium cursor-pointer" required>
                    </div>
                    <button type="submit"
                        class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white text-sm font-semibold rounded-xl transition-all shadow-md hover:shadow-lg active:scale-95 w-full justify-center">
                        <i class="fas fa-download text-xs"></i>
                        Export to Excel
                    </button>
                </form>
            </div>
        </div>

        <!-- Defaulters List -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="p-6 sm:p-8">
                <div class="flex items-start gap-4 mb-6">
                    <div class="w-12 h-12 rounded-2xl bg-rose-100 dark:bg-rose-900/40 flex items-center justify-center text-rose-600 dark:text-rose-400 shrink-0">
                        <i class="fas fa-file-invoice-dollar text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-gray-800 dark:text-white">Defaulters List</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Export a list of all students with pending fees past their due date.</p>
                    </div>
                </div>

                <form action="{{ route('school.reports.fees.defaulters') }}" method="GET">
                    <div class="space-y-2 mb-4">
                        <label class="modal-label-premium">As Of Date</label>
                        <input type="date" name="date" value="{{ date('Y-m-d') }}"
                            class="modal-input-premium cursor-pointer" required>
                    </div>
                    <button type="submit"
                        class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-rose-600 to-red-600 hover:from-rose-700 hover:to-red-700 text-white text-sm font-semibold rounded-xl transition-all shadow-md hover:shadow-lg active:scale-95 w-full justify-center">
                        <i class="fas fa-download text-xs"></i>
                        Export to Excel
                    </button>
                </form>
            </div>
        </div>

    </div>
</div>
@endsection
