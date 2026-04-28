@extends('layouts.school')

@section('title', 'School Logo - ' . $school->name)

@section('content')
<div class="w-full space-y-6 animate-in fade-in duration-500">

    {{-- Page Header --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 px-1">
        <div>
            <nav class="flex mb-2" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3 text-sm font-bold uppercase tracking-wider text-gray-400">
                    <li class="inline-flex items-center">
                        <a href="{{ route('school.dashboard') }}" class="hover:text-indigo-600 transition-colors">Dashboard</a>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center text-indigo-600">
                            <i class="fas fa-chevron-right mx-2 text-[11px] text-gray-300"></i>
                            <span>School Logo</span>
                        </div>
                    </li>
                </ol>
            </nav>
            <h1 class="text-3xl font-black text-gray-900 dark:text-white tracking-tight">School Logo</h1>
            <p class="text-base text-gray-500 mt-1 flex items-center font-medium">
                <span class="w-2 h-2 rounded-full bg-indigo-500 mr-2"></span>
                Appears on reports, receipts and the sidebar
            </p>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden"
         x-data="logoUpload()">

        <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-900/40 flex items-center justify-center flex-shrink-0">
                <i class="fas fa-image text-indigo-600 dark:text-indigo-400"></i>
            </div>
            <div>
                <h2 class="text-base font-bold text-gray-900 dark:text-white">Upload Logo</h2>
                <p class="text-xs text-gray-400 mt-0.5">PNG with transparent background recommended. Max 2MB.</p>
            </div>
        </div>

        <form action="{{ route('school.settings.logo.update') }}" method="POST"
              enctype="multipart/form-data" id="logo-form">
            @csrf
            @method('PUT')

            <div class="p-6 flex flex-col sm:flex-row items-center gap-8">

                {{-- Preview --}}
                <div class="flex-shrink-0">
                    <div class="w-32 h-32 rounded-2xl border-2 border-gray-100 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 flex items-center justify-center overflow-hidden">
                        <img x-show="preview" :src="preview" alt="Logo preview"
                             class="w-full h-full object-contain p-2" x-cloak>
                        @if($school->logo)
                            <img src="{{ asset('storage/' . $school->logo) }}" alt="{{ $school->name }}"
                                 class="w-full h-full object-contain p-2" x-show="!preview">
                        @else
                            <div x-show="!preview" class="text-center">
                                <i class="fas fa-image text-3xl text-gray-200 dark:text-gray-600"></i>
                                <p class="text-[10px] text-gray-300 dark:text-gray-600 mt-1 font-semibold uppercase tracking-wide">No logo</p>
                            </div>
                        @endif
                    </div>
                    @if($school->logo)
                        <p class="text-xs text-center text-gray-400 mt-2 font-medium">Current logo</p>
                    @endif
                </div>

                {{-- Drop zone --}}
                <div class="flex-1 w-full">
                    <label
                        class="flex flex-col items-center justify-center w-full h-36 border-2 border-dashed rounded-2xl cursor-pointer transition-all"
                        :class="dragging
                            ? 'border-indigo-400 bg-indigo-50'
                            : 'border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 hover:border-indigo-300 hover:bg-indigo-50/40 dark:hover:bg-indigo-900/20'"
                        @dragover.prevent="dragging = true"
                        @dragleave.prevent="dragging = false"
                        @drop.prevent="handleDrop($event)">

                        <div class="flex flex-col items-center gap-2 pointer-events-none">
                            <div class="w-10 h-10 rounded-xl bg-white dark:bg-gray-700 shadow-sm border border-gray-100 dark:border-gray-600 flex items-center justify-center">
                                <i class="fas fa-cloud-upload-alt text-indigo-500"></i>
                            </div>
                            <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                                <span class="text-indigo-600">Click to upload</span> or drag & drop
                            </p>
                            <p class="text-xs text-gray-400">PNG, JPG, GIF — max 2MB</p>
                        </div>

                        <input type="file" name="logo" id="logo-input" class="hidden"
                               accept="image/*"
                               @change="handleFile($event)">
                    </label>

                    {{-- Selected file name --}}
                    <div x-show="fileName" class="mt-3 flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300" x-cloak>
                        <i class="fas fa-file-image text-indigo-400 text-xs"></i>
                        <span x-text="fileName" class="font-medium truncate"></span>
                        <button type="button" @click="clearFile()"
                                class="ml-auto text-gray-300 dark:text-gray-600 hover:text-red-400 transition-colors">
                            <i class="fas fa-times text-xs"></i>
                        </button>
                    </div>

                    @error('logo')
                        <p class="mt-2 text-xs text-red-500 flex items-center gap-1">
                            <i class="fas fa-exclamation-circle"></i> {{ $message }}
                        </p>
                    @enderror
                </div>
            </div>

            {{-- Footer --}}
            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700/50 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between">
                <p class="text-xs text-gray-400">
                    <i class="fas fa-info-circle mr-1"></i>
                    Use a square image with transparent background for best results on ID cards and PDFs.
                </p>
                <button type="submit" :disabled="!fileName"
                        class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-40 disabled:cursor-not-allowed text-white text-sm font-semibold rounded-xl transition-all shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    <i class="fas fa-upload text-xs"></i>
                    Upload Logo
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function logoUpload() {
    return {
        preview: null,
        fileName: null,
        dragging: false,

        handleFile(event) {
            const file = event.target.files[0];
            if (file) this.setFile(file);
        },

        handleDrop(event) {
            this.dragging = false;
            const file = event.dataTransfer.files[0];
            if (file && file.type.startsWith('image/')) {
                // Assign to the actual input
                const dt = new DataTransfer();
                dt.items.add(file);
                document.getElementById('logo-input').files = dt.files;
                this.setFile(file);
            }
        },

        setFile(file) {
            this.fileName = file.name;
            const reader = new FileReader();
            reader.onload = (e) => { this.preview = e.target.result; };
            reader.readAsDataURL(file);
        },

        clearFile() {
            this.preview = null;
            this.fileName = null;
            document.getElementById('logo-input').value = '';
        }
    }
}
</script>
@endpush
@endsection
