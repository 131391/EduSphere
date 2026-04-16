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
        fetchUrl: '{{ route('receptionist.visitors.index') }}',
        defaultSort: 'created_at',
        defaultDirection: 'desc',
        defaultPerPage: 25,
        defaultFilters: { priority: '', meeting_type: '', search: '' },
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

        <!-- Page Header & Filters -->
        <div x-data="{ searchOpen: true }">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 border border-teal-100/50 dark:border-gray-700 mb-6">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-teal-100 flex items-center justify-center text-teal-600">
                            <i class="fas fa-users-cog text-xs"></i>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-gray-800 dark:text-white">Visitor Registry</h2>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5" x-text="'Managing ' + stats.total + ' visitors in the registry'">Managing {{ number_format($stats['total']) }} visitors in the registry</p>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <button type="button" @click="searchOpen = !searchOpen"
                            class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 text-gray-700 dark:text-gray-200 text-xs font-semibold rounded-xl hover:bg-gray-50 transition-all shadow-sm">
                            <i class="fas fa-filter mr-2 text-teal-500"></i>
                            Advanced Filters
                        </button>
                        <button @click="openAddModal()"
                            class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-teal-600 to-emerald-600 hover:from-teal-700 hover:to-emerald-700 text-white text-sm font-semibold rounded-xl transition-all shadow-md hover:shadow-lg active:scale-95">
                            <i class="fas fa-plus mr-2"></i>
                            New Visitor
                        </button>
                        <button @click="exportData()"
                            class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-slate-700 to-slate-900 hover:from-black hover:to-slate-800 text-white text-sm font-semibold rounded-xl transition-all shadow-md hover:shadow-lg active:scale-95">
                            <i class="fas fa-file-excel mr-2 text-xs"></i>
                            Export CSV
                        </button>
                    </div>
                </div>
            </div>

            <!-- Filters Section -->
            <div x-show="searchOpen" x-collapse x-cloak
                class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 mb-6">
                <div class="grid grid-cols-1 md:grid-cols-4 lg:grid-cols-6 gap-6">
                    <!-- Search -->
                    <div class="lg:col-span-2">
                        <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1.5">Search Visitor</label>
                        <div class="relative group">
                            <input type="text" x-model="search" placeholder="Name, Mobile, No..."
                                class="w-full h-11 pl-10 bg-gray-50 dark:bg-gray-700 border-gray-200 dark:border-gray-600 rounded-xl text-sm text-gray-700 dark:text-gray-200 focus:ring-4 focus:ring-teal-500/10 focus:border-teal-500 transition-all outline-none">
                            <div class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-teal-500 transition-colors">
                                <i class="fas fa-search text-xs"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Priority Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1.5">Priority</label>
                        <select x-model="filters.priority" @change="applyFilter('priority', $event.target.value)"
                            class="w-full h-11 px-4 bg-gray-50 dark:bg-gray-700 border-gray-200 dark:border-gray-600 rounded-xl text-sm text-gray-700 dark:text-gray-200 focus:ring-4 focus:ring-teal-500/10 focus:border-teal-500 transition-all outline-none">
                            <option value="">All Priorities</option>
                            @foreach($priorities as $priority)
                                <option value="{{ $priority->value }}">{{ $priority->label() }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Meeting Type Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1.5">Meeting Type</label>
                        <select x-model="filters.meeting_type" @change="applyFilter('meeting_type', $event.target.value)"
                            class="w-full h-11 px-4 bg-gray-50 dark:bg-gray-700 border-gray-200 dark:border-gray-600 rounded-xl text-sm text-gray-700 dark:text-gray-200 focus:ring-4 focus:ring-teal-500/10 focus:border-teal-500 transition-all outline-none">
                            <option value="">All Types</option>
                            @foreach($meetingTypes as $type)
                                <option value="{{ $type->value }}">{{ $type->label() }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-end gap-2 lg:col-span-2">
                        <button type="button" @click="clearAllFilters()"
                            x-show="hasActiveFilters() || search !== ''"
                            class="flex-1 h-11 flex items-center justify-center bg-rose-50 dark:bg-rose-900/20 text-rose-600 dark:text-rose-400 border border-rose-100 dark:border-rose-900/30 rounded-xl hover:bg-rose-100 dark:hover:bg-rose-900/40 transition-all shadow-sm font-semibold text-xs px-4">
                            <i class="fas fa-trash-alt text-[10px] mr-2"></i> Reset
                        </button>
                        <div class="w-24">
                            <x-table.per-page model="perPage" action="changePerPage($event.target.value)" />
                        </div>
                    </div>
                </div>

                <!-- Active Tags -->
                <div class="mt-4 flex flex-wrap gap-2" x-show="hasActiveFilters() || search !== ''" x-cloak>
                    <div x-show="search !== ''" class="flex items-center gap-1 bg-teal-100 text-teal-800 px-3 py-1 rounded-full text-xs">
                        <span>Search: <span x-text="search" class="font-semibold"></span></span>
                        <button @click="search = ''" class="ml-1 hover:text-teal-600"><i class="fas fa-times"></i></button>
                    </div>
                    <template x-for="(value, key) in filters" :key="key">
                        <div x-show="value !== ''" class="flex items-center gap-1 bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-xs">
                            <span x-text="getFilterLabel(key, value)"></span>
                            <button @click="removeFilter(key)" class="ml-1 hover:text-blue-600"><i class="fas fa-times"></i></button>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- AJAX Data Table -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="overflow-x-auto relative min-h-[400px]">
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

                    <!-- Initial Blade Render (Zero Blink) -->
                    <tbody x-show="!rows.length || (initialLoad && rows.length && initialRows.length === rows.length)" class="divide-y divide-gray-100 dark:divide-gray-700">
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

                    <!-- Dynamic Table Body -->
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700" x-show="rows.length && !initialLoad" x-cloak>
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
                    </tbody>

                    <!-- Empty State -->
                    <tbody x-show="!loading && rows.length === 0" x-cloak>
                        <x-table.empty-state :colspan="7" icon="fas fa-users-slash" message="No visitors found matching your criteria." />
                    </tbody>
                </table>
            </div>
                </table>
            </div>

            <div class="px-6 py-4 bg-gray-50/50 dark:bg-gray-700/50 border-t border-gray-100 dark:border-gray-700">
                <x-table.pagination />
            </div>
        </div>

        <x-modal name="visitor-modal" alpineTitle="editMode ? 'Modify Visitor Information' : 'Register New Visitor'"
            maxWidth="4xl">
            <form @submit.prevent="submitForm()" id="visitorForm" method="POST" novalidate>
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
                                oninput="this.value = this.value.replace(/[^0-9]/g, '')" class="modal-input-premium"
                                placeholder="Enter contact number"
                                :class="{'border-red-500 ring-red-500/10': errors.mobile}">
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
                                class="modal-input-premium" placeholder="Full name of visitor"
                                :class="{'border-red-500 ring-red-500/10': errors.name}">
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
                                class="modal-input-premium" placeholder="visitor@example.com"
                                :class="{'border-red-500 ring-red-500/10': errors.email}">
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
                                class="modal-input-premium" placeholder="City, Area"
                                :class="{'border-red-500 ring-red-500/10': errors.address}">
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
                                class="modal-input-premium"
                                :class="{'border-red-500 ring-red-500/10': errors.visitor_type}">
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
                                @change="clearError('visit_purpose')" class="modal-input-premium"
                                :class="{'border-red-500 ring-red-500/10': errors.visit_purpose}">
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
                                class="modal-input-premium"
                                :class="{'border-red-500 ring-red-500/10': errors.meeting_with}">
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
                                @input="clearError('meeting_purpose')" class="modal-input-premium"
                                placeholder="Specific reason for meeting"
                                :class="{'border-red-500 ring-red-500/10': errors.meeting_purpose}">
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
                                class="modal-input-premium"
                                :class="{'border-red-500 ring-red-500/10': errors.meeting_type}">
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
                                class="modal-input-premium" :class="{'border-red-500 ring-red-500/10': errors.priority}">
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
                                @input="clearError('no_of_guests')" class="modal-input-premium" placeholder="1"
                                :class="{'border-red-500 ring-red-500/10': errors.no_of_guests}">
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
                                    class="w-20 h-20 bg-slate-50 border-2 border-dashed border-slate-200 rounded-xl flex items-center justify-center overflow-hidden shrink-0 relative">
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
                                        class="cursor-pointer inline-flex items-center px-4 py-2 bg-white border border-slate-200 text-slate-600 rounded-xl text-xs font-semibold hover:bg-slate-50 transition-colors shadow-sm">
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
                                    class="w-20 h-20 bg-slate-50 border-2 border-dashed border-slate-200 rounded-xl flex items-center justify-center overflow-hidden shrink-0 relative">
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
                                        class="cursor-pointer inline-flex items-center px-4 py-2 bg-white border border-slate-200 text-slate-600 rounded-xl text-xs font-semibold hover:bg-slate-50 transition-colors shadow-sm">
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
                    class="mb-8 flex items-center justify-between bg-[#f0f5ff] border border-[#e5edff] p-5 rounded-2xl shadow-sm">
                    <div class="flex flex-col">
                        <span class="text-sm font-bold text-slate-900 leading-tight">Procedural Notice</span>
                        <span class="text-[10px] text-slate-500 font-bold uppercase mt-1 tracking-wide opacity-80">Visitor
                            records are audited entry nodes. Ensure all identification documents are verified before
                            check-in.</span>
                    </div>
                    <div class="w-10 h-10 bg-white rounded-xl shadow-sm flex items-center justify-center shrink-0">
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
                        if (this.errors[field]) {
                            delete this.errors[field];
                        }
                    },

                    init() {
                        // Listen for modal close event to reset form
                        window.addEventListener('close-modal', (event) => {
                            if (event.detail === 'visitor-modal') {
                                this.resetForm();
                            }
                        });

                        // Robust sync for all selects (including Select2)
                        this.$nextTick(() => {
                            if (typeof $ !== 'undefined') {
                                $('select[name="visit_purpose"], select[name="visitor_type"], select[name="meeting_with"], select[name="priority"], select[name="meeting_type"]').on('change', (e) => {
                                    const field = e.target.getAttribute('name');
                                    if (field && this.formData.hasOwnProperty(field)) {
                                        this.formData[field] = e.target.value;
                                        this.clearError(field);
                                    }
                                });
                            }
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

                        if (typeof $ !== 'undefined') {
                            $('select[name="visit_purpose"]').val(null).trigger('change');
                            $('select[name="visitor_type"]').val(null).trigger('change');
                            $('select[name="meeting_with"]').val(null).trigger('change');
                            $('select[name="priority"]').val('{{ VisitorPriority::Medium->value }}').trigger('change');
                            $('select[name="meeting_type"]').val('{{ VisitorMode::Offline->value }}').trigger('change');
                        }

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
                            setTimeout(() => {
                                if (typeof $ !== 'undefined') {
                                    ['priority', 'visit_purpose', 'visitor_type', 'meeting_with', 'meeting_type'].forEach(name => {
                                        if (this.formData[name]) {
                                            $(`select[name="${name}"]`).val(this.formData[name]).trigger('change');
                                        }
                                    });
                                }
                            }, 150);
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