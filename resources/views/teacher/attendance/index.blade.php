@extends('layouts.teacher')

@section('title', 'Attendance Registry')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-5 border border-indigo-100/50">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-gray-800 dark:text-white flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-indigo-100 flex items-center justify-center text-indigo-600">
                        <i class="fas fa-calendar-check text-xs"></i>
                    </div>
                    Attendance Registry
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Marking and monitoring student daily attendance records.</p>
            </div>
            
            <div class="flex items-center gap-3">
                <form method="GET" action="{{ route('teacher.attendance.index') }}" class="flex items-center gap-3 bg-gray-50 dark:bg-gray-700/50 p-1.5 rounded-xl border border-gray-200 dark:border-gray-600 shadow-inner">
                    <div class="relative">
                        <input type="date" name="date" value="{{ $date }}" max="{{ now()->toDateString() }}" onchange="this.form.submit()"
                               class="h-9 pl-9 pr-3 bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-600 rounded-lg text-xs font-bold text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-indigo-500 transition-all outline-none shadow-sm">
                        <div class="absolute left-3 top-1/2 -translate-y-1/2 text-indigo-500">
                            <i class="fas fa-calendar-alt text-[10px]"></i>
                        </div>
                    </div>
                    <div class="relative">
                        <select name="class_id" onchange="this.form.submit()"
                                class="h-9 pl-9 pr-10 bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-600 rounded-lg text-xs font-bold text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-indigo-500 appearance-none outline-none shadow-sm cursor-pointer">
                            <option value="">Select Class</option>
                            @foreach($classes as $class)
                            <option value="{{ $class->id }}" {{ $classId == $class->id ? 'selected' : '' }}>
                                {{ $class->name }}
                            </option>
                            @endforeach
                        </select>
                        <div class="absolute left-3 top-1/2 -translate-y-1/2 text-indigo-500">
                            <i class="fas fa-chalkboard text-[10px]"></i>
                        </div>
                        <div class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">
                            <i class="fas fa-chevron-down text-[8px]"></i>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Stats Section -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-indigo-50 flex items-center gap-4 group hover:shadow-md transition-all duration-300">
            <div class="w-12 h-12 bg-indigo-50 text-indigo-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform shadow-sm">
                <i class="fas fa-users text-xl"></i>
            </div>
            <div>
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest leading-tight">Total Students</p>
                <p class="text-xl font-black text-gray-800">{{ $stats['total'] }}</p>
            </div>
        </div>
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-indigo-50 flex items-center gap-4 group hover:shadow-md transition-all duration-300">
            <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform shadow-sm">
                <i class="fas fa-check-circle text-xl"></i>
            </div>
            <div>
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest leading-tight">Marked Present</p>
                <p class="text-xl font-black text-emerald-600">{{ $stats['present'] }}</p>
            </div>
        </div>
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-indigo-50 flex items-center gap-4 group hover:shadow-md transition-all duration-300">
            <div class="w-12 h-12 bg-rose-50 text-rose-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform shadow-sm">
                <i class="fas fa-times-circle text-xl"></i>
            </div>
            <div>
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest leading-tight">Reported Absent</p>
                <p class="text-xl font-black text-rose-600">{{ $stats['absent'] }}</p>
            </div>
        </div>
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-indigo-50 flex items-center gap-4 group hover:shadow-md transition-all duration-300">
            <div class="w-12 h-12 bg-amber-50 text-amber-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform shadow-sm">
                <i class="fas fa-hourglass-half text-xl"></i>
            </div>
            <div>
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest leading-tight">Unmarked Count</p>
                <p class="text-xl font-black text-amber-600">{{ $stats['unmarked'] }}</p>
            </div>
        </div>
    </div>

    @if($students->isNotEmpty())
    <!-- Attendance Marking Form -->
    <form method="POST" action="{{ route('teacher.attendance.store') }}" class="space-y-6">
        @csrf
        <input type="hidden" name="date" value="{{ $date }}">
        <input type="hidden" name="class_id" value="{{ $classId }}">

        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden shadow-indigo-100/20 shadow-lg">
            <div class="px-6 py-4 bg-gray-50/50 dark:bg-gray-700/30 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-black text-gray-800 dark:text-white uppercase tracking-widest flex items-center gap-2">
                        <i class="fas fa-list-ul text-indigo-500"></i>
                        Student Roll Call
                    </h3>
                </div>
                <!-- Quick Operations -->
                <div class="flex gap-2">
                    <button type="button" onclick="setAll(1)"
                            class="px-3 py-1.5 text-[10px] font-black uppercase tracking-widest bg-emerald-50 text-emerald-600 rounded-xl hover:bg-emerald-600 hover:text-white border border-emerald-100/50 transition-all duration-300 shadow-sm">
                        <i class="fas fa-check mr-1.5"></i> All Present
                    </button>
                    <button type="button" onclick="setAll(2)"
                            class="px-3 py-1.5 text-[10px] font-black uppercase tracking-widest bg-rose-50 text-rose-600 rounded-xl hover:bg-rose-600 hover:text-white border border-rose-100/50 transition-all duration-300 shadow-sm">
                        <i class="fas fa-times mr-1.5"></i> All Absent
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto text-sm">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-50/30 dark:bg-gray-700/10">
                            <th class="text-left px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">ID</th>
                            <th class="text-left px-4 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Student Information</th>
                            <th class="text-center px-4 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Roll</th>
                            <th class="text-center px-4 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Status Recording</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @foreach($students as $i => $student)
                        @php $currentStatus = $existingAttendance->get($student->id)?->value ?? null; @endphp
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/20 transition-colors group">
                            <td class="px-6 py-4">
                                <span class="text-xs font-black text-gray-300 group-hover:text-indigo-400 transition-colors">#{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</span>
                            </td>
                            <td class="px-4 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-gray-100 flex items-center justify-center text-gray-500 font-bold border border-gray-200 uppercase text-xs">
                                        {{ substr($student->first_name, 0, 1) }}{{ substr($student->last_name, 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="text-sm font-bold text-gray-800 dark:text-gray-200 group-hover:text-indigo-600 transition-colors uppercase tracking-tight">{{ $student->full_name }}</div>
                                        <div class="text-[10px] font-bold text-gray-400">Adm: {{ $student->admission_no }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-4 text-center">
                                <span class="text-xs font-black text-gray-500 bg-gray-100 px-2 py-0.5 rounded shadow-sm">{{ $student->roll_no ?? '—' }}</span>
                            </td>
                            <td class="px-4 py-4">
                                <div class="flex justify-center gap-2 flex-wrap">
                                    @foreach($statuses as $status)
                                    @php
                                        $colors = [
                                            1 => ['bg' => 'emerald', 'icon' => 'fa-check'],
                                            2 => ['bg' => 'rose', 'icon' => 'fa-times'],
                                            3 => ['bg' => 'amber', 'icon' => 'fa-clock'],
                                            4 => ['bg' => 'blue', 'icon' => 'fa-hospital'],
                                            5 => ['bg' => 'orange', 'icon' => 'fa-exclamation-triangle'],
                                        ];
                                        $c = $colors[$status->value] ?? ['bg' => 'gray', 'icon' => 'fa-info-circle'];
                                    @endphp
                                    <label class="inline-flex items-center cursor-pointer group/label">
                                        <input type="radio"
                                               name="attendance[{{ $student->id }}]"
                                               value="{{ $status->value }}"
                                               class="sr-only peer"
                                               {{ $currentStatus === $status->value ? 'checked' : ($currentStatus === null && $status->value === 1 ? 'checked' : '') }}>
                                        <span class="px-3 py-1.5 rounded-xl text-[10px] font-black uppercase tracking-widest border-2 transition-all duration-300
                                            border-gray-100 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-400 group-hover/label:border-{{ $c['bg'] }}-200
                                            peer-checked:border-{{ $c['bg'] }}-500 peer-checked:bg-{{ $c['bg'] }}-50 peer-checked:text-{{ $c['bg'] }}-700 shadow-sm
                                            flex items-center gap-1.5">
                                            <i class="fas {{ $c['icon'] }} text-[8px] opacity-0 peer-checked:opacity-100 transition-opacity"></i>
                                            {{ $status->label() }}
                                        </span>
                                    </label>
                                    @endforeach
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-5 bg-gray-50/50 dark:bg-gray-700/30 border-t border-gray-100 dark:border-gray-700 flex justify-end">
                <button type="submit"
                        class="px-8 h-12 bg-gray-800 dark:bg-gray-700 hover:bg-black dark:hover:bg-gray-600 text-white font-black text-xs uppercase tracking-widest rounded-2xl transition-all duration-300 shadow-xl flex items-center gap-2 ring-4 ring-gray-100 dark:ring-gray-800">
                    <i class="fas fa-save opacity-50"></i>
                    Save Registry Records
                </button>
            </div>
        </div>
    </form>
    @else
    <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 p-20 text-center">
        <div class="w-20 h-20 bg-gray-50 dark:bg-gray-700 rounded-3xl flex items-center justify-center mx-auto mb-6">
            <i class="fas fa-users text-4xl text-gray-300 dark:text-gray-600"></i>
        </div>
        <h3 class="text-xl font-bold text-gray-800 dark:text-white">Awaiting Student List</h3>
        <p class="text-gray-500 dark:text-gray-400 mt-2 max-w-sm mx-auto">Select a class from the registry filters above to load the student list and record attendance.</p>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
function setAll(value) {
    document.querySelectorAll('input[type="radio"]').forEach(radio => {
        if (radio.value == value) radio.checked = true;
    });
}
</script>
@endpush

