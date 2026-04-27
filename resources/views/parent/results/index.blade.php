@extends('layouts.parent')

@section('title', 'Academic Results')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-5 border border-indigo-100/50">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-gray-800 dark:text-white flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-indigo-100 flex items-center justify-center text-indigo-600">
                        <i class="fas fa-trophy text-xs"></i>
                    </div>
                    Academic Performance
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Detailed breakdown of examination results and subject grading.</p>
            </div>
            
            @if($children->count() > 1)
            <div class="flex items-center gap-3">
                <form method="GET" action="{{ route('parent.results.index') }}" class="relative group">
                    <select name="student_id" onchange="this.form.submit()"
                            class="appearance-none pl-10 pr-10 py-2 border border-gray-200 dark:border-gray-600 rounded-xl text-sm bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all cursor-pointer">
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
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-indigo-50 flex items-center gap-5 group hover:shadow-md transition-all duration-300">
            <div class="w-14 h-14 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform">
                <i class="fas fa-file-signature text-2xl"></i>
            </div>
            <div>
                <p class="text-xs font-black text-gray-400 uppercase tracking-widest">Exams Taken</p>
                <p class="text-2xl font-black text-gray-800">{{ $stats['total_exams'] }}</p>
            </div>
        </div>
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-indigo-50 flex items-center gap-5 group hover:shadow-md transition-all duration-300">
            <div class="w-14 h-14 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform">
                <i class="fas fa-chart-line text-2xl"></i>
            </div>
            <div>
                <p class="text-xs font-black text-gray-400 uppercase tracking-widest">Avg. Percentage</p>
                <p class="text-2xl font-black text-emerald-600">{{ $stats['avg_pct'] }}%</p>
            </div>
        </div>
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-indigo-50 flex items-center gap-5 group hover:shadow-md transition-all duration-300">
            <div class="w-14 h-14 bg-amber-50 text-amber-600 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform">
                <i class="fas fa-star text-2xl"></i>
            </div>
            <div>
                <p class="text-xs font-black text-gray-400 uppercase tracking-widest">Top Subject</p>
                <p class="text-2xl font-black text-amber-600 truncate max-w-[150px]">{{ $stats['best_subject'] }}</p>
            </div>
        </div>
    </div>

    @forelse($results as $examResults)
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="px-6 py-4 bg-gray-50/50 dark:bg-gray-700/30 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
            <h3 class="text-base font-bold text-gray-800 dark:text-white flex items-center gap-2">
                <i class="fas fa-file-alt text-indigo-500"></i>
                {{ $examResults->first()?->exam?->display_name ?? 'Exam' }}
            </h3>
            <span class="px-2.5 py-1 rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 text-[10px] font-black uppercase tracking-widest text-gray-500 dark:text-gray-400 shadow-sm">
                {{ $examResults->count() }} Subjects
            </span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50/30 dark:bg-gray-700/10">
                        <th class="text-left px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Subject Information</th>
                        <th class="text-center px-4 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Obtained</th>
                        <th class="text-center px-4 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Max Marks</th>
                        <th class="text-center px-4 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Achievement</th>
                        <th class="text-center px-4 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Grade</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @foreach($examResults as $result)
                    @php 
                        $pct = (float)$result->percentage;
                        $color = $pct >= 75 ? 'emerald' : ($pct >= 50 ? 'amber' : 'rose');
                    @endphp
                    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/20 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-1.5 h-1.5 rounded-full bg-{{ $color }}-400"></div>
                                <span class="font-bold text-gray-700 dark:text-gray-200">{{ optional($result->subject)->name ?? '—' }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-4 text-center font-black text-gray-800 dark:text-gray-100">{{ $result->marks_obtained }}</td>
                        <td class="px-4 py-4 text-center font-bold text-gray-400">{{ $result->total_marks }}</td>
                        <td class="px-4 py-4">
                            <div class="flex flex-col items-center gap-1.5">
                                <span class="text-[10px] font-black text-{{ $color }}-600 uppercase tracking-widest">{{ $pct }}%</span>
                                <div class="w-20 h-1.5 bg-gray-100 dark:bg-gray-600 rounded-full overflow-hidden">
                                    <div class="h-full bg-{{ $color }}-500 rounded-full" style="width: {{ $pct }}%"></div>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-4 text-center">
                            <span class="inline-flex items-center px-3 py-1 rounded-xl bg-{{ $color }}-50 dark:bg-{{ $color }}-900/20 text-{{ $color }}-700 dark:text-{{ $color }}-300 border border-{{ $color }}-100/50 text-[10px] font-black uppercase tracking-widest">
                                {{ $result->grade ?? '—' }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @empty
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-20 text-center">
        <div class="w-20 h-20 bg-gray-50 dark:bg-gray-700 rounded-3xl flex items-center justify-center mx-auto mb-6">
            <i class="fas fa-trophy text-4xl text-gray-300 dark:text-gray-600"></i>
        </div>
        <h3 class="text-xl font-bold text-gray-800 dark:text-white">Awaiting Results</h3>
        <p class="text-gray-500 dark:text-gray-400 mt-2 max-w-sm mx-auto">Results will be published here once evaluation is complete. Stay tuned for academic updates.</p>
    </div>
    @endforelse
</div>
@endsection
