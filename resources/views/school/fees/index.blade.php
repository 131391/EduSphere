@extends('layouts.school')

@section('title', 'Fees Ledger')

@section('content')
    <div x-data="Object.assign(ajaxDataTable({
        fetchUrl: '{{ route('school.fees.index') }}',
        defaultSort: 'due_date',
        defaultDirection: 'desc',
        defaultPerPage: 25,
        defaultFilters: { class_id: '', status: '' },
        initialRows: @js($initialData['rows']),
        initialPagination: @js($initialData['pagination']),
        initialStats: @js($initialData['stats']),
        filterLabels: {
            class_id: { @foreach($classes as $c) '{{ $c->id }}': '{{ $c->name }}', @endforeach },
            status: { '1': 'Pending', '2': 'Partial', '4': 'Overdue' }
        }
    }), feeManagement())" class="space-y-6">

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <x-stat-card label="Total Receivable" :value="'₹' . $stats['total_receivable']" icon="fas fa-wallet" color="emerald" alpine-text="'₹' + stats.total_receivable" />
            <x-stat-card label="Pending Records" :value="$stats['pending_records']" icon="fas fa-file-invoice" color="indigo" alpine-text="stats.pending_records" />
            <x-stat-card label="Overdue Notices" :value="$stats['overdue_count']" icon="fas fa-exclamation-triangle" color="rose" alpine-text="stats.overdue_count" />
            <x-stat-card label="Partial Collections" :value="$stats['partial_payments']" icon="fas fa-chart-pie" color="amber" alpine-text="stats.partial_payments" />
        </div>

        <!-- Header Section -->
        <x-page-header title="Fees Ledger" description="Real-time tracking of institutional receivables, pending obligations, and student payment history." icon="fas fa-file-invoice-dollar">
            <a href="{{ route('school.fees.create') }}"
                class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white text-sm font-semibold rounded-xl transition-all shadow-md hover:shadow-lg active:scale-95">
                <i class="fas fa-calculator mr-2 text-xs"></i>
                Generate Class Fees
            </a>
        </x-page-header>

        <!-- AJAX Data Table -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <!-- Table Header -->
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div class="flex-1 flex flex-col md:flex-row md:items-center gap-4">
                        <h2 class="text-lg font-semibold text-gray-800 dark:text-white">Transaction Inventory</h2>
                        <x-table.search placeholder="Search by admission no, name or bill..." />
                    </div>

                    <div class="flex items-center gap-3">
                        <x-table.filter-select
                            model="filters.status"
                            action="applyFilter('status', $event.target.value)"
                            placeholder="All Status"
                            :options="['1' => 'Pending', '2' => 'Partial', '4' => 'Overdue']"
                        />
                        <x-table.filter-select
                            model="filters.class_id"
                            action="applyFilter('class_id', $event.target.value)"
                            placeholder="All Classes"
                            :options="$classes->pluck('name', 'id')->toArray()"
                        />
                        <x-table.per-page model="perPage" action="changePerPage($event.target.value)" :default="25" />
                    </div>
                </div>

                <!-- Active Filter Tags -->
                <div class="mt-3 flex flex-wrap gap-2" x-show="hasActiveFilters()" x-cloak>
                    <template x-for="(value, key) in filters" :key="key">
                        <template x-if="value">
                            <div class="flex items-center gap-1 bg-indigo-50 text-indigo-700 border border-indigo-100 px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider">
                                <span x-text="key.replace('_', ' ') + ': ' + getFilterLabel(key, value)"></span>
                                <button @click="removeFilter(key)" class="ml-1 hover:text-indigo-900 transition-colors">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </template>
                    </template>
                    <button @click="clearAllFilters()" class="text-[10px] font-bold text-red-600 hover:text-red-700 uppercase tracking-widest ml-2 transition-colors">
                        Clear All
                    </button>
                </div>
            </div>

            <!-- Table Body -->
            <div class="overflow-x-auto relative ajax-table-wrapper">
                <x-table.loading-overlay />

                <table class="w-full text-left border-collapse">
                    <thead class="bg-gray-50/50 dark:bg-gray-700/50 border-b border-gray-100 dark:border-gray-700">
                        <tr>
                            <x-table.sort-header column="bill_no" label="Billing Particulars" sort-var="sort" direction-var="direction" />
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Beneficiary</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Fee Head</th>
                            <x-table.sort-header column="payable_amount" label="Obligation" sort-var="sort" direction-var="direction" />
                            <x-table.sort-header column="due_amount" label="Balance" sort-var="sort" direction-var="direction" />
                            <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider w-32">Actions</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700" data-ssr x-show="!hydrated">
                        @foreach($initialData['rows'] as $row)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/50 transition-colors group">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex flex-col">
                                        <span class="text-sm font-bold text-gray-800 dark:text-gray-100">{{ $row['bill_no'] }}</span>
                                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter italic">Due: {{ $row['due_date'] }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-xl bg-emerald-50 dark:bg-emerald-900/40 flex items-center justify-center text-emerald-600 border border-emerald-100 dark:border-emerald-800 font-bold text-xs">
                                            {{ substr($row['student_name'], 0, 1) }}
                                        </div>
                                        <div>
                                            <div class="text-sm font-bold text-gray-800 dark:text-gray-100">{{ $row['student_name'] }}</div>
                                            <div class="text-[10px] text-gray-400 font-medium uppercase tracking-tighter">{{ $row['admission_no'] }} | {{ $row['class_name'] }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex flex-col">
                                        <span class="text-sm font-bold text-gray-700 dark:text-gray-200">{{ $row['fee_name'] }}</span>
                                        <span class="text-[10px] font-medium text-indigo-500 uppercase tracking-tighter italic">{{ $row['fee_period'] }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm font-bold text-gray-800 dark:text-gray-100">₹{{ $row['payable_amount'] }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <div class="text-rose-700 dark:text-rose-400 font-bold bg-rose-50 dark:bg-rose-900/40 px-3 py-1 rounded-lg inline-block border border-rose-100 dark:border-rose-800">
                                            ₹{{ $row['due_amount'] }}
                                        </div>
                                        @if($row['is_overdue'])
                                            <span class="w-2 h-2 rounded-full bg-rose-500 animate-pulse" title="Overdue"></span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="{{ route('school.fees.show', $row['id']) }}" class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center hover:bg-emerald-100 transition-colors shadow-sm" title="View Details"><i class="fas fa-eye text-xs"></i></a>
                                        <button @click="confirmDelete(@js($row))" class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center hover:bg-rose-100 transition-colors shadow-sm" title="Remove Record"><i class="fas fa-trash-alt text-xs"></i></button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>

                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700 transition-opacity duration-150" x-show="hydrated" :class="loading && rows.length &gt; 0 ? 'opacity-50' : 'opacity-100'">
                        <template x-for="row in rows" :key="row.id">
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/50 transition-colors group">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex flex-col">
                                        <span class="text-sm font-bold text-gray-800 dark:text-gray-100" x-text="row.bill_no"></span>
                                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter italic" x-text="'Due: ' + row.due_date"></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-xl bg-emerald-50 dark:bg-emerald-900/40 flex items-center justify-center text-emerald-600 border border-emerald-100 dark:border-emerald-800 font-bold text-xs" x-text="row.student_name.charAt(0)"></div>
                                        <div>
                                            <div class="text-sm font-bold text-gray-800 dark:text-gray-100" x-text="row.student_name"></div>
                                            <div class="text-[10px] text-gray-400 font-medium uppercase tracking-tighter" x-text="row.admission_no + ' | ' + row.class_name"></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex flex-col">
                                        <span class="text-sm font-bold text-gray-700 dark:text-gray-200" x-text="row.fee_name"></span>
                                        <span class="text-[10px] font-medium text-indigo-500 uppercase tracking-tighter italic" x-text="row.fee_period"></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap font-bold text-gray-800 dark:text-gray-100" x-text="'₹' + row.payable_amount"></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <div class="text-rose-700 dark:text-rose-400 font-bold bg-rose-50 dark:bg-rose-900/40 px-3 py-1 rounded-lg inline-block border border-rose-100 dark:border-rose-800" x-text="'₹' + row.due_amount"></div>
                                        <template x-if="row.is_overdue">
                                            <span class="w-2 h-2 rounded-full bg-rose-500 animate-pulse" title="Overdue"></span>
                                        </template>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <a :href="'/school/fees/' + row.id" class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center hover:bg-emerald-100 transition-colors shadow-sm" title="View Details"><i class="fas fa-eye text-xs"></i></a>
                                        <button @click="confirmDelete(row)" class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center hover:bg-rose-100 transition-colors shadow-sm" title="Remove Record"><i class="fas fa-trash-alt text-xs"></i></button>
                                    </div>
                                </td>
                            </tr>
                        </template>

                        <x-table.empty-state :colspan="6" icon="fas fa-file-invoice-dollar" message="No pending fees found." />
                    </tbody>
                </table>
            </div>

            <x-table.pagination :initial="$initialData['pagination']" />
        </div>

        <x-confirm-modal />
    </div>

    @push('scripts')
        <script>
            function feeManagement() {
                return {
                    confirmDelete(row) {
                        const self = this;
                        window.dispatchEvent(new CustomEvent('open-confirm-modal', {
                            detail: {
                                title: 'Remove Fee Record',
                                message: `Are you sure you want to permanently remove the bill ${row.bill_no} for "${row.student_name}"? This action cannot be undone and may affect financial reports.`,
                                callback: async () => {
                                    try {
                                        const response = await fetch(`/school/fees/${row.id}`, {
                                            method: 'POST',
                                            headers: {
                                                'Content-Type': 'application/json',
                                                'Accept': 'application/json',
                                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                            },
                                            body: JSON.stringify({ _method: 'DELETE' })
                                        });

                                        const result = await response.json();

                                        if (response.ok) {
                                            if (window.Toast) window.Toast.fire({ icon: 'success', title: result.message || 'Removed successfully' });
                                            if (typeof self.refreshTable === 'function') self.refreshTable();
                                        } else {
                                            if (window.Toast) window.Toast.fire({ icon: 'error', title: window.resolveApiMessage(result, '') });
                                        }
                                    } catch (error) {
                                        if (window.Toast) window.Toast.fire({ icon: 'error', title: 'Removal failed' });
                                    }
                                }
                            }
                        }));
                    },
                }
            }
        </script>
    @endpush
@endsection
