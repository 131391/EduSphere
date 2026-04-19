{{-- Step 4: Photos --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-8">

    {{-- Student Photo --}}
    <div class="flex flex-col items-center">
        <div class="w-10 h-10 rounded-full bg-teal-100 dark:bg-teal-900/30 flex items-center justify-center mb-3">
            <i class="fas fa-user-graduate text-teal-600 text-sm"></i>
        </div>
        <p class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Student Photo</p>
        <div class="w-36 h-36 rounded-xl bg-gray-100 dark:bg-gray-700 border-2 border-dashed border-gray-300 dark:border-gray-500
                    flex items-center justify-center overflow-hidden relative mb-3 group">
            @isset($studentEnquiry)
                @if($studentEnquiry->student_photo)
                <img id="student-photo-preview" src="{{ asset('storage/' . $studentEnquiry->student_photo) }}"
                     alt="Student Photo" class="w-full h-full object-cover">
                <i class="fas fa-user text-gray-300 text-4xl hidden" id="student-photo-icon"></i>
                @else
                <img id="student-photo-preview" src="#" alt="Student Photo" class="hidden w-full h-full object-cover">
                <i class="fas fa-user text-gray-300 text-4xl" id="student-photo-icon"></i>
                @endif
            @else
            <img id="student-photo-preview" src="#" alt="Student Photo" class="hidden w-full h-full object-cover">
            <i class="fas fa-user text-gray-300 text-4xl" id="student-photo-icon"></i>
            @endisset
            <button type="button" id="student-photo-remove"
                    @click.prevent="removePhoto('student_photo','student-photo-preview','student-photo-icon','student-photo-remove')"
                    class="{{ isset($studentEnquiry) && $studentEnquiry->student_photo ? '' : 'hidden' }} absolute top-1 right-1 bg-red-500 hover:bg-red-600 text-white rounded-full w-6 h-6 flex items-center justify-center shadow-lg">
                <i class="fas fa-times text-xs"></i>
            </button>
        </div>
        <label class="cursor-pointer w-full">
            <input type="file" name="student_photo" accept="image/*"
                   @change="previewPhoto($event,'student-photo-preview','student-photo-icon','student-photo-remove'); clearError('student_photo')"
                   class="hidden">
            <span class="flex items-center justify-center gap-2 w-full px-4 py-2 text-sm font-semibold text-teal-700 bg-teal-50 hover:bg-teal-100 border border-teal-200 rounded-lg transition-colors cursor-pointer">
                <i class="fas fa-upload text-xs"></i> Choose Photo
            </span>
        </label>
        <p class="text-[10px] text-gray-400 mt-1.5">JPG, PNG · Max 2MB</p>
        <template x-if="errors.student_photo">
            <p class="text-red-500 text-xs mt-1" x-text="errors.student_photo[0]"></p>
        </template>
    </div>

    {{-- Father Photo --}}
    <div class="flex flex-col items-center">
        <div class="w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center mb-3">
            <i class="fas fa-user-tie text-blue-600 text-sm"></i>
        </div>
        <p class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Father's Photo</p>
        <div class="w-36 h-36 rounded-xl bg-gray-100 dark:bg-gray-700 border-2 border-dashed border-gray-300 dark:border-gray-500
                    flex items-center justify-center overflow-hidden relative mb-3">
            @isset($studentEnquiry)
                @if($studentEnquiry->father_photo)
                <img id="father-photo-preview" src="{{ asset('storage/' . $studentEnquiry->father_photo) }}"
                     alt="Father Photo" class="w-full h-full object-cover">
                <i class="fas fa-user text-gray-300 text-4xl hidden" id="father-photo-icon"></i>
                @else
                <img id="father-photo-preview" src="#" alt="Father Photo" class="hidden w-full h-full object-cover">
                <i class="fas fa-user text-gray-300 text-4xl" id="father-photo-icon"></i>
                @endif
            @else
            <img id="father-photo-preview" src="#" alt="Father Photo" class="hidden w-full h-full object-cover">
            <i class="fas fa-user text-gray-300 text-4xl" id="father-photo-icon"></i>
            @endisset
            <button type="button" id="father-photo-remove"
                    @click.prevent="removePhoto('father_photo','father-photo-preview','father-photo-icon','father-photo-remove')"
                    class="{{ isset($studentEnquiry) && $studentEnquiry->father_photo ? '' : 'hidden' }} absolute top-1 right-1 bg-red-500 hover:bg-red-600 text-white rounded-full w-6 h-6 flex items-center justify-center shadow-lg">
                <i class="fas fa-times text-xs"></i>
            </button>
        </div>
        <label class="cursor-pointer w-full">
            <input type="file" name="father_photo" accept="image/*"
                   @change="previewPhoto($event,'father-photo-preview','father-photo-icon','father-photo-remove'); clearError('father_photo')"
                   class="hidden">
            <span class="flex items-center justify-center gap-2 w-full px-4 py-2 text-sm font-semibold text-blue-700 bg-blue-50 hover:bg-blue-100 border border-blue-200 rounded-lg transition-colors cursor-pointer">
                <i class="fas fa-upload text-xs"></i> Choose Photo
            </span>
        </label>
        <p class="text-[10px] text-gray-400 mt-1.5">JPG, PNG · Max 2MB</p>
        <template x-if="errors.father_photo">
            <p class="text-red-500 text-xs mt-1" x-text="errors.father_photo[0]"></p>
        </template>
    </div>

    {{-- Mother Photo --}}
    <div class="flex flex-col items-center">
        <div class="w-10 h-10 rounded-full bg-pink-100 dark:bg-pink-900/30 flex items-center justify-center mb-3">
            <i class="fas fa-user text-pink-600 text-sm"></i>
        </div>
        <p class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Mother's Photo</p>
        <div class="w-36 h-36 rounded-xl bg-gray-100 dark:bg-gray-700 border-2 border-dashed border-gray-300 dark:border-gray-500
                    flex items-center justify-center overflow-hidden relative mb-3">
            @isset($studentEnquiry)
                @if($studentEnquiry->mother_photo)
                <img id="mother-photo-preview" src="{{ asset('storage/' . $studentEnquiry->mother_photo) }}"
                     alt="Mother Photo" class="w-full h-full object-cover">
                <i class="fas fa-user text-gray-300 text-4xl hidden" id="mother-photo-icon"></i>
                @else
                <img id="mother-photo-preview" src="#" alt="Mother Photo" class="hidden w-full h-full object-cover">
                <i class="fas fa-user text-gray-300 text-4xl" id="mother-photo-icon"></i>
                @endif
            @else
            <img id="mother-photo-preview" src="#" alt="Mother Photo" class="hidden w-full h-full object-cover">
            <i class="fas fa-user text-gray-300 text-4xl" id="mother-photo-icon"></i>
            @endisset
            <button type="button" id="mother-photo-remove"
                    @click.prevent="removePhoto('mother_photo','mother-photo-preview','mother-photo-icon','mother-photo-remove')"
                    class="{{ isset($studentEnquiry) && $studentEnquiry->mother_photo ? '' : 'hidden' }} absolute top-1 right-1 bg-red-500 hover:bg-red-600 text-white rounded-full w-6 h-6 flex items-center justify-center shadow-lg">
                <i class="fas fa-times text-xs"></i>
            </button>
        </div>
        <label class="cursor-pointer w-full">
            <input type="file" name="mother_photo" accept="image/*"
                   @change="previewPhoto($event,'mother-photo-preview','mother-photo-icon','mother-photo-remove'); clearError('mother_photo')"
                   class="hidden">
            <span class="flex items-center justify-center gap-2 w-full px-4 py-2 text-sm font-semibold text-pink-700 bg-pink-50 hover:bg-pink-100 border border-pink-200 rounded-lg transition-colors cursor-pointer">
                <i class="fas fa-upload text-xs"></i> Choose Photo
            </span>
        </label>
        <p class="text-[10px] text-gray-400 mt-1.5">JPG, PNG · Max 2MB</p>
        <template x-if="errors.mother_photo">
            <p class="text-red-500 text-xs mt-1" x-text="errors.mother_photo[0]"></p>
        </template>
    </div>

</div>
