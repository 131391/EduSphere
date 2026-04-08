@extends('layouts.school')

@section('title', 'Mark Entry Grid')

@section('content')
<div class="mb-6 flex items-center justify-between no-print">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Mark Entry Grid</h1>
        <p class="text-gray-600">
            Exam: <span class="font-bold">{{ $exam->examType->name }}</span> | 
            Subject: <span class="font-bold text-blue-600">{{ $subject->name }}</span> | 
            Class: <span class="font-bold">{{ $class->name }}</span>
        </p>
    </div>
    <a href="{{ route('school.examination.marks.index') }}" class="text-gray-600 hover:text-gray-900 flex items-center">
        <i class="fas fa-arrow-left mr-2"></i> Back
    </a>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <form action="{{ route('school.examination.marks.store') }}" method="POST">
        @csrf
        <input type="hidden" name="exam_id" value="{{ $exam->id }}">
        <input type="hidden" name="subject_id" value="{{ $subject->id }}">
        <input type="hidden" name="class_id" value="{{ $class->id }}">
        <input type="hidden" name="academic_year_id" value="{{ $exam->academic_year_id }}">

        <div class="p-6 bg-gray-50 border-b flex items-center justify-between">
            <div>
                <label for="total_marks" class="block text-xs font-bold text-gray-500 uppercase">Max Marks</label>
                <input type="number" name="total_marks" id="total_marks" required value="100" class="mt-1 border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-lg font-bold w-24">
            </div>
            <div class="text-right">
                <span class="text-xs font-bold text-gray-500 uppercase block">Students</span>
                <span class="text-2xl font-black text-blue-600">{{ $students->count() }}</span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase w-20">SR</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase w-40">Adm No</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase">Student Name</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase w-48">Obtained Marks</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase">Remarks</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($students as $index => $student)
                    @php $result = $results->get($student->id); @endphp
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $index + 1 }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">{{ $student->admission_no }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $student->full_name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <input type="hidden" name="marks[{{ $index }}][student_id]" value="{{ $student->id }}">
                            <input type="number" name="marks[{{ $index }}][marks_obtained]" 
                                   step="0.01" min="0" 
                                   class="mark-input border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-24 sm:text-sm font-bold" 
                                   value="{{ $result ? $result->marks_obtained : '' }}" required>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <input type="text" name="marks[{{ $index }}][remarks]" 
                                   class="border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm" 
                                   value="{{ $result ? $result->remarks : '' }}" placeholder="Absent, etc.">
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="p-6 bg-gray-50 border-t flex justify-end">
            <button type="submit" class="bg-blue-600 text-white px-10 py-3 rounded-lg font-bold hover:bg-blue-700 shadow-lg transform active:scale-95 transition duration-150">
                <i class="fas fa-save mr-2"></i> Save Marks
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const totalMarksInput = document.getElementById('total_marks');
    const markInputs = document.querySelectorAll('.mark-input');

    function validate() {
        const total = parseFloat(totalMarksInput.value || 0);
        markInputs.forEach(input => {
            input.max = total;
            if (parseFloat(input.value) > total) {
                input.classList.add('border-red-500');
            } else {
                input.classList.remove('border-red-500');
            }
        });
    }

    totalMarksInput.addEventListener('input', validate);
    markInputs.forEach(input => input.addEventListener('input', validate));
    validate();
});
</script>
@endsection
