@extends('layouts.school')

@section('title', 'Library Circulation Desk')

@section('content')
    <div x-data="Object.assign(ajaxDataTable({
        fetchUrl: '{{ route('school.library.issues.fetch') }}',
        defaultSort: 'due_date',
        defaultDirection: 'asc',
        defaultPerPage: 25,
        initialRows: @js($initialData['rows']),
        initialPagination: @js($initialData['pagination']),
        initialStats: @js($initialData['stats'])
    }), circulationManager())" class="space-y-6">

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <x-stat-card label="Total Assets" :value="$stats['total_books']" icon="fas fa-boxes-stacked" color="amber" alpine-text="stats.total_books" />
            <x-stat-card label="Active Issues" :value="$stats['issued_books']" icon="fas fa-hand-holding-heart" color="indigo" alpine-text="stats.issued_books" />
            <x-stat-card label="Overdue Returns" :value="$stats['overdue_returns']" icon="fas fa-clock" color="rose" alpine-text="stats.overdue_returns" />
            <x-stat-card label="Available Titles" :value="$stats['available_titles']" icon="fas fa-atlas" color="emerald" alpine-text="stats.available_titles" />
        </div>

        <!-- Header Section -->
        <x-page-header title="Library Circulation Desk" description="Track book issuances, facilitate returns, and manage overdue assessments for student beneficiaries." icon="fas fa-exchange-alt">
            <div class="flex items-center gap-3">
                <a href="{{ route('school.library.index') }}" 
                    class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 text-sm font-semibold rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition-all shadow-sm">
                    <i class="fas fa-atlas mr-2 text-xs text-amber-500"></i>
                    Knowledge Repository
                </a>
                <button @click="openIssueModal()"
                    class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-700 hover:to-violet-700 text-white text-sm font-semibold rounded-xl transition-all shadow-md hover:shadow-lg active:scale-95">
                    <i class="fas fa-signature mr-2 text-xs"></i>
                    Issue New Book
                </button>
            </div>
        </x-page-header>

        <!-- AJAX Data Table -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <!-- Table Header -->
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div class="flex-1 flex flex-col md:flex-row md:items-center gap-4">
                        <h2 class="text-lg font-semibold text-gray-800 dark:text-white">Active Circulation Ledger</h2>
                        <x-table.search placeholder="Search by student, book title..." />
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
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Asset Details</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Beneficiary</th>
                            <x-table.sort-header column="due_date" label="Timeline" sort-var="sort" direction-var="direction" />
                            <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider w-40">Actions</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700" :class="{ 'hidden': true }">
                        @foreach($initialData['rows'] as $row)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/50 transition-colors group">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-amber-50 dark:bg-amber-900/40 border border-amber-100 dark:border-amber-800 flex items-center justify-center text-amber-600 dark:text-amber-400">
                                            <i class="fas fa-atlas text-[10px]"></i>
                                        </div>
                                        <div>
                                            <div class="text-sm font-bold text-gray-800 dark:text-gray-100">{{ $row['book_title'] }}</div>
                                            <div class="text-[9px] font-bold text-gray-400 uppercase tracking-tight">Active Issue</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-bold text-gray-700 dark:text-gray-200">{{ $row['student_name'] }}</div>
                                    <div class="text-[10px] font-medium text-gray-400 tracking-tighter">{{ $row['admission_no'] }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex flex-col gap-1">
                                        <div class="flex items-center gap-1.5 text-[9px] text-gray-400 font-bold uppercase tracking-tight">
                                            <i class="fas fa-arrow-circle-up text-indigo-500"></i> Issued: {{ $row['issue_date'] }}
                                        </div>
                                        <div class="flex items-center gap-1.5 text-[10px] font-black uppercase tracking-tight {{ $row['overdue'] ? 'text-rose-600' : 'text-emerald-600' }}">
                                            <i class="fas fa-calendar-check opacity-50"></i> Due: {{ $row['due_date'] }}
                                            @if($row['overdue'])
                                                <span class="ml-1 px-1.5 py-0.5 bg-rose-100 dark:bg-rose-900/40 rounded text-[8px] animate-pulse">OVERDUE ({{ $row['overdue_days'] }}d)</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <button @click="processReturn({{ $row['id'] }})" class="px-4 py-1.5 bg-emerald-50 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-400 text-[10px] font-black rounded-lg border border-emerald-100 dark:border-emerald-800 hover:bg-emerald-600 hover:text-white transition-all shadow-sm">
                                        RETURN ASSET
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>

                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700 transition-opacity duration-150" x-cloak :class="loading ? 'opacity-50' : 'opacity-100'">
                        <template x-for="row in rows" :key="row.id">
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/50 transition-colors group">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-amber-50 dark:bg-amber-900/40 border border-amber-100 dark:border-amber-800 flex items-center justify-center text-amber-600 dark:text-amber-400">
                                            <i class="fas fa-atlas text-[10px]"></i>
                                        </div>
                                        <div>
                                            <div class="text-sm font-bold text-gray-800 dark:text-gray-100" x-text="row.book_title"></div>
                                            <div class="text-[9px] font-bold text-gray-400 uppercase tracking-tight">Active Issue</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-bold text-gray-700 dark:text-gray-200" x-text="row.student_name"></div>
                                    <div class="text-[10px] font-medium text-gray-400 tracking-tighter" x-text="row.admission_no"></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex flex-col gap-1">
                                        <div class="flex items-center gap-1.5 text-[9px] text-gray-400 font-bold uppercase tracking-tight">
                                            <i class="fas fa-arrow-circle-up text-indigo-500"></i> Issued: <span x-text="row.issue_date"></span>
                                        </div>
                                        <div class="flex items-center gap-1.5 text-[10px] font-black uppercase tracking-tight" :class="row.overdue ? 'text-rose-600' : 'text-emerald-600'">
                                            <i class="fas fa-calendar-check opacity-50"></i> Due: <span x-text="row.due_date"></span>
                                            <template x-if="row.overdue">
                                                <span class="ml-1 px-1.5 py-0.5 bg-rose-100 dark:bg-rose-900/40 rounded text-[8px] animate-pulse" x-text="'OVERDUE (' + row.overdue_days + 'd)'"></span>
                                            </template>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <button @click="processReturn(row.id)" class="px-4 py-1.5 bg-emerald-50 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-400 text-[10px] font-black rounded-lg border border-emerald-100 dark:border-emerald-800 hover:bg-emerald-600 hover:text-white transition-all shadow-sm">
                                        RETURN ASSET
                                    </button>
                                </td>
                            </tr>
                        </template>

                        <x-table.empty-state :colspan="4" icon="fas fa-clipboard-list" message="Circulation registry is clear. No active issues match your search." />
                    </tbody>
                </table>
            </div>

            <x-table.pagination />
        </div>

        <!-- Issue Book Modal -->
        <x-modal name="issue-book-modal" alpineTitle="'Register New Issuance'" maxWidth="md">
            <form @submit.prevent="submitIssue()" method="POST" class="p-1">
                @csrf
                <div class="space-y-6">
                    <div class="space-y-2">
                        <label class="modal-label-premium">Targeted Asset <span class="text-red-500">*</span></label>
                        <select x-model="formData.book_id" class="modal-input-premium appearance-none pr-10">
                            <option value="">-- Choose Book --</option>
                            @foreach($books as $book)
                                <option value="{{ $book->id }}">{{ $book->title }} ({{ $book->available_quantity }} units left)</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label class="modal-label-premium">Beneficiary Student <span class="text-red-500">*</span></label>
                        <select x-model="formData.student_id" class="modal-input-premium appearance-none pr-10">
                            <option value="">-- Choose Student --</option>
                            @foreach($students as $student)
                                <option value="{{ $student->id }}">{{ $student->admission_no }} - {{ $student->full_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label class="modal-label-premium">Return Obligation (Due Date) <span class="text-red-500">*</span></label>
                        <input type="date" x-model="formData.due_date" min="{{ date('Y-m-d') }}" class="modal-input-premium">
                    </div>
                </div>

                <x-slot name="footer">
                    <button type="button" @click="$dispatch('close-modal', 'issue-book-modal')" class="btn-premium-cancel px-10">Discard</button>
                    <button type="submit" :disabled="submitting" class="btn-premium-primary min-w-[200px] !from-indigo-600 !to-violet-600 shadow-indigo-200">
                        <template x-if="submitting"><span class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin mr-3 inline-block"></span></template>
                        <span x-text="submitting ? 'Transmitting...' : 'Confirm Issuance'"></span>
                    </button>
                </x-slot>
            </form>
        </x-modal>

        <!-- Return Confirmation Modal -->
        <x-modal name="return-modal" alpineTitle="'Validate Return Process'" maxWidth="md">
            <div class="px-8 py-8 space-y-6 text-center">
                <div class="w-20 h-20 bg-emerald-50 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400 rounded-3xl flex items-center justify-center text-3xl mx-auto shadow-inner border border-emerald-100 dark:border-emerald-800">
                    <i class="fas fa-check-double"></i>
                </div>
                <div>
                    <h3 class="text-xl font-black text-gray-800 dark:text-white uppercase tracking-tight">Confirm Retrieval?</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 font-medium mt-1">This will update inventory levels and clear student obligations.</p>
                </div>
            </div>
            <div class="px-8 py-6 bg-gray-50/50 dark:bg-gray-800/50 flex items-center justify-center gap-4 rounded-b-3xl border-t border-gray-100 dark:border-gray-700">
                <button @click="$dispatch('close-modal', 'return-modal')" class="px-6 py-2.5 text-xs font-black text-gray-400 hover:text-gray-600 uppercase tracking-widest transition-all">Abort</button>
                <button @click="confirmReturn()" class="px-10 py-3 bg-emerald-600 text-white text-xs font-black rounded-2xl shadow-xl shadow-emerald-100 hover:bg-emerald-700 transition-all active:scale-95 uppercase tracking-widest min-w-[160px]">Finalize Retrieval</button>
            </div>
        </x-modal>

        <x-confirm-modal />
    </div>

    @push('scripts')
        <script>
            function circulationManager() {
                return {
                    submitting: false,
                    formData: {
                        book_id: '',
                        student_id: '',
                        due_date: '{{ date('Y-m-d', strtotime('+14 days')) }}'
                    },
                    returnIssueId: null,

                    openIssueModal() {
                        this.formData = { book_id: '', student_id: '', due_date: '{{ date('Y-m-d', strtotime('+14 days')) }}' };
                        this.$dispatch('open-modal', 'issue-book-modal');
                    },

                    async submitIssue() {
                        if (this.submitting) return;
                        this.submitting = true;

                        try {
                            const response = await fetch('{{ route('school.library.issue.store') }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify(this.formData)
                            });

                            const result = await response.json();
                            if (response.ok) {
                                if (window.Toast) window.Toast.fire({ icon: 'success', title: result.message });
                                this.$dispatch('close-modal', 'issue-book-modal');
                                if (typeof this.refreshTable === 'function') this.refreshTable();
                            } else {
                                throw new Error(result.message || 'Issuance failed');
                            }
                        } catch (e) {
                            if (window.Toast) window.Toast.fire({ icon: 'error', title: e.message });
                        } finally {
                            this.submitting = false;
                        }
                    },

                    processReturn(issueId) {
                        this.returnIssueId = issueId;
                        this.$dispatch('open-modal', 'return-modal');
                    },

                    async confirmReturn() {
                        try {
                            const response = await fetch(`/school/library/return/${this.returnIssueId}`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                }
                            });

                            const result = await response.json();
                            if (response.ok) {
                                if (window.Toast) window.Toast.fire({ 
                                    icon: 'success', 
                                    title: result.message + (result.fine > 0 ? ` Penalty: ₹${result.fine}` : '') 
                                });
                                this.$dispatch('close-modal', 'return-modal');
                                if (typeof this.refreshTable === 'function') this.refreshTable();
                            } else {
                                if (window.Toast) window.Toast.fire({ icon: 'error', title: result.message || 'Operation failed' });
                            }
                        } catch (e) {
                            if (window.Toast) window.Toast.fire({ icon: 'error', title: 'Connection error' });
                        }
                    }
                }
            }
        </script>
    @endpush
@endsection
