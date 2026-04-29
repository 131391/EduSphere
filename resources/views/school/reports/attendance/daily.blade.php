@extends('layouts.school')

@section('title', 'Daily Attendance Summary')

@section('content')
<div x-data="{
    date: '{{ $date }}',
    loading: false,
    summary: @js($summary),
    async fetchSummary() {
        this.loading = true;
        try {
            const res = await fetch('{{ route('school.reports.attendance.daily') }}?date=' + this.date, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await res.json();
            this.summary = data.summary;
        } catch(e) { console.error(e); }
        finally { this.loading = false; }
    }
}" class="space-y-6">

    {{-- Page Header --}}
    <x-page-header
        title="Daily Attendance Summary"
        description="Overview of attendance across all classes for the selected date."
        icon="fas fa-clipboard-check">
        <div class="flex items-center gap-3">
            <input type="date" x-model="date" @change="fetchSummary()"
                class="rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 text-sm px-3 py-2 shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            <button onclick="window.print()"
                class="inline-flex items-center gap-2 px-4 py-2 bg-gray-800 dark:bg-gray-700 text-white text-sm font-semibold rounded-lg hover:bg-gray-900 transition-colors shadow-sm no-print">
                <i class="fas fa-print"></i> Print
            </button>
        </div>
    </x-page-header>

    {{-- Loading overlay --}}
    <div x-show="loading" x-cloak class="flex justify-center py-12">
        <div class="flex items-center gap-3 text-indigo-600">
            <i class="fas fa-spinner fa-spin text-xl"></i>
            <span class="text-sm font-medium">Loading attendance data…</span>
        </div>
    </div>

    {{-- Empty state --}}
    <div x-show="!loading && summary.length === 0" x-cloak
        class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-16 text-center">
        <i class="fas fa-clipboard-list text-4xl text-gray-300 dark:text-gray-600 mb-4"></i>
        <p class="text-gray-500 dark:text-gray-400 font-medium">No attendance records found for this date.</p>
    </div>

    {{-- Class Cards Grid --}}
    <div x-show="!loading && summary.length > 0"
        class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5 print-grid">
        <template x-for="classData in summary" :key="classData.class_name">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                {{-- Card Header --}}
                <div class="px-5 py-3.5 bg-indigo-50 dark:bg-indigo-900/30 border-b border-indigo-100 dark:border-indigo-800 flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-indigo-600 flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-chalkboard text-white text-xs"></i>
                    </div>
                    <h3 class="font-bold text-gray-800 dark:text-white text-sm" x-text="'Class ' + classData.class_name"></h3>
                </div>

                {{-- Sections Table --}}
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50/70 dark:bg-gray-700/50">
                            <th class="px-4 py-2.5 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Section</th>
                            <th class="px-4 py-2.5 text-center text-[10px] font-bold text-gray-400 uppercase tracking-wider">Total</th>
                            <th class="px-4 py-2.5 text-center text-[10px] font-bold text-emerald-500 uppercase tracking-wider">P</th>
                            <th class="px-4 py-2.5 text-center text-[10px] font-bold text-rose-500 uppercase tracking-wider">A</th>
                            <th class="px-4 py-2.5 text-center text-[10px] font-bold text-amber-500 uppercase tracking-wider">L</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="section in classData.sections" :key="section.section_name">
                            <tr class="border-t border-gray-100 dark:border-gray-700 hover:bg-gray-50/50 dark:hover:bg-gray-700/30 transition-colors">
                                <td class="px-4 py-3 font-semibold text-gray-700 dark:text-gray-200" x-text="section.section_name"></td>
                                <td class="px-4 py-3 text-center font-bold text-gray-900 dark:text-white" x-text="section.total_students"></td>
                                <td class="px-4 py-3 text-center font-bold text-emerald-600 dark:text-emerald-400" x-text="section.present"></td>
                                <td class="px-4 py-3 text-center font-bold text-rose-600 dark:text-rose-400" x-text="section.absent"></td>
                                <td class="px-4 py-3 text-center font-bold text-amber-600 dark:text-amber-400" x-text="section.leave"></td>
                            </tr>
                        </template>
                    </tbody>
                </table>

                {{-- Attendance Rate Footer --}}
                <div class="px-5 py-3 bg-gray-50/50 dark:bg-gray-700/30 border-t border-gray-100 dark:border-gray-700">
                    <div x-data="{
                        get percent() {
                            const total = classData.sections.reduce((s,x) => s + x.total_students, 0);
                            const present = classData.sections.reduce((s,x) => s + x.present, 0);
                            return total > 0 ? Math.round((present / total) * 100) : 0;
                        }
                    }">
                        <div class="flex justify-between items-center mb-1.5">
                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Attendance Rate</span>
                            <span class="text-sm font-black"
                                :class="percent > 90 ? 'text-emerald-600' : (percent > 75 ? 'text-indigo-600' : 'text-rose-600')"
                                x-text="percent + '%'"></span>
                        </div>
                        <div class="w-full bg-gray-200 dark:bg-gray-600 rounded-full h-1.5">
                            <div class="h-1.5 rounded-full transition-all duration-500"
                                :class="percent > 90 ? 'bg-emerald-500' : (percent > 75 ? 'bg-indigo-500' : 'bg-rose-500')"
                                :style="'width:' + percent + '%'"></div>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>
</div>

@push('styles')
<style>
@media print {
    .no-print, aside, header { display: none !important; }
    .print-grid { display: grid !important; grid-template-columns: repeat(2, 1fr); gap: 1rem; }
    body { background: white !important; }
}
</style>
@endpush

@push('scripts')
<script>
// Convert initial PHP data to JSON for Alpine
document.addEventListener('alpine:init', () => {});
</script>
@endpush
@endsection
