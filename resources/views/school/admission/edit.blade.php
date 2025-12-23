@extends('layouts.school')

@section('title', 'Edit Admission')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-800">Edit Admission</h1>
        <a href="{{ route('school.admission.index') }}" class="text-blue-600 hover:text-blue-800 flex items-center gap-2">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <strong class="font-bold">Success!</strong>
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <strong class="font-bold">Error!</strong>
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    <form action="{{ route('school.admission.update', $student->id) }}" method="POST" enctype="multipart/form-data" class="space-y-8">
        @csrf
        @method('PUT')
        
        <!-- Admission Info -->
        @include('school.admission.partials._admission_info')

        <!-- Personal Info -->
        @include('school.admission.partials._personal_info')

        <!-- Father Details -->
        @include('school.admission.partials._father_details')

        <!-- Mother Details -->
        @include('school.admission.partials._mother_details')

        <!-- Address Details -->
        @include('school.admission.partials._address_details')

        <!-- Correspondence Address -->
        @include('school.admission.partials._correspondence_address')

        <!-- Photo Details -->
        @include('school.admission.partials._photo_details')

        <!-- Signature Details -->
        @include('school.admission.partials._signature_details')

        <div class="flex justify-end gap-4">
            <button type="reset" class="bg-gray-500 text-white px-6 py-2 rounded hover:bg-gray-600">Reset</button>
            <button type="submit" id="submit-btn" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">Update Admission</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
// Helper function to load image preview from storage path
function loadImagePreview(imagePath, previewId, iconId, removeBtnId) {
    if (!imagePath) return;
    
    const preview = document.getElementById(previewId);
    const icon = document.getElementById(iconId);
    const removeBtn = document.getElementById(removeBtnId);
    
    if (preview) {
        // Construct the full URL to the image
        const imageUrl = `/storage/${imagePath}`;
        preview.src = imageUrl;
        preview.classList.remove('hidden');
    }
    if (icon) {
        icon.classList.add('hidden');
    }
    if (removeBtn) {
        removeBtn.classList.remove('hidden');
    }
}

$(document).ready(function() {
    // Initialize Select2 for registration dropdown
    $('#registration_select').select2({
        placeholder: 'Select a registration',
        allowClear: true,
        width: '100%'
    });
    
    // Load existing photos and signatures
    @if($student->photo)
        loadImagePreview('{{ $student->photo }}', 'student-photo-preview', 'student-photo-icon', 'student-photo-remove');
    @endif
    @if($student->father_photo)
        loadImagePreview('{{ $student->father_photo }}', 'father-photo-preview', 'father-photo-icon', 'father-photo-remove');
    @endif
    @if($student->mother_photo)
        loadImagePreview('{{ $student->mother_photo }}', 'mother-photo-preview', 'mother-photo-icon', 'mother-photo-remove');
    @endif

    @if($student->signature)
        loadImagePreview('{{ $student->signature }}', 'student-signature-preview', 'student-signature-icon', 'student-signature-remove');
    @endif
    @if($student->father_signature)
        loadImagePreview('{{ $student->father_signature }}', 'father-signature-preview', 'father-signature-icon', 'father-signature-remove');
    @endif
    @if($student->mother_signature)
        loadImagePreview('{{ $student->mother_signature }}', 'mother-signature-preview', 'mother-signature-icon', 'mother-signature-remove');
    @endif
});
</script>
@endpush
