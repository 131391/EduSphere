{{-- Signature Details --}}
<div class="mb-6">
    <div class="bg-teal-500 text-white px-4 py-3 rounded-t-lg font-semibold">
        Signature Details
    </div>
    <div class="bg-white dark:bg-gray-800 p-6 rounded-b-lg border border-gray-200 dark:border-gray-700">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Father's Signature
                </label>
                <div class="flex flex-col items-center">
                    <div class="w-32 h-32 bg-gray-200 dark:bg-gray-600 rounded-lg mb-2 flex items-center justify-center overflow-hidden relative">
                        <img id="father-signature-preview" src="#" alt="Father's Signature" class="hidden w-full h-full object-cover">
                        <i class="fas fa-pen text-gray-400 text-4xl" id="father-signature-icon"></i>
                        <button type="button" 
                                id="father-signature-remove" 
                                onclick="removeImage(event, 'father_signature', 'father-signature-preview', 'father-signature-icon', 'father-signature-remove')"
                                class="hidden absolute top-1 right-1 bg-red-500 hover:bg-red-600 text-white rounded-full w-6 h-6 flex items-center justify-center transition-colors duration-200 shadow-lg">
                            <i class="fas fa-times text-xs"></i>
                        </button>
                    </div>
                    <input type="file" name="father_signature" accept="image/*" 
                           onchange="previewImage(event, 'father-signature-preview', 'father-signature-icon', 'father-signature-remove')"
                           class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-teal-50 file:text-teal-700 hover:file:bg-teal-100">
                    <input type="hidden" name="father_signature_path" id="father_signature_path" value="">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Mother's Signature
                </label>
                <div class="flex flex-col items-center">
                    <div class="w-32 h-32 bg-gray-200 dark:bg-gray-600 rounded-lg mb-2 flex items-center justify-center overflow-hidden relative">
                        <img id="mother-signature-preview" src="#" alt="Mother's Signature" class="hidden w-full h-full object-cover">
                        <i class="fas fa-pen text-gray-400 text-4xl" id="mother-signature-icon"></i>
                        <button type="button" 
                                id="mother-signature-remove" 
                                onclick="removeImage(event, 'mother_signature', 'mother-signature-preview', 'mother-signature-icon', 'mother-signature-remove')"
                                class="hidden absolute top-1 right-1 bg-red-500 hover:bg-red-600 text-white rounded-full w-6 h-6 flex items-center justify-center transition-colors duration-200 shadow-lg">
                            <i class="fas fa-times text-xs"></i>
                        </button>
                    </div>
                    <input type="file" name="mother_signature" accept="image/*" 
                           onchange="previewImage(event, 'mother-signature-preview', 'mother-signature-icon', 'mother-signature-remove')"
                           class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-teal-50 file:text-teal-700 hover:file:bg-teal-100">
                    <input type="hidden" name="mother_signature_path" id="mother_signature_path" value="">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Student's Signature
                </label>
                <div class="flex flex-col items-center">
                    <div class="w-32 h-32 bg-gray-200 dark:bg-gray-600 rounded-lg mb-2 flex items-center justify-center overflow-hidden relative">
                        <img id="student-signature-preview" src="#" alt="Student's Signature" class="hidden w-full h-full object-cover">
                        <i class="fas fa-pen text-gray-400 text-4xl" id="student-signature-icon"></i>
                        <button type="button" 
                                id="student-signature-remove" 
                                onclick="removeImage(event, 'student_signature', 'student-signature-preview', 'student-signature-icon', 'student-signature-remove')"
                                class="hidden absolute top-1 right-1 bg-red-500 hover:bg-red-600 text-white rounded-full w-6 h-6 flex items-center justify-center transition-colors duration-200 shadow-lg">
                            <i class="fas fa-times text-xs"></i>
                        </button>
                    </div>
                    <input type="file" name="student_signature" accept="image/*" 
                           onchange="previewImage(event, 'student-signature-preview', 'student-signature-icon', 'student-signature-remove')"
                           class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-teal-50 file:text-teal-700 hover:file:bg-teal-100">
                    <input type="hidden" name="student_signature_path" id="student_signature_path" value="">
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function previewImage(event, previewId, iconId, removeBtnId) {
    const file = event.target.files[0];
    const inputName = event.target.name; // e.g., 'father_signature'
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
            
            // Clear hidden path field when a new file is selected
            $(`#${inputName}_path`).val('');
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
    
    // Clear hidden path field
    $(`#${inputName}_path`).val('');
    
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
