@extends('layouts.teacher')

@section('title', 'Mark Attendance')
@section('page-title', 'Mark Attendance')

@section('content')
<div class="space-y-6">

    <!-- Filters -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
        <form method="GET" action="{{ route('teacher.attendance.index') }}" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[140px]">
                <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1 uppercase tracking-wide">Date</label>
                <input type="date" name="date" value="{{ $date }}" max="{{ now()->toDateString() }}"
                       class="w-full px-3 py-2 border border-gray-200 dark:border-gray-600 rounded-lg text-sm bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
            </div>
            <div class="flex-1 min-w-[160px]">
                <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1 uppercase tracking-wide">Class</label>
                <select name="class_id" class="w-full px-3 py-2 border border-gray-200 dark:border-gray-600 rounded-lg text-sm bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-indigo-500">
                    <option value="">Select Class</option>
                    @foreach($classes as $class)
                    <option value="{{ $class->id }}" {{ $classId == $class->id ? 'selected' : '' }}>
                        {{ $class->name }}
                    </option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors">
                <i class="fas fa-search mr-1.5"></i>Load Students
            </button>
        </form>
    </div>

    @if($students->isEmpty() && $classId)
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-12 text-center">
        <i class="fas fa-users text-4xl text-gray-300 dark:text-gray-600 mb-3"></i>
        <p class="text-gray-500 dark:text-gray-400">No students found for the selected class.</p>
    </div>
    @elseif($students->isNotEmpty())

    <!-- Attendance Form -->
    <form method="POST" action="{{ route('teacher.attendance.store') }}">
        @csrf
        <input type="hidden" name="date" value="{{ $date }}">
        <input type="hidden" name="class_id" value="{{ $classId }}">

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-semibold text-gray-800 dark:text-white">
                        Attendance for {{ \Carbon\Carbon::parse($date)->format('D, d M Y') }}
                    </h3>
                    <p class="text-xs text-gray-400 mt-0.5">{{ $students->count() }} students</p>
                </div>
                <!-- Quick Select Buttons -->
                <div class="flex gap-2">
                    <button type="button" onclick="setAll(1)"
                            class="px-3 py-1.5 text-xs font-medium bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 rounded-lg hover:bg-green-200 dark:hover:bg-green-900/50 transition-colors">
                        All Present
                    </button>
                    <button type="button" onclick="setAll(2)"
                            class="px-3 py-1.5 text-xs font-medium bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400 rounded-lg hover:bg-red-200 dark:hover:bg-red-900/50 transition-colors">
                        All Absent
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-700/50">
                        <tr>
                            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-8">#</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Student</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Roll No.</th>
                            <th class="text-center px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @foreach($students as $i => $student)
                        @php $currentStatus = $existingAttendance->get($student->id)?->value ?? null; @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                            <td class="px-6 py-3 text-gray-400 dark:text-gray-500 text-xs">{{ $i + 1 }}</td>
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-800 dark:text-gray-200">{{ $student->full_name }}</div>
                                <div class="text-xs text-gray-400">{{ $student->admission_no }}</div>
                            </td>
                            <td class="px-4 py-3 text-gray-500 dark:text-gray-400 text-xs">{{ $student->roll_no ?? '—' }}</td>
                            <td class="px-4 py-3">
                                <div class="flex justify-center gap-2 flex-wrap">
                                    @foreach($statuses as $status)
                                    @php
                                        $colors = [1=>'green',2=>'red',3=>'yellow',4=>'blue',5=>'orange'];
                                        $c = $colors[$status->value] ?? 'gray';
                                    @endphp
                                    <label class="inline-flex items-center gap-1.5 cursor-pointer">
                                        <input type="radio"
                                               name="attendance[{{ $student->id }}]"
                                               value="{{ $status->value }}"
                                               class="sr-only peer"
                                               {{ $currentStatus === $status->value ? 'checked' : ($currentStatus === null && $status->value === 1 ? 'checked' : '') }}>
                                        <span class="px-2.5 py-1 rounded-lg text-xs font-medium border-2 transition-all
                                            border-gray-200 dark:border-gray-600 text-gray-500 dark:text-gray-400
                                            peer-checked:border-{{ $c }}-500 peer-checked:bg-{{ $c }}-100 dark:peer-checked:bg-{{ $c }}-900/30 peer-checked:text-{{ $c }}-700 dark:peer-checked:text-{{ $c }}-300
                                            hover:border-{{ $c }}-300 hover:text-{{ $c }}-600">
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

            <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 flex justify-end">
                <button type="submit"
                        class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg transition-colors shadow-sm">
                    <i class="fas fa-save mr-2"></i>Save Attendance
                </button>
            </div>
        </div>
    </form>
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
