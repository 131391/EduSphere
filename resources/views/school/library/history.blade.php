@extends('layouts.school')

@section('title', 'Library Transaction History')

@section('content')
    <div x-data="Object.assign(ajaxDataTable({
        fetchUrl: '{{ route('school.library.history.fetch') }}',
        defaultSort: 'updated_at',
        defaultDirection: 'desc',
        defaultPerPage: 25,
        initialRows: @js($initialData['rows']),
        initialPagination: @js($initialData['pagination'])
    }), {
        async settleFine(issueId) {
            this.$dispatch('open-confirm-modal', {
                title: 'Confirm Payment Settlement?',
                message: 'Validate that the pending fine has been received in full. This action will finalize the audit record.',
                callback: async () => {
                    try {
                        const response = await fetch(`{{ route('school.library.settle-fine', ['issue' => '__ISSUE__']) }}`.replace('__ISSUE__', issueId), {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            }
                        });

                        const result = await response.json();
                        if (response.ok) {
                            if (window.Toast) window.Toast.fire({ icon: 'success', title: result.message });
                            if (typeof this.refreshTable === 'function') this.refreshTable();
                        } else {
                            if (window.Toast) window.Toast.fire({ icon: 'error', title: result.message || 'Operation failed' });
                        }
                    } catch (e) {
                        if (window.Toast) window.Toast.fire({ icon: 'error', title: 'Connection error' });
                    }
                }
            });
        }
    })" class="space-y-6">

        <!-- Header Section -->
        <x-page-header title="Library Transaction Ledger" description="Comprehensive audit trail of all book returns, losses, and fine settlements." icon="fas fa-history">
            <div class="flex items-center gap-3">
                <a href="{{ route('school.library.issues') }}" 
                    class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 text-sm font-semibold rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition-all shadow-sm">
                    <i class="fas fa-exchange-alt mr-2 text-xs text-indigo-500"></i>
                    Circulation Desk
                </a>
            </div>
        </x-page-header>

        <!-- AJAX Data Table -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <!-- Table Header -->
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div class="flex-1 flex flex-col md:flex-row md:items-center gap-4">
                        <h2 class="text-lg font-semibold text-gray-800 dark:text-white">Audit Records</h2>
                        <x-table.search placeholder="Search by title, student, ID..." />
                    </div>

                    <div class="flex items-center gap-3">
                        <x-table.per-page model="perPage" action="changePerPage($event.target.value)" :default="25" />
                    </div>
                </div>
            </div>

            <!-- Table Body -->
            <div class="overflow-x-auto relative ajax-table-wrapper">
                <x-table.loading-overlay />

                <table class="w-full text-left border-collapse">
                    <thead class="bg-gray-50/50 dark:bg-gray-700/50 border-b border-gray-100 dark:border-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Asset & Beneficiary</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Timeline</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Status & Fines</th>
                            <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider w-40">Actions</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700 transition-opacity duration-150" x-cloak :class="loading && rows.length > 0 ? 'opacity-50' : 'opacity-100'">
                        <template x-for="row in rows" :key="row.id">
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/50 transition-colors group">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg flex items-center justify-center text-xs" 
                                            :class="row.beneficiary_type === 'student' ? 'bg-indigo-50 text-indigo-600' : 'bg-amber-50 text-amber-600'">
                                            <i :class="row.beneficiary_type === 'student' ? 'fas fa-user-graduate' : 'fas fa-user-tie'"></i>
                                        </div>
                                        <div>
                                            <div class="text-sm font-bold text-gray-800 dark:text-gray-100" x-text="row.book_title"></div>
                                            <div class="text-[10px] font-medium text-gray-400">
                                                <span x-text="row.beneficiary_name"></span> 
                                                <span class="mx-1 opacity-30">|</span> 
                                                <span x-text="row.beneficiary_id"></span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-xs">
                                    <div class="flex flex-col gap-1">
                                        <div class="text-gray-400 font-medium">Issued: <span class="text-gray-600 dark:text-gray-300 font-bold" x-text="row.issue_date"></span></div>
                                        <div class="text-gray-400 font-medium">Actioned: <span class="text-gray-600 dark:text-gray-300 font-bold" x-text="row.return_date"></span></div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex flex-col gap-1">
                                        <div>
                                            <span x-show="row.status === 'returned'" class="px-2 py-0.5 bg-emerald-100 text-emerald-700 text-[9px] font-black rounded-lg uppercase tracking-widest">Returned</span>
                                            <span x-show="row.status === 'lost'" class="px-2 py-0.5 bg-rose-100 text-rose-700 text-[9px] font-black rounded-lg uppercase tracking-widest">Lost Asset</span>
                                        </div>
                                        <div x-show="row.fine_amount > 0" class="flex items-center gap-1.5 mt-1">
                                            <div class="text-[10px] font-bold" :class="row.fine_settled ? 'text-emerald-500' : 'text-rose-500'">
                                                <i class="fas fa-coins mr-1"></i>
                                                <span x-text="row.currency + row.fine_amount"></span>
                                            </div>
                                            <span x-show="row.fine_settled" class="text-[8px] font-black text-emerald-400 uppercase tracking-tighter">Settled</span>
                                            <span x-show="!row.fine_settled" class="text-[8px] font-black text-rose-400 uppercase tracking-tighter animate-pulse">Pending</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <template x-if="row.fine_amount > 0 && !row.fine_settled">
                                        <button @click="settleFine(row.id)" class="px-4 py-1.5 bg-slate-800 text-white text-[9px] font-black rounded-lg hover:bg-slate-900 transition-all shadow-md active:scale-95 uppercase tracking-widest">
                                            Settle Fine
                                        </button>
                                    </template>
                                    <template x-if="row.fine_amount == 0 || row.fine_settled">
                                        <span class="text-[10px] text-gray-300 font-black uppercase tracking-widest italic">Cleared</span>
                                    </template>
                                </td>
                            </tr>
                        </template>

                        <x-table.empty-state :colspan="4" icon="fas fa-clipboard-list" message="No transaction history matches your criteria." />
                    </tbody>
                </table>
            </div>

            <x-table.pagination />
        </div>

        <x-confirm-modal />
    </div>

@endsection
