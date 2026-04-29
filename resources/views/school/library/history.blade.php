@extends('layouts.school')

@section('title', 'Library Transaction History')

@section('content')
    <div x-data="Object.assign(ajaxDataTable({
        fetchUrl: '{{ route('school.library.history.fetch') }}',
        defaultSort: 'return_date',
        defaultDirection: 'desc',
        defaultPerPage: 25,
        defaultFilters: { from_date: '', to_date: '', status_filter: '', fines_pending: '' },
        filterLabels: {
            status_filter: { 'returned': 'Returned', 'lost': 'Lost' },
            fines_pending: { '1': 'Pending fine' }
        },
        initialRows: @js($initialData['rows']),
        initialPagination: @js($initialData['pagination'])
    }), {
        settleData: { issue_id: null, fine_amount: 0, paid_amount: '', payment_method: 'cash', note: '' },
        settleSubmitting: false,
        settleErrors: {},

        openSettleModal(row) {
            this.settleErrors = {};
            const fine = parseFloat(String(row.fine_amount).replace(/,/g, '')) || 0;
            this.settleData = {
                issue_id: row.id,
                fine_amount: fine,
                paid_amount: fine.toFixed(2),
                payment_method: 'cash',
                note: ''
            };
            this.$dispatch('open-modal', 'settle-fine-modal');
        },

        confirmRecover(issueId) {
            this.$dispatch('open-confirm-modal', {
                title: 'Recover Lost Book?',
                message: 'This will restore the book to inventory and void any unpaid fine. Continue?',
                callback: async () => {
                    try {
                        const url = `{{ route('school.library.recover', ['issue' => '__ISSUE__']) }}`.replace('__ISSUE__', issueId);
                        const response = await fetch(url, {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            }
                        });
                        const result = await response.json();
                        if (response.ok && result.success) {
                            if (window.Toast) window.Toast.fire({ icon: 'success', title: result.message });
                            if (typeof this.refreshTable === 'function') this.refreshTable();
                        } else {
                            throw new Error(result.message || 'Operation failed');
                        }
                    } catch (e) {
                        if (window.Toast) window.Toast.fire({ icon: 'error', title: e.message });
                    }
                }
            });
        },

        async submitSettle() {
            if (this.settleSubmitting) return;
            this.settleSubmitting = true;
            this.settleErrors = {};
            try {
                const url = `{{ route('school.library.settle-fine', ['issue' => '__ISSUE__']) }}`.replace('__ISSUE__', this.settleData.issue_id);
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        paid_amount: this.settleData.paid_amount,
                        payment_method: this.settleData.payment_method,
                        note: this.settleData.note
                    })
                });
                const result = await response.json();
                if (response.ok && result.success) {
                    if (window.Toast) window.Toast.fire({ icon: 'success', title: result.message });
                    this.$dispatch('close-modal', 'settle-fine-modal');
                    if (typeof this.refreshTable === 'function') this.refreshTable();
                } else if (response.status === 422 && result.errors) {
                    this.settleErrors = result.errors;
                } else {
                    throw new Error(result.message || 'Settlement failed');
                }
            } catch (e) {
                if (window.Toast) window.Toast.fire({ icon: 'error', title: e.message });
            } finally {
                this.settleSubmitting = false;
            }
        }
    })" class="space-y-6">

        <!-- Header Section -->
        <x-page-header title="Library Transaction Ledger" description="Comprehensive audit trail of all book returns, losses, and fine settlements." icon="fas fa-history">
            <div class="flex items-center gap-3">
                <a :href="`{{ route('school.library.export.history') }}?from_date=${filters.from_date || ''}&to_date=${filters.to_date || ''}`"
                    class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 text-sm font-semibold rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition-all shadow-sm">
                    <i class="fas fa-file-csv mr-2 text-xs text-emerald-500"></i>
                    Export CSV
                </a>
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
                        <input type="date" x-model="filters.from_date" @change="applyFilter('from_date', $event.target.value)"
                            class="px-3 py-1.5 text-xs font-bold rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800" placeholder="From" title="From date">
                        <input type="date" x-model="filters.to_date" @change="applyFilter('to_date', $event.target.value)"
                            class="px-3 py-1.5 text-xs font-bold rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800" placeholder="To" title="To date">
                        <x-table.filter-select
                            model="filters.status_filter"
                            action="applyFilter('status_filter', $event.target.value)"
                            placeholder="All Status"
                            :options="['returned' => 'Returned', 'lost' => 'Lost']"
                        />
                        <label class="flex items-center gap-1.5 text-[10px] font-bold text-gray-500 uppercase tracking-tight cursor-pointer">
                            <input type="checkbox" :checked="filters.fines_pending === '1'" @change="applyFilter('fines_pending', $event.target.checked ? '1' : '')" class="rounded border-gray-300">
                            Pending fines
                        </label>
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

                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700 transition-opacity duration-150" x-show="hydrated" :class="loading && rows.length > 0 ? 'opacity-50' : 'opacity-100'">
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
                                    <div class="flex flex-col items-center gap-1.5">
                                        <template x-if="row.fine_amount > 0 && !row.fine_settled">
                                            <button @click="openSettleModal(row)" class="px-4 py-1.5 bg-slate-800 text-white text-[9px] font-black rounded-lg hover:bg-slate-900 transition-all shadow-md active:scale-95 uppercase tracking-widest">
                                                Settle Fine
                                            </button>
                                        </template>
                                        <template x-if="row.status === 'lost'">
                                            <button @click="confirmRecover(row.id)" class="px-3 py-1 bg-emerald-50 text-emerald-700 border border-emerald-200 text-[9px] font-black rounded-lg hover:bg-emerald-100 transition-all uppercase tracking-widest">
                                                <i class="fas fa-undo mr-1"></i>Recover
                                            </button>
                                        </template>
                                        <template x-if="(row.fine_amount == 0 || row.fine_settled) && row.status !== 'lost'">
                                            <span class="text-[10px] text-gray-300 font-black uppercase tracking-widest italic">Cleared</span>
                                        </template>
                                    </div>
                                </td>
                            </tr>
                        </template>

                        <x-table.empty-state :colspan="4" icon="fas fa-clipboard-list" message="No transaction history matches your criteria." />
                    </tbody>
                </table>
            </div>

            <x-table.pagination :initial="$initialData['pagination']" />
        </div>

        <!-- Settle Fine Modal -->
        <x-modal name="settle-fine-modal" alpineTitle="'Settle Outstanding Fine'" maxWidth="lg">
            <form @submit.prevent="submitSettle()" class="p-1">
                @csrf
                <div class="space-y-6">
                    <div class="grid grid-cols-2 gap-4 p-4 bg-gray-50 dark:bg-gray-900/50 rounded-xl">
                        <div>
                            <div class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Outstanding Fine</div>
                            <div class="text-2xl font-black text-rose-600">{{ $currency }}<span x-text="settleData.fine_amount.toFixed(2)"></span></div>
                        </div>
                        <div>
                            <div class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Collected by</div>
                            <div class="text-sm font-bold text-gray-700">{{ auth()->user()->name ?? '—' }}</div>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="modal-label-premium">Amount Received <span class="text-red-500">*</span></label>
                        <input type="number" step="0.01" min="0.01" :max="settleData.fine_amount" x-model="settleData.paid_amount" class="modal-input-premium font-bold">
                        <p class="text-[10px] text-gray-400 italic">Defaults to the full outstanding fine. Partial settlements are allowed.</p>
                        <template x-if="settleErrors.paid_amount"><p class="modal-error-message" x-text="settleErrors.paid_amount[0]"></p></template>
                    </div>

                    <div class="space-y-2">
                        <label class="modal-label-premium">Payment Method <span class="text-red-500">*</span></label>
                        <select x-model="settleData.payment_method" class="modal-input-premium no-select2 appearance-none pr-10">
                            <option value="cash">Cash</option>
                            <option value="upi">UPI</option>
                            <option value="card">Card</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="cheque">Cheque</option>
                            <option value="other">Other</option>
                        </select>
                        <template x-if="settleErrors.payment_method"><p class="modal-error-message" x-text="settleErrors.payment_method[0]"></p></template>
                    </div>

                    <div class="space-y-2">
                        <label class="modal-label-premium">Receipt Note</label>
                        <input type="text" x-model="settleData.note" maxlength="500" placeholder="Receipt number, parent contact, etc." class="modal-input-premium">
                        <template x-if="settleErrors.note"><p class="modal-error-message" x-text="settleErrors.note[0]"></p></template>
                    </div>
                </div>

                <x-slot name="footer">
                    <button type="button" @click="$dispatch('close-modal', 'settle-fine-modal')" class="btn-premium-cancel px-10">Cancel</button>
                    <button type="submit" :disabled="settleSubmitting" class="btn-premium-primary min-w-[200px] !from-slate-800 !to-slate-900">
                        <template x-if="settleSubmitting"><span class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin mr-3 inline-block"></span></template>
                        <span x-text="settleSubmitting ? 'Recording...' : 'Record Settlement'"></span>
                    </button>
                </x-slot>
            </form>
        </x-modal>

        <x-confirm-modal />
    </div>

@endsection
