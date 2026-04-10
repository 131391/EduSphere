@extends('layouts.school')

@section('title', 'Circulation Desk - Library')

@section('content')
<div x-data="circulationManager()">
    <!-- Header Section -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 mb-6 border border-amber-100/50">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-gray-800 dark:text-white flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-amber-100 flex items-center justify-center text-amber-600">
                        <i class="fas fa-exchange-alt text-xs"></i>
                    </div>
                    Library Circulation Desk
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Track book issuances, facilitate returns, and manage overdue assessments</p>
            </div>
            <a href="{{ route('school.library.index') }}" 
               class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-amber-500 to-orange-600 hover:from-amber-600 hover:to-orange-700 text-white text-sm font-semibold rounded-xl transition-all shadow-md hover:shadow-lg active:scale-95">
                <i class="fas fa-atlas mr-2"></i>
                Knowledge Repository
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        <!-- Dashboard Statistics -->
        <div class="lg:col-span-12 grid grid-cols-1 md:grid-cols-4 gap-6 mb-2">
            <div class="bg-white p-6 rounded-3xl border border-gray-100 shadow-sm flex items-center gap-4 group hover:border-amber-200 transition-colors">
                <div class="w-12 h-12 rounded-2xl bg-amber-50 flex items-center justify-center text-amber-600 group-hover:scale-110 transition-transform">
                    <i class="fas fa-hand-holding-heart"></i>
                </div>
                <div>
                    <span class="block text-[10px] font-black text-gray-400 uppercase tracking-widest">Active Issues</span>
                    <span class="text-2xl font-black text-gray-800">{{ $activeIssues->total() }}</span>
                </div>
            </div>
            <div class="bg-white p-6 rounded-3xl border border-gray-100 shadow-sm flex items-center gap-4 group hover:border-red-200 transition-colors">
                <div class="w-12 h-12 rounded-2xl bg-red-50 flex items-center justify-center text-red-600 group-hover:scale-110 transition-transform">
                    <i class="fas fa-clock"></i>
                </div>
                <div>
                    <span class="block text-[10px] font-black text-gray-400 uppercase tracking-widest">Overdue</span>
                    <span class="text-2xl font-black text-red-600">{{ $activeIssues->getCollection()->where('due_date', '<', now())->count() }}</span>
                </div>
            </div>
        </div>

        <!-- Issue Book Form (Check-out) -->
        <div class="lg:col-span-4">
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden sticky top-6">
                <div class="bg-gradient-to-r from-amber-500 to-orange-600 p-6">
                    <h3 class="text-white font-black uppercase tracking-widest text-sm flex items-center gap-2">
                        <i class="fas fa-plus-circle"></i>
                        Express Check-Out
                    </h3>
                </div>
                <form @submit.prevent="issueBook" method="POST" class="p-8 space-y-6">
                    @csrf
                    <!-- Book Picker -->
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5 ml-1">Asset Identity</label>
                        <select name="book_id" x-model="formData.book_id" required class="w-full px-4 py-3 bg-gray-50 border-transparent rounded-2xl focus:ring-4 focus:ring-amber-500/10 focus:border-amber-500 focus:bg-white transition-all font-bold text-gray-700">
                            <option value="">-- Targeted Book --</option>
                            @foreach($books as $book)
                                <option value="{{ $book->id }}">{{ $book->title }} ({{ $book->available_quantity }} left)</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Student Picker -->
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5 ml-1">Beneficiary (Student)</label>
                        <select name="student_id" x-model="formData.student_id" required class="w-full px-4 py-3 bg-gray-50 border-transparent rounded-2xl focus:ring-4 focus:ring-amber-500/10 focus:border-amber-500 focus:bg-white transition-all font-bold text-gray-700">
                            <option value="">-- Targeted Beneficiary --</option>
                            @foreach($students as $student)
                                <option value="{{ $student->id }}">{{ $student->admission_no }} - {{ $student->full_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Due Date -->
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5 ml-1">Return Obligation (Due Date)</label>
                        <input 
                            type="date" 
                            name="due_date" 
                            x-model="formData.due_date"
                            min="{{ date('Y-m-d') }}"
                            class="w-full px-4 py-3 bg-gray-50 border-transparent rounded-2xl focus:border-amber-500 transition-all font-bold text-gray-700"
                        >
                    </div>

                    <button 
                        type="submit" 
                        :disabled="submitting"
                        class="w-full py-4 bg-gradient-to-r from-amber-500 to-orange-600 text-white font-black rounded-2xl shadow-xl shadow-amber-100 hover:from-amber-600 hover:to-orange-700 transition-all active:scale-95 disabled:opacity-50 flex items-center justify-center gap-3 uppercase tracking-widest text-xs"
                    >
                        <template x-if="submitting">
                            <span class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                        </template>
                        <i x-show="!submitting" class="fas fa-signature"></i>
                        <span x-text="submitting ? 'Transmitting...' : 'Confirm Issuance'"></span>
                    </button>
                </form>
            </div>
        </div>

        <!-- Active Issues Tracker (Digital Register) -->
        <div class="lg:col-span-8">
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-8 py-6 border-b border-gray-50 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-black text-gray-800 uppercase tracking-widest">Digital Circulation Register</h3>
                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-tighter mt-0.5">Real-time status tracking of all checked-out assets</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-50">
                        <thead class="bg-gray-50/50">
                            <tr>
                                <th class="px-8 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Asset Details</th>
                                <th class="px-8 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Beneficiary</th>
                                <th class="px-8 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Timeline</th>
                                <th class="px-8 py-4 text-right text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @forelse($activeIssues as $issue)
                            <tr class="group hover:bg-amber-50/10 transition-colors duration-150">
                                <td class="px-8 py-5">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center text-gray-400 font-bold group-hover:bg-amber-100 group-hover:text-amber-600 transition-all">
                                            <i class="fas fa-atlas text-[10px]"></i>
                                        </div>
                                        <div>
                                            <div class="text-sm font-bold text-gray-800">{{ $issue->book->title }}</div>
                                            <div class="text-[9px] font-bold text-gray-400 uppercase">{{ $issue->book->author }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-5">
                                    <div class="text-sm font-bold text-gray-700">{{ $issue->student->full_name }}</div>
                                    <div class="text-[10px] font-medium text-gray-400 tracking-tighter">{{ $issue->student->admission_no }}</div>
                                </td>
                                <td class="px-8 py-5">
                                    <div class="flex flex-col gap-1.5">
                                        <div class="flex items-center gap-1.5 text-[9px] text-gray-400 font-bold uppercase tracking-tighter">
                                            <i class="fas fa-arrow-circle-up text-amber-500"></i>
                                            {{ $issue->issue_date->format('d M, Y') }}
                                        </div>
                                        @php $isOverdue = $issue->due_date->isPast(); @endphp
                                        <div class="flex items-center gap-1.5 text-[10px] font-black uppercase tracking-tighter {{ $isOverdue ? 'text-red-600' : 'text-emerald-600' }}">
                                            <i class="fas fa-calendar-check opacity-50"></i>
                                            Due: {{ $issue->due_date->format('d M') }}
                                            @if($isOverdue)
                                                <span class="ml-1 px-1.5 py-0.5 bg-red-100 rounded text-[8px] animate-pulse">OVERDUE</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-5 text-right whitespace-nowrap">
                                    <button 
                                        @click="processReturn({{ $issue->id }})"
                                        class="px-5 py-2 bg-emerald-50 text-emerald-700 text-[10px] font-black rounded-xl border border-emerald-100 hover:bg-emerald-600 hover:text-white hover:border-emerald-600 transition-all shadow-sm hover:shadow-lg active:scale-95 uppercase tracking-widest"
                                    >
                                        Return Asset
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-8 py-20 text-center">
                                    <div class="flex flex-col items-center opacity-30">
                                        <i class="fas fa-clipboard-list text-6xl mb-4"></i>
                                        <p class="font-black uppercase tracking-widest text-sm">Circulation Registry is Clear</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($activeIssues->hasPages())
                <div class="px-8 py-6 border-t border-gray-50 bg-gray-50/30">
                    {{ $activeIssues->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Return Confirmation Modal (Fine Preview) -->
    <x-modal name="return-modal" alpineTitle="'Validate Return Process'" maxWidth="md">
        <div class="px-8 py-8 space-y-6 text-center">
            <div class="w-20 h-20 bg-emerald-50 text-emerald-600 rounded-3xl flex items-center justify-center text-3xl mx-auto shadow-inner">
                <i class="fas fa-check-double"></i>
            </div>
            <div>
                <h3 class="text-xl font-black text-gray-800 uppercase tracking-tight">Confirm Asset Retrieval?</h3>
                <p class="text-xs text-gray-500 font-medium mt-1">This will update shelf stock and clear beneficiary obligations.</p>
            </div>

            <!-- Fine Display -->
            <template x-if="fineAmount > 0">
                <div class="p-6 bg-red-50 rounded-3xl border border-red-100 flex flex-col items-center gap-2">
                    <span class="text-[10px] font-black text-red-500 uppercase tracking-[0.2em]">Overdue Penalty Valuation</span>
                    <span class="text-3xl font-black text-red-700">$<span x-text="fineAmount.toFixed(2)"></span></span>
                    <p class="text-[9px] text-red-400 font-bold uppercase mt-1">Penalty applied to student ledger upon return</p>
                </div>
            </template>

            <template x-if="fineAmount == 0">
                <div class="p-4 bg-emerald-50 rounded-2xl border border-emerald-100 inline-flex items-center gap-2 text-[10px] font-black text-emerald-700 uppercase tracking-widest">
                    <i class="fas fa-medal"></i>
                    Return on Schedule (No Fine)
                </div>
            </template>
        </div>
        <div class="px-8 py-6 bg-gray-50/50 flex items-center justify-center gap-4 rounded-b-3xl border-t border-gray-100">
            <button @click="closeModal()" class="px-6 py-2.5 text-xs font-black text-gray-400 hover:text-gray-600 uppercase tracking-widest transition-all">Abort</button>
            <button @click="confirmReturn()" class="px-10 py-3 bg-emerald-600 text-white text-xs font-black rounded-2xl shadow-xl shadow-emerald-100 hover:bg-emerald-700 transition-all active:scale-95 uppercase tracking-[0.2em] min-w-[160px]">Finalize Retrieval</button>
        </div>
    </x-modal>
</div>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('circulationManager', () => ({
        submitting: false,
        formData: {
            book_id: '',
            student_id: '',
            due_date: '{{ date('Y-m-d', strtotime('+14 days')) }}'
        },
        returnIssueId: null,
        fineAmount: 0,

        async issueBook() {
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
                    window.Toast.fire({ icon: 'success', title: result.message });
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    throw new Error(result.message || 'Issuance failed');
                }
            } catch (error) {
                window.Toast.fire({ icon: 'error', title: error.message });
            } finally {
                this.submitting = false;
            }
        },

        async processReturn(issueId) {
            this.returnIssueId = issueId;
            // Optimistic pre-check for fines could happen here if we had an endpoint
            // For now, let service calculate it on return. 
            // Optional: Preview calculation logic (5/day)
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
                    window.Toast.fire({ 
                        icon: 'success', 
                        title: result.message + (result.fine > 0 ? ` Fine applied: $${result.fine}` : '') 
                    });
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    alert(result.message);
                }
            } catch (error) {
                alert('An error occurred');
            }
        },

        closeModal() {
            this.$dispatch('close-modal', 'return-modal');
        }
    }));
});
</script>
@endpush
@endsection
