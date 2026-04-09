@extends('layouts.parent')

@section('title', 'Results')
@section('page-title', 'Results')

@section('content')
<div class="space-y-6">

    <!-- Child Selector -->
    @if($children->count() > 1)
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
        <form method="GET" action="{{ route('parent.results.index') }}" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[180px]">
                <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1 uppercase tracking-wide">Select Child</label>
                <select name="student_id" onchange="this.form.submit()"
                        class="w-full px-3 py-2 border border-gray-200 dark:border-gray-600 rounded-lg text-sm bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-indigo-500">
                    @foreach($children as $child)
                    <option value="{{ $child->id }}" {{ $selectedChildId == $child->id ? 'selected' : '' }}>
                        {{ $child->full_name }} ({{ optional($child->class)->name }})
                    </option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>
    @endif

    @forelse($results as $examName => $examResults)
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
            <h3 class="text-base font-semibold text-gray-800 dark:text-white">
                <i class="fas fa-file-alt text-indigo-500 mr-2"></i>{{ $examName }}
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-700/50">
                    <tr>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Subject</th>
                        <th class="text-center px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Obtained</th>
                        <th class="text-center px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total</th>
                        <th class="text-center px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">%</th>
                        <th class="text-center px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Grade</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @foreach($examResults as $result)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                        <td class="px-6 py-3 font-medium text-gray-800 dark:text-gray-200">{{ optional($result->subject)->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-center text-gray-700 dark:text-gray-300">{{ $result->marks_obtained }}</td>
                        <td class="px-4 py-3 text-center text-gray-700 dark:text-gray-300">{{ $result->total_marks }}</td>
                        <td class="px-4 py-3 text-center">
                            @php $pct = (float)$result->percentage; @endphp
                            <span class="font-semibold {{ $pct >= 75 ? 'text-green-600 dark:text-green-400' : ($pct >= 50 ? 'text-yellow-600 dark:text-yellow-400' : 'text-red-600 dark:text-red-400') }}">
                                {{ $pct }}%
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300">
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
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-16 text-center">
        <i class="fas fa-trophy text-4xl text-gray-300 dark:text-gray-600 mb-4"></i>
        <p class="text-gray-500 dark:text-gray-400 font-medium">No results published yet.</p>
        <p class="text-gray-400 dark:text-gray-500 text-sm mt-1">Results will appear here once exams are evaluated.</p>
    </div>
    @endforelse
</div>
@endsection
