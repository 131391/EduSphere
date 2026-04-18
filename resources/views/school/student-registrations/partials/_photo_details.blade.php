{{-- Photo Details --}}
<div class="mb-6">
    <div class="bg-teal-500 text-white px-4 py-3 rounded-t-lg font-semibold">
        Photo Details
    </div>
    <div class="bg-white dark:bg-gray-800 p-6 rounded-b-lg border border-gray-200 dark:border-gray-700">
        {{-- Hidden fields to store photo paths from enquiry (Managed by Alpine formData) --}}
        <input type="hidden" name="enquiry_father_photo" x-model="formData.enquiry_father_photo">
        <input type="hidden" name="enquiry_mother_photo" x-model="formData.enquiry_mother_photo">
        <input type="hidden" name="enquiry_student_photo" x-model="formData.enquiry_student_photo">

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Father Photo
                </label>
                <div class="flex flex-col items-center">
                    <div class="w-32 h-32 bg-gray-200 dark:bg-gray-600 rounded-lg mb-2 flex items-center justify-center overflow-hidden relative border border-dashed border-gray-300 dark:border-gray-500">
                        <img id="father-photo-preview" src="#" alt="Father Photo" 
                             class="w-full h-full object-cover" 
                             x-show="previews.father_photo"
                             :src="previews.father_photo">
                        
                        <i class="fas fa-user text-gray-400 text-4xl" 
                           x-show="!previews.father_photo"></i>

                        <button type="button" 
                                x-show="previews.father_photo"
                                @click="removePhoto('father_photo')"
                                class="absolute top-1 right-1 bg-red-500 hover:bg-red-600 text-white rounded-full w-6 h-6 flex items-center justify-center transition-colors duration-200 shadow-lg group">
                            <i class="fas fa-times text-xs group-hover:scale-110 transition-transform"></i>
                        </button>
                    </div>
                    <label class="block w-full">
                        <input type="file" name="father_photo" accept="image/*" 
                               @change="handlePhotoUpload($event, 'father_photo')"
                               class="block w-full text-[10px] text-gray-500 file:mr-4 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-[10px] file:font-semibold file:bg-teal-50 file:text-teal-700 hover:file:bg-teal-100 transition-all cursor-pointer">
                    </label>
                    <template x-if="errors.father_photo">
                        <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.father_photo[0]"></p>
                    </template>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Mother Photo
                </label>
                <div class="flex flex-col items-center">
                    <div class="w-32 h-32 bg-gray-200 dark:bg-gray-600 rounded-lg mb-2 flex items-center justify-center overflow-hidden relative border border-dashed border-gray-300 dark:border-gray-500">
                        <img id="mother-photo-preview" src="#" alt="Mother Photo" 
                             class="w-full h-full object-cover" 
                             x-show="previews.mother_photo"
                             :src="previews.mother_photo">
                        
                        <i class="fas fa-user text-gray-400 text-4xl" 
                           x-show="!previews.mother_photo"></i>

                        <button type="button" 
                                x-show="previews.mother_photo"
                                @click="removePhoto('mother_photo')"
                                class="absolute top-1 right-1 bg-red-500 hover:bg-red-600 text-white rounded-full w-6 h-6 flex items-center justify-center transition-colors duration-200 shadow-lg group">
                            <i class="fas fa-times text-xs group-hover:scale-110 transition-transform"></i>
                        </button>
                    </div>
                    <label class="block w-full">
                        <input type="file" name="mother_photo" accept="image/*" 
                               @change="handlePhotoUpload($event, 'mother_photo')"
                               class="block w-full text-[10px] text-gray-500 file:mr-4 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-[10px] file:font-semibold file:bg-teal-50 file:text-teal-700 hover:file:bg-teal-100 transition-all cursor-pointer">
                    </label>
                    <template x-if="errors.mother_photo">
                        <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.mother_photo[0]"></p>
                    </template>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Student Photo
                </label>
                <div class="flex flex-col items-center">
                    <div class="w-32 h-32 bg-gray-200 dark:bg-gray-600 rounded-lg mb-2 flex items-center justify-center overflow-hidden relative border border-dashed border-gray-300 dark:border-gray-500">
                        <img id="student-photo-preview" src="#" alt="Student Photo" 
                             class="w-full h-full object-cover" 
                             x-show="previews.student_photo"
                             :src="previews.student_photo">
                        
                        <i class="fas fa-user text-gray-400 text-4xl" 
                           x-show="!previews.student_photo"></i>

                        <button type="button" 
                                x-show="previews.student_photo"
                                @click="removePhoto('student_photo')"
                                class="absolute top-1 right-1 bg-red-500 hover:bg-red-600 text-white rounded-full w-6 h-6 flex items-center justify-center transition-colors duration-200 shadow-lg group">
                            <i class="fas fa-times text-xs group-hover:scale-110 transition-transform"></i>
                        </button>
                    </div>
                    <label class="block w-full">
                        <input type="file" name="student_photo" accept="image/*" 
                               @change="handlePhotoUpload($event, 'student_photo')"
                               class="block w-full text-[10px] text-gray-500 file:mr-4 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-[10px] file:font-semibold file:bg-teal-50 file:text-teal-700 hover:file:bg-teal-100 transition-all cursor-pointer">
                    </label>
                    <template x-if="errors.student_photo">
                        <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.student_photo[0]"></p>
                    </template>
                </div>
            </div>
        </div>
    </div>
</div>
