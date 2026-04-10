@extends('layouts.school')

@section('title', 'User Management')

@section('content')
<div x-data="userManagement()">
    @php
        $tableColumns = [
            [
                'key' => 'name',
                'label' => 'NAME',
                'sortable' => true,
            ],
            [
                'key' => 'email',
                'label' => 'EMAIL',
                'sortable' => true,
            ],
            [
                'key' => 'phone',
                'label' => 'PHONE',
                'sortable' => true,
                'render' => fn($row) => $row->phone ?? '-'
            ],
            [
                'key' => 'role',
                'label' => 'ROLE',
                'sortable' => true,
                'render' => fn($row) => '<span class="px-3 py-1 bg-indigo-100 text-indigo-800 rounded-full text-sm font-semibold">' . ucfirst($row->role->name ?? $row->role) . '</span>'
            ],
            [
                'key' => 'status',
                'label' => 'STATUS',
                'sortable' => true,
                'render' => function($row) {
                    $colorClass = match($row->status->color()) {
                        'green' => 'bg-green-100 text-green-800',
                        'gray' => 'bg-gray-100 text-gray-800',
                        'red' => 'bg-red-100 text-red-800',
                        'yellow' => 'bg-yellow-100 text-yellow-800',
                        default => 'bg-gray-100 text-gray-800',
                    };
                    $label = $row->status->label();
                    return '<span class="px-2 py-1 rounded-full text-xs font-semibold ' . $colorClass . '">' . ucfirst($label) . '</span>';
                }
            ],
        ];

        $tableActions = [
            [
                'type' => 'button',
                'icon' => 'fas fa-edit',
                'class' => 'text-blue-600 hover:text-blue-900',
                'onclick' => function($row) {
                    $encoded = base64_encode(json_encode($row));
                    return "window.dispatchEvent(new CustomEvent('open-edit-user', { detail: JSON.parse(atob('$encoded')) }))";
                },
                'title' => 'Edit',
            ],
            [
                'type' => 'form',
                'action' => function($row) {
                    return route('school.users.destroy', $row->id);
                },
                'method' => 'DELETE',
                'icon' => 'fas fa-trash',
                'class' => 'text-red-600 hover:text-red-900',
                'title' => 'Delete',
                'confirm' => 'Are you sure you want to delete this user?',
            ],
        ];
    @endphp
    <!-- Header Section -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 mb-6" x-on:open-edit-user.window="openEditModal($event.detail)">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-gray-800 dark:text-white">User Management</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">Manage school staff users (teachers, receptionists, etc.)</p>
            </div>
            <button @click="openAddModal()" 
                    class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md transition-colors shadow-sm">
                <i class="fas fa-plus mr-2"></i>
                Add User
            </button>
        </div>
    </div>

    <!-- Users Table -->
    <x-data-table 
        :columns="$tableColumns"
        :data="$users"
        :searchable="true"
        :actions="$tableActions"
        empty-message="No users found"
        empty-icon="fas fa-users"
    >
        Users List
    </x-data-table>

    <!-- Add/Edit User Modal -->
    <x-modal name="user-modal" alpineTitle="editMode ? 'Edit User' : 'Create New Staff User'" maxWidth="2xl">
        <form @submit.prevent="submitForm" method="POST" class="p-0" novalidate>
            @csrf
            <template x-if="editMode">
                <input type="hidden" name="_method" value="PUT">
            </template>

            <div class="px-8 py-8">
                <div class="grid grid-cols-2 gap-x-8 gap-y-2">
                    <!-- Full Name -->
                    <div class="col-span-1">
                        <label class="block text-sm font-semibold text-gray-800 mb-1.5 ml-1">Full Name <span class="text-red-500">*</span></label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none transition-colors duration-200 group-focus-within:text-indigo-600 text-gray-400">
                                <i class="fas fa-user text-sm"></i>
                            </div>
                            <input 
                                type="text" 
                                name="name" 
                                x-model="formData.name"
                                @input="if(errors.name) delete errors.name"
                                placeholder="John Doe"
                                class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 focus:bg-white transition-all duration-200 shadow-sm placeholder:text-gray-400 text-gray-700"
                                :class="{'border-red-500 ring-red-500/10': errors.name}"
                            >
                        </div>
                        <div class="min-h-[24px] mt-1 ml-1">
                            <template x-if="errors.name">
                                <p class="text-[12px] font-medium text-red-500 flex items-center gap-1">
                                    <i class="fas fa-exclamation-circle"></i>
                                    <span x-text="errors.name[0]"></span>
                                </p>
                            </template>
                        </div>
                    </div>

                    <!-- Email -->
                    <div class="col-span-1">
                        <label class="block text-sm font-semibold text-gray-800 mb-1.5 ml-1">Email <span class="text-red-500">*</span></label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none transition-colors duration-200 group-focus-within:text-indigo-600 text-gray-400">
                                <i class="fas fa-envelope text-sm"></i>
                            </div>
                            <input 
                                type="email" 
                                name="email" 
                                x-model="formData.email"
                                @input="if(errors.email) delete errors.email"
                                placeholder="example@school.com"
                                class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 focus:bg-white transition-all duration-200 shadow-sm placeholder:text-gray-400 text-gray-700"
                                :class="{'border-red-500 ring-red-500/10': errors.email}"
                            >
                        </div>
                        <div class="min-h-[24px] mt-1 ml-1">
                            <template x-if="errors.email">
                                <p class="text-[12px] font-medium text-red-500 flex items-center gap-1">
                                    <i class="fas fa-exclamation-circle"></i>
                                    <span x-text="errors.email[0]"></span>
                                </p>
                            </template>
                        </div>
                    </div>

                    <!-- Password -->
                    <div class="col-span-1">
                        <label class="block text-sm font-semibold text-gray-800 mb-1.5 ml-1">
                            Password <span class="text-red-500" x-show="!editMode">*</span>
                            <span class="text-[11px] text-gray-400 font-normal italic ml-1" x-show="editMode">Leave blank to keep current</span>
                        </label>
                        <div class="relative group" x-data="{ show: false }">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none transition-colors duration-200 group-focus-within:text-indigo-600 text-gray-400">
                                <i class="fas fa-lock text-sm"></i>
                            </div>
                            <input 
                                :type="show ? 'text' : 'password'" 
                                name="password" 
                                x-model="formData.password"
                                @input="if(errors.password) delete errors.password"
                                placeholder="••••••••"
                                class="w-full pl-10 pr-10 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 focus:bg-white transition-all duration-200 shadow-sm placeholder:text-gray-400 text-gray-700"
                                :class="{'border-red-500 ring-red-500/10': errors.password}"
                            >
                            <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-gray-400 hover:text-indigo-600 transition-colors">
                                <i class="fas" :class="show ? 'fa-eye-slash' : 'fa-eye'"></i>
                            </button>
                        </div>
                        <div class="min-h-[24px] mt-1 ml-1">
                            <template x-if="errors.password">
                                <p class="text-[12px] font-medium text-red-500 flex items-center gap-1">
                                    <i class="fas fa-exclamation-circle"></i>
                                    <span x-text="errors.password[0]"></span>
                                </p>
                            </template>
                        </div>
                    </div>

                    <!-- Phone Number -->
                    <div class="col-span-1">
                        <label class="block text-sm font-semibold text-gray-800 mb-1.5 ml-1">Phone Number</label>
                        <div class="relative group phone-input-wrapper">
                            <div class="absolute inset-y-0 left-0 flex items-center pointer-events-none z-10 transition-colors duration-200 group-focus-within:text-indigo-600">
                                <span class="px-3.5 py-2.5 text-gray-500 font-bold bg-gray-100/50 border-r border-gray-200 rounded-l-xl h-full flex items-center select-none text-xs">
                                    +91
                                </span>
                            </div>
                            <input 
                                type="tel" 
                                name="phone" 
                                x-model="formData.phone"
                                @input="if(errors.phone) delete errors.phone"
                                placeholder="888 888 8888"
                                inputmode="numeric"
                                oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10)"
                                class="w-full pl-[70px] pr-16 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 focus:bg-white transition-all duration-200 shadow-sm placeholder:text-gray-400 text-gray-700"
                                :class="{'border-red-500 ring-red-500/10': errors.phone}"
                            >
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3.5 pointer-events-none">
                                <span class="text-[10px] font-bold tracking-tighter" 
                                      :class="formData.phone.length === 10 ? 'text-green-500' : (formData.phone.length > 0 ? 'text-amber-500' : 'text-gray-300')"
                                      x-text="formData.phone.length + '/10'">0/10</span>
                            </div>
                        </div>
                        <div class="min-h-[24px] mt-1 ml-1">
                            <template x-if="errors.phone">
                                <p class="text-[12px] font-medium text-red-500 flex items-center gap-1">
                                    <i class="fas fa-exclamation-circle"></i>
                                    <span x-text="errors.phone[0]"></span>
                                </p>
                            </template>
                        </div>
                    </div>

                    <!-- Role -->
                    <div class="col-span-1">
                        <label class="block text-sm font-semibold text-gray-800 mb-1.5 ml-1">Account Role <span class="text-red-500">*</span></label>
                        <div class="relative group">
                            <select 
                                name="role" 
                                x-model="formData.role"
                                @change="if(errors.role) delete errors.role"
                                class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 focus:bg-white transition-all duration-200 shadow-sm text-gray-700 appearance-none bg-[url('data:image/svg+xml;charset=utf-8,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20fill%3D%22none%22%20viewBox%3D%220%200%2020%2020%22%3E%3Cpath%20stroke%3D%22%236b7280%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%20stroke-width%3D%221.5%22%20d%3D%22m6%208%204%204%204-4%22%2F%3E%3C%2Fsvg%3E')] bg-[length:1.25rem_1.25rem] bg-[right_0.75rem_center] bg-no-repeat pr-10"
                                :class="{'border-red-500 ring-red-500/10': errors.role}"
                            >
                                <option value="">Choose a role</option>
                                @foreach($roles as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="min-h-[24px] mt-1 ml-1">
                            <template x-if="errors.role">
                                <p class="text-[12px] font-medium text-red-500 flex items-center gap-1">
                                    <i class="fas fa-exclamation-circle"></i>
                                    <span x-text="errors.role[0]"></span>
                                </p>
                            </template>
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="col-span-1" x-show="editMode">
                        <label class="block text-sm font-semibold text-gray-800 mb-1.5 ml-1">Account Status <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <select 
                                name="status" 
                                x-model="formData.status"
                                @change="if(errors.status) delete errors.status"
                                class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 focus:bg-white transition-all duration-200 shadow-sm text-gray-700 appearance-none bg-[url('data:image/svg+xml;charset=utf-8,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20fill%3D%22none%22%20viewBox%3D%220%200%2020%2020%22%3E%3Cpath%20stroke%3D%22%236b7280%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%20stroke-width%3D%221.5%22%20d%3D%22m6%208%204%204%204-4%22%2F%3E%3C%2Fsvg%3E')] bg-[length:1.25rem_1.25rem] bg-[right_0.75rem_center] bg-no-repeat pr-10"
                                :class="{'border-red-500 ring-red-500/10': errors.status}"
                            >
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="suspended">Suspended</option>
                            </select>
                        </div>
                        <div class="min-h-[24px] mt-1 ml-1">
                            <template x-if="errors.status">
                                <p class="text-[12px] font-medium text-red-500 flex items-center gap-1">
                                    <i class="fas fa-exclamation-circle"></i>
                                    <span x-text="errors.status[0]"></span>
                                </p>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="px-8 py-6 bg-gray-50/50 flex items-center justify-end gap-3 rounded-b-lg border-t border-gray-100">
                <button 
                    type="button" 
                    @click="closeModal()"
                    class="px-5 py-2.5 text-sm font-bold text-gray-500 hover:text-gray-700 hover:bg-gray-100/50 rounded-xl transition-all duration-200"
                >
                    Cancel
                </button>
                <button 
                    type="submit"
                    :disabled="submitting"
                    class="px-8 py-2.5 bg-gradient-to-r from-indigo-600 to-violet-600 text-white text-sm font-bold rounded-xl hover:from-indigo-700 hover:to-violet-700 transition-all duration-200 shadow-lg shadow-indigo-200 flex items-center justify-center min-w-[140px] gap-2 active:scale-95 disabled:opacity-50"
                >
                    <template x-if="submitting">
                        <span class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                    </template>
                    <span x-text="editMode ? (submitting ? 'Updating' : 'Update Changes') : (submitting ? 'Creating' : 'Create User')"></span>
                </button>
            </div>
        </form>
    </x-modal>
</div>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('userManagement', () => ({
        showModal: false,
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
            status: 'active'
        },
        
        init() {
            // Sync Select2 with Alpine state
            if (typeof $ !== 'undefined') {
                this.$nextTick(() => {
                    const $role = $('select[name="role"]');
                    const $status = $('select[name="status"]');

                    $role.on('change', (e) => {
                        this.formData.role = e.target.value;
                        if (this.errors.role) delete this.errors.role;
                    });

                    $status.on('change', (e) => {
                        this.formData.status = e.target.value;
                        if (this.errors.status) delete this.errors.status;
                    });
                });
            }
        },
        
        async submitForm() {
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
                    body: JSON.stringify({
                        ...this.formData,
                        _method: this.editMode ? 'PUT' : 'POST'
                    })
                });
                
                const result = await response.json();
                
                if (response.ok) {
                    if (window.Toast) {
                        window.Toast.fire({
                            icon: 'success',
                            title: result.message || 'User saved successfully'
                        });
                    }
                    setTimeout(() => window.location.reload(), 1000);
                } else if (response.status === 422) {
                    this.errors = result.errors || {};
                } else {
                    throw new Error(result.message || 'Something went wrong');
                }
            } catch (error) {
                console.error('Submission error:', error);
                if (window.Toast) {
                    window.Toast.fire({
                        icon: 'error',
                        title: error.message || 'Could not save user'
                    });
                }
            } finally {
                this.submitting = false;
            }
        },
        
        openAddModal() {
            this.editMode = false;
            this.userId = null;
            this.errors = {};
            this.formData = { 
                name: '', 
                email: '', 
                password: '', 
                phone: '', 
                role: '', 
                status: 'active' 
            };
            this.$dispatch('open-modal', 'user-modal');
        },
        
        openEditModal(user) {
            console.log('Opening edit modal for user:', user);
            this.editMode = true;
            this.userId = user.id;
            this.errors = {};
            
            // Map status integer to string
            const statusMap = {
                0: 'inactive',
                1: 'active',
                2: 'suspended',
                3: 'pending'
            };

            // Determine role slug
            let roleSlug = '';
            if (user.role) {
                roleSlug = typeof user.role === 'object' ? user.role.slug : user.role;
            }
            console.log('Determined role slug:', roleSlug);

            this.formData = {
                name: user.name,
                email: user.email,
                password: '',
                phone: user.phone || '',
                role: roleSlug,
                status: statusMap[user.status] || 'active'
            };
            this.$dispatch('open-modal', 'user-modal');
            
            // Trigger Select2 update with a small delay to ensure modal is rendered and Select2 is ready
            setTimeout(() => {
                if (typeof $ !== 'undefined') {
                    console.log('Updating Select2 values:', this.formData.role, this.formData.status);
                    $('select[name="role"]').val(this.formData.role).trigger('change');
                    $('select[name="status"]').val(this.formData.status).trigger('change');
                }
            }, 100);
        },
        
        closeModal() {
            this.$dispatch('close-modal', 'user-modal');
        }
    }));
});
</script>
@endpush
@endsection
