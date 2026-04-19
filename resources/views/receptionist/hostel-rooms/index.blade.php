@php use App\Enums\YesNo; @endphp
@extends('layouts.receptionist')

@section('title', 'Room Inventory - Receptionist')
@section('page-title', 'Room Inventory')
@section('page-description', 'Manage residential units and amenities across hostel blocks')

@section('content')
    <div x-data="Object.assign(ajaxDataTable({
            fetchUrl: '{{ route('receptionist.hostel-rooms.fetch') }}',
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
        }), hostelRoomForm())" class="space-y-6" @close-modal.window="if($event.detail === 'room-modal') resetForm()">

        {{-- Statistics --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <x-stat-card label="Total Rooms" :value="$stats['total_rooms']" icon="fas fa-door-open" color="blue"
                alpine-text="stats.total_rooms" />
            <x-stat-card label="AC Rooms" :value="$stats['ac_rooms']" icon="fas fa-snowflake" color="emerald"
                alpine-text="stats.ac_rooms" />
            <x-stat-card label="Fan Rooms" :value="$stats['fan_rooms']" icon="fas fa-fan" color="amber"
                alpine-text="stats.fan_rooms" />
            <x-stat-card label="Residents" :value="$stats['total_beds']" icon="fas fa-user-graduate" color="purple"
                alpine-text="stats.total_beds" />
        </div>

        {{-- Header --}}
        <x-page-header title="Room Inventory" description="Configure residential units and amenities" icon="fas fa-door-open">
            <button @click="open()"
                class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-teal-600 to-emerald-600 hover:from-teal-700 hover:to-emerald-700 text-white text-sm font-semibold rounded-xl transition-all shadow-md hover:shadow-lg active:scale-95">
                <i class="fas fa-plus mr-2 text-xs"></i>
                Add Room
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
                        <h2 class="text-lg font-semibold text-gray-800 dark:text-white">Room List</h2>
                        <x-table.search placeholder="Search rooms, hostels..." />
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
                            <x-table.sort-header column="room_name" label="Room Name" sort-var="sort" direction-var="direction" />
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Hostel / Floor</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Amenities</th>
                            <x-table.sort-header column="room_create_date" label="Established" sort-var="sort" direction-var="direction" />
                            <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider w-32">Actions</th>
                        </tr>
                    </thead>

                    {{-- Server-rendered rows (Hidden once Alpine initializes) --}}
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700" :class="{ 'hidden': true }">
                        @if(empty($initialData['rows']))
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <i class="fas fa-door-open text-4xl text-gray-300 mb-4"></i>
                                    <p class="text-lg text-gray-500">No rooms found matching your criteria.</p>
                                </div>
                            </td>
                        </tr>
                        @endif
                        @foreach($initialData['rows'] as $row)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/50 transition-colors group">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-teal-50 flex items-center justify-center text-teal-500 group-hover:bg-teal-100">
                                        <i class="fas fa-door-open"></i>
                                    </div>
                                    <span class="font-bold text-gray-800 dark:text-gray-100">{{ $row['room_name'] }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="space-y-1">
                                    <div class="flex items-center gap-1.5 text-xs font-bold text-gray-700 dark:text-gray-200">
                                        <i class="fas fa-building text-[10px] text-gray-400"></i>
                                        {{ $row['hostel_name'] }}
                                    </div>
                                    <div class="flex items-center gap-1.5 text-[10px] text-gray-400">
                                        <i class="fas fa-layer-group text-[9px]"></i>
                                        {{ $row['floor_name'] }}
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1 {{ $row['amenities_label'] !== 'None' ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-500' }} rounded-full text-xs font-bold">
                                    {{ $row['amenities_label'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-xs font-medium text-gray-500">{{ $row['room_create_date'] }}</td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button @click="open({{ json_encode($row) }})" title="Edit"
                                        class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white transition-all flex items-center justify-center">
                                        <i class="fas fa-edit text-xs"></i>
                                    </button>
                                    <button @click="quickAction(`/receptionist/hostel-rooms/{{ $row['id'] }}`, 'Delete Room', 'DELETE')" title="Delete"
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
                                        <div class="w-10 h-10 rounded-xl bg-teal-50 flex items-center justify-center text-teal-500 group-hover:bg-teal-100">
                                            <i class="fas fa-door-open"></i>
                                        </div>
                                        <span class="font-bold text-gray-800 dark:text-gray-100" x-text="row.room_name"></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="space-y-1">
                                        <div class="flex items-center gap-1.5 text-xs font-bold text-gray-700 dark:text-gray-200">
                                            <i class="fas fa-building text-[10px] text-gray-400"></i>
                                            <span x-text="row.hostel_name"></span>
                                        </div>
                                        <div class="flex items-center gap-1.5 text-[10px] text-gray-400">
                                            <i class="fas fa-layer-group text-[9px]"></i>
                                            <span x-text="row.floor_name"></span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-3 py-1 rounded-full text-xs font-bold"
                                        :class="row.amenities_label !== 'None' ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-500'"
                                        x-text="row.amenities_label"></span>
                                </td>
                                <td class="px-6 py-4 text-xs font-medium text-gray-500" x-text="row.room_create_date"></td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button @click="open(row)" title="Edit"
                                            class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white transition-all flex items-center justify-center">
                                            <i class="fas fa-edit text-xs"></i>
                                        </button>
                                        <button @click="quickAction(`/receptionist/hostel-rooms/${row.id}`, 'Delete Room', 'DELETE')" title="Delete"
                                            class="w-8 h-8 rounded-lg bg-red-50 text-red-600 hover:bg-red-600 hover:text-white transition-all flex items-center justify-center">
                                            <i class="fas fa-trash-alt text-xs"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </template>

                        <x-table.empty-state :colspan="5" icon="fas fa-door-open" message="No rooms found matching your criteria." />
                    </tbody>
                </table>
            </div>

            @if($initialData['pagination']['total'] > 0)
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50" :class="{ 'hidden': true }">
                <div class="text-sm text-gray-700 dark:text-gray-300">
                    Showing {{ $initialData['pagination']['from'] }} to {{ $initialData['pagination']['to'] }} of {{ $initialData['pagination']['total'] }} results
                </div>
            </div>
            @endif

            <x-table.pagination />
        </div>

        <x-confirm-modal title="Delete Room?"
            message="This will remove the room. Make sure there are no students assigned to this room."
            confirm-text="Delete" confirm-color="red" />

        {{-- Add/Edit Room Modal --}}
        <x-modal name="room-modal" alpineTitle="editMode ? 'Edit Room' : 'Add New Room'" maxWidth="2xl">
            <form @submit.prevent="save" id="roomForm" class="p-1" novalidate>
                @csrf
                <template x-if="editMode">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="modal-label-premium">Hostel Block <span class="text-red-600 font-bold">*</span></label>
                        <select x-model="formData.hostel_id" @change="loadFloors(); clearError('hostel_id')"
                            class="no-select2 w-full bg-white border rounded-xl py-3 px-4 text-sm font-bold focus:ring-2 focus:ring-teal-500/20 transition-all"
                            :class="errors.hostel_id ? 'border-red-500' : 'border-slate-200'">
                            <option value="">Select Hostel</option>
                            @foreach($hostels as $hostel)
                                <option value="{{ $hostel->id }}">{{ $hostel->hostel_name }}</option>
                            @endforeach
                        </select>
                        <template x-if="errors.hostel_id">
                            <p x-text="errors.hostel_id[0]" class="text-[10px] font-bold text-red-500 ml-1"></p>
                        </template>
                    </div>

                    <div class="space-y-2">
                        <label class="modal-label-premium">Floor Level <span class="text-red-600 font-bold">*</span></label>
                        <select x-model="formData.hostel_floor_id" :disabled="!formData.hostel_id"
                            @change="clearError('hostel_floor_id')"
                            class="no-select2 w-full bg-white border rounded-xl py-3 px-4 text-sm font-bold focus:ring-2 focus:ring-teal-500/20 transition-all disabled:opacity-50"
                            :class="errors.hostel_floor_id ? 'border-red-500' : 'border-slate-200'">
                            <option value="">Select Floor</option>
                            <template x-for="floor in floors" :key="floor.id">
                                <option :value="floor.id" x-text="floor.floor_name"></option>
                            </template>
                        </select>
                        <template x-if="errors.hostel_floor_id">
                            <p x-text="errors.hostel_floor_id[0]" class="text-[10px] font-bold text-red-500 ml-1"></p>
                        </template>
                    </div>

                    <div class="space-y-2 md:col-span-2">
                        <label class="modal-label-premium">Room Name <span class="text-red-600 font-bold">*</span></label>
                        <input type="text" x-model="formData.room_name" @input="clearError('room_name')"
                            placeholder="e.g., Room 101"
                            class="w-full bg-white border rounded-xl py-3 px-4 text-sm font-bold focus:ring-2 focus:ring-teal-500/20 transition-all"
                            :class="errors.room_name ? 'border-red-500' : 'border-slate-200'">
                        <template x-if="errors.room_name">
                            <p x-text="errors.room_name[0]" class="text-[10px] font-bold text-red-500 ml-1"></p>
                        </template>
                    </div>

                    <div class="space-y-2">
                        <label class="modal-label-premium text-emerald-600">Air Conditioning</label>
                        <select x-model="formData.ac"
                            class="no-select2 w-full bg-white border border-slate-200 rounded-xl py-3 px-4 text-sm font-bold focus:ring-2 focus:ring-teal-500/20 transition-all">
                            @foreach(YesNo::options() as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label class="modal-label-premium text-indigo-600">Cooler</label>
                        <select x-model="formData.cooler"
                            class="no-select2 w-full bg-white border border-slate-200 rounded-xl py-3 px-4 text-sm font-bold focus:ring-2 focus:ring-teal-500/20 transition-all">
                            @foreach(YesNo::options() as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label class="modal-label-premium text-amber-600">Fan</label>
                        <select x-model="formData.fan"
                            class="no-select2 w-full bg-white border border-slate-200 rounded-xl py-3 px-4 text-sm font-bold focus:ring-2 focus:ring-teal-500/20 transition-all">
                            @foreach(YesNo::options() as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label class="modal-label-premium">Established On</label>
                        <input type="date" x-model="formData.room_create_date"
                            class="w-full bg-white border border-slate-200 rounded-xl py-3 px-4 text-sm font-bold focus:ring-2 focus:ring-teal-500/20 transition-all">
                    </div>
                </div>

                <div class="mt-6 bg-indigo-50 border border-indigo-100 p-4 rounded-2xl flex items-start gap-3">
                    <i class="fas fa-info-circle text-indigo-600 mt-0.5"></i>
                    <p class="text-[11px] text-slate-500 leading-relaxed">
                        After adding a room, assign students to it via the Bed Assignments section.
                    </p>
                </div>
            </form>

            <x-slot name="footer">
                <button type="button" @click="$dispatch('close-modal', 'room-modal')" :disabled="submitting"
                    class="px-6 py-2.5 text-xs font-bold text-slate-500 uppercase tracking-widest hover:text-slate-700 transition-colors">
                    Cancel
                </button>
                <button type="submit" form="roomForm" :disabled="submitting"
                    class="bg-slate-900 hover:bg-black text-white px-8 py-2.5 rounded-xl text-xs font-bold uppercase tracking-widest transition-all shadow-md active:scale-95 disabled:opacity-50">
                    <template x-if="submitting">
                        <i class="fas fa-spinner animate-spin mr-2"></i>
                    </template>
                    <span x-text="submitting ? 'Saving...' : (editMode ? 'Update Room' : 'Save Room')"></span>
                </button>
            </x-slot>
        </x-modal>
    </div>

    @push('scripts')
        <script>
            function hostelRoomForm() {
                return {
                    editMode: false,
                    submitting: false,
                    roomId: null,
                    floors: [],
                    formData: {
                        hostel_id: '',
                        hostel_floor_id: '',
                        room_name: '',
                        ac: '{{ YesNo::No->value }}',
                        cooler: '{{ YesNo::No->value }}',
                        fan: '{{ YesNo::Yes->value }}',
                        room_create_date: '',
                    },
                    errors: {},

                    clearError(field) {
                        if (this.errors[field]) delete this.errors[field];
                    },

                    async loadFloors(targetFloorId = null) {
                        if (!this.formData.hostel_id) {
                            this.floors = [];
                            this.formData.hostel_floor_id = '';
                            return;
                        }
                        try {
                            const response = await fetch('{{ route('receptionist.hostel-rooms.get-floors') }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify({
                                    hostel_id: this.formData.hostel_id
                                })
                            });
                            const data = await response.json();
                            if (data.success) {
                                this.floors = data.floors;
                                if (targetFloorId) {
                                    this.$nextTick(() => {
                                        this.formData.hostel_floor_id = String(targetFloorId);
                                    });
                                }
                            }
                        } catch (e) {
                            console.error(e);
                        }
                    },

                    resetForm() {
                        this.editMode = false;
                        this.roomId = null;
                        this.errors = {};
                        this.floors = [];
                        this.formData = {
                            hostel_id: '',
                            hostel_floor_id: '',
                            room_name: '',
                            ac: '{{ YesNo::No->value }}',
                            cooler: '{{ YesNo::No->value }}',
                            fan: '{{ YesNo::Yes->value }}',
                            room_create_date: '',
                        };
                    },

                    async open(room = null) {
                        this.errors = {};
                        if (room) {
                            this.editMode = true;
                            this.roomId = room.id;
                            this.formData = {
                                hostel_id: String(room.raw.hostel_id),
                                hostel_floor_id: '', // Will be set by loadFloors after options render
                                room_name: room.raw.room_name,
                                ac: String(room.raw.ac),
                                cooler: String(room.raw.cooler),
                                fan: String(room.raw.fan),
                                room_create_date: room.raw.room_create_date || '',
                            };
                            this.loadFloors(room.raw.hostel_floor_id);
                        } else {
                            this.editMode = false;
                            this.roomId = null;
                            this.formData = {
                                hostel_id: '',
                                hostel_floor_id: '',
                                room_name: '',
                                ac: '{{ YesNo::No->value }}',
                                cooler: '{{ YesNo::No->value }}',
                                fan: '{{ YesNo::Yes->value }}',
                                room_create_date: '{{ date('Y-m-d') }}',
                            };
                            this.floors = [];
                        }
                        this.$dispatch('open-modal', 'room-modal');
                    },

                    async save() {
                        this.submitting = true;
                        this.errors = {};
                        const url = this.editMode
                            ? `/receptionist/hostel-rooms/${this.roomId}`
                            : `{{ route('receptionist.hostel-rooms.store') }}`;

                        try {
                            const response = await fetch(url, {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                                body: JSON.stringify({ ...this.formData, _method: this.editMode ? 'PUT' : 'POST' })
                            });

                            const result = await response.json();
                            if (response.ok) {
                                if (window.Toast) window.Toast.fire({ icon: 'success', title: result.message });
                                this.$dispatch('close-modal', 'room-modal');
                                this.fetchData();
                            } else {
                                this.errors = result.errors || {};
                                if (result.message && response.status !== 422) {
                                    if (window.Toast) window.Toast.fire({ icon: 'error', title: result.message });
                                }
                            }
                        } catch (e) {
                            if (window.Toast) window.Toast.fire({ icon: 'error', title: 'Failed to save room' });
                        } finally {
                            this.submitting = false;
                        }
                    }
                }
            }
        </script>
    @endpush
@endsection
