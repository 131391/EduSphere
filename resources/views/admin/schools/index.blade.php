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
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <x-stat-card label="Total Schools" :value="$stats['total'] ?? '—'" icon="fas fa-university" color="blue" alpine-text="stats.total ?? '—'" />
        <x-stat-card label="Active Schools" :value="$stats['active'] ?? '—'" icon="fas fa-check-double" color="green" alpine-text="stats.active ?? '—'" />
        <x-stat-card label="Inactive Schools" :value="$stats['inactive'] ?? '—'" icon="fas fa-times-circle" color="red" alpine-text="stats.inactive ?? '—'" />
        <x-stat-card label="Suspended Schools" :value="$stats['suspended'] ?? '—'" icon="fas fa-exclamation-triangle" color="amber" alpine-text="stats.suspended ?? '—'" />
    </div>

    <!-- Header Section -->
    <x-page-header title="Schools Management" description="Manage and track all schools and their configurations in the system." icon="fas fa-university">
        <a href="{{ route('admin.schools.create') }}"
            class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white text-sm font-semibold rounded-xl transition-all shadow-md hover:shadow-lg active:scale-95">
            <i class="fas fa-plus mr-2 text-xs"></i>
            Add New School
        </a>
        <button @click="exportData('csv')" :disabled="exporting"
            class="min-w-[140px] justify-center inline-flex items-center px-4 py-2 bg-gradient-to-r from-slate-700 to-slate-900 hover:from-black hover:to-slate-800 text-white text-sm font-semibold rounded-xl transition-all shadow-md hover:shadow-lg active:scale-95 disabled:opacity-50">
            <span x-show="exporting" class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin mr-2 inline-block" x-cloak></span>
            <i x-show="!exporting" class="fas fa-file-excel mr-2 text-xs"></i>
            <span x-text="exporting ? 'Exporting...' : 'Excel Export'">Excel Export</span>
        </button>
    </x-page-header>

    <!-- AJAX Data Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <!-- Table Header with Search and Filters -->
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <!-- Left: Title and Search -->
                <div class="flex-1 flex flex-col md:flex-row md:items-center gap-4">
                    <h2 class="text-lg font-semibold text-gray-800">All Schools</h2>
                    <x-table.search placeholder="Search..." />
                </div>

                <!-- Right: Filters and Actions -->
                <div class="flex items-center gap-3">
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

                    <button
                        @click="exportData('csv')"
                        :disabled="exporting"
                        class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 text-sm flex items-center disabled:opacity-50"
                        title="Export"
                    >
                        <i class="fas fa-download mr-2"></i>
                        Export
                    </button>
                </div>
            </div>

            <!-- Active Filters Display -->
            <div class="mt-3 flex flex-wrap gap-2" x-show="hasActiveFilters()" x-cloak>
                <template x-for="(value, key) in filters" :key="key">
                    <div x-show="value" class="flex items-center gap-1 bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-xs">
                        <span x-text="getFilterLabel(key, value)"></span>
                        <button @click="removeFilter(key)" class="ml-1 hover:text-blue-600">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </template>
                <button @click="clearAllFilters()" class="flex items-center gap-1 bg-red-100 text-red-700 px-3 py-1 rounded-full text-xs hover:bg-red-200 transition-colors">
                    <i class="fas fa-times-circle"></i>
                    <span>Clear All</span>
                </button>
            </div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto relative ajax-table-wrapper">
            <x-table.loading-overlay message="Loading schools..." />

            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <x-table.sort-header column="id" label="ID" />
                        <x-table.sort-header column="name" label="School Name" />
                        <x-table.sort-header column="code" label="Code" />
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subdomain</th>
                        <x-table.sort-header column="email" label="Email" />
                        <x-table.sort-header column="status" label="Status" />
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subscription</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>

                {{-- Server-rendered rows: visible instantly, hidden once Alpine initializes --}}
                <tbody class="bg-white divide-y divide-gray-200" :class="{ 'hidden': true }">
                    @foreach($schools as $school)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $school->id }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <div class="flex items-center">
                                @if($school->logo)
                                    <img src="{{ asset('storage/' . $school->logo) }}" alt="{{ $school->name }}" class="w-10 h-10 rounded-full mr-3 object-cover">
                                @else
                                    <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                                        <i class="fas fa-school text-blue-600"></i>
                                    </div>
                                @endif
                                <div>
                                    <div class="text-sm font-medium text-gray-900">{{ $school->name }}</div>
                                    @if($school->city || $school->state)
                                        <div class="text-sm text-gray-500">{{ trim(($school->city->name ?? '') . ', ' . ($school->state->name ?? ''), ', ') }}</div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $school->code }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $school->subdomain }}
                            @if($school->domain)
                                <div class="text-xs text-gray-500">{{ $school->domain }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $school->email }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full {{ match($school->status) {
                                \App\Enums\SchoolStatus::Active => 'bg-green-100 text-green-800',
                                \App\Enums\SchoolStatus::Inactive => 'bg-gray-100 text-gray-800',
                                \App\Enums\SchoolStatus::Suspended => 'bg-yellow-100 text-yellow-800',
                            } }}">{{ $school->status->label() }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            @if($school->subscription_end_date)
                                <div class="text-xs">
                                    <div>Until: {{ $school->subscription_end_date->format('M d, Y') }}</div>
                                    <span class="{{ $school->isSubscriptionActive() ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $school->isSubscriptionActive() ? 'Active' : 'Expired' }}
                                    </span>
                                </div>
                            @else
                                <span class="text-xs text-gray-500">No limit</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex items-center space-x-2">
                                <a href="{{ route('admin.schools.show', $school->id) }}" class="text-blue-600 hover:text-blue-900" title="View"><i class="fas fa-eye"></i></a>
                                <a href="{{ route('admin.schools.edit', $school->id) }}" class="text-yellow-600 hover:text-yellow-900" title="Edit"><i class="fas fa-edit"></i></a>
                                <button class="text-red-600 hover:text-red-900" title="Delete" type="button"><i class="fas fa-trash"></i></button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>

                {{-- Alpine-managed rows: takes over once initialized --}}
                <tbody class="bg-white divide-y divide-gray-200 transition-opacity duration-150" x-cloak :class="loading && rows.length &gt; 0 ? 'opacity-50' : 'opacity-100'">
                    <template x-for="school in rows" :key="school.id">
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" x-text="school.id"></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <div class="flex items-center">
                                    <template x-if="school.logo_url">
                                        <img :src="school.logo_url" :alt="school.name" class="w-10 h-10 rounded-full mr-3 object-cover">
                                    </template>
                                    <template x-if="!school.logo_url">
                                        <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                                            <i class="fas fa-school text-blue-600"></i>
                                        </div>
                                    </template>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900" x-text="school.name"></div>
                                        <div class="text-sm text-gray-500" x-show="school.location" x-text="school.location"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" x-text="school.code"></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <span x-text="school.subdomain"></span>
                                <div class="text-xs text-gray-500" x-show="school.domain" x-text="school.domain"></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" x-text="school.email"></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full"
                                      :class="{
                                          'bg-green-100 text-green-800': school.status_color === 'green',
                                          'bg-gray-100 text-gray-800': school.status_color === 'gray',
                                          'bg-yellow-100 text-yellow-800': school.status_color === 'yellow',
                                      }"
                                      x-text="school.status_label">
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <template x-if="school.subscription_end_date">
                                    <div class="text-xs">
                                        <div>Until: <span x-text="school.subscription_end_date"></span></div>
                                        <span :class="school.subscription_active ? 'text-green-600' : 'text-red-600'"
                                              x-text="school.subscription_active ? 'Active' : 'Expired'"></span>
                                    </div>
                                </template>
                                <template x-if="!school.subscription_end_date">
                                    <span class="text-xs text-gray-500">No limit</span>
                                </template>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center space-x-2">
                                    <a :href="school.show_url" class="text-blue-600 hover:text-blue-900" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a :href="school.edit_url" class="text-yellow-600 hover:text-yellow-900" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button @click="window._deleteSchool(school)" class="text-red-600 hover:text-red-900" title="Delete" type="button">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>

                    <x-table.empty-state :colspan="8" icon="fas fa-school" message="No schools found. Get started by creating your first school." />
                </tbody>
            </table>
        </div>

        <!-- Server-rendered pagination info: visible instantly, hidden once Alpine takes over -->
        @if($schools->total() > 0)
        <div class="px-6 py-4 border-t border-gray-200 bg-gray-50" :class="{ 'hidden': true }">
            <div class="text-sm text-gray-700">
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
                            const response = await axios.post(school.delete_url, {
                                _method: 'DELETE'
                            }, {
                                headers: { 'Accept': 'application/json' }
                            });

                            if (response.data.success) {
                                window.dispatchEvent(new CustomEvent('show-toast', {
                                    detail: { message: response.data.message, type: 'success' }
                                }));
                                const tableEl = document.querySelector('[x-data*="ajaxDataTable"]');
                                if (tableEl && tableEl._x_dataStack) {
                                    tableEl._x_dataStack[0].fetchData();
                                }
                            }
                        } catch (error) {
                            window.dispatchEvent(new CustomEvent('show-toast', {
                                detail: { message: error.response?.data?.message || 'Delete failed.', type: 'error' }
                            }));
                        }
                    }
                }
            }));
        };
    });
</script>
@endpush
@endsection
