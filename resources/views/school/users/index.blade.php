@extends('layouts.school')

@section('title', 'User Management')

@section('content')
<div x-data="Object.assign(ajaxDataTable({
        fetchUrl: '{{ route('school.users.fetch') }}',
        defaultSort: 'created_at',
        defaultDirection: 'desc',
        defaultPerPage: 25,
        defaultFilters: { role: '', status: '' },
        initialRows: @js($initialData['rows']),
        initialPagination: @js($initialData['pagination']),
        initialStats: @js($initialData['stats']),
        filterLabels: {
            role: {
                @foreach($roles as $slug => $label) '{{ $slug }}': '{{ $label }}', @endforeach
            },
            status: {
                @foreach($statuses as $value => $label) '{{ $value }}': '{{ $label }}', @endforeach
            }
        }
    }), userManagementData())"
    class="space-y-6"
    @close-modal.window="if ($event.detail === 'user-modal') resetForm()">

    {{-- ── Stats ── --}}
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
        <x-stat-card label="Total Users"  :value="$stats['total']"     icon="fas fa-users"        color="blue"   alpine-text="stats.total"     />
        <x-stat-card label="Active"       :value="$stats['active']"    icon="fas fa-check-circle" color="emerald" alpine-text="stats.active"    />
        <x-stat-card label="Inactive"     :value="$stats['inactive']"  icon="fas fa-minus-circle" color="gray"   alpine-text="stats.inactive"  />
        <x-stat-card label="Suspended"    :value="$stats['suspended']" icon="fas fa-ban"          color="rose"   alpine-text="stats.suspended" />
        <x-stat-card label="Pending"      :value="$stats['pending']"   icon="fas fa-clock"        color="amber"  alpine-text="stats.pending"   />
    </div>

    {{-- ── Page Header ── --}}
    <x-page-header title="User Management" description="Manage school staff accounts — teachers, receptionists and more" icon="fas fa-users-cog">
        <button @click="openAddModal()"
            class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-700 hover:to-blue-700 text-white text-sm font-semibold rounded-xl transition-all shadow-md hover:shadow-lg active:scale-95">
            <i class="fas fa-plus mr-2 text-xs"></i>
            Add User
        </button>
    </x-page-header>

    {{-- ── AJAX Data Table ── --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">

        {{-- Table toolbar --}}
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div class="flex-1 flex flex-col md:flex-row md:items-center gap-4">
                    <h2 class="text-lg font-semibold text-gray-800 dark:text-white">User List</h2>
                    <x-table.search placeholder="Search by name, email or phone..." />
                </div>
                <div class="flex items-center gap-3">
                    <x-table.filter-select
                        model="filters.role"
                        action="applyFilter('role', $event.target.value)"
                        placeholder="All Roles"
                        :options="$roles"
                    />
                    <x-table.filter-select
                        model="filters.status"
                        action="applyFilter('status', $event.target.value)"
                        placeholder="All Statuses"
                        :options="$statuses"
                    />
                    <x-table.per-page model="perPage" action="changePerPage($event.target.value)" :default="25" />
                </div>
            </div>

            {{-- Active filters --}}
            <div class="mt-3 flex flex-wrap gap-2" x-show="hasActiveFilters()" x-cloak>
                <template x-for="(value, key) in filters" :key="key">
                    <div x-show="value" class="flex items-center gap-1 bg-indigo-100 text-indigo-800 px-3 py-1 rounded-full text-xs">
                        <span x-text="getFilterLabel(key, value)"></span>
                        <button @click="removeFilter(key)" class="ml-1 hover:text-indigo-600">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </template>
                <button @click="clearAllFilters()" class="flex items-center gap-1 bg-red-100 text-red-700 px-3 py-1 rounded-full text-xs hover:bg-red-200 transition-colors">
                    <i class="fas fa-times-circle"></i> Clear All
                </button>
            </div>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto relative ajax-table-wrapper">
            <x-table.loading-overlay />

            <table class="w-full text-left border-collapse">
                <thead class="bg-gray-50/50 dark:bg-gray-700/50 border-b border-gray-100 dark:border-gray-700">
                    <tr>
                        <x-table.sort-header column="name"       label="User"         sort-var="sort" direction-var="direction" />
                        <x-table.sort-header column="email"      label="Email"        sort-var="sort" direction-var="direction" />
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Phone</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Role</th>
                        <x-table.sort-header column="status"     label="Status"       sort-var="sort" direction-var="direction" />
                        <x-table.sort-header column="created_at" label="Joined"       sort-var="sort" direction-var="direction" />
                        <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider w-28">Actions</th>
                    </tr>
                </thead>

                {{-- SSR rows — hidden once Alpine initialises --}}
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700" data-ssr x-show="!hydrated">
                    @if(empty($initialData['rows']))
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center">
                                <i class="fas fa-users text-4xl text-gray-300 mb-4"></i>
                                <p class="text-lg text-gray-500">No users found.</p>
                            </div>
                        </td>
                    </tr>
                    @endif
                    @foreach($initialData['rows'] as $row)
                    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-indigo-500 to-blue-500 flex items-center justify-center text-white font-bold text-xs shadow-sm shrink-0">
                                    {{ $row['initials'] }}
                                </div>
                                <div>
                                    <div class="text-sm font-bold text-gray-800 dark:text-gray-100">{{ $row['name'] }}</div>
                                    <div class="text-[10px] text-gray-400">{{ $row['last_login'] === 'Never' ? 'Never logged in' : 'Last: ' . $row['last_login'] }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">{{ $row['email'] }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $row['phone'] }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-indigo-50 text-indigo-700 border border-indigo-100">
                                {{ $row['role_name'] }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php $cfg = $row['status_config']; @endphp
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider border {{ $cfg['class'] }}">
                                <i class="fas {{ $cfg['icon'] }} text-[8px]"></i>
                                {{ $row['status_label'] }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500">{{ $row['created_at'] }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <div class="flex items-center justify-center gap-2">
                                <button @click="openEditModal(@js($row))"
                                    class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center hover:bg-indigo-100 transition-colors" title="Edit">
                                    <i class="fas fa-edit text-xs"></i>
                                </button>
                                <button @click="confirmDelete({ id: {{ $row['id'] }}, name: '{{ addslashes($row['name']) }}' })"
                                    class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center hover:bg-rose-100 transition-colors" title="Delete">
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
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-indigo-500 to-blue-500 flex items-center justify-center text-white font-bold text-xs shadow-sm shrink-0"
                                        x-text="row.initials"></div>
                                    <div>
                                        <div class="text-sm font-bold text-gray-800 dark:text-gray-100" x-text="row.name"></div>
                                        <div class="text-[10px] text-gray-400"
                                            x-text="row.last_login === 'Never' ? 'Never logged in' : 'Last: ' + row.last_login"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300" x-text="row.email"></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="row.phone"></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-indigo-50 text-indigo-700 border border-indigo-100"
                                    x-text="row.role_name"></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider border"
                                    :class="row.status_config.class">
                                    <i class="fas text-[8px]" :class="row.status_config.icon"></i>
                                    <span x-text="row.status_label"></span>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500" x-text="row.created_at"></td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button @click="openEditModal(row)"
                                        class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center hover:bg-indigo-100 transition-colors" title="Edit">
                                        <i class="fas fa-edit text-xs"></i>
                                    </button>
                                    <button @click="confirmDelete({ id: row.id, name: row.name })"
                                        class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center hover:bg-rose-100 transition-colors" title="Delete">
                                        <i class="fas fa-trash-alt text-xs"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>

                    <x-table.empty-state :colspan="7" icon="fas fa-users" message="No users found matching your criteria." />
                </tbody>
            </table>
        </div>

        <x-table.pagination :initial="$initialData['pagination']" />
    </div>

    {{-- ── Add / Edit Modal ── --}}
    <x-modal name="user-modal" alpineTitle="editMode ? 'Edit User' : 'Add New User'" maxWidth="2xl">
        <form id="user-form" @submit.prevent="submitForm()" method="POST" novalidate>
            @csrf
            <template x-if="editMode">
                <input type="hidden" name="_method" value="PUT">
            </template>

            <div class="space-y-6 pt-2">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    {{-- Full Name --}}
                    <div class="space-y-2">
                        <label class="modal-label-premium">Full Name <span class="text-red-600 font-bold">*</span></label>
                        <input type="text" x-model="formData.name" @input="clearError('name')" placeholder="John Doe"
                            class="modal-input-premium" :class="{'border-red-500 ring-red-500/10': errors.name}">
                        <template x-if="errors.name">
                            <p class="modal-error-message" x-text="errors.name[0]"></p>
                        </template>
                    </div>

                    {{-- Email --}}
                    <div class="space-y-2">
                        <label class="modal-label-premium">Email Address <span class="text-red-600 font-bold">*</span></label>
                        <input type="email" x-model="formData.email" @input="clearError('email')" placeholder="user@school.com"
                            class="modal-input-premium" :class="{'border-red-500 ring-red-500/10': errors.email}">
                        <template x-if="errors.email">
                            <p class="modal-error-message" x-text="errors.email[0]"></p>
                        </template>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    {{-- Role --}}
                    <div class="space-y-2">
                        <label class="modal-label-premium">Role <span class="text-red-600 font-bold">*</span></label>
                        <select x-model="formData.role" @change="clearError('role')" name="role"
                            class="modal-input-premium no-select2" :class="{'border-red-500 ring-red-500/10': errors.role}">
                            <option value="">Select Role</option>
                            @foreach($roles as $slug => $label)
                            <option value="{{ $slug }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        <template x-if="errors.role">
                            <p class="modal-error-message" x-text="errors.role[0]"></p>
                        </template>
                    </div>

                    {{-- Phone --}}
                    <div class="space-y-2">
                        <label class="modal-label-premium">Phone Number</label>
                        <input type="tel" x-model="formData.phone" @input="clearError('phone')" placeholder="888 888 8888"
                            class="modal-input-premium" :class="{'border-red-500 ring-red-500/10': errors.phone}">
                        <template x-if="errors.phone">
                            <p class="modal-error-message" x-text="errors.phone[0]"></p>
                        </template>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    {{-- Password --}}
                    <div class="space-y-2" x-data="{ show: false }">
                        <label class="modal-label-premium">
                            Password
                            <span class="text-red-600 font-bold" x-show="!editMode">*</span>
                            <span class="text-[10px] text-gray-400 font-normal italic ml-1" x-show="editMode">Leave blank to keep</span>
                        </label>
                        <div class="relative">
                            <input :type="show ? 'text' : 'password'" x-model="formData.password" @input="clearError('password')"
                                class="modal-input-premium !pr-10" :class="{'border-red-500 ring-red-500/10': errors.password}">
                            <button type="button" @click="show = !show"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-indigo-600 transition-colors">
                                <i class="fas" :class="show ? 'fa-eye-slash' : 'fa-eye'"></i>
                            </button>
                        </div>
                        <template x-if="errors.password">
                            <p class="modal-error-message" x-text="errors.password[0]"></p>
                        </template>
                    </div>

                    {{-- Status (edit only) --}}
                    <div class="space-y-2" x-show="editMode" x-cloak>
                        <label class="modal-label-premium">Status <span class="text-red-600 font-bold">*</span></label>
                        <select x-model="formData.status" @change="clearError('status')" name="status"
                            class="modal-input-premium no-select2" :class="{'border-red-500 ring-red-500/10': errors.status}">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="suspended">Suspended</option>
                        </select>
                        <template x-if="errors.status">
                            <p class="modal-error-message" x-text="errors.status[0]"></p>
                        </template>
                    </div>
                </div>
            </div>

            <x-slot name="footer">
                <button type="button" @click="$dispatch('close-modal', 'user-modal')" class="btn-premium-cancel px-10">
                    Cancel
                </button>
                <button type="submit" form="user-form" :disabled="submitting" class="btn-premium-primary min-w-[180px]">
                    <template x-if="submitting">
                        <span class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin mr-3 inline-block"></span>
                    </template>
                    <span x-text="editMode ? 'Update User' : 'Create User'"></span>
                </button>
            </x-slot>
        </form>
    </x-modal>

    <x-confirm-modal />
</div>

@push('scripts')
<script>
function userManagementData() {
    return {
        editMode: false,
        userId: null,
        submitting: false,
        errors: {},
        formData: {
            name: '',
            email: '',
            password: '',
            phone: '',
            role: '',
            status: 'active',
        },

        clearError(field) {
            if (this.errors && this.errors[field]) {
                const e = { ...this.errors };
                delete e[field];
                this.errors = e;
            }
        },

        resetForm() {
            this.editMode = false;
            this.userId = null;
            this.errors = {};
            this.formData = { name: '', email: '', password: '', phone: '', role: '', status: 'active' };
        },

        init() {
            window.addEventListener('open-edit-user', (e) => this.openEditModal(e.detail));
            window.addEventListener('open-delete-user', (e) => this.confirmDelete(e.detail));
        },

        openAddModal() {
            this.resetForm();
            this.$dispatch('open-modal', 'user-modal');
        },

        openEditModal(user) {
            this.editMode = true;
            this.userId = user.id;
            this.errors = {};

            const statusMap = { 1: 'active', 4: 'inactive', 2: 'suspended', 3: 'pending' };
            const roleSlug = typeof user.role === 'object' ? user.role?.slug : (user.role_slug || user.role || '');

            this.formData = {
                name: user.name || '',
                email: user.email || '',
                password: '',
                phone: user.phone || '',
                role: roleSlug,
                status: statusMap[user.status] || 'active',
            };

            this.$dispatch('open-modal', 'user-modal');
        },

        async confirmDelete(user) {
            window.dispatchEvent(new CustomEvent('open-confirm-modal', {
                detail: {
                    title: 'Delete User',
                    message: `Are you sure you want to delete "${user.name}"? This action cannot be undone.`,
                    callback: async () => {
                        try {
                            const response = await fetch(`/school/users/${user.id}`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify({ _method: 'DELETE' })
                            });
                            const result = await response.json();
                            if (response.ok) {
                                if (window.Toast) window.Toast.fire({ icon: 'success', title: result.message || 'User deleted.' });
                                this.refreshTable();
                            } else {
                                throw new Error(result.message || 'Delete failed');
                            }
                        } catch (e) {
                            if (window.Toast) window.Toast.fire({ icon: 'error', title: e.message });
                        }
                    }
                }
            }));
        },

        async submitForm() {
            if (this.submitting) return;
            this.submitting = true;
            this.errors = {};

            const url = this.editMode
                ? `/school/users/${this.userId}`
                : '{{ route('school.users.store') }}';

            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ ...this.formData, _method: this.editMode ? 'PUT' : 'POST' })
                });

                const result = await response.json();

                if (response.ok) {
                    if (window.Toast) window.Toast.fire({ icon: 'success', title: result.message || 'User saved.' });
                    this.$dispatch('close-modal', 'user-modal');
                    this.refreshTable();
                } else if (response.status === 422) {
                    this.errors = result.errors || {};
                } else {
                    throw new Error(result.message || 'Something went wrong');
                }
            } catch (e) {
                if (window.Toast) window.Toast.fire({ icon: 'error', title: e.message });
            } finally {
                this.submitting = false;
            }
        },
    };
}
</script>
@endpush
@endsection
