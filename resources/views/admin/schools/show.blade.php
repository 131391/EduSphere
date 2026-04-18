@extends('layouts.admin')

@section('title', $school->name . ' — School Details')

@section('content')
<div class="space-y-6">

    {{-- ── Breadcrumb + Header ── --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <nav class="flex items-center gap-2 text-xs font-semibold text-gray-400 mb-2">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-blue-600 transition-colors">Admin</a>
                <i class="fas fa-chevron-right text-[9px]"></i>
                <a href="{{ route('admin.schools.index') }}" class="hover:text-blue-600 transition-colors">Schools</a>
                <i class="fas fa-chevron-right text-[9px]"></i>
                <span class="text-gray-600">{{ $school->name }}</span>
            </nav>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $school->name }}</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">School profile and management</p>
        </div>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('admin.schools.edit', $school->id) }}"
                class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-700 hover:to-blue-700 text-white text-sm font-semibold rounded-xl transition-all shadow-md hover:shadow-lg active:scale-95">
                <i class="fas fa-edit mr-2 text-xs"></i> Edit School
            </a>
            <a href="{{ route('admin.schools.index') }}"
                class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold rounded-xl transition-all">
                <i class="fas fa-arrow-left mr-2 text-xs"></i> Back
            </a>
        </div>
    </div>

    {{-- ── Hero Card ── --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="h-2 bg-gradient-to-r from-blue-600 to-indigo-600"></div>
        <div class="p-6">
            <div class="flex flex-col sm:flex-row items-center sm:items-start gap-6">

                {{-- Logo --}}
                <div class="shrink-0">
                    <div class="w-24 h-24 rounded-2xl border-4 border-white dark:border-gray-700 shadow-md overflow-hidden bg-gray-100 dark:bg-gray-700">
                        @if($school->logo)
                            <img src="{{ asset('storage/' . $school->logo) }}" alt="{{ $school->name }}" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex items-center justify-center">
                                <i class="fas fa-school text-gray-400 text-3xl"></i>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Info --}}
                <div class="flex-1 text-center sm:text-left min-w-0">
                    <div class="flex flex-col sm:flex-row sm:items-center gap-3 mb-2">
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white truncate">{{ $school->name }}</h2>
                        @php
                            $statusColor = match($school->status?->color()) {
                                'green' => 'bg-emerald-100 text-emerald-700',
                                'red'   => 'bg-rose-100 text-rose-600',
                                default => 'bg-gray-100 text-gray-600',
                            };
                        @endphp
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold {{ $statusColor }} shrink-0">
                            <i class="fas fa-circle text-[6px]"></i>
                            {{ $school->status?->label() ?? 'Unknown' }}
                        </span>
                    </div>

                    <div class="flex flex-wrap justify-center sm:justify-start gap-2 mb-4">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 rounded-lg text-xs font-semibold border border-blue-100 dark:border-blue-800/40">
                            <i class="fas fa-fingerprint text-[10px]"></i> {{ $school->code }}
                        </span>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-50 dark:bg-indigo-900/20 text-indigo-700 dark:text-indigo-300 rounded-lg text-xs font-semibold border border-indigo-100 dark:border-indigo-800/40">
                            <i class="fas fa-link text-[10px]"></i> {{ $school->subdomain }}.edusphere.local
                        </span>
                        @if($school->website)
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-purple-50 dark:bg-purple-900/20 text-purple-700 dark:text-purple-300 rounded-lg text-xs font-semibold border border-purple-100 dark:border-purple-800/40">
                            <i class="fas fa-globe text-[10px]"></i> {{ $school->website }}
                        </span>
                        @endif
                    </div>

                    <div class="flex flex-wrap justify-center sm:justify-start gap-6 text-sm text-gray-500 dark:text-gray-400">
                        @if($school->email)
                        <span class="flex items-center gap-1.5"><i class="fas fa-envelope text-gray-400 text-xs"></i> {{ $school->email }}</span>
                        @endif
                        @if($school->phone)
                        <span class="flex items-center gap-1.5"><i class="fas fa-phone text-gray-400 text-xs"></i> {{ $school->phone }}</span>
                        @endif
                        @if($school->city || $school->state)
                        <span class="flex items-center gap-1.5"><i class="fas fa-map-marker-alt text-gray-400 text-xs"></i> {{ $school->city?->name }}{{ $school->state ? ', ' . $school->state?->name : '' }}</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Stats Row ── --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <x-stat-card label="Students"  :value="$school->students()->count()"  icon="fas fa-user-graduate"      color="blue"   />
        <x-stat-card label="Teachers"  :value="$school->teachers()->count()"  icon="fas fa-chalkboard-teacher" color="emerald"/>
        <x-stat-card label="Classes"   :value="$school->classes()->count()"   icon="fas fa-door-open"          color="amber"  />
        <x-stat-card label="Users"     :value="$school->users()->count()"     icon="fas fa-users"              color="indigo" />
    </div>

    {{-- ── Main Grid ── --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- LEFT COLUMN --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- General Information --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="flex items-center gap-3 px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/60 dark:bg-gray-800/60">
                    <div class="w-8 h-8 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center text-blue-600 dark:text-blue-400">
                        <i class="fas fa-info text-xs"></i>
                    </div>
                    <h3 class="text-sm font-bold text-gray-800 dark:text-white">General Information</h3>
                </div>
                <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-5">
                    @include('admin.schools.partials._detail_row', ['icon' => 'fa-school',       'label' => 'School Name',  'value' => $school->name])
                    @include('admin.schools.partials._detail_row', ['icon' => 'fa-fingerprint',  'label' => 'School Code',  'value' => $school->code])
                    @include('admin.schools.partials._detail_row', ['icon' => 'fa-link',         'label' => 'Subdomain',    'value' => $school->subdomain . '.edusphere.local'])
                    @include('admin.schools.partials._detail_row', ['icon' => 'fa-envelope',     'label' => 'Email',        'value' => $school->email ?? 'N/A'])
                    @include('admin.schools.partials._detail_row', ['icon' => 'fa-phone',        'label' => 'Phone',        'value' => $school->phone ?? 'N/A'])
                    @include('admin.schools.partials._detail_row', ['icon' => 'fa-globe',        'label' => 'Website',      'value' => $school->website ?? 'N/A'])
                </div>
            </div>

            {{-- Location --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="flex items-center gap-3 px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/60 dark:bg-gray-800/60">
                    <div class="w-8 h-8 rounded-lg bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center text-emerald-600 dark:text-emerald-400">
                        <i class="fas fa-map-marker-alt text-xs"></i>
                    </div>
                    <h3 class="text-sm font-bold text-gray-800 dark:text-white">Location</h3>
                </div>
                <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-5">
                    @include('admin.schools.partials._detail_row', ['icon' => 'fa-map-marker-alt','label' => 'Address',  'value' => $school->address ?? 'N/A'])
                    @include('admin.schools.partials._detail_row', ['icon' => 'fa-city',          'label' => 'City',     'value' => $school->city?->name ?? 'N/A'])
                    @include('admin.schools.partials._detail_row', ['icon' => 'fa-map',           'label' => 'State',    'value' => $school->state?->name ?? 'N/A'])
                    @include('admin.schools.partials._detail_row', ['icon' => 'fa-globe-asia',    'label' => 'Country',  'value' => $school->country?->name ?? 'N/A'])
                    @include('admin.schools.partials._detail_row', ['icon' => 'fa-mail-bulk',     'label' => 'Pincode',  'value' => $school->pincode ?? 'N/A'])
                </div>
            </div>

            {{-- Primary Administrator --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="flex items-center gap-3 px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/60 dark:bg-gray-800/60">
                    <div class="w-8 h-8 rounded-lg bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                        <i class="fas fa-user-shield text-xs"></i>
                    </div>
                    <h3 class="text-sm font-bold text-gray-800 dark:text-white">Primary Administrator</h3>
                </div>
                <div class="p-6">
                    @if($admin)
                    <div class="flex items-center gap-5">
                        <div class="w-14 h-14 rounded-2xl bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-100 dark:border-indigo-800/40 flex items-center justify-center shrink-0">
                            <i class="fas fa-user text-indigo-500 text-xl"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-base font-bold text-gray-900 dark:text-white truncate">{{ $admin->name }}</p>
                            <p class="text-sm text-blue-600 dark:text-blue-400 truncate">{{ $admin->email }}</p>
                        </div>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400 shrink-0">
                            School Admin
                        </span>
                    </div>
                    @else
                    <div class="flex flex-col items-center py-6 text-center">
                        <div class="w-14 h-14 rounded-2xl bg-amber-50 dark:bg-amber-900/20 flex items-center justify-center mb-3 border border-amber-100 dark:border-amber-800/40">
                            <i class="fas fa-user-slash text-amber-500 text-xl"></i>
                        </div>
                        <p class="text-sm font-semibold text-gray-700 dark:text-white mb-1">No Admin Assigned</p>
                        <p class="text-xs text-gray-400 mb-4">This school has no active administrator account.</p>
                        <a href="{{ route('admin.schools.edit', $school->id) }}"
                            class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-xl transition-all">
                            <i class="fas fa-plus mr-1.5"></i> Assign Admin
                        </a>
                    </div>
                    @endif
                </div>
            </div>

        </div>{{-- end left --}}

        {{-- RIGHT COLUMN --}}
        <div class="space-y-6">

            {{-- Subscription --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="flex items-center gap-3 px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/60 dark:bg-gray-800/60">
                    <div class="w-8 h-8 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center text-blue-600 dark:text-blue-400">
                        <i class="fas fa-gem text-xs"></i>
                    </div>
                    <h3 class="text-sm font-bold text-gray-800 dark:text-white">Subscription</h3>
                    @if($school->isSubscriptionActive())
                        <span class="ml-auto inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700">
                            <i class="fas fa-circle text-[6px]"></i> Active
                        </span>
                    @else
                        <span class="ml-auto inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-100 text-rose-600">
                            <i class="fas fa-circle text-[6px]"></i> Expired
                        </span>
                    @endif
                </div>
                <div class="p-5">
                    @if($school->subscription_end_date)
                    @php
                        $daysRem = now()->diffInDays($school->subscription_end_date, false);
                        $total   = $school->subscription_start_date?->diffInDays($school->subscription_end_date) ?? 365;
                        $elapsed = $school->subscription_start_date?->diffInDays(now()) ?? 0;
                        $perc    = $total > 0 ? min(100, max(0, ($elapsed / $total) * 100)) : 100;
                        $barColor = $daysRem > 60 ? 'bg-emerald-500' : ($daysRem > 14 ? 'bg-amber-500' : 'bg-rose-500');
                    @endphp
                    <div class="space-y-4">
                        <div class="flex items-end justify-between">
                            <div>
                                <p class="text-xs font-semibold text-gray-400 mb-1">Days Remaining</p>
                                <p class="text-3xl font-bold {{ $daysRem > 60 ? 'text-emerald-600' : ($daysRem > 14 ? 'text-amber-600' : 'text-rose-600') }}">
                                    {{ max(0, $daysRem) }}
                                </p>
                            </div>
                            <i class="fas fa-hourglass-half text-gray-300 text-2xl mb-1"></i>
                        </div>
                        <div class="w-full h-2 bg-gray-100 dark:bg-gray-700 rounded-full overflow-hidden">
                            <div class="h-full {{ $barColor }} rounded-full transition-all" style="width: {{ $perc }}%"></div>
                        </div>
                        <div class="grid grid-cols-2 gap-3 pt-1">
                            <div>
                                <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-0.5">Start Date</p>
                                <p class="text-xs font-bold text-gray-700 dark:text-gray-200">{{ $school->subscription_start_date?->format('d M Y') ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-0.5">End Date</p>
                                <p class="text-xs font-bold text-gray-700 dark:text-gray-200">{{ $school->subscription_end_date->format('d M Y') }}</p>
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="py-6 text-center">
                        <i class="fas fa-infinity text-3xl text-gray-300 mb-3 block"></i>
                        <p class="text-sm font-semibold text-gray-600 dark:text-gray-300">Lifetime Access</p>
                        <p class="text-xs text-gray-400 mt-1">No expiry date set</p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Quick Actions --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="flex items-center gap-3 px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/60 dark:bg-gray-800/60">
                    <div class="w-8 h-8 rounded-lg bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center text-amber-600 dark:text-amber-400">
                        <i class="fas fa-bolt text-xs"></i>
                    </div>
                    <h3 class="text-sm font-bold text-gray-800 dark:text-white">Quick Actions</h3>
                </div>
                <div class="p-4 space-y-2">
                    <a href="{{ route('admin.schools.edit', $school->id) }}"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-indigo-50 dark:hover:bg-indigo-900/20 text-gray-700 dark:text-gray-300 hover:text-indigo-700 dark:hover:text-indigo-300 transition-all group">
                        <div class="w-8 h-8 rounded-lg bg-indigo-50 dark:bg-indigo-900/20 flex items-center justify-center text-indigo-500 group-hover:bg-indigo-600 group-hover:text-white transition-all">
                            <i class="fas fa-edit text-xs"></i>
                        </div>
                        <span class="text-sm font-semibold">Edit School Profile</span>
                        <i class="fas fa-chevron-right text-[10px] ml-auto text-gray-300 group-hover:text-indigo-400 transition-colors"></i>
                    </a>
                    <a href="{{ route('admin.schools.features', $school->id) }}"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-purple-50 dark:hover:bg-purple-900/20 text-gray-700 dark:text-gray-300 hover:text-purple-700 dark:hover:text-purple-300 transition-all group">
                        <div class="w-8 h-8 rounded-lg bg-purple-50 dark:bg-purple-900/20 flex items-center justify-center text-purple-500 group-hover:bg-purple-600 group-hover:text-white transition-all">
                            <i class="fas fa-toggle-on text-xs"></i>
                        </div>
                        <span class="text-sm font-semibold">Manage Features</span>
                        <i class="fas fa-chevron-right text-[10px] ml-auto text-gray-300 group-hover:text-purple-400 transition-colors"></i>
                    </a>
                </div>
            </div>

            {{-- School Logo & Icon --}}
            @if($school->logo || $school->site_icon)
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="flex items-center gap-3 px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/60 dark:bg-gray-800/60">
                    <div class="w-8 h-8 rounded-lg bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center text-purple-600 dark:text-purple-400">
                        <i class="fas fa-images text-xs"></i>
                    </div>
                    <h3 class="text-sm font-bold text-gray-800 dark:text-white">Branding</h3>
                </div>
                <div class="p-5 grid grid-cols-2 gap-4">
                    <div class="text-center">
                        <div class="w-full aspect-square rounded-xl overflow-hidden bg-gray-100 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 mb-1.5 flex items-center justify-center">
                            @if($school->logo)
                                <img src="{{ asset('storage/' . $school->logo) }}" alt="Logo" class="w-full h-full object-contain p-2">
                            @else
                                <i class="fas fa-school text-gray-300 text-2xl"></i>
                            @endif
                        </div>
                        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Logo</p>
                    </div>
                    <div class="text-center">
                        <div class="w-full aspect-square rounded-xl overflow-hidden bg-gray-100 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 mb-1.5 flex items-center justify-center">
                            @if($school->site_icon)
                                <img src="{{ asset('storage/' . $school->site_icon) }}" alt="Icon" class="w-full h-full object-contain p-2">
                            @else
                                <i class="fas fa-star text-gray-300 text-2xl"></i>
                            @endif
                        </div>
                        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Site Icon</p>
                    </div>
                </div>
            </div>
            @endif

        </div>{{-- end right --}}
    </div>

</div>
@endsection
