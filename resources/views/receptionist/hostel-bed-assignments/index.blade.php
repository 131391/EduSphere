@extends('layouts.receptionist')

@section('title', 'Hostel Bed Assignments - Receptionist')
@section('page-title', 'Bed Assignments')
@section('page-description', 'Assign students to hostel rooms and beds')

@section('content')
    <div x-data="Object.assign(ajaxDataTable({
        fetchUrl: '{{ route('receptionist.hostel-bed-assignments.fetch') }}',
        defaultSort: 'created_at',
        defaultDirection: 'desc',
        defaultPerPage: 25,
        defaultFilters: { hostel_id: '' },
        initialRows: @js($initialData['rows']),
        initialPagination: @js($initialData['pagination']),
        initialStats: @js($initialData['stats']),
        filterLabels: {
            hostel_id: {
                @foreach($hostels as $h) '{{ $h->id }}': '{{ $h->hostel_name }}', @endforeach
            }
        }
    }), bedAssignmentManagementData())" class="space-y-6" @close-modal.window="if ($event.detail === 'assignment-modal') { resetForm(); }">
        
        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <x-stat-card label="Total Assignments" :value="$stats['total_assignments']" icon="fas fa-link" color="blue" alpine-text="stats.total_assignments" />
            <x-stat-card label="Hostel Blocks" :value="$stats['total_hostels']" icon="fas fa-building" color="emerald" alpine-text="stats.total_hostels" />
            <x-stat-card label="Total Rooms" :value="$stats['total_rooms']" icon="fas fa-bed" color="amber" alpine-text="stats.total_rooms" />
            <x-stat-card label="Revenue (Monthly)" :value="'₹' . number_format($stats['total_rent'], 0)" icon="fas fa-wallet" color="indigo" alpine-text="stats.total_rent" is-currency="true" />
        </div>

        <!-- Header Section -->
        <x-page-header title="Bed Assignments" description="Assign students to hostel rooms and beds" icon="fas fa-link">
            <button @click="openAddModal()"
                class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-teal-600 to-emerald-600 hover:from-teal-700 hover:to-emerald-700 text-white text-sm font-semibold rounded-xl transition-all shadow-md hover:shadow-lg active:scale-95">
                <i class="fas fa-plus mr-2 text-xs"></i>
                Add Assignment
            </button>
            <button @click="exportData('csv')" :disabled="exporting"
                class="min-w-[140px] justify-center inline-flex items-center px-4 py-2 bg-gradient-to-r from-slate-700 to-slate-900 hover:from-black hover:to-slate-800 text-white text-sm font-semibold rounded-xl transition-all shadow-md hover:shadow-lg active:scale-95 disabled:opacity-50">
                <span x-show="exporting" class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin mr-2 inline-block" x-cloak></span>
                <i x-show="!exporting" class="fas fa-file-excel mr-2 text-xs"></i>
                <span x-text="exporting ? 'Exporting...' : 'Excel Export'">Excel Export</span>
            </button>
        </x-page-header>

        <!-- AJAX Data Table -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <!-- Table Header with Search and Filters -->
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <!-- Left: Title and Search -->
                    <div class="flex-1 flex flex-col md:flex-row md:items-center gap-4">
                        <h2 class="text-lg font-semibold text-gray-800 dark:text-white">Bed Assignment List</h2>
                        <x-table.search placeholder="Search by student, admission or bed..." />
                    </div>

                    <!-- Right: Filters and Actions -->
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
            <div class="overflow-x-auto relative ajax-table-wrapper min-h-[400px]">
                <x-table.loading-overlay />

                <table class="w-full text-left border-collapse">
                    <thead class="bg-gray-50/50 dark:bg-gray-700/50 border-b border-gray-100 dark:border-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider w-16">SR NO</th>
                            <x-table.sort-header column="student_name" label="Student" sort-var="sort" direction-var="direction" />
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Room Details</th>
                            <x-table.sort-header column="rent" label="Financials" sort-var="sort" direction-var="direction" />
                            <x-table.sort-header column="created_at" label="Assigned On" sort-var="sort" direction-var="direction" />
                            <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider w-32">Actions</th>
                        </tr>
                    </thead>

                    {{-- Server-rendered rows (Hidden once Alpine initializes) --}}
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700" :class="{ 'hidden': true }">
                        @if(empty($initialData['rows']))
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <i class="fas fa-link-slash text-4xl text-gray-300 mb-4"></i>
                                    <p class="text-lg text-gray-500">No bed assignments found.</p>
                                </div>
                            </td>
                        </tr>
                        @endif
                        @foreach($initialData['rows'] as $index => $row)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-400 font-medium">
                                {{ ($initialData['pagination']['from'] + $index) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-teal-500 to-emerald-500 flex items-center justify-center text-white font-bold text-xs shadow-sm">{{ $row['initials'] }}</div>
                                    <div>
                                        <div class="text-sm font-bold text-gray-800 dark:text-gray-100">{{ $row['student_name'] }}</div>
                                        <div class="text-[10px] font-medium text-gray-400">{{ $row['admission_no'] }} • {{ $row['class_name'] }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="space-y-1">
                                    <div class="text-xs font-bold text-gray-700 dark:text-gray-200">
                                        <i class="fas fa-building text-[10px] text-gray-400 mr-1"></i>
                                        {{ $row['hostel_name'] }}
                                    </div>
                                    <div class="text-[10px] text-gray-500 dark:text-gray-400 flex items-center gap-2">
                                        <span>{{ $row['floor_name'] }}</span>
                                        <span class="w-1 h-1 bg-gray-300 rounded-full"></span>
                                        <span>Unit {{ $row['room_name'] }}</span>
                                        <span class="w-1 h-1 bg-gray-300 rounded-full"></span>
                                        <span class="font-bold text-teal-600 italic">Bed {{ $row['bed_no'] }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-xs font-bold text-gray-900 dark:text-gray-100">{{ $row['rent_label'] }}</div>
                                <div class="text-[9px] text-gray-400 font-medium uppercase tracking-wider mt-0.5">Per Month</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-xs font-bold text-gray-700 dark:text-gray-300">
                                    <i class="far fa-calendar-alt mr-1 text-gray-400"></i>
                                    {{ $row['hostel_assign_date'] }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button @click="openEditModal(@js($row))" class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center hover:bg-blue-100 transition-colors" title="Edit"><i class="fas fa-edit text-xs"></i></button>
                                    <button @click="quickAction('{{ route('receptionist.hostel-bed-assignments.destroy', $row['id']) }}', 'Delete', 'DELETE', 'Are you sure you want to remove this assignment?')" class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center hover:bg-rose-100 transition-colors" title="Delete"><i class="fas fa-trash-alt text-xs"></i></button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>

                    {{-- Alpine-managed rows --}}
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700 transition-opacity duration-150" x-cloak :class="loading && rows.length &gt; 0 ? 'opacity-50' : 'opacity-100'">
                        <template x-for="(row, index) in rows" :key="row.id">
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-400 font-medium" x-text="pagination.from + index"></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-teal-500 to-emerald-500 flex items-center justify-center text-white font-bold text-xs shadow-sm" x-text="row.initials"></div>
                                        <div>
                                            <div class="text-sm font-bold text-gray-800 dark:text-gray-100" x-text="row.student_name"></div>
                                            <div class="text-[10px] font-medium text-gray-400" x-text="`${row.admission_no} • ${row.class_name}`"></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="space-y-1">
                                        <div class="text-xs font-bold text-gray-700 dark:text-gray-200">
                                            <i class="fas fa-building text-[10px] text-gray-400 mr-1"></i>
                                            <span x-text="row.hostel_name"></span>
                                        </div>
                                        <div class="text-[10px] text-gray-500 dark:text-gray-400 flex items-center gap-2">
                                            <span x-text="row.floor_name"></span>
                                            <span class="w-1 h-1 bg-gray-300 rounded-full"></span>
                                            <span x-text="`Unit ${row.room_name}`"></span>
                                            <span class="w-1 h-1 bg-gray-300 rounded-full"></span>
                                            <span class="font-bold text-teal-600 italic" x-text="`Bed ${row.bed_no}`"></span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-xs font-bold text-gray-900 dark:text-gray-100" x-text="row.rent_label"></div>
                                    <div class="text-[9px] text-gray-400 font-medium uppercase tracking-wider mt-0.5">Per Month</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-xs font-bold text-gray-700 dark:text-gray-300">
                                        <i class="far fa-calendar-alt mr-1 text-gray-400"></i>
                                        <span x-text="row.hostel_assign_date"></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <button @click="openEditModal(row)" class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center hover:bg-blue-100 transition-colors"><i class="fas fa-edit text-xs"></i></button>
                                        <button @click="quickAction(`/receptionist/hostel-bed-assignments/${row.id}`, 'Delete', 'DELETE', 'Are you sure you want to remove this assignment?')" class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center hover:bg-rose-100 transition-colors"><i class="fas fa-trash-alt text-xs"></i></button>
                                    </div>
                                </td>
                            </tr>
                        </template>

                        <x-table.empty-state :colspan="6" icon="fas fa-link-slash" message="No bed assignments found." />
                    </tbody>
                </table>
            </div>

            <x-table.pagination />
        </div>

        <!-- Add/Edit Mapping Modal -->
        <x-modal name="assignment-modal" alpineTitle="editMode ? 'Edit Bed Assignment' : 'Add Bed Assignment'"
            maxWidth="3xl">
            <form @submit.prevent="submitForm()" id="assignmentForm" method="POST" novalidate class="p-1">
                @csrf
                <template x-if="editMode">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <div class="space-y-8">
                    {{-- Student Selection --}}
                    <div class="bg-gray-50/50 dark:bg-gray-800/50 p-6 rounded-2xl border border-gray-100 dark:border-gray-700 space-y-6">
                        <h4 class="text-sm font-bold text-indigo-600 dark:text-indigo-400 flex items-center gap-2">
                            <i class="fas fa-user-graduate"></i>
                            Select Student
                        </h4>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2 relative">
                                <label class="modal-label-premium">Search Student <span class="text-red-500 font-bold">*</span></label>
                                <input type="text" x-model="admissionSearch"
                                    @input="searchStudents(); clearError('student_id')"
                                    @focus="showStudentDropdown = true" placeholder="Type student name or ID..."
                                    class="w-full bg-white border rounded-xl py-3 px-4 text-sm font-bold focus:ring-2 focus:ring-teal-500/20 transition-all font-premium shadow-sm"
                                    :class="errors.student_id ? 'border-red-500' : 'border-slate-200'"
                                    autocomplete="off">

                                {{-- Student Dropdown Results --}}
                                <div x-show="showStudentDropdown && studentResults.length > 0"
                                    @click.outside="showStudentDropdown = false"
                                    class="absolute z-[60] w-full mt-1 bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-100 dark:border-gray-700 max-h-60 overflow-y-auto">
                                    <template x-for="student in studentResults" :key="student.id">
                                        <div @click="selectStudent(student)"
                                            class="px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer border-b border-gray-100 dark:border-gray-700 last:border-0 flex items-center gap-3 transition-colors">
                                            <div class="w-8 h-8 bg-indigo-100 dark:bg-indigo-900/50 rounded-lg flex items-center justify-center text-indigo-600 dark:text-indigo-400 font-bold text-xs"
                                                x-text="student.admission_no.slice(-2)"></div>
                                            <div>
                                                <div class="font-bold text-sm text-gray-800 dark:text-gray-200" x-text="student.name"></div>
                                                <div class="text-[10px] font-bold text-gray-400 uppercase tracking-widest"
                                                    x-text="`${student.admission_no} • ${student.class_name}`"></div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                                <template x-if="errors.student_id">
                                    <p class="modal-error-message" x-text="errors.student_id[0]"></p>
                                </template>
                            </div>

                            <div class="space-y-2">
                                <label class="modal-label-premium">Selected Student</label>
                                <div class="px-4 py-2.5 bg-indigo-50/50 dark:bg-indigo-900/20 border border-indigo-100/50 dark:border-indigo-800 rounded-xl flex items-center justify-between min-h-[46px] shadow-sm">
                                    <div>
                                        <span class="block text-sm font-bold text-gray-800 dark:text-gray-200 leading-tight"
                                            x-text="formData.student_name || 'No student selected'"></span>
                                        <span class="block text-[10px] font-bold text-indigo-600 dark:text-indigo-400 uppercase tracking-widest mt-0.5"
                                            x-text="formData.class_name"></span>
                                    </div>
                                    <template x-if="formData.student_id">
                                        <i class="fas fa-check-circle text-emerald-500 shadow-sm"></i>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Room Selection --}}
                    <div class="space-y-6">
                        <h4 class="text-sm font-bold text-emerald-600 dark:text-emerald-400 flex items-center gap-2">
                            <i class="fas fa-map-location-dot"></i>
                            Room Selection
                        </h4>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="space-y-2">
                                <label class="modal-label-premium">Hostel Block <span class="text-red-500 font-bold">*</span></label>
                                <select name="hostel_id" x-model="formData.hostel_id" @change="loadFloors(); clearError('hostel_id')"
                                    class="no-select2 w-full bg-white border rounded-xl py-3 px-4 text-sm font-bold focus:ring-2 focus:ring-teal-500/20 transition-all shadow-sm"
                                    :class="errors.hostel_id ? 'border-red-500' : 'border-slate-200'">
                                    <option value="">Select Block</option>
                                    @foreach($hostels as $hostel)
                                        <option value="{{ $hostel->id }}">{{ $hostel->hostel_name }}</option>
                                    @endforeach
                                </select>
                                <template x-if="errors.hostel_id">
                                    <p class="modal-error-message" x-text="errors.hostel_id[0]"></p>
                                </template>
                            </div>

                            <div class="space-y-2">
                                <label class="modal-label-premium">Floor Level <span class="text-red-500 font-bold">*</span></label>
                                <select name="hostel_floor_id" x-model="formData.hostel_floor_id"
                                    @change="loadRooms(); clearError('hostel_floor_id')" :disabled="!formData.hostel_id"
                                    class="no-select2 w-full bg-white border rounded-xl py-3 px-4 text-sm font-bold focus:ring-2 focus:ring-teal-500/20 transition-all shadow-sm disabled:opacity-50 disabled:bg-gray-50"
                                    :class="errors.hostel_floor_id ? 'border-red-500' : 'border-slate-200'">
                                    <option value="">Select Floor</option>
                                    <template x-for="floor in floors" :key="floor.id">
                                        <option :value="String(floor.id)" x-text="floor.floor_name"></option>
                                    </template>
                                </select>
                                <template x-if="errors.hostel_floor_id">
                                    <p class="modal-error-message" x-text="errors.hostel_floor_id[0]"></p>
                                </template>
                            </div>

                            <div class="space-y-2">
                                <label class="modal-label-premium">Room <span class="text-red-500 font-bold">*</span></label>
                                <select name="hostel_room_id" x-model="formData.hostel_room_id" @change="clearError('hostel_room_id')"
                                    :disabled="!formData.hostel_floor_id"
                                    class="no-select2 w-full bg-white border rounded-xl py-3 px-4 text-sm font-bold focus:ring-2 focus:ring-teal-500/20 transition-all shadow-sm disabled:opacity-50 disabled:bg-gray-50"
                                    :class="errors.hostel_room_id ? 'border-red-500' : 'border-slate-200'">
                                    <option value="">Select Room</option>
                                    <template x-for="room in rooms" :key="room.id">
                                        <option :value="String(room.id)" x-text="room.room_name"></option>
                                    </template>
                                </select>
                                <template x-if="errors.hostel_room_id">
                                    <p class="modal-error-message" x-text="errors.hostel_room_id[0]"></p>
                                </template>
                            </div>
                        </div>
                    </div>

                    {{-- Financial & Scheduling --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="space-y-4">
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Fee Details</label>
                            <div class="grid grid-cols-2 gap-4 bg-slate-50/50 p-5 rounded-2xl border border-slate-100 shadow-inner">
                                <div class="space-y-2">
                                    <label class="modal-label-premium">Bed No</label>
                                    <input type="text" name="bed_no" x-model="formData.bed_no" @input="clearError('bed_no')"
                                        placeholder="e.g., Bed A"
                                        class="w-full bg-white border rounded-xl py-3 px-4 text-sm font-bold focus:ring-2 focus:ring-teal-500/20 transition-all shadow-sm"
                                        :class="errors.bed_no ? 'border-red-500' : 'border-slate-200'">
                                    <template x-if="errors.bed_no">
                                        <p class="modal-error-message" x-text="errors.bed_no[0]"></p>
                                    </template>
                                </div>
                                <div class="space-y-2">
                                    <label class="modal-label-premium">Monthly Fee</label>
                                    <input type="number" name="rent" step="0.01" x-model="formData.rent" @input="clearError('rent')"
                                        placeholder="0.00"
                                        class="w-full bg-white border rounded-xl py-3 px-4 text-sm font-bold focus:ring-2 focus:ring-teal-500/20 transition-all shadow-sm"
                                        :class="errors.rent ? 'border-red-500' : 'border-slate-200'">
                                    <template x-if="errors.rent">
                                        <p class="modal-error-message" x-text="errors.rent[0]"></p>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Dates</label>
                            <div class="grid grid-cols-2 gap-4 bg-slate-50/50 p-5 rounded-2xl border border-slate-100 shadow-inner">
                                <div class="space-y-2">
                                    <label class="modal-label-premium">Start Date</label>
                                    <input type="date" name="hostel_assign_date" x-model="formData.hostel_assign_date"
                                        @input="clearError('hostel_assign_date')"
                                        class="w-full bg-white border rounded-xl py-3 px-4 text-sm font-bold focus:ring-2 focus:ring-teal-500/20 transition-all shadow-sm"
                                        :class="errors.hostel_assign_date ? 'border-red-500' : 'border-slate-200'">
                                    <template x-if="errors.hostel_assign_date">
                                        <p class="modal-error-message" x-text="errors.hostel_assign_date[0]"></p>
                                    </template>
                                </div>
                                <div class="space-y-2">
                                    <label class="modal-label-premium">Billing Month <span class="text-red-500 font-bold">*</span></label>
                                    <select name="starting_month" x-model="formData.starting_month" @change="clearError('starting_month')"
                                        class="no-select2 w-full bg-white border rounded-xl py-3 px-4 text-sm font-bold focus:ring-2 focus:ring-teal-500/20 transition-all shadow-sm"
                                        :class="errors.starting_month ? 'border-red-500' : 'border-slate-200'">
                                        <option value="">Select Month</option>
                                        <template x-for="month in months" :key="month.value">
                                            <option :value="String(month.value)" x-text="month.label"></option>
                                        </template>
                                    </select>
                                    <template x-if="errors.starting_month">
                                        <p class="modal-error-message" x-text="errors.starting_month[0]"></p>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- notice card -->
                <div class="mt-6 flex items-center justify-between bg-[#f0f5ff] border border-[#e5edff] p-5 rounded-2xl shadow-sm">
                    <div class="flex flex-col">
                        <span class="text-sm font-bold text-slate-900 leading-tight">Note</span>
                        <span class="text-[10px] text-slate-500 font-bold uppercase mt-1 tracking-wide opacity-80">The assignment is linked to the student's fee account. Make sure the student and room details are correct before saving.</span>
                    </div>
                    <div class="w-10 h-10 bg-white rounded-xl shadow-sm flex items-center justify-center shrink-0">
                        <i class="fas fa-info-circle text-indigo-600 text-sm"></i>
                    </div>
                </div>

                {{-- Footer --}}
                <x-slot name="footer">
                    <button type="button" @click="$dispatch('close-modal', 'assignment-modal')"
                        class="btn-premium-cancel px-10">
                        Cancel
                    </button>
                    <button type="button" @click="submitForm()" :disabled="submitting"
                        class="btn-premium-primary min-w-[170px]">
                        <template x-if="submitting">
                            <span class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin mr-3 inline-block"></span>
                        </template>
                        <span x-text="editMode ? 'Update Assignment' : 'Save Assignment'"></span>
                    </button>
                </x-slot>
            </form>
        </x-modal>

        {{-- Delete Confirmation Modal --}}
        <x-confirm-modal />
    </div>

    @push('scripts')
        <script>
            function bedAssignmentManagementData() {
                return {
                    submitting: false,
                    errors: {},
                    editMode: false,
                    assignmentId: null,
                    admissionSearch: '',
                    showStudentDropdown: false,
                    studentResults: [],
                    searchTimeout: null,
                    floors: [],
                    rooms: [],
                    months: [],
                    formData: {
                        student_id: '',
                        student_name: '',
                        class_name: '',
                        hostel_id: '',
                        hostel_floor_id: '',
                        hostel_room_id: '',
                        bed_no: '',
                        rent: '',
                        hostel_assign_date: '{{ date('Y-m-d') }}',
                        starting_month: '',
                    },

                    clearError(field) {
                        if (this.errors && this.errors[field]) {
                            const e = { ...this.errors }; delete e[field]; this.errors = e;
                        }
                    },

                    async init() {
                        this.loadMonths();
                    },

                    async loadMonths() {
                        try {
                            const response = await fetch('{{ route('receptionist.hostel-bed-assignments.get-months') }}');
                            const data = await response.json();
                            if (data.success) {
                                this.months = data.months;
                            }
                        } catch (error) {
                            console.error('Lifecycle loading failure:', error);
                        }
                    },

                    searchStudents() {
                        if (this.admissionSearch.length < 2) {
                            this.studentResults = [];
                            return;
                        }

                        if (this.searchTimeout) clearTimeout(this.searchTimeout);

                        this.searchTimeout = setTimeout(async () => {
                            try {
                                const response = await fetch('{{ route('receptionist.hostel-bed-assignments.search-students') }}', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                        'Accept': 'application/json'
                                    },
                                    body: JSON.stringify({ search: this.admissionSearch })
                                });
                                const data = await response.json();
                                if (data.success) {
                                    this.studentResults = data.students;
                                }
                            } catch (error) {
                                console.error('Registry search failure:', error);
                            }
                        }, 300);
                    },

                    selectStudent(student) {
                        this.formData.student_id = student.id;
                        this.formData.student_name = student.name;
                        this.formData.class_name = student.class_name;
                        this.admissionSearch = student.admission_no;
                        this.showStudentDropdown = false;
                        this.clearError('student_id');
                    },

                    async loadFloors(targetFloorId = null) {
                        if (!this.formData.hostel_id) {
                            this.floors = [];
                            this.formData.hostel_floor_id = '';
                            return;
                        }

                        try {
                            const response = await fetch('{{ route('receptionist.hostel-bed-assignments.get-floors') }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify({ hostel_id: this.formData.hostel_id })
                            });
                            const data = await response.json();
                            if (data.success) {
                                this.floors = data.floors;
                                if (targetFloorId) {
                                    this.$nextTick(() => {
                                        this.formData.hostel_floor_id = String(targetFloorId);
                                        this.loadRooms(this.formData.tempRoomId);
                                    });
                                } else {
                                    this.formData.hostel_floor_id = '';
                                }
                            }
                        } catch (error) {
                            console.error('Structural retrieval failure:', error);
                        }
                    },

                    async loadRooms(targetRoomId = null) {
                        if (!this.formData.hostel_floor_id) {
                            this.rooms = [];
                            this.formData.hostel_room_id = '';
                            return;
                        }

                        try {
                            const response = await fetch('{{ route('receptionist.hostel-bed-assignments.get-rooms') }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify({ hostel_floor_id: this.formData.hostel_floor_id })
                            });
                            const data = await response.json();
                            if (data.success) {
                                this.rooms = data.rooms;
                                if (targetRoomId) {
                                    this.$nextTick(() => {
                                        this.formData.hostel_room_id = String(targetRoomId);
                                    });
                                } else {
                                    this.formData.hostel_room_id = '';
                                }
                            }
                        } catch (error) {
                            console.error('Inventory retrieval failure:', error);
                        }
                    },

                    resetForm() {
                        this.editMode = false;
                        this.assignmentId = null;
                        this.errors = {};
                        this.admissionSearch = '';
                        this.studentResults = [];
                        this.floors = [];
                        this.rooms = [];
                        this.formData = {
                            student_id: '',
                            student_name: '',
                            class_name: '',
                            hostel_id: '',
                            hostel_floor_id: '',
                            hostel_room_id: '',
                            bed_no: '',
                            rent: '',
                            hostel_assign_date: '{{ date('Y-m-d') }}',
                            starting_month: '',
                        };
                    },

                    openAddModal() {
                        this.resetForm();
                        this.$dispatch('open-modal', 'assignment-modal');
                    },

                    async openEditModal(row) {
                        this.editMode = true;
                        this.assignmentId = row.id;
                        this.errors = {};
                        
                        this.formData = {
                            student_id: row.student_id,
                            student_name: row.student_name,
                            class_name: row.class_name,
                            hostel_id: String(row.hostel_id),
                            hostel_floor_id: '',
                            hostel_room_id: '',
                            bed_no: row.bed_no !== 'N/A' ? row.bed_no : '',
                            rent: row.rent || '',
                            hostel_assign_date: row.hostel_assign_date ? row.hostel_assign_date : '{{ date('Y-m-d') }}',
                            starting_month: String(row.starting_month),
                            tempRoomId: String(row.hostel_room_id)
                        };
                        this.admissionSearch = row.admission_no;

                        this.loadFloors(row.hostel_floor_id);

                        this.$dispatch('open-modal', 'assignment-modal');
                    },

                    async submitForm() {
                        this.submitting = true;
                        this.errors = {};

                        try {
                            const url = this.editMode 
                                ? `/receptionist/hostel-bed-assignments/${this.assignmentId}` 
                                : '{{ route('receptionist.hostel-bed-assignments.store') }}';
                            
                            const method = this.editMode ? 'PUT' : 'POST';

                            const response = await fetch(url, {
                                method: method,
                                body: JSON.stringify(this.formData),
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                }
                            });

                            const result = await response.json();

                            if (response.ok) {
                                if (window.Toast) window.Toast.fire({ icon: 'success', title: result.message });
                                this.$dispatch('close-modal', 'assignment-modal');
                                this.refreshTable();
                            } else if (response.status === 422) {
                                this.errors = result.errors || {};
                            } else {
                                throw new Error(result.message || 'Operation failed');
                            }
                        } catch (error) {
                            if (window.Toast) window.Toast.fire({ icon: 'error', title: error.message });
                        } finally {
                            this.submitting = false;
                        }
                    }
                }
            }
        </script>
    @endpush
@endsection