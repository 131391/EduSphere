@extends('layouts.receptionist')

@section('title', 'Hostel Floors - Receptionist')
@section('page-title', 'Hostel Floors')
@section('page-description', 'Manage floors inside each hostel')

@section('content')
    <div x-data="Object.assign(ajaxDataTable({
            fetchUrl: '{{ route('receptionist.hostel-floors.fetch') }}',
            defaultSort: 'created_at',
            defaultDirection: 'desc',
            defaultPerPage: 25,
            defaultFilters: { hostel_id: '' },
            initialRows: @js($initialData['rows']),
            initialPagination: @js($initialData['pagination']),
            initialStats: @js($stats),
            filterLabels: {
                hostel_id: {
                    @foreach($hostels as $h) '{{ $h->id }}': '{{ $h->hostel_name }}', @endforeach
                }
            }
        }), floorForm())" class="space-y-6" @close-modal.window="if($event.detail === 'floor-modal') resetForm()">

        {{-- Statistics --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <x-stat-card label="Total Hostels" :value="$stats['total_hostels']" icon="fas fa-building" color="blue"
                alpine-text="stats.total_hostels" />
            <x-stat-card label="Total Floors" :value="$stats['total_floors']" icon="fas fa-layer-group" color="indigo"
                alpine-text="stats.total_floors" />
            <x-stat-card label="Total Rooms" :value="$stats['total_rooms']" icon="fas fa-door-open" color="emerald"
                alpine-text="stats.total_rooms" />
        </div>

        {{-- Header --}}
        <x-page-header title="Hostel Floors" description="Add floors to each hostel and set room counts" icon="fas fa-layer-group">
            <button @click="open()"
                class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-teal-600 to-emerald-600 hover:from-teal-700 hover:to-emerald-700 text-white text-sm font-semibold rounded-xl transition-all shadow-md hover:shadow-lg active:scale-95">
                <i class="fas fa-plus mr-2 text-xs"></i>
                Add Floor
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
                        <h2 class="text-lg font-semibold text-gray-800 dark:text-white">Floor List</h2>
                        <x-table.search placeholder="Search floors or hostels..." />
                    </div>
                    <div class="flex items-center gap-3">
                        <x-table.filter-select
                            model="filters.hostel_id"
                            action="applyFilter('hostel_id', $event.target.value)"
                            placeholder="All Hostels"
                            :options="collect($hostels)->mapWithKeys(fn($h) => [$h->id => $h->hostel_name])->toArray()"
                        />
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
                            <x-table.sort-header column="floor_name" label="Floor Name" sort-var="sort" direction-var="direction" />
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Hostel</th>
                            <x-table.sort-header column="total_room" label="Rooms" sort-var="sort" direction-var="direction" />
                            <x-table.sort-header column="floor_create_date" label="Established" sort-var="sort" direction-var="direction" />
                            <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider w-32">Actions</th>
                        </tr>
                    </thead>

                    {{-- Server-rendered rows (Hidden once Alpine initializes) --}}
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700" x-show="!hydrated" x-cloak>
                        @if(empty($initialData['rows']))
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <i class="fas fa-layer-group text-4xl text-gray-300 mb-4"></i>
                                    <p class="text-lg text-gray-500">No floors found matching your criteria.</p>
                                </div>
                            </td>
                        </tr>
                        @endif
                        @foreach($initialData['rows'] as $row)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/50 transition-colors group">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-900/40 flex items-center justify-center text-indigo-500 dark:text-indigo-400 group-hover:bg-indigo-100 dark:group-hover:bg-indigo-900/60">
                                        <i class="fas fa-layer-group"></i>
                                    </div>
                                    <span class="font-bold text-gray-800 dark:text-gray-100">{{ $row['floor_name'] }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2 text-sm text-gray-600 font-medium">
                                    <i class="fas fa-building text-[10px] text-gray-400"></i>
                                    <span>{{ $row['hostel_name'] }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1 bg-emerald-50 text-emerald-700 rounded-full text-xs font-bold">{{ $row['total_room_label'] }}</span>
                            </td>
                            <td class="px-6 py-4 text-xs font-medium text-gray-500">{{ $row['floor_create_date'] }}</td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button @click="open({{ json_encode($row) }})" title="Edit"
                                        class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white transition-all flex items-center justify-center">
                                        <i class="fas fa-edit text-xs"></i>
                                    </button>
                                    <button @click="quickAction(`/receptionist/hostel-floors/{{ $row['id'] }}`, 'Delete Floor', 'DELETE')" title="Delete"
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
                                            <i class="fas fa-layer-group"></i>
                                        </div>
                                        <span class="font-bold text-gray-800 dark:text-gray-100" x-text="row.floor_name"></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2 text-sm text-gray-600 font-medium">
                                        <i class="fas fa-building text-[10px] text-gray-400"></i>
                                        <span x-text="row.hostel_name"></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-3 py-1 bg-emerald-50 text-emerald-700 rounded-full text-xs font-bold" x-text="row.total_room_label"></span>
                                </td>
                                <td class="px-6 py-4 text-xs font-medium text-gray-500" x-text="row.floor_create_date"></td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button @click="open(row)" title="Edit"
                                            class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white transition-all flex items-center justify-center">
                                            <i class="fas fa-edit text-xs"></i>
                                        </button>
                                        <button @click="quickAction(`/receptionist/hostel-floors/${row.id}`, 'Delete Floor', 'DELETE')" title="Delete"
                                            class="w-8 h-8 rounded-lg bg-red-50 text-red-600 hover:bg-red-600 hover:text-white transition-all flex items-center justify-center">
                                            <i class="fas fa-trash-alt text-xs"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </template>

                        <x-table.empty-state :colspan="5" icon="fas fa-layer-group" message="No floors found matching your criteria." />
                    </tbody>
                </table>
            </div>
            <x-table.pagination :initial="$initialData['pagination']" />
        </div>

        <x-confirm-modal title="Delete Floor?"
            message="This will remove the floor. Make sure there are no rooms attached to this floor."
            confirm-text="Delete" confirm-color="red" />

        {{-- Add/Edit Floor Modal --}}
        <x-modal name="floor-modal" alpineTitle="editMode ? 'Edit Floor' : 'Add New Floor'" maxWidth="2xl">
            <form @submit.prevent="save" id="floorForm" class="p-1" novalidate>
                @csrf
                <template x-if="editMode">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="modal-label-premium">Hostel <span class="text-red-600 font-bold">*</span></label>
                        <select x-model="formData.hostel_id" @change="clearError('hostel_id')"
                            name="hostel_id"
                            class="no-select2 w-full bg-white dark:bg-gray-700 border rounded-xl py-3 px-4 text-sm font-bold text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-teal-500/20 transition-all"
                            :class="errors.hostel_id ? 'border-red-500' : 'border-slate-200 dark:border-gray-600'">
                            <option value="">Select Hostel</option>
                            @foreach($hostels as $hostel)
                                <option value="{{ $hostel->id }}">{{ $hostel->hostel_name }}</option>
                            @endforeach
                        </select>
                        <template x-if="errors.hostel_id">
                            <template x-if="errors.hostel_id[0]"><p class="modal-error-message" x-text="errors.hostel_id[0]"></p></template>
                        </template>
                    </div>

                    <div class="space-y-2">
                        <label class="modal-label-premium">Floor Name <span class="text-red-600 font-bold">*</span></label>
                        <input type="text" x-model="formData.floor_name" @input="clearError('floor_name')"
                            placeholder="e.g., Ground Floor, First Floor"
                            class="w-full bg-white dark:bg-gray-700 border rounded-xl py-3 px-4 text-sm font-bold text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-teal-500/20 transition-all"
                            :class="errors.floor_name ? 'border-red-500' : 'border-slate-200 dark:border-gray-600'">
                        <template x-if="errors.floor_name">
                            <template x-if="errors.floor_name[0]"><p class="modal-error-message" x-text="errors.floor_name[0]"></p></template>
                        </template>
                    </div>

                    <div class="space-y-2">
                        <label class="modal-label-premium">Total Rooms</label>
                        <input type="number" x-model="formData.total_room" @input="clearError('total_room')"
                            placeholder="0" min="0"
                            class="w-full bg-white dark:bg-gray-700 border rounded-xl py-3 px-4 text-sm font-bold text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-teal-500/20 transition-all"
                            :class="errors.total_room ? 'border-red-500' : 'border-slate-200 dark:border-gray-600'">
                    </div>

                    <div class="space-y-2">
                        <label class="modal-label-premium">Established On</label>
                        <input type="date" x-model="formData.floor_create_date" @input="clearError('floor_create_date')"
                            class="w-full bg-white dark:bg-gray-700 border rounded-xl py-3 px-4 text-sm font-bold text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-teal-500/20 transition-all"
                            :class="errors.floor_create_date ? 'border-red-500' : 'border-slate-200 dark:border-gray-600'">
                    </div>
                </div>

                <div class="mt-6 bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-100 dark:border-indigo-800 p-4 rounded-2xl flex items-start gap-3">
                    <i class="fas fa-info-circle text-indigo-600 mt-0.5"></i>
                    <div class="flex flex-col">
                        <span class="text-xs font-bold text-slate-900 dark:text-gray-100">Tip</span>
                        <p class="text-[11px] text-slate-500 dark:text-gray-400 mt-1 leading-relaxed">
                            Once you add a floor, you can add rooms to it to start assigning beds to students.
                        </p>
                    </div>
                </div>
            </form>

            <x-slot name="footer">
                <button type="button" @click="$dispatch('close-modal', 'floor-modal')" :disabled="submitting"
                    class="px-6 py-2.5 text-xs font-bold text-slate-500 dark:text-gray-400 uppercase tracking-widest hover:text-slate-700 dark:hover:text-gray-200 transition-colors">
                    Cancel
                </button>
                <button type="submit" form="floorForm" :disabled="submitting"
                    class="bg-slate-900 hover:bg-black text-white px-8 py-2.5 rounded-xl text-xs font-bold uppercase tracking-widest transition-all shadow-md active:scale-95 disabled:opacity-50">
                    <template x-if="submitting">
                        <i class="fas fa-spinner animate-spin mr-2"></i>
                    </template>
                    <span x-text="submitting ? 'Saving...' : (editMode ? 'Update Floor' : 'Save Floor')"></span>
                </button>
            </x-slot>
        </x-modal>
    </div>

    @push('scripts')
        <script>
            function floorForm() {
                return {
                    editMode: false,
                    submitting: false,
                    floorId: null,
                    formData: {
                        hostel_id: '',
                        floor_name: '',
                        total_room: '',
                        floor_create_date: '',
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
                        this.floorId = null;
                        this.errors = {};
                        this.formData = {
                            hostel_id: '',
                            floor_name: '',
                            total_room: '',
                            floor_create_date: '',
                        };
                    },

                    open(floor = null) {
                        this.errors = {};
                        if (floor) {
                            this.editMode = true;
                            this.floorId = floor.id;
                            this.formData = { ...floor.raw };
                        } else {
                            this.editMode = false;
                            this.floorId = null;
                            this.formData = {
                                hostel_id: '',
                                floor_name: '',
                                total_room: '',
                                floor_create_date: '{{ date('Y-m-d') }}',
                            };
                        }
                        this.$dispatch('open-modal', 'floor-modal');
                    },

                    async save() {
                        if (!this.formData.hostel_id) {
                            this.errors.hostel_id = ['The hostel id field is required.'];
                            return;
                        }

                        this.submitting = true;
                        this.errors = {};
                        const url = this.editMode
                            ? `{{ route('receptionist.hostel-floors.index') }}/${this.floorId}`
                            : `{{ route('receptionist.hostel-floors.store') }}`;

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
                                this.$dispatch('close-modal', 'floor-modal');
                                this.fetchData();
                            } else {
                                this.errors = result.errors || {};
                                if (result.message && response.status !== 422) {
                                    if (window.Toast) window.Toast.fire({ icon: 'error', title: result.message });
                                }
                            }
                        } catch (e) {
                            if (window.Toast) window.Toast.fire({ icon: 'error', title: 'Failed to save floor' });
                        } finally {
                            this.submitting = false;
                        }
                    }
                }
            }
        </script>
    @endpush
@endsection
