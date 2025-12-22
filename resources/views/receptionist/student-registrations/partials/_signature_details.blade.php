{{-- Signature Details --}}
<div class="mb-6">
    <div class="bg-teal-500 text-white px-4 py-3 rounded-t-lg font-semibold">
        Signature Details
    </div>
    <div class="bg-white dark:bg-gray-800 p-6 rounded-b-lg border border-gray-200 dark:border-gray-700">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Father Signature
                </label>
                <input type="file" name="father_signature" accept="image/*"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                @if(isset($studentRegistration) && $studentRegistration->father_signature)
                    <div class="mt-2">
                        <img src="{{ asset('storage/' . $studentRegistration->father_signature) }}" alt="Father Signature" class="w-20 h-20 object-cover rounded">
                    </div>
                @endif
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Mother Signature
                </label>
                <input type="file" name="mother_signature" accept="image/*"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                @if(isset($studentRegistration) && $studentRegistration->mother_signature)
                    <div class="mt-2">
                        <img src="{{ asset('storage/' . $studentRegistration->mother_signature) }}" alt="Mother Signature" class="w-20 h-20 object-cover rounded">
                    </div>
                @endif
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Student Signature
                </label>
                <input type="file" name="student_signature" accept="image/*"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                @if(isset($studentRegistration) && $studentRegistration->student_signature)
                    <div class="mt-2">
                        <img src="{{ asset('storage/' . $studentRegistration->student_signature) }}" alt="Student Signature" class="w-20 h-20 object-cover rounded">
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
