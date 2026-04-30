@extends('layouts.parent')

@section('title', 'Fee Statement')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-5 border border-emerald-100/50">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-gray-800 dark:text-white flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-emerald-100 flex items-center justify-center text-emerald-600">
                        <i class="fas fa-receipt text-xs"></i>
                    </div>
                    Fee Management
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Tracking financial records and payment status for your children.</p>
            </div>
            
            <div class="flex items-center gap-3">
                <a href="{{ route('parent.fees.export', request()->only('student_id')) }}"
                   class="inline-flex items-center px-4 py-2 bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-bold rounded-xl hover:bg-emerald-100 transition-all uppercase tracking-wider">
                    <i class="fas fa-file-csv mr-2"></i> Export
                </a>
            @if($children->count() > 1)
            <div class="flex items-center gap-3">
                <form method="GET" action="{{ route('parent.fees.index') }}" class="relative group">
                    <select name="student_id" onchange="this.form.submit()"
                            class="appearance-none pl-10 pr-10 py-2 border border-gray-200 dark:border-gray-600 rounded-xl text-sm bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all cursor-pointer">
                        <option value="">All Children</option>
                        @foreach($children as $child)
                        <option value="{{ $child->id }}" {{ $selectedChildId == $child->id ? 'selected' : '' }}>
                            {{ $child->full_name }}
                        </option>
                        @endforeach
                    </select>
                    <div class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400">
                        <i class="fas fa-child text-xs"></i>
                    </div>
                    <div class="absolute right-3.5 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">
                        <i class="fas fa-chevron-down text-[10px]"></i>
                    </div>
                </form>
            </div>
            @endif
        </div>
    </div>

    <!-- Stats Section -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-emerald-50 flex items-center gap-5 group hover:shadow-md transition-all duration-300">
            <div class="w-14 h-14 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform">
                <i class="fas fa-file-invoice-dollar text-2xl"></i>
            </div>
            <div>
                <p class="text-xs font-black text-gray-400 uppercase tracking-widest">Total Payable</p>
                <p class="text-2xl font-black text-gray-800">₹{{ number_format($summary['total_payable'], 2) }}</p>
            </div>
        </div>
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-emerald-50 flex items-center gap-5 group hover:shadow-md transition-all duration-300">
            <div class="w-14 h-14 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform">
                <i class="fas fa-check-double text-2xl"></i>
            </div>
            <div>
                <p class="text-xs font-black text-gray-400 uppercase tracking-widest">Total Paid</p>
                <p class="text-2xl font-black text-emerald-600">₹{{ number_format($summary['total_paid'], 2) }}</p>
            </div>
        </div>
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-emerald-50 flex items-center gap-5 group hover:shadow-md transition-all duration-300">
            <div class="w-14 h-14 bg-rose-50 text-rose-600 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform">
                <i class="fas fa-exclamation-circle text-2xl"></i>
            </div>
            <div>
                <p class="text-xs font-black text-gray-400 uppercase tracking-widest">Outstanding Due</p>
                <p class="text-2xl font-black text-rose-600">₹{{ number_format($summary['total_due'], 2) }}</p>
            </div>
        </div>
    </div>

    @php
        $tableColumns = [
            [
                'key' => 'fee_head',
                'label' => 'Fee Structure',
                'sortable' => false,
                'render' => function($row) {
                    return '
                        <div>
                            <div class="text-sm font-bold text-gray-800">'.e($row->feeName->name ?? '—').'</div>
                            <div class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">'.e($row->fee_period ?? '—').'</div>
                        </div>';
                }
            ],
            [
                'key' => 'student',
                'label' => 'Student',
                'sortable' => false,
                'hidden' => (bool)$selectedChildId,
                'render' => function($row) {
                    return '<div class="text-xs font-semibold text-gray-600">'.e($row->student->full_name ?? '—').'</div>';
                }
            ],
            [
                'key' => 'amounts',
                'label' => 'Payments',
                'sortable' => false,
                'render' => function($row) {
                    return '
                        <div class="space-y-1">
                            <div class="flex items-center justify-between gap-4">
                                <span class="text-[10px] font-bold text-gray-400 uppercase">Payable</span>
                                <span class="text-sm font-bold text-gray-700 text-right">₹'.number_format($row->payable_amount, 2).'</span>
                            </div>
                            <div class="flex items-center justify-between gap-4">
                                <span class="text-[10px] font-bold text-emerald-500 uppercase">Paid</span>
                                <span class="text-sm font-bold text-emerald-600 text-right">₹'.number_format($row->paid_amount ?? 0, 2).'</span>
                            </div>
                        </div>';
                }
            ],
            [
                'key' => 'due',
                'label' => 'Balance Due',
                'sortable' => true,
                'render' => function($row) {
                    $due = $row->due_amount ?? 0;
                    $color = $due > 0 ? 'rose' : 'emerald';
                    return '
                        <div class="flex flex-col items-end">
                            <span class="text-sm font-black text-'.$color.'-600">₹'.number_format($due, 2).'</span>
                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Remaining</span>
                        </div>';
                }
            ],
            [
                'key' => 'status',
                'label' => 'Status',
                'sortable' => true,
                'render' => function($row) {
                    $label = $row->payment_status?->label() ?? 'Pending';
                    $config = match($label) {
                        'Paid'    => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'border' => 'border-emerald-100', 'icon' => 'fa-check-circle'],
                        'Partial' => ['bg' => 'bg-blue-50', 'text' => 'text-blue-700', 'border' => 'border-blue-100', 'icon' => 'fa-adjust'],
                        'Pending' => ['bg' => 'bg-amber-50', 'text' => 'text-amber-700', 'border' => 'border-amber-100', 'icon' => 'fa-clock'],
                        'Overdue' => ['bg' => 'bg-rose-50', 'text' => 'text-rose-700', 'border' => 'border-rose-100', 'icon' => 'fa-exclamation-triangle'],
                        default   => ['bg' => 'bg-gray-50', 'text' => 'text-gray-700', 'border' => 'border-gray-100', 'icon' => 'fa-info-circle'],
                    };
                    
                    return '
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-2xl '.$config['bg'].' '.$config['text'].' '.$config['border'].' border text-[10px] font-black uppercase tracking-widest">
                            <i class="fas '.$config['icon'].' text-[8px]"></i>
                            '.$label.'
                        </span>';
                }
            ],
        ];

        $tableActions = [
            [
                'label' => 'Details',
                'icon' => 'fas fa-arrow-right',
                'url' => function($row) { return route('parent.fees.show', $row->id); },
                'class' => 'bg-teal-50 text-teal-600 hover:bg-teal-600 hover:text-white shadow-sm ring-1 ring-teal-100'
            ],
        ];
    @endphp

    <div class="mt-4">
        <x-data-table
            :columns="$tableColumns"
            :data="$fees"
            :actions="$tableActions"
            :searchable="false"
            :show-per-page="false"
            :exportable="false"
            empty-message="No fee records found for the selected criteria."
            empty-icon="fas fa-receipt"
        >
            Fee Statement & Payment History
        </x-data-table>
        @if(method_exists($fees, 'hasPages') && $fees->hasPages())
            <div class="mt-4">{{ $fees->links() }}</div>
        @endif
    </div>
</div>
@endsection
