{{-- Signature Details --}}
<div class="mb-6">
    <div class="bg-teal-500 text-white px-4 py-3 rounded-t-lg font-semibold">
        Signature Details
    </div>
    <div class="bg-white dark:bg-gray-800 p-6 rounded-b-lg border border-gray-200 dark:border-gray-700">
        {{-- Hidden fields to store signature paths from enquiry (Managed by Alpine formData) --}}
        <input type="hidden" name="enquiry_father_signature" x-model="formData.enquiry_father_signature">
        <input type="hidden" name="enquiry_mother_signature" x-model="formData.enquiry_mother_signature">
        <input type="hidden" name="enquiry_student_signature" x-model="formData.enquiry_student_signature">

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Father Signature
                </label>
                <div class="flex flex-col items-center">
                    <div class="w-32 h-32 bg-gray-200 dark:bg-gray-600 rounded-lg mb-2 flex items-center justify-center overflow-hidden relative border border-dashed border-gray-300 dark:border-gray-500">
                        <img id="father-signature-preview" src="#" alt="Father Signature" 
                             class="w-full h-full object-cover" 
                             x-show="previews.father_signature"
                             :src="previews.father_signature">
                        
                        <i class="fas fa-pen text-gray-400 text-4xl" 
                           x-show="!previews.father_signature"></i>

                        <button type="button" 
                                x-show="previews.father_signature"
                                @click="removePhoto('father_signature')"
                                class="absolute top-1 right-1 bg-red-500 hover:bg-red-600 text-white rounded-full w-6 h-6 flex items-center justify-center transition-colors duration-200 shadow-lg group">
                            <i class="fas fa-times text-xs group-hover:scale-110 transition-transform"></i>
                        </button>
                    </div>
                    <label class="block w-full">
                        <input type="file" name="father_signature" accept="image/*" 
                               @change="handlePhotoUpload($event, 'father_signature')"
                               class="block w-full text-[10px] text-gray-500 file:mr-4 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-[10px] file:font-semibold file:bg-teal-50 file:text-teal-700 hover:file:bg-teal-100 transition-all cursor-pointer">
                    </label>
                    <template x-if="errors.father_signature">
                        <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.father_signature[0]"></p>
                    </template>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Mother Signature
                </label>
                <div class="flex flex-col items-center">
                    <div class="w-32 h-32 bg-gray-200 dark:bg-gray-600 rounded-lg mb-2 flex items-center justify-center overflow-hidden relative border border-dashed border-gray-300 dark:border-gray-500">
                        <img id="mother-signature-preview" src="#" alt="Mother Signature" 
                             class="w-full h-full object-cover" 
                             x-show="previews.mother_signature"
                             :src="previews.mother_signature">
                        
                        <i class="fas fa-pen text-gray-400 text-4xl" 
                           x-show="!previews.mother_signature"></i>

                        <button type="button" 
                                x-show="previews.mother_signature"
                                @click="removePhoto('mother_signature')"
                                class="absolute top-1 right-1 bg-red-500 hover:bg-red-600 text-white rounded-full w-6 h-6 flex items-center justify-center transition-colors duration-200 shadow-lg group">
                            <i class="fas fa-times text-xs group-hover:scale-110 transition-transform"></i>
                        </button>
                    </div>
                    <label class="block w-full">
                        <input type="file" name="mother_signature" accept="image/*" 
                               @change="handlePhotoUpload($event, 'mother_signature')"
                               class="block w-full text-[10px] text-gray-500 file:mr-4 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-[10px] file:font-semibold file:bg-teal-50 file:text-teal-700 hover:file:bg-teal-100 transition-all cursor-pointer">
                    </label>
                    <template x-if="errors.mother_signature">
                        <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.mother_signature[0]"></p>
                    </template>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Student Signature
                </label>
                <div class="flex flex-col items-center">
                    <div class="w-32 h-32 bg-gray-200 dark:bg-gray-600 rounded-lg mb-2 flex items-center justify-center overflow-hidden relative border border-dashed border-gray-300 dark:border-gray-500">
                        <img id="student-signature-preview" src="#" alt="Student Signature" 
                             class="w-full h-full object-cover" 
                             x-show="previews.student_signature"
                             :src="previews.student_signature">
                        
                        <i class="fas fa-pen text-gray-400 text-4xl" 
                           x-show="!previews.student_signature"></i>

                        <button type="button" 
                                x-show="previews.student_signature"
                                @click="removePhoto('student_signature')"
                                class="absolute top-1 right-1 bg-red-500 hover:bg-red-600 text-white rounded-full w-6 h-6 flex items-center justify-center transition-colors duration-200 shadow-lg group">
                            <i class="fas fa-times text-xs group-hover:scale-110 transition-transform"></i>
                        </button>
                    </div>
                    <label class="block w-full">
                        <input type="file" name="student_signature" accept="image/*" 
                               @change="handlePhotoUpload($event, 'student_signature')"
                               class="block w-full text-[10px] text-gray-500 file:mr-4 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-[10px] file:font-semibold file:bg-teal-50 file:text-teal-700 hover:file:bg-teal-100 transition-all cursor-pointer">
                    </label>
                    <template x-if="errors.student_signature">
                        <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.student_signature[0]"></p>
                    </template>
                </div>
            </div>
        </div>
    </div>
</div>
