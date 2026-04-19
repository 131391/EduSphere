@extends('layouts.school')

@section('title', 'Student Details — ' . $student->full_name)

@section('content')
<div class="space-y-6">

    {{-- ── Page Header ── --}}
    <x-page-header
        title="Student Details"
        description="Full admission profile for {{ $student->full_name }}"
        icon="fas fa-user-graduate">
        <a href="{{ route('school.admission.pdf', $student->id) }}"
            class="inline-flex items-center px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white text-sm font-semibold rounded-xl transition-all shadow-md hover:shadow-lg active:scale-95">
            <i class="fas fa-file-pdf mr-2 text-xs"></i> Download PDF
        </a>
        <a href="{{ route('school.admission.edit', $student->id) }}"
            class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-teal-600 to-emerald-600 hover:from-teal-700 hover:to-emerald-700 text-white text-sm font-semibold rounded-xl transition-all shadow-md hover:shadow-lg active:scale-95">
            <i class="fas fa-edit mr-2 text-xs"></i> Edit
        </a>
        <a href="{{ route('school.admission.index') }}"
            class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold rounded-xl transition-all">
            <i class="fas fa-arrow-left mr-2 text-xs"></i> Back
        </a>
    </x-page-header>

    {{-- ── Hero Profile Card ── --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
        {{-- Coloured top strip --}}
        <div class="h-2 bg-gradient-to-r from-teal-500 to-emerald-500"></div>

        <div class="p-6">
            <div class="flex flex-col sm:flex-row items-center sm:items-start gap-6">

                {{-- Photo --}}
                <div class="shrink-0">
                    <div class="w-28 h-28 rounded-2xl border-4 border-white dark:border-gray-700 shadow-md overflow-hidden bg-gray-100 dark:bg-gray-700">
                        @if($student->student_photo)
                            <img src="{{ asset('storage/' . $student->student_photo) }}"
                                 alt="{{ $student->full_name }}"
                                 class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex items-center justify-center">
                                <i class="fas fa-user text-gray-400 text-4xl"></i>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Core info --}}
                <div class="flex-1 text-center sm:text-left min-w-0">
                    <div class="flex flex-col sm:flex-row sm:items-center gap-3 mb-2">
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white truncate">
                            {{ $student->full_name }}
                        </h2>
                        @php
                            $statusColor = match($student->status?->value ?? 1) {
                                1 => 'bg-emerald-100 text-emerald-700',
                                2 => 'bg-blue-100 text-blue-700',
                                3 => 'bg-amber-100 text-amber-700',
                                default => 'bg-gray-100 text-gray-600',
                            };
                        @endphp
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold {{ $statusColor }} shrink-0">
                            <i class="fas fa-circle text-[6px]"></i>
                            {{ $student->status?->label() ?? 'Active' }}
                        </span>
                    </div>

                    {{-- Key badges --}}
                    <div class="flex flex-wrap justify-center sm:justify-start gap-2 mb-4">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-teal-50 dark:bg-teal-900/20 text-teal-700 dark:text-teal-300 rounded-lg text-xs font-semibold border border-teal-100 dark:border-teal-800/40">
                            <i class="fas fa-id-card text-[10px]"></i>
                            {{ $student->admission_no }}
                        </span>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-50 dark:bg-indigo-900/20 text-indigo-700 dark:text-indigo-300 rounded-lg text-xs font-semibold border border-indigo-100 dark:border-indigo-800/40">
                            <i class="fas fa-graduation-cap text-[10px]"></i>
                            {{ $student->class->name ?? 'N/A' }} — {{ $student->section->name ?? 'N/A' }}
                        </span>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-purple-50 dark:bg-purple-900/20 text-purple-700 dark:text-purple-300 rounded-lg text-xs font-semibold border border-purple-100 dark:border-purple-800/40">
                            <i class="fas fa-calendar-alt text-[10px]"></i>
                            {{ $student->academicYear->name ?? 'N/A' }}
                        </span>
                        @if($student->roll_no)
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-amber-50 dark:bg-amber-900/20 text-amber-700 dark:text-amber-300 rounded-lg text-xs font-semibold border border-amber-100 dark:border-amber-800/40">
                            <i class="fas fa-hashtag text-[10px]"></i>
                            Roll {{ $student->roll_no }}
                        </span>
                        @endif
                    </div>

                    {{-- Quick stats row --}}
                    <div class="flex flex-wrap justify-center sm:justify-start gap-6 text-sm text-gray-500 dark:text-gray-400">
                        <span class="flex items-center gap-1.5">
                            <i class="fas fa-birthday-cake text-gray-400 text-xs"></i>
                            {{ $student->dob?->format('d M Y') ?? 'N/A' }}
                        </span>
                        <span class="flex items-center gap-1.5">
                            <i class="fas fa-venus-mars text-gray-400 text-xs"></i>
                            {{ $student->gender_label }}
                        </span>
                        @if($student->bloodGroup)
                        <span class="flex items-center gap-1.5">
                            <i class="fas fa-tint text-rose-400 text-xs"></i>
                            {{ $student->bloodGroup->name }}
                        </span>
                        @endif
                        @if($student->mobile_no)
                        <span class="flex items-center gap-1.5">
                            <i class="fas fa-phone text-gray-400 text-xs"></i>
                            {{ $student->mobile_no }}
                        </span>
                        @endif
                        <span class="flex items-center gap-1.5">
                            <i class="fas fa-calendar-check text-gray-400 text-xs"></i>
                            Admitted {{ $student->admission_date?->format('d M Y') ?? 'N/A' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Main Content Grid ── --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- ── LEFT COLUMN (2/3) ── --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Personal Information --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="flex items-center gap-3 px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/60 dark:bg-gray-800/60">
                    <div class="w-8 h-8 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center text-blue-600 dark:text-blue-400">
                        <i class="fas fa-user text-xs"></i>
                    </div>
                    <h3 class="text-sm font-bold text-gray-800 dark:text-white">Personal Information</h3>
                </div>
                <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-5">
                    @include('school.admission.partials._detail_row', ['icon' => 'fa-user',        'label' => 'Full Name',            'value' => $student->full_name])
                    @include('school.admission.partials._detail_row', ['icon' => 'fa-birthday-cake','label' => 'Date of Birth',       'value' => $student->dob?->format('d F Y') ?? 'N/A'])
                    @include('school.admission.partials._detail_row', ['icon' => 'fa-venus-mars',  'label' => 'Gender',               'value' => $student->gender_label])
                    @include('school.admission.partials._detail_row', ['icon' => 'fa-tint',        'label' => 'Blood Group',          'value' => $student->bloodGroup?->name ?? 'N/A'])
                    @include('school.admission.partials._detail_row', ['icon' => 'fa-phone',       'label' => 'Mobile',               'value' => $student->mobile_no ?? 'N/A'])
                    @include('school.admission.partials._detail_row', ['icon' => 'fa-envelope',    'label' => 'Email',                'value' => $student->email ?? 'N/A'])
                    @include('school.admission.partials._detail_row', ['icon' => 'fa-pray',        'label' => 'Religion',             'value' => $student->religion?->name ?? 'N/A'])
                    @include('school.admission.partials._detail_row', ['icon' => 'fa-layer-group', 'label' => 'Category',             'value' => $student->category?->name ?? 'N/A'])
                    @include('school.admission.partials._detail_row', ['icon' => 'fa-id-badge',    'label' => 'Aadhaar No',           'value' => $student->aadhaar_no ?? 'N/A'])
                    @include('school.admission.partials._detail_row', ['icon' => 'fa-globe',       'label' => 'Nationality',          'value' => $student->nationality ?? 'N/A'])
                </div>
            </div>

            {{-- Father's Details --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="flex items-center gap-3 px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/60 dark:bg-gray-800/60">
                    <div class="w-8 h-8 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center text-blue-600 dark:text-blue-400">
                        <i class="fas fa-male text-xs"></i>
                    </div>
                    <h3 class="text-sm font-bold text-gray-800 dark:text-white">Father's Details</h3>
                </div>
                <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-5">
                    @include('school.admission.partials._detail_row', ['icon' => 'fa-user',        'label' => 'Name',          'value' => $student->father_name])
                    @include('school.admission.partials._detail_row', ['icon' => 'fa-phone',       'label' => 'Mobile',        'value' => $student->father_mobile_no ?? 'N/A'])
                    @include('school.admission.partials._detail_row', ['icon' => 'fa-envelope',    'label' => 'Email',         'value' => $student->father_email ?? 'N/A'])
                    @include('school.admission.partials._detail_row', ['icon' => 'fa-briefcase',   'label' => 'Occupation',    'value' => $student->father_occupation ?? 'N/A'])
                    @include('school.admission.partials._detail_row', ['icon' => 'fa-graduation-cap','label' => 'Qualification','value' => $student->fatherQualification?->name ?? 'N/A'])
                </div>
            </div>

            {{-- Mother's Details --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="flex items-center gap-3 px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/60 dark:bg-gray-800/60">
                    <div class="w-8 h-8 rounded-lg bg-pink-100 dark:bg-pink-900/30 flex items-center justify-center text-pink-600 dark:text-pink-400">
                        <i class="fas fa-female text-xs"></i>
                    </div>
                    <h3 class="text-sm font-bold text-gray-800 dark:text-white">Mother's Details</h3>
                </div>
                <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-5">
                    @include('school.admission.partials._detail_row', ['icon' => 'fa-user',        'label' => 'Name',          'value' => $student->mother_name])
                    @include('school.admission.partials._detail_row', ['icon' => 'fa-phone',       'label' => 'Mobile',        'value' => $student->mother_mobile_no ?? 'N/A'])
                    @include('school.admission.partials._detail_row', ['icon' => 'fa-envelope',    'label' => 'Email',         'value' => $student->mother_email ?? 'N/A'])
                    @include('school.admission.partials._detail_row', ['icon' => 'fa-briefcase',   'label' => 'Occupation',    'value' => $student->mother_occupation ?? 'N/A'])
                    @include('school.admission.partials._detail_row', ['icon' => 'fa-graduation-cap','label' => 'Qualification','value' => $student->motherQualification?->name ?? 'N/A'])
                </div>
            </div>

            {{-- Address --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="flex items-center gap-3 px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/60 dark:bg-gray-800/60">
                    <div class="w-8 h-8 rounded-lg bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center text-emerald-600 dark:text-emerald-400">
                        <i class="fas fa-map-marker-alt text-xs"></i>
                    </div>
                    <h3 class="text-sm font-bold text-gray-800 dark:text-white">Address</h3>
                </div>
                <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-6">
                    {{-- Permanent --}}
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3 flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-teal-500"></span> Permanent Address
                        </p>
                        <div class="space-y-1 text-sm text-gray-700 dark:text-gray-300">
                            <p>{{ $student->permanent_address ?? '—' }}</p>
                            @if($student->permanent_city || $student->permanent_state)
                            <p class="text-gray-500">{{ $student->permanent_city }}, {{ $student->permanent_state }}</p>
                            @endif
                            @if($student->permanent_pin)
                            <p class="text-gray-500">PIN: {{ $student->permanent_pin }}</p>
                            @endif
                        </div>
                    </div>
                    {{-- Correspondence --}}
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3 flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-purple-500"></span> Correspondence Address
                        </p>
                        <div class="space-y-1 text-sm text-gray-700 dark:text-gray-300">
                            <p>{{ $student->correspondence_address ?? '—' }}</p>
                            @if($student->correspondence_city || $student->correspondence_state)
                            <p class="text-gray-500">{{ $student->correspondence_city }}, {{ $student->correspondence_state }}</p>
                            @endif
                            @if($student->correspondence_pin)
                            <p class="text-gray-500">PIN: {{ $student->correspondence_pin }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

        </div>{{-- end left column --}}

        {{-- ── RIGHT COLUMN (1/3) ── --}}
        <div class="space-y-6">

            {{-- Admission Info --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="flex items-center gap-3 px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/60 dark:bg-gray-800/60">
                    <div class="w-8 h-8 rounded-lg bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                        <i class="fas fa-graduation-cap text-xs"></i>
                    </div>
                    <h3 class="text-sm font-bold text-gray-800 dark:text-white">Admission Info</h3>
                </div>
                <div class="p-5 space-y-4">
                    @include('school.admission.partials._detail_row', ['icon' => 'fa-id-card',       'label' => 'Admission No',    'value' => $student->admission_no])
                    @include('school.admission.partials._detail_row', ['icon' => 'fa-file-alt',      'label' => 'Registration No', 'value' => $student->registration_no ?? 'N/A'])
                    @include('school.admission.partials._detail_row', ['icon' => 'fa-hashtag',       'label' => 'Roll No',         'value' => $student->roll_no ?? 'N/A'])
                    @include('school.admission.partials._detail_row', ['icon' => 'fa-calendar-check','label' => 'Admission Date',  'value' => $student->admission_date?->format('d M Y') ?? 'N/A'])
                    @include('school.admission.partials._detail_row', ['icon' => 'fa-chalkboard',    'label' => 'Class',           'value' => ($student->class->name ?? 'N/A') . ' — ' . ($student->section->name ?? 'N/A')])
                    @include('school.admission.partials._detail_row', ['icon' => 'fa-calendar-alt',  'label' => 'Academic Year',   'value' => $student->academicYear->name ?? 'N/A'])
                </div>
            </div>

            {{-- Photos --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="flex items-center gap-3 px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/60 dark:bg-gray-800/60">
                    <div class="w-8 h-8 rounded-lg bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center text-purple-600 dark:text-purple-400">
                        <i class="fas fa-images text-xs"></i>
                    </div>
                    <h3 class="text-sm font-bold text-gray-800 dark:text-white">Photos</h3>
                </div>
                <div class="p-5 grid grid-cols-3 gap-3">
                    @foreach([
                        ['photo' => $student->student_photo,        'label' => 'Student'],
                        ['photo' => $student->father_photo, 'label' => 'Father'],
                        ['photo' => $student->mother_photo, 'label' => 'Mother'],
                    ] as $item)
                    <div class="text-center">
                        <div class="w-full aspect-square rounded-xl overflow-hidden bg-gray-100 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 mb-1.5">
                            @if($item['photo'])
                                <img src="{{ asset('storage/' . $item['photo']) }}"
                                     alt="{{ $item['label'] }}"
                                     class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center">
                                    <i class="fas fa-user text-gray-300 text-2xl"></i>
                                </div>
                            @endif
                        </div>
                        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">{{ $item['label'] }}</p>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Signatures --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="flex items-center gap-3 px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/60 dark:bg-gray-800/60">
                    <div class="w-8 h-8 rounded-lg bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center text-amber-600 dark:text-amber-400">
                        <i class="fas fa-signature text-xs"></i>
                    </div>
                    <h3 class="text-sm font-bold text-gray-800 dark:text-white">Signatures</h3>
                </div>
                <div class="p-5 space-y-4">
                    @foreach([
                        ['sig' => $student->student_signature,        'label' => 'Student'],
                        ['sig' => $student->father_signature, 'label' => 'Father'],
                        ['sig' => $student->mother_signature, 'label' => 'Mother'],
                    ] as $item)
                    <div>
                        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-1.5">{{ $item['label'] }}</p>
                        @if($item['sig'])
                            <div class="bg-gray-50 dark:bg-gray-700/50 border border-dashed border-gray-200 dark:border-gray-600 rounded-xl p-3 flex items-center justify-center">
                                <img src="{{ asset('storage/' . $item['sig']) }}"
                                     alt="{{ $item['label'] }} Signature"
                                     class="h-12 object-contain">
                            </div>
                        @else
                            <div class="bg-gray-50 dark:bg-gray-700/50 border border-dashed border-gray-200 dark:border-gray-600 rounded-xl p-3 text-center">
                                <p class="text-xs text-gray-400 italic">Not available</p>
                            </div>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>

        </div>{{-- end right column --}}
    </div>{{-- end grid --}}

</div>
@endsection
