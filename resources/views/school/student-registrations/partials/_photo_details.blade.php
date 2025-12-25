{{-- Photo Details --}}
<div class="mb-6">
    <div class="bg-teal-500 text-white px-4 py-3 rounded-t-lg font-semibold">
        Photo Details
    </div>
    <div class="bg-white dark:bg-gray-800 p-6 rounded-b-lg border border-gray-200 dark:border-gray-700">
        {{-- Hidden fields to store photo paths from enquiry --}}
        <input type="hidden" name="enquiry_father_photo" id="enquiry_father_photo" value="">
        <input type="hidden" name="enquiry_mother_photo" id="enquiry_mother_photo" value="">
        <input type="hidden" name="enquiry_student_photo" id="enquiry_student_photo" value="">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Father Photo
                </label>
                <div class="flex flex-col items-center">
                    <div class="w-32 h-32 bg-gray-200 dark:bg-gray-600 rounded-lg mb-2 flex items-center justify-center overflow-hidden relative">
                        @if(isset($studentRegistration) && $studentRegistration->father_photo)
                            <img id="father-photo-preview" src="{{ asset('storage/' . $studentRegistration->father_photo) }}" alt="Father Photo" class="w-full h-full object-cover">
                        @else
                            <img id="father-photo-preview" src="#" alt="Father Photo" class="hidden w-full h-full object-cover">
                            <i class="fas fa-user text-gray-400 text-4xl" id="father-photo-icon"></i>
                        @endif
                        <button type="button" 
                                id="father-photo-remove" 
                                onclick="removeImage(event, 'father_photo', 'father-photo-preview', 'father-photo-icon', 'father-photo-remove')"
                                class="hidden absolute top-1 right-1 bg-red-500 hover:bg-red-600 text-white rounded-full w-6 h-6 flex items-center justify-center transition-colors duration-200 shadow-lg">
                            <i class="fas fa-times text-xs"></i>
                        </button>
                    </div>
                    <input type="file" name="father_photo" accept="image/*" 
                           onchange="previewImage(event, 'father-photo-preview', 'father-photo-icon', 'father-photo-remove')"
                           class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-teal-50 file:text-teal-700 hover:file:bg-teal-100">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Mother Photo
                </label>
                <div class="flex flex-col items-center">
                    <div class="w-32 h-32 bg-gray-200 dark:bg-gray-600 rounded-lg mb-2 flex items-center justify-center overflow-hidden relative">
                        @if(isset($studentRegistration) && $studentRegistration->mother_photo)
                            <img id="mother-photo-preview" src="{{ asset('storage/' . $studentRegistration->mother_photo) }}" alt="Mother Photo" class="w-full h-full object-cover">
                        @else
                            <img id="mother-photo-preview" src="#" alt="Mother Photo" class="hidden w-full h-full object-cover">
                            <i class="fas fa-user text-gray-400 text-4xl" id="mother-photo-icon"></i>
                        @endif
                        <button type="button" 
                                id="mother-photo-remove" 
                                onclick="removeImage(event, 'mother_photo', 'mother-photo-preview', 'mother-photo-icon', 'mother-photo-remove')"
                                class="hidden absolute top-1 right-1 bg-red-500 hover:bg-red-600 text-white rounded-full w-6 h-6 flex items-center justify-center transition-colors duration-200 shadow-lg">
                            <i class="fas fa-times text-xs"></i>
                        </button>
                    </div>
                    <input type="file" name="mother_photo" accept="image/*" 
                           onchange="previewImage(event, 'mother-photo-preview', 'mother-photo-icon', 'mother-photo-remove')"
                           class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-teal-50 file:text-teal-700 hover:file:bg-teal-100">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Student Photo
                </label>
                <div class="flex flex-col items-center">
                    <div class="w-32 h-32 bg-gray-200 dark:bg-gray-600 rounded-lg mb-2 flex items-center justify-center overflow-hidden relative">
                        @if(isset($studentRegistration) && $studentRegistration->student_photo)
                            <img id="student-photo-preview" src="{{ asset('storage/' . $studentRegistration->student_photo) }}" alt="Student Photo" class="w-full h-full object-cover">
                        @else
                            <img id="student-photo-preview" src="#" alt="Student Photo" class="hidden w-full h-full object-cover">
                            <i class="fas fa-user text-gray-400 text-4xl" id="student-photo-icon"></i>
                        @endif
                        <button type="button" 
                                id="student-photo-remove" 
                                onclick="removeImage(event, 'student_photo', 'student-photo-preview', 'student-photo-icon', 'student-photo-remove')"
                                class="hidden absolute top-1 right-1 bg-red-500 hover:bg-red-600 text-white rounded-full w-6 h-6 flex items-center justify-center transition-colors duration-200 shadow-lg">
                            <i class="fas fa-times text-xs"></i>
                        </button>
                    </div>
                    <input type="file" name="student_photo" accept="image/*" 
                           onchange="previewImage(event, 'student-photo-preview', 'student-photo-icon', 'student-photo-remove')"
                           class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-teal-50 file:text-teal-700 hover:file:bg-teal-100">
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function previewImage(event, previewId, iconId, removeBtnId) {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById(previewId);
            const icon = document.getElementById(iconId);
            const removeBtn = document.getElementById(removeBtnId);
            
            preview.src = e.target.result;
            preview.classList.remove('hidden');
            if (icon) {
                icon.classList.add('hidden');
            }
            if (removeBtn) {
                removeBtn.classList.remove('hidden');
            }
            
            // Clear hidden enquiry field when new file is selected
            const inputName = event.target.name;
            const enquiryField = document.getElementById(`enquiry_${inputName}`);
            if (enquiryField) {
                enquiryField.value = '';
            }
            
            // Clear from sessionStorage when new file is selected
            sessionStorage.removeItem(`registration_${inputName}`);
        };
        reader.readAsDataURL(file);
    }
}

function removeImage(event, inputName, previewId, iconId, removeBtnId) {
    event.preventDefault();
    event.stopPropagation();
    
    const input = document.querySelector(`input[name="${inputName}"]`);
    const preview = document.getElementById(previewId);
    const icon = document.getElementById(iconId);
    const removeBtn = document.getElementById(removeBtnId);
    
    // Reset file input
    if (input) {
        input.value = '';
    }
    
    // Clear hidden enquiry field if it exists
    const enquiryField = document.getElementById(`enquiry_${inputName}`);
    if (enquiryField) {
        enquiryField.value = '';
    }
    
    // Clear from sessionStorage
    sessionStorage.removeItem(`registration_${inputName}`);
    
    // Hide preview and show icon
    if (preview) {
        preview.src = '#';
        preview.classList.add('hidden');
    }
    if (icon) {
        icon.classList.remove('hidden');
    }
    if (removeBtn) {
        removeBtn.classList.add('hidden');
    }
}
</script>
