@extends('layouts.parent')

@section('title', 'My Children')
@section('page-title', 'My Children')

@section('content')
<div class="space-y-6">
    @forelse($children as $child)
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
        <div class="flex flex-col sm:flex-row gap-5 items-start sm:items-center justify-between">
            <div class="flex items-center gap-4">
                @if($child->photo)
                <img src="{{ asset('storage/' . $child->photo) }}" class="w-16 h-16 rounded-full object-cover border-2 border-gray-200 dark:border-gray-600" alt="">
                @else
                <div class="w-16 h-16 rounded-full bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center text-2xl font-bold text-indigo-600 dark:text-indigo-400 flex-shrink-0">
                    {{ strtoupper(substr($child->first_name, 0, 1)) }}
                </div>
                @endif
                <div>
                    <h3 class="text-lg font-bold text-gray-800 dark:text-white">{{ $child->full_name }}</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                        {{ optional($child->class)->name }}
                        @if($child->section)&middot; {{ $child->section->name }}@endif
                        &middot; Adm: {{ $child->admission_no }}
                    </p>
                    @if($child->academicYear)
                    <p class="text-xs text-gray-400 mt-0.5">{{ $child->academicYear->name ?? '' }}</p>
                    @endif
                </div>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('parent.results.index', ['student_id' => $child->id]) }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium bg-indigo-50 dark:bg-indigo-900/20 text-indigo-600 dark:text-indigo-400 rounded-lg hover:bg-indigo-100 dark:hover:bg-indigo-900/40 transition-colors">
                    <i class="fas fa-trophy"></i> Results
                </a>
                <a href="{{ route('parent.fees.index', ['student_id' => $child->id]) }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium bg-green-50 dark:bg-green-900/20 text-green-600 dark:text-green-400 rounded-lg hover:bg-green-100 dark:hover:bg-green-900/40 transition-colors">
                    <i class="fas fa-receipt"></i> Fees
                </a>
                <a href="{{ route('parent.children.show', $child->id) }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                    <i class="fas fa-eye"></i> Profile
                </a>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700 grid grid-cols-3 gap-4 text-center">
            @php
                $att   = $child->attendance ?? collect();
                $total = $att->count();
                $pres  = $att->filter(fn($a) => $a->status?->value === 1)->count();
                $attPct = $total > 0 ? round($pres/$total*100, 1) : 0;
                $due   = $child->fees->sum('due_amount') ?? 0;
            @endphp
            <div>
                <p class="text-xs text-gray-400 dark:text-gray-500">Attendance</p>
                <p class="text-base font-bold {{ $attPct >= 75 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">{{ $attPct }}%</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 dark:text-gray-500">Fee Due</p>
                <p class="text-base font-bold {{ $due > 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }}">
                    ₹{{ number_format($due, 2) }}
                </p>
            </div>
            <div>
                <p class="text-xs text-gray-400 dark:text-gray-500">Exams</p>
                <p class="text-base font-bold text-gray-700 dark:text-gray-300">{{ $child->results->count() }}</p>
            </div>
        </div>
    </div>
    @empty
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-16 text-center">
        <i class="fas fa-child text-4xl text-gray-300 dark:text-gray-600 mb-4"></i>
        <p class="text-gray-500 dark:text-gray-400 font-medium">No children linked to your account.</p>
        <p class="text-gray-400 dark:text-gray-500 text-sm mt-1">Please contact the school administrator to link your children.</p>
    </div>
    @endforelse
</div>
@endsection
