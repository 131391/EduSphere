@extends('layouts.school')

@section('title', 'Registration Details — {{ $studentRegistration->registration_no }}')

@section('content')
<div class="space-y-6">

    {{-- ── Page Header ── --}}
    <x-page-header
        title="Registration Details"
        description="Full registration profile for {{ $studentRegistration->full_name }}"
        icon="fas fa-file-alt">
        <a href="{{ route('school.student-registrations.pdf', $studentRegistration->id) }}"
            class="inline-flex items-center px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white text-sm font-semibold rounded-xl transition-all shadow-md hover:shadow-lg active:scale-95">
            <i class="fas fa-file-pdf mr-2 text-xs"></i> Download PDF
        </a>
        <a href="{{ route('school.student-registrations.edit', $studentRegistration->id) }}"
            class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-700 hover:to-blue-700 text-white text-sm font-semibold rounded-xl transition-all shadow-md hover:shadow-lg active:scale-95">
            <i class="fas fa-edit mr-2 text-xs"></i> Edit
        </a>
        <a href="{{ route('school.student-registrations.index') }}"
            class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold rounded-xl transition-all">
            <i class="fas fa-arrow-left mr-2 text-xs"></i> Back
        </a>
    </x-page-header>

    {{-- ── Hero Card ── --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="h-2 bg-gradient-to-r from-blue-500 to-indigo-500"></div>
        <div class="p-6">
            <div class="flex flex-col sm:flex-row items-center sm:items-start gap-6">

                {{-- Photo --}}
                <div class="shrink-0">
                    <div class="w-28 h-28 rounded-2xl border-4 border-white dark:border-gray-700 shadow-md overflow-hidden bg-gray-100 dark:bg-gray-700">
                        @if($studentRegistration->student_photo)
                            <img src="{{ asset('storage/' . $studentRegistration->student_photo) }}"
                                 alt="{{ $studentRegistration->full_name }}"
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
                            {{ $studentRegistration->full_name }}
                        </h2>
                        @php
                            $statusColor = match($studentRegistration->admission_status->value) {
                                'pending'   => 'bg-amber-100 text-amber-700',
                                'admitted'  => 'bg-emerald-100 text-emerald-700',
                                'cancelled' => 'bg-rose-100 text-rose-600',
                                default     => 'bg-gray-100 text-gray-600',
                            };
                        @endphp
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold {{ $statusColor }} shrink-0">
                            <i class="fas fa-circle text-[6px]"></i>
                            {{ $studentRegistration->admission_status->label() }}
                        </span>
                    </div>

                    {{-- Badges --}}
                    <div class="flex flex-wrap justify-center sm:justify-start gap-2 mb-4">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 rounded-lg text-xs font-semibold border border-blue-100 dark:border-blue-800/40">
                            <i class="fas fa-file-alt text-[10px]"></i>
                            {{ $studentRegistration->registration_no }}
                        </span>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-50 dark:bg-indigo-900/20 text-indigo-700 dark:text-indigo-300 rounded-lg text-xs font-semibold border border-indigo-100 dark:border-indigo-800/40">
                            <i class="fas fa-graduation-cap text-[10px]"></i>
                            {{ $studentRegistration->class->name ?? 'N/A' }}
                        </span>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-purple-50 dark:bg-purple-900/20 text-purple-700 dark:text-purple-300 rounded-lg text-xs font-semibold border border-purple-100 dark:border-purple-800/40">
                            <i class="fas fa-calendar-alt text-[10px]"></i>
                            {{ $studentRegistration->academicYear->name ?? 'N/A' }}
                        </span>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-300 rounded-lg text-xs font-semibold border border-emerald-100 dark:border-emerald-800/40">
                            <i class="fas fa-rupee-sign text-[10px]"></i>
                            Fee: ₹{{ number_format($studentRegistration->registration_fee, 2) }}
                        </span>
                    </div>

                    {{-- Quick stats --}}
                    <div class="flex flex-wrap justify-center sm:justify-start gap-6 text-sm text-gray-500 dark:text-gray-400">
                        <span class="flex items-center gap-1.5">
                            <i class="fas fa-birthday-cake text-gray-400 text-xs"></i>
                            {{ $studentRegistration->dob?->format('d M Y') ?? 'N/A' }}
                        </span>
                        <span class="flex items-center gap-1.5">
                            <i class="fas fa-venus-mars text-gray-400 text-xs"></i>
                            {{ $studentRegistration->gender_label }}
                        </span>
                        <span class="flex items-center gap-1.5">
                            <i class="fas fa-phone text-gray-400 text-xs"></i>
                            {{ $studentRegistration->mobile_no ?? 'N/A' }}
                        </span>
                        <span class="flex items-center gap-1.5">
                            <i class="fas fa-calendar-check text-gray-400 text-xs"></i>
                            Registered {{ $studentRegistration->registration_date?->format('d M Y') ?? 'N/A' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Main Grid ── --}}
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
                    @include('school.admission.partials._detail_row', ['icon' => 'fa-user',         'label' => 'Full Name',   'value' => $studentRegistration->full_name])
                    @include('school.admission.partials._detail_row', ['icon' => 'fa-birthday-cake','label' => 'Date of Birth','value' => $studentRegistration->dob?->format('d F Y') ?? 'N/A'])
                    @include('school.admission.partials._detail_row', ['icon' => 'fa-venus-mars',   'label' => 'Gender',      'value' => $studentRegistration->gender_label])
                    @include('school.admission.partials._detail_row', ['icon' => 'fa-tint',         'label' => 'Blood Group', 'value' => $studentRegistration->blood_group ?? 'N/A'])
                    @include('school.admission.partials._detail_row', ['icon' => 'fa-phone',        'label' => 'Mobile',      'value' => $studentRegistration->mobile_no ?? 'N/A'])
                    @include('school.admission.partials._detail_row', ['icon' => 'fa-envelope',     'label' => 'Email',       'value' => $studentRegistration->email ?? 'N/A'])
                    @include('school.admission.partials._detail_row', ['icon' => 'fa-pray',         'label' => 'Religion',    'value' => $studentRegistration->religion ?? 'N/A'])
                    @include('school.admission.partials._detail_row', ['icon' => 'fa-layer-group',  'label' => 'Category',    'value' => $studentRegistration->category ?? 'N/A'])
                    @include('school.admission.partials._detail_row', ['icon' => 'fa-id-badge',     'label' => 'Aadhaar No',  'value' => $studentRegistration->aadhar_no ?? 'N/A'])
                    @include('school.admission.partials._detail_row', ['icon' => 'fa-globe',        'label' => 'Nationality', 'value' => $studentRegistration->nationality ?? 'N/A'])
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
                    @include('school.admission.partials._detail_row', ['icon' => 'fa-user',          'label' => 'Name',          'value' => trim($studentRegistration->father_name_prefix . ' ' . $studentRegistration->father_first_name . ' ' . $studentRegistration->father_last_name)])
                    @include('school.admission.partials._detail_row', ['icon' => 'fa-phone',         'label' => 'Mobile',        'value' => $studentRegistration->father_mobile_no ?? 'N/A'])
                    @include('school.admission.partials._detail_row', ['icon' => 'fa-envelope',      'label' => 'Email',         'value' => $studentRegistration->father_email ?? 'N/A'])
                    @include('school.admission.partials._detail_row', ['icon' => 'fa-briefcase',     'label' => 'Occupation',    'value' => $studentRegistration->father_occupation ?? 'N/A'])
                    @include('school.admission.partials._detail_row', ['icon' => 'fa-graduation-cap','label' => 'Qualification', 'value' => $studentRegistration->father_qualification ?? 'N/A'])
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
                    @include('school.admission.partials._detail_row', ['icon' => 'fa-user',          'label' => 'Name',          'value' => trim($studentRegistration->mother_name_prefix . ' ' . $studentRegistration->mother_first_name . ' ' . $studentRegistration->mother_last_name)])
                    @include('school.admission.partials._detail_row', ['icon' => 'fa-phone',         'label' => 'Mobile',        'value' => $studentRegistration->mother_mobile_no ?? 'N/A'])
                    @include('school.admission.partials._detail_row', ['icon' => 'fa-envelope',      'label' => 'Email',         'value' => $studentRegistration->mother_email ?? 'N/A'])
                    @include('school.admission.partials._detail_row', ['icon' => 'fa-briefcase',     'label' => 'Occupation',    'value' => $studentRegistration->mother_occupation ?? 'N/A'])
                    @include('school.admission.partials._detail_row', ['icon' => 'fa-graduation-cap','label' => 'Qualification', 'value' => $studentRegistration->mother_qualification ?? 'N/A'])
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
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3 flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-blue-500"></span> Permanent Address
                        </p>
                        <div class="space-y-1 text-sm text-gray-700 dark:text-gray-300">
                            <p>{{ $studentRegistration->permanent_address ?? '—' }}</p>
                            @if($studentRegistration->permanent_city || $studentRegistration->permanent_state)
                            <p class="text-gray-500">{{ $studentRegistration->permanent_city }}{{ $studentRegistration->permanent_state ? ', ' . $studentRegistration->permanent_state : '' }}</p>
                            @endif
                            @if($studentRegistration->permanent_pin)
                            <p class="text-gray-500">PIN: {{ $studentRegistration->permanent_pin }}</p>
                            @endif
                        </div>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3 flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-purple-500"></span> Correspondence Address
                        </p>
                        <div class="space-y-1 text-sm text-gray-700 dark:text-gray-300">
                            @if($studentRegistration->correspondence_address)
                                <p>{{ $studentRegistration->correspondence_address }}</p>
                                @if($studentRegistration->correspondence_city || $studentRegistration->correspondence_state)
                                <p class="text-gray-500">{{ $studentRegistration->correspondence_city }}{{ $studentRegistration->correspondence_state ? ', ' . $studentRegistration->correspondence_state : '' }}</p>
                                @endif
                                @if($studentRegistration->correspondence_pin)
                                <p class="text-gray-500">PIN: {{ $studentRegistration->correspondence_pin }}</p>
                                @endif
                            @else
                                <p class="text-gray-400 italic text-xs">Same as permanent address</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

        </div>{{-- end left --}}

        {{-- ── RIGHT COLUMN (1/3) ── --}}
        <div class="space-y-6">

            {{-- Registration Info --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="flex items-center gap-3 px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/60 dark:bg-gray-800/60">
                    <div class="w-8 h-8 rounded-lg bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                        <i class="fas fa-file-alt text-xs"></i>
                    </div>
                    <h3 class="text-sm font-bold text-gray-800 dark:text-white">Registration Info</h3>
                </div>
                <div class="p-5 space-y-4">
                    @include('school.admission.partials._detail_row', ['icon' => 'fa-file-alt',       'label' => 'Registration No',  'value' => $studentRegistration->registration_no])
                    @include('school.admission.partials._detail_row', ['icon' => 'fa-calendar-check', 'label' => 'Registration Date','value' => $studentRegistration->registration_date?->format('d M Y') ?? 'N/A'])
                    @include('school.admission.partials._detail_row', ['icon' => 'fa-graduation-cap', 'label' => 'Applied Class',    'value' => $studentRegistration->class->name ?? 'N/A'])
                    @include('school.admission.partials._detail_row', ['icon' => 'fa-calendar-alt',   'label' => 'Academic Year',    'value' => $studentRegistration->academicYear->name ?? 'N/A'])
                    @include('school.admission.partials._detail_row', ['icon' => 'fa-rupee-sign',     'label' => 'Registration Fee', 'value' => '₹' . number_format($studentRegistration->registration_fee, 2)])
                    @include('school.admission.partials._detail_row', ['icon' => 'fa-info-circle',    'label' => 'Status',           'value' => $studentRegistration->admission_status->label()])
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
                        ['photo' => $studentRegistration->student_photo, 'label' => 'Student'],
                        ['photo' => $studentRegistration->father_photo,  'label' => 'Father'],
                        ['photo' => $studentRegistration->mother_photo,  'label' => 'Mother'],
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
                        ['sig' => $studentRegistration->student_signature, 'label' => 'Student'],
                        ['sig' => $studentRegistration->father_signature,  'label' => 'Father'],
                        ['sig' => $studentRegistration->mother_signature,  'label' => 'Mother'],
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

        </div>{{-- end right --}}
    </div>

</div>
@endsection
