@extends('layouts.admin')

@section('title', 'Schools Management')

@section('content')
<div class="space-y-6"
     x-data="ajaxDataTable({
         fetchUrl: '{{ route('admin.schools.data') }}',
         defaultSort: 'id',
         defaultDirection: 'desc',
         defaultPerPage: 15,
         defaultFilters: { status: '', subscription_status: '' },
         initialRows: @js($initialData['rows']),
         initialPagination: @js($initialData['pagination']),
         initialStats: @js($initialData['stats']),
         filterLabels: {
             status: {
                 {{ \App\Enums\SchoolStatus::Active->value }}: '{{ \App\Enums\SchoolStatus::Active->label() }}',
                 {{ \App\Enums\SchoolStatus::Inactive->value }}: '{{ \App\Enums\SchoolStatus::Inactive->label() }}',
                 {{ \App\Enums\SchoolStatus::Suspended->value }}: '{{ \App\Enums\SchoolStatus::Suspended->label() }}'
             },
             subscription_status: { 1: 'Active Subscription', 0: 'Expired Subscription' }
         }
     })">

    <!-- Statistics Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <x-stat-card label="Total Schools"     :value="$stats['total'] ?? '—'"     icon="fas fa-university"          color="blue"   alpine-text="stats.total ?? '—'" />
        <x-stat-card label="Active Schools"    :value="$stats['active'] ?? '—'"    icon="fas fa-check-double"        color="emerald" alpine-text="stats.active ?? '—'" />
        <x-stat-card label="Inactive Schools"  :value="$stats['inactive'] ?? '—'"  icon="fas fa-times-circle"        color="rose"   alpine-text="stats.inactive ?? '—'" />
        <x-stat-card label="Suspended Schools" :value="$stats['suspended'] ?? '—'" icon="fas fa-exclamation-triangle" color="amber"  alpine-text="stats.suspended ?? '—'" />
    </div>

    <!-- Page Header -->
    <x-page-header title="Schools Management" description="Manage and track all schools and their configurations." icon="fas fa-university">
        <a href="{{ route('admin.schools.create') }}"
            class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white text-sm font-semibold rounded-xl transition-all shadow-md hover:shadow-lg active:scale-95">
            <i class="fas fa-plus mr-2 text-xs"></i> Add New School
        </a>
        <button @click="exportData('csv')" :disabled="exporting"
            class="min-w-[140px] justify-center inline-flex items-center px-4 py-2 bg-gradient-to-r from-slate-700 to-slate-900 hover:from-black hover:to-slate-800 text-white text-sm font-semibold rounded-xl transition-all shadow-md hover:shadow-lg active:scale-95 disabled:opacity-50">
            <span x-show="exporting" class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin mr-2 inline-block" x-cloak></span>
            <i x-show="!exporting" class="fas fa-file-excel mr-2 text-xs"></i>
            <span x-text="exporting ? 'Exporting...' : 'Excel Export'">Excel Export</span>
        </button>
    </x-page-header>

    <!-- Data Table Card -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">

        <!-- Table Header -->
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div class="flex-1 flex flex-col md:flex-row md:items-center gap-4">
                    <h2 class="text-lg font-semibold text-gray-800 dark:text-white whitespace-nowrap">All Schools</h2>
                    <x-table.search placeholder="Search schools..." />
                </div>
                <div class="flex items-center gap-3 flex-wrap">
                    <x-table.filter-select
                        model="filters.status"
                        action="applyFilter('status', $event.target.value)"
                        placeholder="Status"
                        :options="collect(\App\Enums\SchoolStatus::cases())->mapWithKeys(fn($s) => [$s->value => $s->label()])->toArray()"
                    />
                    <x-table.filter-select
                        model="filters.subscription_status"
                        action="applyFilter('subscription_status', $event.target.value)"
                        placeholder="Subscription"
                        :options="[1 => 'Active Subscription', 0 => 'Expired Subscription']"
                    />
                    <x-table.per-page model="perPage" action="changePerPage($event.target.value)" />
                </div>
            </div>

            <!-- Active Filters -->
            <div class="mt-3 flex flex-wrap gap-2" x-show="hasActiveFilters() || search !== ''" x-cloak>
                <div x-show="search !== ''" class="flex items-center gap-1 bg-purple-100 dark:bg-purple-900/30 text-purple-800 dark:text-purple-300 px-3 py-1 rounded-full text-xs">
                    <span>Search: <span x-text="search" class="font-semibold"></span></span>
                    <button @click="search = ''" class="ml-1 hover:text-purple-600"><i class="fas fa-times"></i></button>
                </div>
                <template x-for="(value, key) in filters" :key="key">
                    <div x-show="value" class="flex items-center gap-1 bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300 px-3 py-1 rounded-full text-xs">
                        <span x-text="getFilterLabel(key, value)"></span>
                        <button @click="removeFilter(key)" class="ml-1 hover:text-blue-600"><i class="fas fa-times"></i></button>
                    </div>
                </template>
                <button @click="clearAllFilters()" class="flex items-center gap-1 bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400 px-3 py-1 rounded-full text-xs hover:bg-red-200 dark:hover:bg-red-900/50 transition-colors">
                    <i class="fas fa-times-circle"></i> Clear All
                </button>
            </div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto relative ajax-table-wrapper">
            <x-table.loading-overlay message="Loading schools..." />

            <table class="w-full text-left border-collapse">
                <thead class="bg-gray-50/50 dark:bg-gray-700/50 border-b border-gray-100 dark:border-gray-700">
                    <tr>
                        <x-table.sort-header column="id"     label="ID" />
                        <x-table.sort-header column="name"   label="School Name" />
                        <x-table.sort-header column="code"   label="Code" />
                        <th class="px-6 py-3 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Subdomain</th>
                        <x-table.sort-header column="email"  label="Email" />
                        <x-table.sort-header column="status" label="Status" />
                        <th class="px-6 py-3 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Subscription</th>
                        <th class="px-6 py-3 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider text-center w-28">Actions</th>
                    </tr>
                </thead>

                {{-- Server-rendered rows --}}
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700" :class="{ 'hidden': true }">
                    @foreach($schools as $school)
                    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/30 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-500 dark:text-gray-400">#{{ $school->id }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-3">
                                @if($school->logo)
                                    <img src="{{ asset('storage/' . $school->logo) }}" alt="{{ $school->name }}" class="w-9 h-9 rounded-xl object-cover shadow-sm">
                                @else
                                    <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-500 flex items-center justify-center text-white font-bold text-xs shadow-sm">
                                        {{ strtoupper(substr($school->name, 0, 2)) }}
                                    </div>
                                @endif
                                <div>
                                    <div class="text-sm font-bold text-gray-800 dark:text-gray-100">{{ $school->name }}</div>
                                    @if($school->city || $school->state)
                                        <div class="text-[10px] text-gray-400">{{ trim(($school->city->name ?? '') . ', ' . ($school->state->name ?? ''), ', ') }}</div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-[10px] font-bold uppercase tracking-wider rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">{{ $school->code }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-xs font-medium text-gray-600 dark:text-gray-300">
                            {{ $school->subdomain }}<span class="text-gray-400">.edusphere</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-600 dark:text-gray-300">{{ $school->email }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $sc = match($school->status) {
                                    \App\Enums\SchoolStatus::Active    => 'bg-emerald-50 text-emerald-700 border-emerald-100 dark:bg-emerald-900/20 dark:text-emerald-400 dark:border-emerald-800/30',
                                    \App\Enums\SchoolStatus::Inactive  => 'bg-gray-100 text-gray-600 border-gray-200 dark:bg-gray-700 dark:text-gray-400 dark:border-gray-600',
                                    \App\Enums\SchoolStatus::Suspended => 'bg-amber-50 text-amber-700 border-amber-100 dark:bg-amber-900/20 dark:text-amber-400 dark:border-amber-800/30',
                                };
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full border text-[10px] font-bold uppercase tracking-wider {{ $sc }}">
                                {{ $school->status->label() }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($school->subscription_end_date)
                                <div class="text-[10px] font-semibold text-gray-600 dark:text-gray-300">Until {{ $school->subscription_end_date->format('M d, Y') }}</div>
                                <span class="text-[10px] font-bold {{ $school->isSubscriptionActive() ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-500 dark:text-red-400' }}">
                                    {{ $school->isSubscriptionActive() ? '● Active' : '● Expired' }}
                                </span>
                            @else
                                <span class="text-[10px] text-gray-400 dark:text-gray-500">No limit</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <div class="flex items-center justify-center gap-1.5">
                                <a href="{{ route('admin.schools.show', $school->id) }}" class="w-8 h-8 rounded-lg bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 flex items-center justify-center hover:bg-blue-100 dark:hover:bg-blue-900/40 transition-colors" title="View"><i class="fas fa-eye text-xs"></i></a>
                                <a href="{{ route('admin.schools.edit', $school->id) }}" class="w-8 h-8 rounded-lg bg-amber-50 dark:bg-amber-900/20 text-amber-600 dark:text-amber-400 flex items-center justify-center hover:bg-amber-100 dark:hover:bg-amber-900/40 transition-colors" title="Edit"><i class="fas fa-edit text-xs"></i></a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>

                {{-- Alpine rows --}}
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700 transition-opacity duration-150" x-cloak
                    :class="loading && rows.length > 0 ? 'opacity-50' : 'opacity-100'">
                    <template x-for="school in rows" :key="school.id">
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/30 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-500 dark:text-gray-400" x-text="'#' + school.id"></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <template x-if="school.logo_url">
                                        <img :src="school.logo_url" :alt="school.name" class="w-9 h-9 rounded-xl object-cover shadow-sm">
                                    </template>
                                    <template x-if="!school.logo_url">
                                        <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-500 flex items-center justify-center text-white font-bold text-xs shadow-sm" x-text="school.initials"></div>
                                    </template>
                                    <div>
                                        <div class="text-sm font-bold text-gray-800 dark:text-gray-100" x-text="school.name"></div>
                                        <div class="text-[10px] text-gray-400" x-show="school.location" x-text="school.location"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-[10px] font-bold uppercase tracking-wider rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300" x-text="school.code"></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-xs font-medium text-gray-600 dark:text-gray-300">
                                <span x-text="school.subdomain"></span><span class="text-gray-400">.edusphere</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-600 dark:text-gray-300" x-text="school.email"></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full border text-[10px] font-bold uppercase tracking-wider"
                                    :class="{
                                        'bg-emerald-50 text-emerald-700 border-emerald-100 dark:bg-emerald-900/20 dark:text-emerald-400 dark:border-emerald-800/30': school.status_color === 'green',
                                        'bg-gray-100 text-gray-600 border-gray-200 dark:bg-gray-700 dark:text-gray-400 dark:border-gray-600': school.status_color === 'gray',
                                        'bg-amber-50 text-amber-700 border-amber-100 dark:bg-amber-900/20 dark:text-amber-400 dark:border-amber-800/30': school.status_color === 'yellow',
                                    }"
                                    x-text="school.status_label">
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <template x-if="school.subscription_end_date">
                                    <div>
                                        <div class="text-[10px] font-semibold text-gray-600 dark:text-gray-300">Until <span x-text="school.subscription_end_date"></span></div>
                                        <span class="text-[10px] font-bold" :class="school.subscription_active ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-500 dark:text-red-400'"
                                            x-text="school.subscription_active ? '● Active' : '● Expired'"></span>
                                    </div>
                                </template>
                                <template x-if="!school.subscription_end_date">
                                    <span class="text-[10px] text-gray-400 dark:text-gray-500">No limit</span>
                                </template>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <a :href="school.show_url" class="w-8 h-8 rounded-lg bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 flex items-center justify-center hover:bg-blue-100 dark:hover:bg-blue-900/40 transition-colors" title="View"><i class="fas fa-eye text-xs"></i></a>
                                    <a :href="school.edit_url" class="w-8 h-8 rounded-lg bg-amber-50 dark:bg-amber-900/20 text-amber-600 dark:text-amber-400 flex items-center justify-center hover:bg-amber-100 dark:hover:bg-amber-900/40 transition-colors" title="Edit"><i class="fas fa-edit text-xs"></i></a>
                                    <button @click="window._deleteSchool(school)" class="w-8 h-8 rounded-lg bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 flex items-center justify-center hover:bg-red-100 dark:hover:bg-red-900/40 transition-colors" title="Delete"><i class="fas fa-trash text-xs"></i></button>
                                </div>
                            </td>
                        </tr>
                    </template>
                    <x-table.empty-state :colspan="8" icon="fas fa-school" message="No schools found. Get started by creating your first school." />
                </tbody>
            </table>
        </div>

        @if($schools->total() > 0)
        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50" :class="{ 'hidden': true }">
            <div class="text-sm text-gray-600 dark:text-gray-400">
                Showing {{ $schools->firstItem() }} to {{ $schools->lastItem() }} of {{ $schools->total() }} results
            </div>
        </div>
        @endif

        <x-table.pagination />
    </div>

    <x-confirm-modal />
</div>

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        window._deleteSchool = function(school) {
            window.dispatchEvent(new CustomEvent('open-confirm-modal', {
                detail: {
                    title: 'Delete School',
                    message: `Are you sure you want to delete "${school.name}"? This action cannot be undone.`,
                    callback: async () => {
                        try {
                            const response = await axios.post(school.delete_url, { _method: 'DELETE' }, {
                                headers: { 'Accept': 'application/json' }
                            });
                            if (response.data.success) {
                                window.dispatchEvent(new CustomEvent('show-toast', { detail: { message: response.data.message, type: 'success' } }));
                                const tableEl = document.querySelector('[x-data*="ajaxDataTable"]');
                                if (tableEl && tableEl._x_dataStack) tableEl._x_dataStack[0].fetchData();
                            }
                        } catch (error) {
                            window.dispatchEvent(new CustomEvent('show-toast', { detail: { message: error.response?.data?.message || 'Delete failed.', type: 'error' } }));
                        }
                    }
                }
            }));
        };
    });
</script>
@endpush
@endsection
