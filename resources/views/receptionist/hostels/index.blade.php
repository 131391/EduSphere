@extends('layouts.receptionist')

@section('title', 'Hostels - Receptionist')
@section('page-title', 'Hostels')
@section('page-description', 'Manage hostel buildings')

@section('content')
    <div x-data="Object.assign(ajaxDataTable({
            fetchUrl: '{{ route('receptionist.hostels.fetch') }}',
            defaultSort: 'created_at',
            defaultDirection: 'desc',
            defaultPerPage: 25,
            defaultFilters: {},
            initialRows: @js($initialData['rows']),
            initialPagination: @js($initialData['pagination']),
            initialStats: @js($stats)
        }), hostelForm())" class="space-y-6" @close-modal.window="if($event.detail === 'hostel-modal') resetForm()">

        {{-- Statistics --}}
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <x-stat-card label="Total Hostels" :value="$stats['total_hostels']" icon="fas fa-building" color="blue"
                alpine-text="stats.total_hostels" />
            <x-stat-card label="Total Floors" :value="$stats['total_floors']" icon="fas fa-layer-group" color="indigo"
                alpine-text="stats.total_floors" />
            <x-stat-card label="Total Rooms" :value="$stats['total_rooms']" icon="fas fa-door-open" color="emerald"
                alpine-text="stats.total_rooms" />
            <x-stat-card label="Total Beds" :value="$stats['total_beds']" icon="fas fa-bed" color="purple"
                alpine-text="stats.total_beds" />
            <x-stat-card label="Residents" :value="$stats['total_residents']" icon="fas fa-user-graduate" color="pink"
                alpine-text="stats.total_residents" />
        </div>

        {{-- Header --}}
        <x-page-header title="Hostels" description="Manage hostel buildings and wardens" icon="fas fa-building">
            <button @click="open()"
                class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-teal-600 to-emerald-600 hover:from-teal-700 hover:to-emerald-700 text-white text-sm font-semibold rounded-xl transition-all shadow-md hover:shadow-lg active:scale-95">
                <i class="fas fa-plus mr-2 text-xs"></i>
                Add Hostel
            </button>
            <button @click="exportData('csv')" :disabled="exporting"
                class="min-w-[140px] justify-center inline-flex items-center px-4 py-2 bg-gradient-to-r from-slate-700 to-slate-900 hover:from-black hover:to-slate-800 text-white text-sm font-semibold rounded-xl transition-all shadow-md hover:shadow-lg active:scale-95 disabled:opacity-50">
                <span x-show="exporting" class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin mr-2 inline-block" x-cloak></span>
                <i x-show="!exporting" class="fas fa-file-excel mr-2 text-xs"></i>
                <span x-text="exporting ? 'Exporting...' : 'Excel Export'">Excel Export</span>
            </button>
        </x-page-header>

        {{-- AJAX Data Table --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div class="flex-1 flex flex-col md:flex-row md:items-center gap-4">
                        <h2 class="text-lg font-semibold text-gray-800 dark:text-white">Hostel List</h2>
                        <x-table.search placeholder="Search hostels..." />
                    </div>
                    <div class="flex items-center gap-3">
                        <x-table.per-page model="perPage" action="changePerPage($event.target.value)" :default="25" />
                    </div>
                </div>

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

            <div class="overflow-x-auto relative ajax-table-wrapper min-h-[400px]">
                <x-table.loading-overlay />

                <table class="w-full text-left border-collapse">
                    <thead class="bg-gray-50/50 dark:bg-gray-700/50 border-b border-gray-100 dark:border-gray-700">
                        <tr>
                            <x-table.sort-header column="hostel_name" label="Hostel Name" sort-var="sort" direction-var="direction" />
                            <x-table.sort-header column="hostel_incharge" label="Warden" sort-var="sort" direction-var="direction" />
                            <x-table.sort-header column="capability" label="Capacity" sort-var="sort" direction-var="direction" />
                            <x-table.sort-header column="hostel_create_date" label="Established" sort-var="sort" direction-var="direction" />
                            <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider w-32">Actions</th>
                        </tr>
                    </thead>

                    {{-- Server-rendered rows (Hidden once Alpine initializes) --}}
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700" x-show="!hydrated" x-cloak>
                        @if(empty($initialData['rows']))
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <i class="fas fa-bed text-4xl text-gray-300 mb-4"></i>
                                    <p class="text-lg text-gray-500">No hostels found matching your criteria.</p>
                                </div>
                            </td>
                        </tr>
                        @endif
                        @foreach($initialData['rows'] as $row)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/50 transition-colors group">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-900/40 flex items-center justify-center text-indigo-500 dark:text-indigo-400 group-hover:bg-indigo-100 dark:group-hover:bg-indigo-900/60">
                                        <i class="fas fa-building"></i>
                                    </div>
                                    <span class="font-bold text-gray-800 dark:text-gray-100">{{ $row['hostel_name'] }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2 text-sm text-gray-600 font-medium">
                                    <i class="fas fa-user-tie text-[10px] text-gray-400"></i>
                                    <span>{{ $row['hostel_incharge'] ?: 'Not Assigned' }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1 bg-emerald-50 text-emerald-700 rounded-full text-xs font-bold">{{ $row['capability_label'] }}</span>
                            </td>
                            <td class="px-6 py-4 text-xs font-medium text-gray-500">{{ $row['hostel_create_date'] }}</td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button @click="open({{ json_encode($row) }})" title="Edit"
                                        class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white transition-all flex items-center justify-center">
                                        <i class="fas fa-edit text-xs"></i>
                                    </button>
                                    <button @click="quickAction(`/receptionist/hostels/{{ $row['id'] }}`, 'Delete Hostel', 'DELETE')" title="Delete"
                                        class="w-8 h-8 rounded-lg bg-red-50 text-red-600 hover:bg-red-600 hover:text-white transition-all flex items-center justify-center">
                                        <i class="fas fa-trash-alt text-xs"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>

                    {{-- Alpine-managed rows --}}
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700 transition-opacity duration-150" x-cloak
                        :class="loading && rows.length &gt; 0 ? 'opacity-50' : 'opacity-100'">
                        <template x-for="row in rows" :key="row.id">
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/50 transition-colors group">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-900/40 flex items-center justify-center text-indigo-500 dark:text-indigo-400 group-hover:bg-indigo-100 dark:group-hover:bg-indigo-900/60">
                                            <i class="fas fa-building"></i>
                                        </div>
                                        <span class="font-bold text-gray-800 dark:text-gray-100" x-text="row.hostel_name"></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2 text-sm text-gray-600 font-medium">
                                        <i class="fas fa-user-tie text-[10px] text-gray-400"></i>
                                        <span x-text="row.hostel_incharge || 'Not Assigned'"></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-3 py-1 bg-emerald-50 text-emerald-700 rounded-full text-xs font-bold" x-text="row.capability_label"></span>
                                </td>
                                <td class="px-6 py-4 text-xs font-medium text-gray-500" x-text="row.hostel_create_date"></td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button @click="open(row)" title="Edit"
                                            class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white transition-all flex items-center justify-center">
                                            <i class="fas fa-edit text-xs"></i>
                                        </button>
                                        <button @click="quickAction(`/receptionist/hostels/${row.id}`, 'Delete Hostel', 'DELETE')" title="Delete"
                                            class="w-8 h-8 rounded-lg bg-red-50 text-red-600 hover:bg-red-600 hover:text-white transition-all flex items-center justify-center">
                                            <i class="fas fa-trash-alt text-xs"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </template>

                        <x-table.empty-state :colspan="5" icon="fas fa-bed" message="No hostels found matching your criteria." />
                    </tbody>
                </table>
            </div>
            <x-table.pagination />
        </div>

        <x-confirm-modal title="Delete Hostel?"
            message="This will remove the hostel from your list. Make sure there are no floors or rooms attached."
            confirm-text="Delete" confirm-color="red" />

        {{-- Add/Edit Hostel Modal --}}
        <x-modal name="hostel-modal" alpineTitle="editMode ? 'Edit Hostel' : 'Add New Hostel'" maxWidth="2xl">
            <form @submit.prevent="save" id="hostelForm" class="p-1" novalidate>
                @csrf
                <template x-if="editMode">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="modal-label-premium">Hostel Name <span class="text-red-600 font-bold">*</span></label>
                        <input type="text" x-model="formData.hostel_name" @input="clearError('hostel_name')"
                            placeholder="e.g., Aravali Boys Hostel"
                            class="w-full bg-white dark:bg-gray-700 border rounded-xl py-3 px-4 text-sm font-bold text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-teal-500/20 transition-all"
                            :class="errors.hostel_name ? 'border-red-500' : 'border-slate-200 dark:border-gray-600'">
                        <template x-if="errors.hostel_name">
                            <template x-if="errors.hostel_name[0]"><p class="modal-error-message" x-text="errors.hostel_name[0]"></p></template>
                        </template>
                    </div>

                    <div class="space-y-2">
                        <label class="modal-label-premium">Warden Name</label>
                        <input type="text" x-model="formData.hostel_incharge" @input="clearError('hostel_incharge')"
                            placeholder="e.g., Mr. Sharma"
                            class="w-full bg-white dark:bg-gray-700 border rounded-xl py-3 px-4 text-sm font-bold text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-teal-500/20 transition-all"
                            :class="errors.hostel_incharge ? 'border-red-500' : 'border-slate-200 dark:border-gray-600'">
                    </div>

                    <div class="space-y-2">
                        <label class="modal-label-premium">Total Bed Capacity</label>
                        <input type="number" x-model="formData.capability" @input="clearError('capability')"
                            placeholder="0" min="0"
                            class="w-full bg-white dark:bg-gray-700 border rounded-xl py-3 px-4 text-sm font-bold text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-teal-500/20 transition-all"
                            :class="errors.capability ? 'border-red-500' : 'border-slate-200 dark:border-gray-600'">
                    </div>

                    <div class="space-y-2">
                        <label class="modal-label-premium">Established On</label>
                        <input type="date" x-model="formData.hostel_create_date" @input="clearError('hostel_create_date')"
                            class="w-full bg-white dark:bg-gray-700 border rounded-xl py-3 px-4 text-sm font-bold text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-teal-500/20 transition-all"
                            :class="errors.hostel_create_date ? 'border-red-500' : 'border-slate-200 dark:border-gray-600'">
                    </div>
                </div>

                <div class="mt-6 bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-100 dark:border-indigo-800 p-4 rounded-2xl flex items-start gap-3">
                    <i class="fas fa-info-circle text-indigo-600 mt-0.5"></i>
                    <div class="flex flex-col">
                        <span class="text-xs font-bold text-slate-900 dark:text-gray-100">Tip</span>
                        <p class="text-[11px] text-slate-500 dark:text-gray-400 mt-1 leading-relaxed">
                            After adding a hostel, add its floors and rooms to start allocating beds to students.
                        </p>
                    </div>
                </div>
            </form>

            <x-slot name="footer">
                <button type="button" @click="$dispatch('close-modal', 'hostel-modal')" :disabled="submitting"
                    class="px-6 py-2.5 text-xs font-bold text-slate-500 dark:text-gray-400 uppercase tracking-widest hover:text-slate-700 dark:hover:text-gray-200 transition-colors">
                    Cancel
                </button>
                <button type="submit" form="hostelForm" :disabled="submitting"
                    class="bg-slate-900 hover:bg-black text-white px-8 py-2.5 rounded-xl text-xs font-bold uppercase tracking-widest transition-all shadow-md active:scale-95 disabled:opacity-50">
                    <template x-if="submitting">
                        <i class="fas fa-spinner animate-spin mr-2"></i>
                    </template>
                    <span x-text="submitting ? 'Saving...' : (editMode ? 'Update Hostel' : 'Save Hostel')"></span>
                </button>
            </x-slot>
        </x-modal>
    </div>

    @push('scripts')
        <script>
            function hostelForm() {
                return {
                    editMode: false,
                    submitting: false,
                    hostelId: null,
                    formData: {
                        hostel_name: '',
                        hostel_incharge: '',
                        capability: '',
                        hostel_create_date: '',
                    },
                    errors: {},

                    clearError(field) {
                        if (this.errors && this.errors[field]) {
                            const e = { ...this.errors };
                            delete e[field];
                            this.errors = e;
                        }
                    },

                    resetForm() {
                        this.editMode = false;
                        this.hostelId = null;
                        this.errors = {};
                        this.formData = {
                            hostel_name: '',
                            hostel_incharge: '',
                            capability: '',
                            hostel_create_date: '',
                        };
                    },

                    open(hostel = null) {
                        this.errors = {};
                        if (hostel) {
                            this.editMode = true;
                            this.hostelId = hostel.id;
                            this.formData = { ...hostel.raw };
                        } else {
                            this.editMode = false;
                            this.hostelId = null;
                            this.formData = {
                                hostel_name: '',
                                hostel_incharge: '',
                                capability: '',
                                hostel_create_date: '{{ date('Y-m-d') }}',
                            };
                        }
                        this.$dispatch('open-modal', 'hostel-modal');
                    },

                    async save() {
                        this.submitting = true;
                        this.errors = {};
                        const url = this.editMode
                            ? `{{ route('receptionist.hostels.index') }}/${this.hostelId}`
                            : `{{ route('receptionist.hostels.store') }}`;

                        try {
                            const response = await fetch(url, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify({
                                    ...this.formData,
                                    _method: this.editMode ? 'PUT' : 'POST'
                                })
                            });

                            const result = await response.json();
                            if (response.ok) {
                                if (window.Toast) window.Toast.fire({ icon: 'success', title: result.message });
                                this.$dispatch('close-modal', 'hostel-modal');
                                this.fetchData();
                            } else {
                                this.errors = result.errors || {};
                                if (result.message && response.status !== 422) {
                                    if (window.Toast) window.Toast.fire({ icon: 'error', title: result.message });
                                }
                            }
                        } catch (e) {
                            if (window.Toast) window.Toast.fire({ icon: 'error', title: 'Failed to save hostel' });
                        } finally {
                            this.submitting = false;
                        }
                    }
                }
            }
        </script>
    @endpush
@endsection
