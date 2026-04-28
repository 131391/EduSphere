@php
    use App\Enums\VisitorPriority;
    use App\Enums\VisitorMode;
@endphp
@extends('layouts.receptionist')

@section('title', 'Visitor Management - Receptionist')
@section('page-title', 'Visitor Entry')
@section('page-description', 'Manage visitor entries and appointments')

@section('content')
    <div x-data="Object.assign(ajaxDataTable({
        fetchUrl: '{{ route('receptionist.visitors.fetch') }}',
        defaultSort: 'created_at',
        defaultDirection: 'desc',
        defaultPerPage: 25,
        defaultFilters: { priority: '', meeting_type: '' },
        initialRows: @js($initialData['rows']),
        initialPagination: @js($initialData['pagination']),
        initialStats: @js($initialData['stats']),
        filterLabels: {
            priority: {
                @foreach($priorities as $p) '{{ $p->value }}': '{{ $p->label() }}', @endforeach
            },
            meeting_type: {
                @foreach($meetingTypes as $m) '{{ $m->value }}': '{{ $m->label() }}', @endforeach
            }
        }
    }), visitorManagementData())" class="space-y-6" @close-modal.window="if ($event.detail === 'visitor-modal') { resetForm(); }">
        
        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <x-stat-card label="Total Visitors" :value="$stats['total']" icon="fas fa-users" color="blue" alpine-text="stats.total" />
            <x-stat-card label="Online" :value="$stats['online']" icon="fas fa-video" color="emerald" alpine-text="stats.online" />
            <x-stat-card label="Campus" :value="$stats['offline']" icon="fas fa-building" color="amber" alpine-text="stats.offline" />
            <x-stat-card label="Cancelled" :value="$stats['cancelled']" icon="fas fa-times-circle" color="rose" alpine-text="stats.cancelled" />
            <x-stat-card label="Meetings" :value="$stats['office']" icon="fas fa-laptop" color="indigo" alpine-text="stats.office" />
        </div>

        <!-- Header Section -->
        <x-page-header title="Visitor Management" description="Manage visitor entries and appointments" icon="fas fa-users-cog">
            <button @click="openAddModal()"
                class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-teal-600 to-emerald-600 hover:from-teal-700 hover:to-emerald-700 text-white text-sm font-semibold rounded-xl transition-all shadow-md hover:shadow-lg active:scale-95">
                <i class="fas fa-plus mr-2 text-xs"></i>
                New Visitor
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
                        <h2 class="text-lg font-semibold text-gray-800 dark:text-white">Visitor List</h2>
                        <x-table.search placeholder="Search visitors..." />
                    </div>

                    <!-- Right: Filters and Actions -->
                    <div class="flex items-center gap-3">
                        <x-table.filter-select
                            model="filters.priority"
                            action="applyFilter('priority', $event.target.value)"
                            placeholder="Priority"
                            :options="collect($priorities)->mapWithKeys(fn($p) => [$p->value => $p->label()])->toArray()"
                        />

                        <x-table.filter-select
                            model="filters.meeting_type"
                            action="applyFilter('meeting_type', $event.target.value)"
                            placeholder="Meeting Type"
                            :options="collect($meetingTypes)->mapWithKeys(fn($m) => [$m->value => $m->label()])->toArray()"
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
            <div class="overflow-x-auto relative ajax-table-wrapper">
                <x-table.loading-overlay />

                <table class="w-full text-left border-collapse">
                    <thead class="bg-gray-50/50 dark:bg-gray-700/50 border-b border-gray-100 dark:border-gray-700">
                        <tr>
                            <x-table.sort-header column="visitor_no" label="Visitor No" sort-var="sort" direction-var="direction" />
                            <x-table.sort-header column="name" label="Visitor Identity" sort-var="sort" direction-var="direction" />
                             <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Contact & Proof</th>
                             <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Purpose & Meeting</th>
                             <x-table.sort-header column="status" label="Status" sort-var="sort" direction-var="direction" />
                             <x-table.sort-header column="created_at" label="Recent Activity" sort-var="sort" direction-var="direction" />
                             <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider w-32">Actions</th>
                        </tr>
                    </thead>

                    {{-- Server-rendered rows: visible instantly, hidden once Alpine initializes --}}
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700" x-show="!hydrated" x-cloak>
                        @if(empty($initialData['rows']))
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <i class="fas fa-users-slash text-4xl text-gray-300 mb-4"></i>
                                    <p class="text-lg text-gray-500">No visitors found matching your criteria.</p>
                                </div>
                            </td>
                        </tr>
                        @endif
                        @foreach($initialData['rows'] as $row)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-xs font-bold text-teal-600 bg-teal-50 px-2 py-1 rounded-lg border border-teal-100">{{ $row['visitor_no'] }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-teal-500 to-emerald-500 flex items-center justify-center text-white font-bold text-xs shadow-sm">{{ $row['initials'] }}</div>
                                    <div>
                                        <div class="text-sm font-bold text-gray-800 dark:text-gray-100">{{ $row['name'] }}</div>
                                        <div class="text-[10px] font-medium text-gray-400">{{ $row['visitor_no'] }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="space-y-1">
                                    <div class="flex items-center gap-1.5 text-xs font-medium text-gray-600 dark:text-gray-300">
                                        <i class="fas fa-phone text-[10px] text-gray-400"></i>
                                        {{ $row['mobile'] }}
                                    </div>
                                    @if($row['email'])
                                    <div class="flex items-center gap-1.5 text-[10px] text-gray-400">
                                        <i class="fas fa-envelope text-[9px]"></i>
                                        {{ $row['email'] }}
                                    </div>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="space-y-1">
                                    <div class="text-xs font-bold text-gray-700 dark:text-gray-200">{{ $row['visit_purpose'] }}</div>
                                    <div class="text-[10px] text-gray-500 dark:text-gray-400 flex items-center gap-1">
                                        <i class="fas fa-user-tie text-[9px]"></i>
                                         With: <span class="font-bold">{{ $row['meeting_with'] }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php $config = $row['status_config']; @endphp
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full border text-[10px] font-bold uppercase tracking-wider {{ $config['bg'] }} {{ $config['text'] }} {{ $config['border'] }}">
                                    <i class="fas {{ $config['icon'] }} text-[8px]"></i>
                                    {{ $row['status_label'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="space-y-1 text-right">
                                    <div class="text-[10px] font-bold text-gray-700 dark:text-gray-300">
                                        <i class="far fa-clock mr-1 text-gray-400"></i>
                                        {{ $row['check_in'] }}
                                    </div>
                                    <div class="text-[9px] text-gray-400">Scheduled: {{ $row['scheduled_at'] }}</div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('receptionist.visitors.show', $row['id']) }}" class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center hover:bg-emerald-100 transition-colors" title="View"><i class="fas fa-eye text-xs"></i></a>
                                    <button @click="openEditModal(@js($row))" class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center hover:bg-blue-100 transition-colors" title="Edit"><i class="fas fa-edit text-xs"></i></button>
                                    @if($row['can_check_in'])
                                        <button @click="quickAction('{{ route('receptionist.visitors.check-in', $row['id']) }}', 'Check In')" class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center hover:bg-indigo-100 transition-colors" title="Check In"><i class="fas fa-sign-in-alt text-xs"></i></button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>

                    {{-- Alpine-managed rows: takes over once initialized --}}
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700 transition-opacity duration-150" x-cloak :class="loading && rows.length &gt; 0 ? 'opacity-50' : 'opacity-100'">
                        <template x-for="row in rows" :key="row.id">
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-xs font-bold text-teal-600">
                                    <span class="bg-teal-50 px-2 py-1 rounded-lg border border-teal-100" x-text="row.visitor_no"></span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-teal-500 to-emerald-500 flex items-center justify-center text-white font-bold text-xs shadow-sm" x-text="row.initials"></div>
                                        <div>
                                            <div class="text-sm font-bold text-gray-800 dark:text-gray-100" x-text="row.name"></div>
                                            <div class="text-[10px] font-medium text-gray-400" x-text="row.visitor_no"></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="space-y-1">
                                        <div class="flex items-center gap-1.5 text-xs font-medium text-gray-600 dark:text-gray-300">
                                            <i class="fas fa-phone text-[10px] text-gray-400"></i>
                                            <span x-text="row.mobile"></span>
                                        </div>
                                        <div x-show="row.email" class="flex items-center gap-1.5 text-[10px] text-gray-400">
                                            <i class="fas fa-envelope text-[9px]"></i>
                                            <span x-text="row.email"></span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="space-y-1">
                                        <div class="text-xs font-bold text-gray-700 dark:text-gray-200" x-text="row.visit_purpose"></div>
                                        <div class="text-[10px] text-gray-500 dark:text-gray-400 flex items-center gap-1">
                                            <i class="fas fa-user-tie text-[9px]"></i>
                                             With: <span class="font-bold text-gray-700 dark:text-gray-300" x-text="row.meeting_with"></span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full border text-[10px] font-bold uppercase tracking-wider shadow-sm"
                                          :class="`${row.status_config.bg} ${row.status_config.text} ${row.status_config.border}`">
                                        <i class="fas text-[8px]" :class="row.status_config.icon"></i>
                                        <span x-text="row.status_label"></span>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="space-y-1 text-right">
                                        <div class="text-[10px] font-bold text-gray-700 dark:text-gray-300">
                                            <i class="far fa-clock mr-1 text-gray-400"></i>
                                            <span x-text="row.check_in"></span>
                                        </div>
                                        <div class="text-[9px] text-gray-400">Sched: <span x-text="row.scheduled_at"></span></div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <a :href="`/receptionist/visitors/${row.id}`" class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center hover:bg-emerald-100 transition-colors"><i class="fas fa-eye text-xs"></i></a>
                                        <button @click="openEditModal(row)" class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center hover:bg-blue-100 transition-colors"><i class="fas fa-edit text-xs"></i></button>
                                        <template x-if="row.can_check_in">
                                            <button @click="quickAction(`/receptionist/visitors/${row.id}/check-in`, 'Check In')" class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center hover:bg-indigo-100 transition-colors" title="Check In"><i class="fas fa-sign-in-alt text-xs"></i></button>
                                        </template>
                                        <template x-if="row.can_check_out">
                                            <button @click="quickAction(`/receptionist/visitors/${row.id}/check-out`, 'Check Out')" class="w-8 h-8 rounded-lg bg-orange-50 text-orange-600 flex items-center justify-center hover:bg-orange-100 transition-colors" title="Check Out"><i class="fas fa-sign-out-alt text-xs"></i></button>
                                        </template>
                                    </div>
                                </td>
                            </tr>
                        </template>

                        <x-table.empty-state :colspan="7" icon="fas fa-users-slash" message="No visitors found matching your criteria." />
                    </tbody>
                </table>
            </div>

            <!-- Server-rendered pagination: visible instantly, hidden once Alpine takes over -->
            <x-table.pagination />
        </div>

        <x-modal name="visitor-modal" alpineTitle="editMode ? 'Modify Visitor Information' : 'Register New Visitor'"
            maxWidth="4xl">
            <form @submit.prevent="submitForm()" id="visitorForm" method="POST" novalidate class="p-1">
                @csrf
                <template x-if="editMode">
                    <input type="hidden" name="_method" value="PUT">
                </template>
                <input type="hidden" name="visitor_id" :value="visitorId" x-show="editMode">

                <div class="grid grid-cols-2 gap-6 mb-6">
                    <!-- Mobile No -->
                    <div class="space-y-2">
                        <label class="modal-label-premium">Mobile No <span class="text-red-600 font-bold">*</span></label>
                        <div class="relative group">
                            <input type="tel" name="mobile" x-model="formData.mobile" @input="clearError('mobile')"
                                pattern="[0-9]{10,15}" inputmode="numeric"
                                oninput="this.value = this.value.replace(/[^0-9]/g, '')" 
                                class="w-full bg-white dark:bg-gray-700 border rounded-xl py-3 px-4 text-sm font-bold text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-teal-500/20 transition-all font-premium"
                                placeholder="Enter contact number"
                                :class="errors.mobile ? 'border-red-500' : 'border-slate-200 dark:border-gray-600'">
                        </div>
                        <template x-if="errors.mobile">
                            <p class="modal-error-message" x-text="errors.mobile[0]"></p>
                        </template>
                    </div>

                    <!-- Visitor's Name -->
                    <div class="space-y-2">
                        <label class="modal-label-premium">Visitor's Name <span
                                class="text-red-600 font-bold">*</span></label>
                        <div class="relative group">
                            <input type="text" name="name" x-model="formData.name" @input="clearError('name')"
                                class="w-full bg-white dark:bg-gray-700 border rounded-xl py-3 px-4 text-sm font-bold text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-teal-500/20 transition-all font-premium" placeholder="Full name of visitor"
                                :class="errors.name ? 'border-red-500' : 'border-slate-200 dark:border-gray-600'">
                        </div>
                        <template x-if="errors.name">
                            <p class="modal-error-message" x-text="errors.name[0]"></p>
                        </template>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-6 mb-6">
                    <!-- Email ID -->
                    <div class="space-y-2">
                        <label class="modal-label-premium">Email ID</label>
                        <div class="relative group">
                            <input type="email" name="email" x-model="formData.email" @input="clearError('email')"
                                class="w-full bg-white dark:bg-gray-700 border rounded-xl py-3 px-4 text-sm font-bold text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-teal-500/20 transition-all font-premium" placeholder="visitor@example.com"
                                :class="errors.email ? 'border-red-500' : 'border-slate-200 dark:border-gray-600'">
                        </div>
                        <template x-if="errors.email">
                            <p class="modal-error-message" x-text="errors.email[0]"></p>
                        </template>
                    </div>

                    <!-- Address -->
                    <div class="space-y-2">
                        <label class="modal-label-premium">Address</label>
                        <div class="relative group">
                            <input type="text" name="address" x-model="formData.address" @input="clearError('address')"
                                class="w-full bg-white dark:bg-gray-700 border rounded-xl py-3 px-4 text-sm font-bold text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-teal-500/20 transition-all font-premium" placeholder="City, Area"
                                :class="errors.address ? 'border-red-500' : 'border-slate-200 dark:border-gray-600'">
                        </div>
                        <template x-if="errors.address">
                            <p class="modal-error-message" x-text="errors.address[0]"></p>
                        </template>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-6 mb-6">
                    <!-- Visitor Type -->
                    <div class="space-y-2">
                        <label class="modal-label-premium">Visitor Type <span
                                class="text-red-600 font-bold">*</span></label>
                        <div class="relative group">
                            <select name="visitor_type" x-model="formData.visitor_type" @change="clearError('visitor_type')"
                                class="w-full bg-white dark:bg-gray-700 border rounded-xl py-3 px-4 text-sm font-bold text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-teal-500/20 transition-all no-select2"
                                :class="errors.visitor_type ? 'border-red-500' : 'border-slate-200 dark:border-gray-600'">
                                <option value="">Select Type</option>
                                @foreach($visitorTypes as $type)
                                     <option value="{{ $type->value }}">{{ $type->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                        <template x-if="errors.visitor_type">
                            <p class="modal-error-message" x-text="errors.visitor_type[0]"></p>
                        </template>
                    </div>

                    <!-- Visit Purpose -->
                    <div class="space-y-2">
                        <label class="modal-label-premium">Visit Purpose <span
                                class="text-red-600 font-bold">*</span></label>
                        <div class="relative group">
                            <select name="visit_purpose" x-model="formData.visit_purpose"
                                @change="clearError('visit_purpose')" class="w-full bg-white dark:bg-gray-700 border rounded-xl py-3 px-4 text-sm font-bold text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-teal-500/20 transition-all no-select2"
                                :class="errors.visit_purpose ? 'border-red-500' : 'border-slate-200 dark:border-gray-600'">
                                <option value="">Select Purpose</option>
                                @foreach($visitPurposes as $purpose)
                                    <option value="{{ $purpose->value }}">{{ $purpose->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                        <template x-if="errors.visit_purpose">
                            <p class="modal-error-message" x-text="errors.visit_purpose[0]"></p>
                        </template>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-6 mb-6">
                    <!-- Meeting with -->
                    <div class="space-y-2">
                        <label class="modal-label-premium">Select Meeting with <span
                                class="text-red-600 font-bold">*</span></label>
                        <div class="relative group">
                            <select name="meeting_with" x-model="formData.meeting_with" @change="clearError('meeting_with')"
                                class="w-full bg-white dark:bg-gray-700 border rounded-xl py-3 px-4 text-sm font-bold text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-teal-500/20 transition-all no-select2"
                                :class="errors.meeting_with ? 'border-red-500' : 'border-slate-200 dark:border-gray-600'">
                                <option value="">Select Person</option>
                                @foreach($meetingWithCases as $person)
                                    <option value="{{ $person->value }}">{{ $person->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                        <template x-if="errors.meeting_with">
                            <p class="modal-error-message" x-text="errors.meeting_with[0]"></p>
                        </template>
                    </div>

                    <!-- Meeting Purpose -->
                    <div class="space-y-2">
                        <label class="modal-label-premium">Meeting Purpose</label>
                        <div class="relative group">
                            <input type="text" name="meeting_purpose" x-model="formData.meeting_purpose"
                                @input="clearError('meeting_purpose')" class="w-full bg-white dark:bg-gray-700 border rounded-xl py-3 px-4 text-sm font-bold text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-teal-500/20 transition-all"
                                placeholder="Specific reason for meeting"
                                :class="errors.meeting_purpose ? 'border-red-500' : 'border-slate-200 dark:border-gray-600'">
                        </div>
                        <template x-if="errors.meeting_purpose">
                            <p class="modal-error-message" x-text="errors.meeting_purpose[0]"></p>
                        </template>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-6 mb-6">
                    <!-- Meeting Type -->
                    <div class="space-y-2">
                        <label class="modal-label-premium">Meeting Type <span
                                class="text-red-600 font-bold">*</span></label>
                        <div class="relative group">
                            <select name="meeting_type" x-model="formData.meeting_type" @change="clearError('meeting_type')"
                                class="w-full bg-white dark:bg-gray-700 border rounded-xl py-3 px-4 text-sm font-bold text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-teal-500/20 transition-all no-select2"
                                :class="errors.meeting_type ? 'border-red-500' : 'border-slate-200 dark:border-gray-600'">
                                <option value="">Select Meeting Type</option>
                                @foreach($meetingTypes as $meetingType)
                                    <option value="{{ $meetingType->value }}">{{ $meetingType->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                        <template x-if="errors.meeting_type">
                            <p class="modal-error-message" x-text="errors.meeting_type[0]"></p>
                        </template>
                    </div>

                    <!-- Priority -->
                    <div class="space-y-2">
                        <label class="modal-label-premium">Priority <span class="text-red-600 font-bold">*</span></label>
                        <div class="relative group">
                            <select name="priority" x-model="formData.priority" @change="clearError('priority')"
                                class="w-full bg-white dark:bg-gray-700 border rounded-xl py-3 px-4 text-sm font-bold text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-teal-500/20 transition-all no-select2"
                                :class="errors.priority ? 'border-red-500' : 'border-slate-200 dark:border-gray-600'">
                                <option value="">Select Priority</option>
                                @foreach($priorities as $priority)
                                    <option value="{{ $priority->value }}">{{ $priority->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                        <template x-if="errors.priority">
                            <p class="modal-error-message" x-text="errors.priority[0]"></p>
                        </template>
                    </div>

                    <!-- No. of Guest(s) -->
                    <div class="space-y-2">
                        <label class="modal-label-premium">No. of Guest(s)</label>
                        <div class="relative group">
                            <input type="number" name="no_of_guests" x-model="formData.no_of_guests" min="1"
                                @input="clearError('no_of_guests')" class="w-full bg-white dark:bg-gray-700 border rounded-xl py-3 px-4 text-sm font-bold text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-teal-500/20 transition-all"
                                :class="errors.no_of_guests ? 'border-red-500' : 'border-slate-200 dark:border-gray-600'">
                        </div>
                        <template x-if="errors.no_of_guests">
                            <p class="modal-error-message" x-text="errors.no_of_guests[0]"></p>
                        </template>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-6 mb-6">
                    <!-- Visitor Photo Upload -->
                    <div class="space-y-2">
                        <label class="modal-label-premium">Visitor's Photo</label>
                        <div class="relative group">
                            <div class="flex items-center gap-4">
                                <div
                                    class="w-20 h-20 bg-white dark:bg-gray-700 border-2 border-dashed border-slate-200 dark:border-gray-600 rounded-xl flex items-center justify-center overflow-hidden shrink-0 relative">
                                    <img id="visitor-photo-preview" src="#" alt="Visitor's Photo"
                                        class="hidden w-full h-full object-cover">
                                    <i id="visitor-photo-icon" class="fas fa-camera text-xl text-slate-300"></i>
                                    <button type="button" id="visitor-photo-remove"
                                        onclick="removeImage(event, 'visitor_photo', 'visitor-photo-preview', 'visitor-photo-icon', 'visitor-photo-remove')"
                                        class="hidden absolute -top-1 -right-1 bg-red-500 hover:bg-red-600 text-white rounded-full w-6 h-6 flex items-center justify-center transition-all duration-200 shadow-sm z-10">
                                        <i class="fas fa-times text-[10px]"></i>
                                    </button>
                                </div>
                                <div class="flex-1">
                                    <label
                                        class="cursor-pointer inline-flex items-center px-4 py-2 bg-white dark:bg-gray-700 border border-slate-200 dark:border-gray-600 text-slate-600 dark:text-gray-300 rounded-xl text-xs font-semibold hover:bg-slate-50 dark:hover:bg-gray-600 transition-colors shadow-sm">
                                        <i class="fas fa-upload mr-2 text-slate-400"></i> Choose File
                                        <input type="file" name="visitor_photo" accept="image/*" class="hidden"
                                            onchange="previewImage(event, 'visitor-photo-preview', 'visitor-photo-icon', 'visitor-photo-remove')">
                                    </label>
                                    <p class="text-[10px] text-slate-400 mt-1">JPG, PNG (max 2MB)</p>
                                </div>
                            </div>
                        </div>
                        <template x-if="errors.visitor_photo">
                            <p class="modal-error-message" x-text="errors.visitor_photo[0]"></p>
                        </template>
                    </div>

                    <!-- ID Proof Upload -->
                    <div class="space-y-2">
                        <label class="modal-label-premium">Identification Proof</label>
                        <div class="relative group">
                            <div class="flex items-center gap-4">
                                <div
                                    class="w-20 h-20 bg-white dark:bg-gray-700 border-2 border-dashed border-slate-200 dark:border-gray-600 rounded-xl flex items-center justify-center overflow-hidden shrink-0 relative">
                                    <img id="id-proof-preview" src="#" alt="ID Proof"
                                        class="hidden w-full h-full object-cover">
                                    <i id="id-proof-icon" class="fas fa-id-card text-xl text-slate-300"></i>
                                    <button type="button" id="id-proof-remove"
                                        onclick="removeImage(event, 'id_proof', 'id-proof-preview', 'id-proof-icon', 'id-proof-remove')"
                                        class="hidden absolute -top-1 -right-1 bg-red-500 hover:bg-red-600 text-white rounded-full w-6 h-6 flex items-center justify-center transition-all duration-200 shadow-sm z-10">
                                        <i class="fas fa-times text-[10px]"></i>
                                    </button>
                                </div>
                                <div class="flex-1">
                                    <label
                                        class="cursor-pointer inline-flex items-center px-4 py-2 bg-white dark:bg-gray-700 border border-slate-200 dark:border-gray-600 text-slate-600 dark:text-gray-300 rounded-xl text-xs font-semibold hover:bg-slate-50 dark:hover:bg-gray-600 transition-colors shadow-sm">
                                        <i class="fas fa-upload mr-2 text-slate-400"></i> Choose File
                                        <input type="file" name="id_proof" accept="image/*,application/pdf" class="hidden"
                                            onchange="previewImage(event, 'id-proof-preview', 'id-proof-icon', 'id-proof-remove')">
                                    </label>
                                    <p class="text-[10px] text-slate-400 mt-1">PDF, JPG, PNG (max 2MB)</p>
                                </div>
                            </div>
                        </div>
                        <template x-if="errors.id_proof">
                            <p class="modal-error-message" x-text="errors.id_proof[0]"></p>
                        </template>
                    </div>
                </div>

                <!-- Notice Card -->
                <div
                    class="mt-6 flex items-center justify-between bg-[#f0f5ff] dark:bg-indigo-900/20 border border-[#e5edff] dark:border-indigo-800 p-5 rounded-2xl shadow-sm">
                    <div class="flex flex-col">
                        <span class="text-sm font-bold text-slate-900 dark:text-gray-100 leading-tight">Procedural Notice</span>
                        <span class="text-[10px] text-slate-500 dark:text-gray-400 font-bold uppercase mt-1 tracking-wide opacity-80">Visitor
                            records are audited entry nodes. Ensure all identification documents are verified before
                            check-in.</span>
                    </div>
                    <div class="w-10 h-10 bg-white dark:bg-gray-700 rounded-xl shadow-sm flex items-center justify-center shrink-0">
                        <i class="fas fa-info-circle text-indigo-600 text-sm"></i>
                    </div>
                </div>

                <!-- Footer -->
                <x-slot name="footer">
                    <button type="button" @click="$dispatch('close-modal', 'visitor-modal')"
                        class="btn-premium-cancel px-10">
                        Cancel
                    </button>
                    <button type="button" @click="submitForm()" :disabled="submitting"
                        class="btn-premium-primary min-w-[160px]">
                        <template x-if="submitting">
                            <span
                                class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin mr-3 inline-block"></span>
                        </template>
                        <span x-text="editMode ? 'Update Record' : 'Submit Entry'"></span>
                    </button>
                </x-slot>
            </form>
        </x-modal>
        
        {{-- Delete Confirmation Modal --}}
        <x-confirm-modal />
    </div>

    @push('scripts')
        <script>
            function visitorManagementData() {
                return {
                    submitting: false,
                    errors: {},
                    editMode: false,
                    visitorId: null,
                    formData: {
                        name: '',
                        mobile: '',
                        email: '',
                        address: '',
                        visitor_type: '',
                        visit_purpose: '',
                        meeting_purpose: '',
                        meeting_with: '',
                        priority: '{{ VisitorPriority::Medium->value }}',
                        no_of_guests: 1,
                        meeting_type: '{{ VisitorMode::Offline->value }}',
                        source: '',
                        meeting_scheduled: '',
                    },

                    clearError(field) {
                        if (this.errors && this.errors[field]) {
                            const e = { ...this.errors }; delete e[field]; this.errors = e;
                        }
                    },

                    init() {
                        // Listen for modal close event to reset form
                        window.addEventListener('close-modal', (event) => {
                            if (event.detail === 'visitor-modal') {
                                this.resetForm();
                            }
                        });

                        // Standard selects with no-select2 don't need jQuery listeners
                        this.$nextTick(() => {
                            // Any additional initialization if required
                        });
                    },

                    resetForm() {
                        this.editMode = false;
                        this.visitorId = null;
                        this.errors = {};
                        this.formData = {
                            name: '',
                            mobile: '',
                            email: '',
                            address: '',
                            visitor_type: '',
                            visit_purpose: '',
                            meeting_purpose: '',
                            meeting_with: '',
                            priority: '{{ VisitorPriority::Medium->value }}',
                            no_of_guests: 1,
                            meeting_type: '{{ VisitorMode::Offline->value }}',
                            source: '',
                            meeting_scheduled: '',
                        };

                        // Select2 triggers removed
                        this.resetImagePreviews();
                    },

                    resetImagePreviews() {
                        ['visitor-photo', 'id-proof'].forEach(id => {
                            const preview = document.getElementById(id + '-preview');
                            const icon = document.getElementById(id + '-icon');
                            const remove = document.getElementById(id + '-remove');
                            if (preview) { preview.src = '#'; preview.classList.add('hidden'); }
                            if (icon) { icon.classList.remove('hidden'); }
                            if (remove) { remove.classList.add('hidden'); }
                        });
                    },

                    openAddModal() {
                        this.resetForm();
                        this.$dispatch('open-modal', 'visitor-modal');
                    },

                    openEditModal(visitor) {
                        this.editMode = true;
                        this.visitorId = visitor.id;
                        this.errors = {};
                        
                        this.formData = {
                            name: visitor.name || '',
                            mobile: visitor.mobile || '',
                            email: visitor.email || '',
                            address: visitor.address || '',
                            visitor_type: visitor.visitor_type || '',
                            visit_purpose: visitor.visit_purpose || '',
                            meeting_purpose: visitor.meeting_purpose || '',
                            meeting_with: visitor.meeting_with || '',
                            priority: String(visitor.priority?.value || visitor.priority || '{{ VisitorPriority::Medium->value }}'),
                            no_of_guests: visitor.no_of_guests || 1,
                            meeting_type: String(visitor.meeting_type?.value || visitor.meeting_type || '{{ VisitorMode::Offline->value }}'),
                            source: visitor.source || '',
                            meeting_scheduled: visitor.meeting_scheduled || '',
                        };

                        this.$dispatch('open-modal', 'visitor-modal');

                        this.$nextTick(() => {
                            // Standard selects will update via x-model
                        });
                    },

                    async submitForm() {
                        this.submitting = true;
                        this.errors = {};

                        try {
                            const url = this.editMode 
                                ? `/receptionist/visitors/${this.visitorId}` 
                                : '{{ route('receptionist.visitors.store') }}';
                            
                            const formData = new FormData(document.getElementById('visitorForm'));
                            if (this.editMode) formData.append('_method', 'PUT');

                            const response = await fetch(url, {
                                method: 'POST',
                                body: formData,
                                headers: {
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                }
                            });

                            const result = await response.json();

                            if (response.ok) {
                                if (window.Toast) window.Toast.fire({ icon: 'success', title: result.message });
                                this.$dispatch('close-modal', 'visitor-modal');
                                // Refresh the AJAX Table!
                                if (typeof this.refreshTable === 'function') {
                                    this.refreshTable();
                                } else {
                                    // If called outside ajaxDataTable scope, dispatch event
                                    window.dispatchEvent(new CustomEvent('refresh-ajax-table'));
                                }
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

            // Quick Action Bridge
            window.addEventListener('refresh-ajax-table', () => {
                const table = Alpine.$data(document.querySelector('[x-data^="Object.assign"]'));
                if (table) table.refreshTable();
            });

            function previewImage(event, previewId, iconId, removeBtnId) {
                const file = event.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        const preview = document.getElementById(previewId);
                        const icon = document.getElementById(iconId);
                        const removeBtn = document.getElementById(removeBtnId);
                        preview.src = e.target.result;
                        preview.classList.remove('hidden');
                        if (icon) icon.classList.add('hidden');
                        if (removeBtn) removeBtn.classList.remove('hidden');
                    };
                    reader.readAsDataURL(file);
                }
            }

            function removeImage(event, inputName, previewId, iconId, removeBtnId) {
                event.preventDefault();
                const input = document.querySelector(`input[name="${inputName}"]`);
                const preview = document.getElementById(previewId);
                const icon = document.getElementById(iconId);
                const removeBtn = document.getElementById(removeBtnId);
                if (input) input.value = '';
                if (preview) { preview.src = '#'; preview.classList.add('hidden'); }
                if (icon) icon.classList.remove('hidden');
                if (removeBtn) removeBtn.classList.add('hidden');
            }
        </script>

    @endpush
@endsection