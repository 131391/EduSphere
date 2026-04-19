{{-- Step 5: Photos & Signatures --}}

{{-- Photos --}}
<div class="mb-8">
    <h3 class="text-sm font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-5 flex items-center gap-2">
        <i class="fas fa-camera text-teal-500"></i> Photos
    </h3>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">

        @foreach([
            ['key' => 'student_photo', 'label' => 'Student Photo',  'icon' => 'fa-user-graduate', 'color' => 'teal'],
            ['key' => 'father_photo',  'label' => "Father's Photo",  'icon' => 'fa-user-tie',      'color' => 'blue'],
            ['key' => 'mother_photo',  'label' => "Mother's Photo",  'icon' => 'fa-user',           'color' => 'pink'],
        ] as $item)
        <div class="flex flex-col items-center">
            <div class="w-10 h-10 rounded-full bg-{{ $item['color'] }}-100 dark:bg-{{ $item['color'] }}-900/30 flex items-center justify-center mb-3">
                <i class="fas {{ $item['icon'] }} text-{{ $item['color'] }}-600 text-sm"></i>
            </div>
            <p class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">{{ $item['label'] }}</p>
            <div class="w-36 h-36 rounded-xl bg-gray-100 dark:bg-gray-700 border-2 border-dashed border-gray-300 dark:border-gray-500
                        flex items-center justify-center overflow-hidden relative mb-3">
                <img x-show="previews.{{ $item['key'] }}"
                     :src="previews.{{ $item['key'] }}"
                     alt="{{ $item['label'] }}"
                     class="w-full h-full object-cover">
                <i x-show="!previews.{{ $item['key'] }}"
                   class="fas {{ $item['icon'] }} text-gray-300 text-4xl"></i>
                <button type="button"
                        x-show="previews.{{ $item['key'] }}"
                        @click="removePhoto('{{ $item['key'] }}')"
                        class="absolute top-1 right-1 bg-red-500 hover:bg-red-600 text-white rounded-full w-6 h-6 flex items-center justify-center shadow-lg">
                    <i class="fas fa-times text-xs"></i>
                </button>
            </div>
            <label class="cursor-pointer w-full">
                <input type="file" name="{{ $item['key'] }}" accept="image/*"
                       @change="handlePhotoUpload($event, '{{ $item['key'] }}')"
                       class="hidden">
                <span class="flex items-center justify-center gap-2 w-full px-4 py-2 text-sm font-semibold text-{{ $item['color'] }}-700 bg-{{ $item['color'] }}-50 hover:bg-{{ $item['color'] }}-100 border border-{{ $item['color'] }}-200 rounded-lg transition-colors cursor-pointer">
                    <i class="fas fa-upload text-xs"></i> Choose Photo
                </span>
            </label>
            <p class="text-[10px] text-gray-400 mt-1.5">JPG, PNG · Max 2MB</p>
        </div>
        @endforeach

    </div>
</div>

{{-- Signatures --}}
<div>
    <h3 class="text-sm font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-5 flex items-center gap-2">
        <i class="fas fa-pen-nib text-teal-500"></i> Signatures
    </h3>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">

        @foreach([
            ['key' => 'student_signature', 'label' => 'Student Signature', 'color' => 'teal'],
            ['key' => 'father_signature',  'label' => "Father's Signature", 'color' => 'blue'],
            ['key' => 'mother_signature',  'label' => "Mother's Signature", 'color' => 'pink'],
        ] as $item)
        <div class="flex flex-col items-center">
            <p class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">{{ $item['label'] }}</p>
            <div class="w-36 h-24 rounded-xl bg-gray-50 dark:bg-gray-700 border-2 border-dashed border-gray-300 dark:border-gray-500
                        flex items-center justify-center overflow-hidden relative mb-3">
                <img x-show="previews.{{ $item['key'] }}"
                     :src="previews.{{ $item['key'] }}"
                     alt="{{ $item['label'] }}"
                     class="w-full h-full object-contain p-1">
                <i x-show="!previews.{{ $item['key'] }}"
                   class="fas fa-pen text-gray-300 text-3xl"></i>
                <button type="button"
                        x-show="previews.{{ $item['key'] }}"
                        @click="removePhoto('{{ $item['key'] }}')"
                        class="absolute top-1 right-1 bg-red-500 hover:bg-red-600 text-white rounded-full w-5 h-5 flex items-center justify-center shadow-lg">
                    <i class="fas fa-times text-[9px]"></i>
                </button>
            </div>
            <label class="cursor-pointer w-full">
                <input type="file" name="{{ $item['key'] }}" accept="image/*"
                       @change="handlePhotoUpload($event, '{{ $item['key'] }}')"
                       class="hidden">
                <span class="flex items-center justify-center gap-2 w-full px-4 py-2 text-sm font-semibold text-{{ $item['color'] }}-700 bg-{{ $item['color'] }}-50 hover:bg-{{ $item['color'] }}-100 border border-{{ $item['color'] }}-200 rounded-lg transition-colors cursor-pointer">
                    <i class="fas fa-upload text-xs"></i> Upload Signature
                </span>
            </label>
            <p class="text-[10px] text-gray-400 mt-1.5">JPG, PNG · Max 2MB</p>
        </div>
        @endforeach

    </div>
</div>
