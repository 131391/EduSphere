@extends('layouts.school')

@section('title', 'Fee Collection')

@section('content')
<div x-data="feeCollectionManager()">
    <!-- Header Section -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 mb-6 border border-emerald-100/50">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-gray-800 dark:text-white flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-emerald-100 flex items-center justify-center text-emerald-600">
                        <i class="fas fa-cash-register text-xs"></i>
                    </div>
                    Fee Collection Portal
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Search students to record and manage their fee payments</p>
            </div>
        </div>
    </div>

    <!-- Search & Filters -->
    <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-gray-100 mb-8 card-gradient">
        <form action="{{ route('school.fee-payments.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-12 gap-6 items-end">
            <div class="md:col-span-5">
                <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1.5 ml-1">Search Criteria</label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none transition-colors duration-200 group-focus-within:text-emerald-600 text-gray-400">
                        <i class="fas fa-search text-sm"></i>
                    </div>
                    <input 
                        type="text" 
                        name="search" 
                        value="{{ request('search') }}" 
                        placeholder="Name, Admission No, or Phone..."
                        class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 focus:bg-white transition-all font-medium text-gray-700"
                    >
                </div>
            </div>
            <div class="md:col-span-4">
                <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1.5 ml-1">Academic Class</label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none transition-colors duration-200 group-focus-within:text-emerald-600 text-gray-400">
                        <i class="fas fa-graduation-cap text-sm"></i>
                    </div>
                    <select name="class_id" class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 focus:bg-white transition-all font-medium text-gray-700 appearance-none">
                        <option value="">All Classes</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="md:col-span-3 flex gap-2">
                <button type="submit" class="flex-1 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold rounded-xl transition-all shadow-md shadow-emerald-200 active:scale-95 flex items-center justify-center gap-2">
                    <i class="fas fa-filter"></i>
                    Filter
                </button>
                <a href="{{ route('school.fee-payments.index') }}" class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-600 text-sm font-bold rounded-xl transition-all flex items-center justify-center">
                    <i class="fas fa-sync-alt"></i>
                </a>
            </div>
        </form>
    </div>

    @php
        $tableColumns = [
            [
                'key' => 'student',
                'label' => 'STUDENT IDENTITY',
                'sortable' => true,
                'render' => function($row) {
                    return '
                    <div class="flex items-center gap-4">
                        <div class="relative">
                            <div class="w-10 h-10 rounded-full bg-emerald-50 flex items-center justify-center text-emerald-600 border border-emerald-100 font-bold text-sm ring-4 ring-emerald-50/50">
                                ' . substr($row->full_name, 0, 1) . '
                            </div>
                            <div class="absolute -bottom-0.5 -right-0.5 w-3.5 h-3.5 bg-emerald-500 border-2 border-white rounded-full"></div>
                        </div>
                        <div>
                            <div class="text-sm font-bold text-gray-800">' . e($row->full_name) . '</div>
                            <div class="text-[10px] font-bold text-gray-400 tracking-widest uppercase">' . e($row->admission_no) . '</div>
                        </div>
                    </div>';
                }
            ],
            [
                'key' => 'class',
                'label' => 'PLACEMENT',
                'render' => function($row) {
                    $className = $row->class->name ?? 'N/A';
                    $sectionName = $row->section->name ?? 'N/A';
                    return '
                    <div class="flex flex-col">
                        <span class="text-sm font-bold text-gray-600">' . e($className) . '</span>
                        <span class="text-[10px] font-medium text-emerald-500 uppercase">' . e($sectionName) . ' Section</span>
                    </div>';
                }
            ],
            [
                'key' => 'phone',
                'label' => 'CONTACT',
                'render' => function($row) {
                    return '<div class="text-xs text-gray-500 font-medium font-mono">' . e($row->phone ?: '--') . '</div>';
                }
            ],
        ];

        $tableActions = [
            [
                'type' => 'link',
                'icon' => 'fas fa-money-bill-transfer',
                'class' => 'inline-flex items-center px-4 py-2 bg-emerald-50 text-emerald-700 text-xs font-bold rounded-xl hover:bg-emerald-600 hover:text-white transition-all border border-emerald-100 flex items-center gap-2 whitespace-nowrap',
                'url' => fn($row) => route('school.fee-payments.collect', $row),
                'title' => 'Process Payment',
            ],
        ];
    @endphp

    <x-data-table 
        :columns="$tableColumns"
        :data="$students"
        :actions="$tableActions"
        empty-message="No students matching your search"
        empty-icon="fas fa-search-minus"
    >
        Available Students
    </x-data-table>
</div>

<style>
.card-gradient {
    background-image: linear-gradient(135deg, #ffffff 0%, #f9fdfc 100%);
}
</style>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('feeCollectionManager', () => ({
        // Currently handled by direct link to 'collect' page
        // Future: could use AJAX to open collection modal
    }));
});
</script>
@endpush
@endsection
