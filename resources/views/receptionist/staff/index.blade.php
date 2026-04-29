@php
    use App\Enums\StaffPost;
    use App\Enums\Gender;
@endphp
@extends('layouts.receptionist')

@section('title', 'Staff Management - Receptionist')
@section('page-title', 'Staff Management')
@section('page-description', 'Manage school staff records')

@section('content')
    <div x-data="Object.assign(ajaxDataTable({
        fetchUrl: '{{ route('receptionist.staff.fetch') }}',
        defaultSort: 'created_at',
        defaultDirection: 'desc',
        defaultPerPage: 25,
        defaultFilters: { post: '', class_id: '' },
        initialRows: @js($initialData['rows']),
        initialPagination: @js($initialData['pagination']),
        initialStats: @js($initialData['stats']),
        filterLabels: {
            post: {
                @foreach($staffPosts as $p) '{{ $p->value }}': '{{ $p->label() }}', @endforeach
            },
            class_id: {
                @foreach($classes as $c) '{{ $c->id }}': '{{ $c->name }}', @endforeach
            }
        }
    }), staffManagementData())" class="space-y-6" @close-modal.window="if ($event.detail === 'staff-modal') { resetForm(); }">
        
        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <x-stat-card label="Total Staff" :value="$stats['total']" icon="fas fa-users-gear" color="blue" alpine-text="stats.total" />
            <x-stat-card label="Teaching Staff" :value="$stats['teaching']" icon="fas fa-chalkboard-user" color="emerald" alpine-text="stats.teaching" />
            <x-stat-card label="Non-Teaching" :value="$stats['non_teaching']" icon="fas fa-user-tie" color="amber" alpine-text="stats.non_teaching" />
            <x-stat-card label="Recent Joiners" :value="$stats['recent']" icon="fas fa-user-plus" color="indigo" alpine-text="stats.recent" />
        </div>

        <!-- Header Section -->
        <x-page-header title="Staff Management" description="Manage school staff records and assignments" icon="fas fa-users-cog">
            <button @click="openAddModal()"
                class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-teal-600 to-emerald-600 hover:from-teal-700 hover:to-emerald-700 text-white text-sm font-semibold rounded-xl transition-all shadow-md hover:shadow-lg active:scale-95">
                <i class="fas fa-plus mr-2 text-xs"></i>
                New Staff
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
                        <h2 class="text-lg font-semibold text-gray-800 dark:text-white">Staff Registry</h2>
                        <x-table.search placeholder="Search by name, mobile, email..." />
                    </div>

                    <!-- Right: Filters and Actions -->
                    <div class="flex items-center gap-3">
                        <x-table.filter-select
                            model="filters.post"
                            action="applyFilter('post', $event.target.value)"
                            placeholder="Designation"
                            :options="collect($staffPosts)->mapWithKeys(fn($p) => [$p->value => $p->label()])->toArray()"
                        />

                        <x-table.filter-select
                            model="filters.class_id"
                            action="applyFilter('class_id', $event.target.value)"
                            placeholder="Class Assignment"
                            :options="collect($classes)->mapWithKeys(fn($c) => [$c->id => $c->name])->toArray()"
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

            <!-- Table Content -->
            <div class="overflow-x-auto relative ajax-table-wrapper min-h-[400px]">
                <x-table.loading-overlay />

                <div>
                    <table class="w-full text-left border-collapse whitespace-nowrap">
                        <thead>
                            <tr class="bg-gray-50 dark:bg-gray-800/50 border-b border-gray-200 dark:border-gray-700">
                                <th class="px-6 py-4 text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest">
                                    Sr No
                                </th>
                                <x-table.sort-header column="name" label="Staff Member" />
                                <x-table.sort-header column="post" label="Designation" />
                                <th class="px-6 py-4 text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest">
                                    Assignment
                                </th>
                                <x-table.sort-header column="mobile" label="Contact Details" />
                                <x-table.sort-header column="joining_date" label="Joining Date" />
                                <x-table.sort-header column="current_salary" label="Salary" />
                                <th class="px-6 py-4 text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest text-right">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        {{-- Server-rendered rows (Hidden once Alpine initializes, prevents FOUC flash) --}}
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700" data-ssr x-show="!hydrated">
                            @forelse($initialData['rows'] as $index => $row)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors group">
                                    <td class="px-6 py-4 text-sm font-medium text-gray-400">{{ ($initialData['pagination']['current_page'] - 1) * $initialData['pagination']['per_page'] + $index + 1 }}</td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-{{ $row['post_color'] ?? 'slate' }}-500 to-{{ $row['post_color'] ?? 'slate' }}-600 flex items-center justify-center text-sm font-bold text-white shadow-sm shrink-0 overflow-hidden">
                                                @if(!empty($row['staff_image']))
                                                    <img src="{{ $row['staff_image'] }}" class="w-full h-full object-cover">
                                                @else
                                                    <span>{{ $row['initials'] }}</span>
                                                @endif
                                            </div>
                                            <div class="flex flex-col">
                                                <span class="text-[13px] font-bold text-gray-800 dark:text-white group-hover:text-teal-600 transition-colors">{{ $row['name'] }}</span>
                                                <span class="text-[10px] text-gray-400 font-medium uppercase tracking-wider">ID: #{{ $row['id'] }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold border bg-{{ $row['post_color'] }}-50 text-{{ $row['post_color'] }}-700 border-{{ $row['post_color'] }}-100">
                                            {{ $row['post_label'] }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-col">
                                            <span class="text-[12px] font-semibold text-gray-700 dark:text-gray-300">{{ $row['class_name'] }}</span>
                                            <span class="text-[11px] text-gray-400">{{ $row['section_name'] }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-col">
                                            <div class="flex items-center gap-1.5">
                                                <i class="fas fa-phone text-[10px] text-gray-300"></i>
                                                <span class="text-[12px] font-semibold text-gray-700 dark:text-gray-300">{{ $row['mobile'] }}</span>
                                            </div>
                                            <div class="flex items-center gap-1.5">
                                                <i class="fas fa-envelope text-[10px] text-gray-300"></i>
                                                <span class="text-[11px] text-gray-400">{{ $row['email'] }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-col">
                                            <span class="text-[12px] font-bold text-gray-700 dark:text-gray-300">{{ $row['joining_date'] }}</span>
                                            <span class="text-[10px] text-gray-400 uppercase font-medium">Joined Staff</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-col text-right pr-4">
                                            <span class="text-[13px] font-black text-slate-800 dark:text-white">₹{{ number_format((float) $row['current_salary']) }}</span>
                                            <span class="text-[10px] text-emerald-600 font-bold uppercase tracking-wider">Active Roll</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex justify-end gap-2">
                                            <span class="w-8 h-8 flex items-center justify-center rounded-lg bg-blue-50 text-blue-600"><i class="fas fa-edit text-xs"></i></span>
                                            <span class="w-8 h-8 flex items-center justify-center rounded-lg bg-rose-50 text-rose-600"><i class="fas fa-trash text-xs"></i></span>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center">
                                            <i class="fas fa-users-slash text-4xl text-gray-300 mb-4"></i>
                                            <p class="text-lg text-gray-500">No staff records match your current search or filter criteria.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>

                        {{-- Alpine-managed rows --}}
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700 transition-opacity duration-150" x-cloak
                            :class="loading && rows.length &gt; 0 ? 'opacity-50' : 'opacity-100'">
                            <template x-for="(row, index) in rows" :key="row.id">
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors group">
                                    <td class="px-6 py-4 text-sm font-medium text-gray-400" x-text="(pagination.current_page - 1) * pagination.per_page + index + 1"></td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br flex items-center justify-center text-sm font-bold text-white shadow-sm shrink-0 overflow-hidden" 
                                                :class="'from-' + (row.post_color || 'slate') + '-500 to-' + (row.post_color || 'slate') + '-600'">
                                                <img x-show="row.staff_image" :src="row.staff_image" class="w-full h-full object-cover">
                                                <span x-show="!row.staff_image" x-text="row.initials"></span>
                                            </div>
                                            <div class="flex flex-col">
                                                <span class="text-[13px] font-bold text-gray-800 dark:text-white group-hover:text-teal-600 transition-colors" x-text="row.name"></span>
                                                <span class="text-[10px] text-gray-400 font-medium uppercase tracking-wider" x-text="'ID: #' + row.id"></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold border"
                                            :class="`bg-${row.post_color}-50 text-${row.post_color}-700 border-${row.post_color}-100 dark:bg-${row.post_color}-900/20 dark:border-${row.post_color}-800`"
                                            x-text="row.post_label">
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-col">
                                            <span class="text-[12px] font-semibold text-gray-700 dark:text-gray-300" x-text="row.class_name"></span>
                                            <span class="text-[11px] text-gray-400" x-text="row.section_name"></span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-col">
                                            <div class="flex items-center gap-1.5">
                                                <i class="fas fa-phone text-[10px] text-gray-300"></i>
                                                <span class="text-[12px] font-semibold text-gray-700 dark:text-gray-300" x-text="row.mobile"></span>
                                            </div>
                                            <div class="flex items-center gap-1.5">
                                                <i class="fas fa-envelope text-[10px] text-gray-300"></i>
                                                <span class="text-[11px] text-gray-400" x-text="row.email"></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-col">
                                            <span class="text-[12px] font-bold text-gray-700 dark:text-gray-300" x-text="row.joining_date"></span>
                                            <span class="text-[10px] text-gray-400 uppercase font-medium" x-text="'Joined Staff'"></span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-col text-right pr-4">
                                            <span class="text-[13px] font-black text-slate-800 dark:text-white" x-text="'₹' + Number(row.current_salary).toLocaleString()"></span>
                                            <span class="text-[10px] text-emerald-600 font-bold uppercase tracking-wider">Active Roll</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex justify-end gap-2">
                                            <button @click="openEditModal(row)" class="w-8 h-8 flex items-center justify-center rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 dark:bg-blue-900/30 dark:text-blue-400 transition-colors" title="Edit Staff">
                                                <i class="fas fa-edit text-xs"></i>
                                            </button>
                                            <button @click="confirmDelete(row)" class="w-8 h-8 flex items-center justify-center rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-100 dark:bg-rose-900/30 dark:text-rose-400 transition-colors" title="Delete Staff">
                                                <i class="fas fa-trash text-xs"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </template>

                            <!-- Empty State -->
                            <x-table.empty-state
                                colspan="8"
                                icon="fas fa-users-slash"
                                message="No staff records match your current search or filter criteria."
                            />
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Table Footer/Pagination -->
            <x-table.pagination :initial="$initialData['pagination']" />
        </div>

    {{-- Add/Edit Staff Modal --}}
    <x-modal name="staff-modal" alpineTitle="editMode ? 'Modify Staff Information' : 'Register New Staff'" maxWidth="6xl">
        <form @submit.prevent="submitForm()" method="POST" novalidate>
            @csrf
            <template x-if="editMode">
                <input type="hidden" name="_method" value="PUT">
            </template>

                <!-- Designated Post -->
                <div class="space-y-2 mb-6">
                    <label class="modal-label-premium">Designated Post <span class="text-red-600 font-bold">*</span></label>
                    <div class="relative group">
                        <select name="post" x-model="formData.post" @change="clearError('post')"
                            class="modal-input-premium no-select2" :class="{'border-red-500 ring-red-500/10': errors.post}">
                            <option value="">Select Designation</option>
                            @foreach(StaffPost::cases() as $post)
                                <option value="{{ $post->value }}">{{ $post->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <template x-if="errors.post">
                        <p class="modal-error-message" x-text="errors.post[0]"></p>
                    </template>
                </div>

                <div class="grid grid-cols-2 gap-6 mb-6">
                    <!-- Full Name -->
                    <div class="space-y-2">
                        <label class="modal-label-premium">Full Staff Name <span class="text-red-600 font-bold">*</span></label>
                        <div class="relative group">
                            <input type="text" name="name" x-model="formData.name"
                                @input="clearError('name')" placeholder="Legal full name"
                                class="modal-input-premium" :class="{'border-red-500 ring-red-500/10': errors.name}">
                        </div>
                        <template x-if="errors.name">
                            <p class="modal-error-message" x-text="errors.name[0]"></p>
                        </template>
                    </div>

                    <!-- Mobile -->
                    <div class="space-y-2">
                        <label class="modal-label-premium">Primary Contact No <span class="text-red-600 font-bold">*</span></label>
                        <div class="relative group">
                            <input type="tel" name="mobile" x-model="formData.mobile"
                                @input="clearError('mobile')" placeholder="Active mobile number"
                                class="modal-input-premium" :class="{'border-red-500 ring-red-500/10': errors.mobile}">
                        </div>
                        <template x-if="errors.mobile">
                            <p class="modal-error-message" x-text="errors.mobile[0]"></p>
                        </template>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-6 mb-6">
                    <!-- Email -->
                    <div class="space-y-2">
                        <label class="modal-label-premium">Email Address <span class="text-red-600 font-bold">*</span></label>
                        <div class="relative group">
                            <input type="email" name="email" x-model="formData.email"
                                @input="clearError('email')" placeholder="staff@school.com"
                                class="modal-input-premium" :class="{'border-red-500 ring-red-500/10': errors.email}">
                        </div>
                        <template x-if="errors.email">
                            <p class="modal-error-message" x-text="errors.email[0]"></p>
                        </template>
                    </div>

                    <!-- Password -->
                    <div class="space-y-2">
                        <label class="modal-label-premium">
                            Password
                            <span class="text-red-600 font-bold" x-show="!editMode">*</span>
                            <span class="text-gray-400 text-xs" x-show="editMode">(leave blank to keep current)</span>
                        </label>
                        <div class="relative group">
                            <input type="password" name="password" x-model="formData.password"
                                @input="clearError('password')" placeholder="Min. 8 characters"
                                class="modal-input-premium" :class="{'border-red-500 ring-red-500/10': errors.password}">
                        </div>
                        <template x-if="errors.password">
                            <p class="modal-error-message" x-text="errors.password[0]"></p>
                        </template>
                    </div>

                    <!-- Gender -->
                    <div class="space-y-2">
                        <label class="modal-label-premium">Gender <span class="text-red-600 font-bold">*</span></label>
                        <div class="relative group">
                            <select name="gender" x-model="formData.gender" @change="clearError('gender')"
                                class="modal-input-premium no-select2" :class="{'border-red-500 ring-red-500/10': errors.gender}">
                                <option value="">Select Gender</option>
                                @foreach(Gender::cases() as $gender)
                                    <option value="{{ $gender->value }}">{{ $gender->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                        <template x-if="errors.gender">
                            <p class="modal-error-message" x-text="errors.gender[0]"></p>
                        </template>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-6 mb-6">
                    <!-- Joining Date -->
                    <div class="space-y-2">
                        <label class="modal-label-premium">Joining Date <span class="text-red-600 font-bold">*</span></label>
                        <div class="relative group">
                            <input type="date" name="joining_date" x-model="formData.joining_date"
                                @input="clearError('joining_date')"
                                class="modal-input-premium !pr-10"
                                :class="{'border-red-500 ring-red-500/10': errors.joining_date}">
                            <div class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">
                                <i class="fas fa-calendar-alt text-sm"></i>
                            </div>
                        </div>
                        <template x-if="errors.joining_date">
                            <p class="modal-error-message" x-text="errors.joining_date[0]"></p>
                        </template>
                    </div>

                    <!-- Higher Qualification -->
                    <div class="space-y-2">
                        <label class="modal-label-premium">Higher Qualification</label>
                        <div class="relative group">
                            <select name="higher_qualification_id" x-model="formData.higher_qualification_id"
                                @change="clearError('higher_qualification_id')"
                                class="modal-input-premium no-select2" :class="{'border-red-500 ring-red-500/10': errors.higher_qualification_id}">
                                <option value="">Select Qualification</option>
                                @foreach($qualifications as $qualification)
                                    <option value="{{ $qualification->id }}">{{ $qualification->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <template x-if="errors.higher_qualification_id">
                            <p class="modal-error-message" x-text="errors.higher_qualification_id[0]"></p>
                        </template>
                    </div>
                </div>

                <!-- Teacher Assignment (Conditional) -->
                <div x-show="isTeacher" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                    <div class="grid grid-cols-2 gap-6 mb-6">
                        <!-- Class Assignment -->
                        <div class="space-y-2">
                            <label class="modal-label-premium">Class Assignment</label>
                            <div class="relative group">
                                <select name="class_id" id="class_id" x-model="formData.class_id"
                                    @change="loadSections(); clearError('class_id')"
                                    class="modal-input-premium no-select2" :disabled="!canSelectClass"
                                    :class="{'border-red-500 ring-red-500/10': errors.class_id}">
                                    <option value="">Select Class</option>
                                    @foreach($classes as $class)
                                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <template x-if="errors.class_id">
                                <p class="modal-error-message" x-text="errors.class_id[0]"></p>
                            </template>
                        </div>

                        <!-- Section Assignment -->
                        <div class="space-y-2">
                            <label class="modal-label-premium">Section Assignment</label>
                            <div class="relative group">
                                <select name="section_id" id="section_id" x-model="formData.section_id"
                                    @change="clearError('section_id')"
                                    class="modal-input-premium no-select2" :disabled="!canSelectSection"
                                    :class="{'border-red-500 ring-red-500/10': errors.section_id}">
                                    <option value="">Select Section</option>
                                    <template x-for="section in sections" :key="section.id">
                                        <option :value="section.id" x-text="section.name"></option>
                                    </template>
                                </select>
                            </div>
                            <template x-if="errors.section_id">
                                <p class="modal-error-message" x-text="errors.section_id[0]"></p>
                            </template>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-6 mb-6">
                    <!-- Total Experience -->
                    <div class="space-y-2">
                        <label class="modal-label-premium">Total Experience (Years)</label>
                        <div class="relative group">
                            <input type="number" name="total_experience" x-model="formData.total_experience"
                                @input="clearError('total_experience')" min="0" placeholder="0"
                                class="modal-input-premium" :class="{'border-red-500 ring-red-500/10': errors.total_experience}">
                        </div>
                        <template x-if="errors.total_experience">
                            <p class="modal-error-message" x-text="errors.total_experience[0]"></p>
                        </template>
                    </div>

                    <!-- Previous Institution -->
                    <div class="space-y-2">
                        <label class="modal-label-premium">Previous Institution / Company</label>
                        <div class="relative group">
                            <input type="text" name="previous_school_company_name" x-model="formData.previous_school_company_name"
                                @input="clearError('previous_school_company_name')" placeholder="Name of last employer"
                                class="modal-input-premium" :class="{'border-red-500 ring-red-500/10': errors.previous_school_company_name}">
                        </div>
                        <template x-if="errors.previous_school_company_name">
                            <p class="modal-error-message" x-text="errors.previous_school_company_name[0]"></p>
                        </template>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-6 mb-6">
                    <!-- Previous Salary -->
                    <div class="space-y-2">
                        <label class="modal-label-premium">Previous Salary (Monthly)</label>
                        <div class="relative group">
                            <span class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 font-bold text-sm pointer-events-none">₹</span>
                            <input type="number" name="previous_school_salary" x-model="formData.previous_school_salary"
                                @input="clearError('previous_school_salary')" step="0.01" min="0" placeholder="0.00"
                                class="modal-input-premium !pr-8" :class="{'border-red-500 ring-red-500/10': errors.previous_school_salary}">
                        </div>
                        <template x-if="errors.previous_school_salary">
                            <p class="modal-error-message" x-text="errors.previous_school_salary[0]"></p>
                        </template>
                    </div>

                    <!-- Current Salary -->
                    <div class="space-y-2">
                        <label class="modal-label-premium">Current Salary (Monthly) <span class="text-red-600 font-bold">*</span></label>
                        <div class="relative group">
                            <span class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 font-bold text-sm pointer-events-none">₹</span>
                            <input type="number" name="current_salary" x-model="formData.current_salary"
                                @input="clearError('current_salary')" step="0.01" min="0" placeholder="0.00"
                                class="modal-input-premium !pr-8" :class="{'border-red-500 ring-red-500/10': errors.current_salary}">
                        </div>
                        <template x-if="errors.current_salary">
                            <p class="modal-error-message" x-text="errors.current_salary[0]"></p>
                        </template>
                    </div>
                </div>

                <div class="grid grid-cols-4 gap-6 mb-6">
                    <!-- Country -->
                    <div class="space-y-2">
                        <label class="modal-label-premium">Country</label>
                        <div class="relative group">
                            <select name="country_id" x-model="formData.country_id" @change="clearError('country_id')"
                                class="modal-input-premium no-select2" data-location-cascade="true" data-country-select="true"
                                :class="{'border-red-500 ring-red-500/10': errors.country_id}">
                                <option value="">Select Country</option>
                                @foreach($countries as $country)
                                    <option value="{{ $country->id }}">{{ $country->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <template x-if="errors.country_id">
                            <p class="modal-error-message" x-text="errors.country_id[0]"></p>
                        </template>
                    </div>

                    <!-- State -->
                    <div class="space-y-2">
                        <label class="modal-label-premium">State</label>
                        <div class="relative group">
                            <select name="state_id" x-model="formData.state_id" @change="clearError('state_id')"
                                class="modal-input-premium no-select2" data-state-select="true"
                                :class="{'border-red-500 ring-red-500/10': errors.state_id}">
                                <option value="">Select State</option>
                            </select>
                        </div>
                        <template x-if="errors.state_id">
                            <p class="modal-error-message" x-text="errors.state_id[0]"></p>
                        </template>
                    </div>

                    <!-- City -->
                    <div class="space-y-2">
                        <label class="modal-label-premium">City</label>
                        <div class="relative group">
                            <select name="city_id" x-model="formData.city_id" @change="clearError('city_id')"
                                class="modal-input-premium no-select2" data-city-select="true"
                                :class="{'border-red-500 ring-red-500/10': errors.city_id}">
                                <option value="">Select City</option>
                            </select>
                        </div>
                        <template x-if="errors.city_id">
                            <p class="modal-error-message" x-text="errors.city_id[0]"></p>
                        </template>
                    </div>

                    <!-- Zip Code -->
                    <div class="space-y-2">
                        <label class="modal-label-premium">Zip Code</label>
                        <div class="relative group">
                            <input type="text" name="zip_code" x-model="formData.zip_code"
                                @input="clearError('zip_code')" placeholder="PIN / ZIP"
                                class="modal-input-premium" :class="{'border-red-500 ring-red-500/10': errors.zip_code}">
                        </div>
                        <template x-if="errors.zip_code">
                            <p class="modal-error-message" x-text="errors.zip_code[0]"></p>
                        </template>
                    </div>
                </div>

                <!-- Address -->
                <div class="space-y-2 mb-6">
                    <label class="modal-label-premium">Permanent Address</label>
                    <div class="relative group">
                        <textarea name="address" x-model="formData.address"
                            @input="clearError('address')" rows="2" placeholder="House no, Street, Landmark..."
                            class="modal-input-premium" :class="{'border-red-500 ring-red-500/10': errors.address}"></textarea>
                    </div>
                    <template x-if="errors.address">
                        <p class="modal-error-message" x-text="errors.address[0]"></p>
                    </template>
                </div>

                <!-- Aadhaar Number -->
                <div class="space-y-2 mb-6">
                    <label class="modal-label-premium">Aadhaar Card Number</label>
                    <div class="relative group">
                        <input type="text" name="aadhaar_no" x-model="formData.aadhaar_no"
                            @input="clearError('aadhaar_no')" placeholder="12-digit Aadhaar number"
                            class="modal-input-premium" :class="{'border-red-500 ring-red-500/10': errors.aadhaar_no}">
                    </div>
                    <template x-if="errors.aadhaar_no">
                        <p class="modal-error-message" x-text="errors.aadhaar_no[0]"></p>
                    </template>
                </div>

                <div class="grid grid-cols-2 gap-6 mb-6">
                    <!-- Aadhaar Card Upload -->
                    <div class="space-y-2">
                        <label class="modal-label-premium">Aadhaar Card Document</label>
                        <div class="relative group">
                            <div class="flex items-center gap-4">
                                <div class="w-20 h-20 bg-slate-50 dark:bg-gray-700 border-2 border-dashed border-slate-200 dark:border-gray-600 rounded-xl flex items-center justify-center overflow-hidden shrink-0">
                                    <img :src="formData.aadhaar_card_preview" x-show="formData.aadhaar_card_preview" class="w-full h-full object-cover">
                                    <i x-show="!formData.aadhaar_card_preview" class="fas fa-id-card text-xl text-slate-300"></i>
                                </div>
                                <div class="flex-1">
                                    <label class="cursor-pointer inline-flex items-center px-4 py-2 bg-white dark:bg-gray-700 border border-slate-200 dark:border-gray-600 text-slate-600 dark:text-gray-300 rounded-xl text-xs font-semibold hover:bg-slate-50 dark:hover:bg-gray-600 transition-colors shadow-sm">
                                        <i class="fas fa-upload mr-2 text-slate-400"></i> Choose File
                                        <input type="file" x-ref="aadhaarCardInput" accept=".pdf,.jpg,.jpeg,.png"
                                            @change="previewAadhaarCard($event); clearError('aadhaar_card')" class="hidden">
                                    </label>
                                    <p class="text-[10px] text-slate-400 mt-1">PDF, JPG, PNG (max 2MB)</p>
                                </div>
                            </div>
                        </div>
                        <template x-if="errors.aadhaar_card">
                            <p class="modal-error-message" x-text="errors.aadhaar_card[0]"></p>
                        </template>
                    </div>

                    <!-- Staff Image Upload -->
                    <div class="space-y-2">
                        <label class="modal-label-premium">Staff Photograph</label>
                        <div class="relative group">
                            <div class="flex items-center gap-4">
                                <div class="w-20 h-20 bg-slate-50 dark:bg-gray-700 border-2 border-dashed border-slate-200 dark:border-gray-600 rounded-xl flex items-center justify-center overflow-hidden shrink-0">
                                    <img :src="formData.staff_image_preview" x-show="formData.staff_image_preview" class="w-full h-full object-cover">
                                    <i x-show="!formData.staff_image_preview" class="fas fa-camera text-xl text-slate-300"></i>
                                </div>
                                <div class="flex-1">
                                    <label class="cursor-pointer inline-flex items-center px-4 py-2 bg-white dark:bg-gray-700 border border-slate-200 dark:border-gray-600 text-slate-600 dark:text-gray-300 rounded-xl text-xs font-semibold hover:bg-slate-50 dark:hover:bg-gray-600 transition-colors shadow-sm">
                                        <i class="fas fa-upload mr-2 text-slate-400"></i> Choose File
                                        <input type="file" x-ref="staffImageInput" accept=".jpg,.jpeg,.png"
                                            @change="previewStaffImage($event); clearError('staff_image')" class="hidden">
                                    </label>
                                    <p class="text-[10px] text-slate-400 mt-1">JPG, PNG (max 2MB)</p>
                                </div>
                            </div>
                        </div>
                        <template x-if="errors.staff_image">
                            <p class="modal-error-message" x-text="errors.staff_image[0]"></p>
                        </template>
                    </div>
                </div>

                <!-- Notice Card (same style as Academic Year toggle card) -->
                <div class="mb-8 flex items-center justify-between bg-[#f0f5ff] dark:bg-indigo-900/20 border border-[#e5edff] dark:border-indigo-800 p-5 rounded-2xl shadow-sm">
                    <div class="flex flex-col">
                        <span class="text-sm font-bold text-slate-900 dark:text-gray-100 leading-tight">Note</span>
                        <span class="text-[10px] text-slate-500 dark:text-gray-400 font-bold uppercase mt-1 tracking-wide opacity-80">Ensure salary and qualification details are verified before saving.</span>
                    </div>
                    <div class="w-10 h-10 bg-white rounded-xl shadow-sm flex items-center justify-center shrink-0">
                        <i class="fas fa-shield-check text-indigo-600 text-sm"></i>
                    </div>
                </div>

                <!-- Footer -->
                <x-slot name="footer">
                    <button type="button" @click="$dispatch('close-modal', 'staff-modal')" class="btn-premium-cancel px-10">
                        Cancel
                    </button>
                    <button type="button" @click="submitForm()" :disabled="submitting" class="btn-premium-primary min-w-[160px]">
                        <template x-if="submitting">
                            <span class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin mr-3 inline-block"></span>
                        </template>
                        <span x-text="editMode ? 'Update Record' : 'Register Staff'"></span>
                    </button>
                </x-slot>
        </form>
    </x-modal>

    {{-- Delete Confirmation Modal --}}
    <x-confirm-modal />
</div>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('staffManagementData', () => ({
        editMode: false,
        submitting: false,
        staffId: null,
        sections: [],
        errors: {},
        exporting: false,
        
        formData: {
            post: '',
            class_id: '',
            section_id: '',
            name: '',
            mobile: '',
            email: '',
            password: '',
            gender: '',
            total_experience: '',
            previous_school_salary: '',
            current_salary: '',
            country_id: '',
            state_id: '',
            city_id: '',
            zip_code: '',
            address: '',
            aadhaar_no: '',
            aadhaar_card_preview: '',
            staff_image_preview: '',
            joining_date: '',
            higher_qualification_id: '',
            previous_school_company_name: '',
        },

        get isTeacher() {
            return String(this.formData.post) === '2';
        },
        get canSelectClass() {
            return this.isTeacher;
        },
        get canSelectSection() {
            return this.isTeacher && !!this.formData.class_id;
        },
        
        init() {
            // Listen for changes from select2 or other components if necessary
            this.$watch('formData.post', (newValue) => {
                if (String(newValue) !== '2') {
                    this.formData.class_id = '';
                    this.formData.section_id = '';
                    this.sections = [];
                }
            });

            this.$watch('formData.class_id', (newValue, oldValue) => {
                if (newValue !== oldValue) {
                    this.formData.section_id = '';
                }
            });
            
            // Re-initialization not needed for no-select2 fields
            this.$nextTick(() => {
                // Any additional initialization if required
            });
        },

        async submitForm() {
            if (this.submitting) return;
            this.submitting = true;
            this.errors = {};

            const url = this.editMode
                ? `/receptionist/staff/${this.staffId}`
                : '{{ route("receptionist.staff.store") }}';

            try {
                const fd = new FormData();
                fd.append('_token', '{{ csrf_token() }}');
                if (this.editMode) fd.append('_method', 'PUT');

                const fields = [
                    'post', 'class_id', 'section_id', 'name', 'mobile', 'email', 'password',
                    'gender', 'total_experience', 'previous_school_salary', 'current_salary',
                    'country_id', 'state_id', 'city_id', 'zip_code', 'address',
                    'aadhaar_no', 'joining_date', 'higher_qualification_id', 'previous_school_company_name'
                ];
                
                fields.forEach(f => {
                    if (this.formData[f] !== '' && this.formData[f] !== null) {
                        fd.append(f, this.formData[f]);
                    }
                });

                if (this.$refs.aadhaarCardInput?.files.length) fd.append('aadhaar_card', this.$refs.aadhaarCardInput.files[0]);
                if (this.$refs.staffImageInput?.files.length) fd.append('staff_image', this.$refs.staffImageInput.files[0]);

                const response = await fetch(url, {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: fd
                });

                const result = await response.json();
                if (response.ok) {
                    window.Toast?.fire({ icon: 'success', title: result.message });
                    this.fetchData(); // Refresh table
                    this.$dispatch('close-modal', 'staff-modal');
                } else if (response.status === 422) {
                    this.errors = result.errors || {};
                    window.Toast?.fire({ icon: 'error', title: window.resolveApiMessage(result, window.firstValidationMessage(this.errors)) });
                } else {
                    throw new Error(window.resolveApiMessage(result, 'System error'));
                }
            } catch (error) {
                window.Toast?.fire({ icon: 'error', title: window.resolveApiMessage(error.response?.data || { message: error.message }, error.message || 'System error') });
            } finally {
                this.submitting = false;
            }
        },

        openAddModal() {
            this.editMode = false;
            this.staffId = null;
            this.resetForm();
            this.$nextTick(() => {
                this.$dispatch('open-modal', 'staff-modal');
            });
        },
        
        openEditModal(row) {
            this.editMode = true;
            this.staffId = row.id;
            this.errors = {};
            this.formData = { ...row };
            
            // Fix joining date format for HTML5 date input (ensure YYYY-MM-DD)
            this.formData.joining_date = row.joining_date_raw || '';
            
            this.$nextTick(() => {
                this.$dispatch('open-modal', 'staff-modal');
                
                // Handle teacher assignments cascading
                if (this.formData.class_id && this.isTeacher) {
                    this.loadSections();
                    // Let segments load before setting the section value again to be safe
                    setTimeout(() => {
                        this.formData.section_id = row.section_id;
                    }, 300);
                }

                // Location Logic - Cascading selects
                if (window.locationCascade && this.formData.country_id) {
                    const countrySelect = document.querySelector('select[name="country_id"]');
                    const stateSelect = document.querySelector('select[name="state_id"]');
                    const citySelect = document.querySelector('select[name="city_id"]');

                    window.locationCascade.loadStates(stateSelect, this.formData.country_id, this.formData.state_id);
                    
                    // Use a slightly longer timeout for city to ensure state options are rendered
                    setTimeout(() => {
                        if (this.formData.state_id) {
                            window.locationCascade.loadCities(citySelect, this.formData.state_id, this.formData.city_id);
                        }
                    }, 500);
                }
            });
        },
        
        async confirmDelete(row) {
            window.dispatchEvent(new CustomEvent('open-confirm-modal', {
                detail: {
                    title: 'Relieve Staff Member',
                    message: `Are you sure you want to permanently delete "${row.name}" from the institutional registry?`,
                    callback: async () => {
                        try {
                            const res = await fetch(`/receptionist/staff/${row.id}`, {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                                body: JSON.stringify({ _method: 'DELETE' })
                            });
                            const result = await res.json();
                            if (res.ok) {
                                window.Toast?.fire({ icon: 'success', title: result.message });
                                this.fetchData();
                            } else {
                                throw new Error(result.message);
                            }
                        } catch (e) {
                            window.Toast?.fire({ icon: 'error', title: e.message });
                        }
                    }
                }
            }));
        },

        exportData(format = 'csv') {
            this.exporting = true;
            const params = new URLSearchParams(this.filters);
            params.append('export', format);
            window.location.href = `{{ route('receptionist.staff.index') }}?${params.toString()}`;
            setTimeout(() => { this.exporting = false; }, 2000);
        },

        loadSections() {
            if (!this.formData.class_id || !this.isTeacher) return;
            fetch(`/receptionist/staff/get-sections/${this.formData.class_id}`)
                .then(res => res.json())
                .then(data => { this.sections = data.sections || []; })
                .catch(err => console.error('Section fetch failed:', err));
        },

        previewAadhaarCard(e) {
            const file = e.target.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = (ev) => this.formData.aadhaar_card_preview = ev.target.result;
            reader.readAsDataURL(file);
        },

        previewStaffImage(e) {
            const file = e.target.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = (ev) => this.formData.staff_image_preview = ev.target.result;
            reader.readAsDataURL(file);
        },


        clearError(field) {
                if (this.errors && this.errors[field]) { const e = { ...this.errors }; delete e[field]; this.errors = e; }
            },

        resetForm() {
            this.formData = {
                post: '', class_id: '', section_id: '', name: '', mobile: '', email: '', password: '', gender: '',
                total_experience: '', previous_school_salary: '', current_salary: '', country_id: '',
                state_id: '', city_id: '', zip_code: '', address: '', aadhaar_no: '',
                aadhaar_card_preview: '', staff_image_preview: '', joining_date: '',
                higher_qualification_id: '', previous_school_company_name: '',
            };
            this.sections = [];
            this.errors = {};
            if (this.$refs.aadhaarCardInput) this.$refs.aadhaarCardInput.value = '';
            if (this.$refs.staffImageInput) this.$refs.staffImageInput.value = '';
        }
    }));
});
</script>
@endpush
@endsection
