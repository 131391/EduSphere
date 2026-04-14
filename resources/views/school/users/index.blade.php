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
                'render' => function($row) {
                    return '<div class="flex items-center gap-3">' .
                           '<div class="w-8 h-8 rounded-lg bg-indigo-50 flex items-center justify-center text-indigo-600"><i class="fas fa-user text-xs"></i></div>' .
                           '<span class="font-bold text-gray-700">' . e($row->name) . '</span>' .
                           '</div>';
                }
            ],
            [
                'key' => 'email',
                'label' => 'EMAIL',
                'sortable' => true,
                'render' => fn($row) => '<div class="text-gray-500 text-sm"><i class="far fa-envelope mr-2 opacity-40"></i>' . e($row->email) . '</div>'
            ],
            [
                'key' => 'phone',
                'label' => 'PHONE',
                'sortable' => true,
                'render' => fn($row) => '<div class="text-gray-500 text-sm"><i class="fas fa-phone-alt mr-2 opacity-40"></i>' . ($row->phone ?? '-') . '</div>'
            ],
            [
                'key' => 'role',
                'label' => 'ROLE',
                'sortable' => true,
                'render' => fn($row) => '<span class="px-2.5 py-0.5 bg-indigo-50 text-indigo-700 rounded-full text-[11px] font-black uppercase tracking-wider border border-indigo-100 shadow-sm">' . e($row->role->name ?? $row->role) . '</span>'
            ],
            [
                'key' => 'status',
                'label' => 'STATUS',
                'sortable' => true,
                'render' => function($row) {
                    $colorClass = match($row->status->color()) {
                        'green' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                        'red' => 'bg-rose-100 text-rose-700 border-rose-200',
                        'yellow' => 'bg-amber-100 text-amber-700 border-amber-200',
                        default => 'bg-slate-100 text-slate-700 border-slate-200',
                    };
                    return '<span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider border shadow-sm ' . $colorClass . '">' . e($row->status->label()) . '</span>';
                }
            ],
        ];

        $tableActions = [
            [
                'type' => 'button',
                'icon' => 'fas fa-edit',
                'class' => 'text-indigo-600 hover:text-indigo-900 bg-indigo-50 hover:bg-indigo-100 p-2 rounded-lg transition-colors',
                'onclick' => function($row) {
                    $data = json_encode($row);
                    return "window.dispatchEvent(new CustomEvent('open-edit-user', { detail: $data }))";
                },
                'title' => 'Edit',
            ],
            [
                'type' => 'button',
                'icon' => 'fas fa-trash-alt',
                'class' => 'text-rose-600 hover:text-rose-900 bg-rose-50 hover:bg-rose-100 p-2 rounded-lg transition-colors',
                'onclick' => function($row) {
                    $name = addslashes($row->name);
                    return "window.dispatchEvent(new CustomEvent('open-delete-user', { detail: { id: " . $row->id . ", name: '{$name}' } }))";
                },
                'title' => 'Delete User',
            ],
        ];
    @endphp
    <!-- Header Section -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 mb-6 border border-indigo-100/50">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-gray-800 dark:text-white flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-indigo-100 flex items-center justify-center text-indigo-600">
                        <i class="fas fa-users-cog text-xs"></i>
                    </div>
                    User Management
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Manage school staff users (teachers, receptionists, etc.)</p>
            </div>
            <button @click="openAddModal()" 
                    class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-700 hover:to-blue-700 text-white text-sm font-semibold rounded-xl transition-all shadow-md hover:shadow-lg active:scale-95">
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
    <x-modal name="user-modal" alpineTitle="editMode ? 'Edit User Configuration' : 'Create New Staff Account'" maxWidth="2xl">
        <form id="user-form" @submit.prevent="submitForm()" method="POST" novalidate>
            @csrf
            <template x-if="editMode">
                <input type="hidden" name="_method" value="PUT">
            </template>

            <div class="space-y-6 pt-2">
                <div class="grid grid-cols-2 gap-6">
                    <!-- Full Name -->
                    <div class="space-y-2">
                        <label class="modal-label-premium">Full Name <span class="text-red-600 font-bold">*</span></label>
                        <div class="relative group">
                            <input type="text" x-model="formData.name" @input="clearError('name')" placeholder="John Doe"
                                class="modal-input-premium pr-10" :class="{'border-red-500 ring-red-500/10': errors.name}">
                            <div class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none transition-colors group-focus-within:text-emerald-500">
                                <i class="fas fa-user text-xs"></i>
                            </div>
                        </div>
                        <template x-if="errors.name">
                            <p class="modal-error-message" x-text="errors.name[0]"></p>
                        </template>
                    </div>

                    <!-- Email -->
                    <div class="space-y-2">
                        <label class="modal-label-premium">Email Address <span class="text-red-600 font-bold">*</span></label>
                        <div class="relative group">
                            <input type="email" x-model="formData.email" @input="clearError('email')" placeholder="example@school.com"
                                class="modal-input-premium pr-10" :class="{'border-red-500 ring-red-500/10': errors.email}">
                            <div class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none transition-colors group-focus-within:text-emerald-500">
                                <i class="fas fa-envelope text-xs"></i>
                            </div>
                        </div>
                        <template x-if="errors.email">
                            <p class="modal-error-message" x-text="errors.email[0]"></p>
                        </template>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-6">
                    <!-- Role -->
                    <div class="space-y-2">
                        <label class="modal-label-premium">Account Role <span class="text-red-600 font-bold">*</span></label>
                        <div class="relative group">
                            <select x-model="formData.role" @change="clearError('role')" class="modal-input-premium appearance-none pr-10" :class="{'border-red-500 ring-red-500/10': errors.role}">
                                <option value="">Choose a role</option>
                                @foreach($roles as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            <div class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none transition-colors group-focus-within:text-emerald-500">
                                <i class="fas fa-chevron-down text-sm"></i>
                            </div>
                        </div>
                        <template x-if="errors.role">
                            <p class="modal-error-message" x-text="errors.role[0]"></p>
                        </template>
                    </div>

                    <!-- Phone Number -->
                    <div class="space-y-2">
                        <label class="modal-label-premium">Phone Number</label>
                        <div class="relative group">
                            <input type="tel" x-model="formData.phone" @input="clearError('phone')" placeholder="888 888 8888"
                                class="modal-input-premium !pl-16 pr-10" :class="{'border-red-500 ring-red-500/10': errors.phone}">
                            <div class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-500 font-black text-[10px] pointer-events-none border-r border-slate-200 pr-3 h-4 flex items-center">
                                +91
                            </div>
                            <div class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none transition-colors group-focus-within:text-emerald-500">
                                <i class="fas fa-phone-alt text-xs"></i>
                            </div>
                        </div>
                        <template x-if="errors.phone">
                            <p class="modal-error-message" x-text="errors.phone[0]"></p>
                        </template>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-6">
                    <!-- Password -->
                    <div class="space-y-2">
                        <label class="modal-label-premium">
                            Password <span class="text-red-600 font-bold" x-show="!editMode">*</span>
                            <span class="text-[10px] text-gray-400 font-normal italic ml-1" x-show="editMode">(Leave blank to keep)</span>
                        </label>
                        <div class="relative group" x-data="{ show: false }">
                            <input :type="show ? 'text' : 'password'" x-model="formData.password" @input="clearError('password')"
                                class="modal-input-premium !pr-16" :class="{'border-red-500 ring-red-500/10': errors.password}">
                            <div class="absolute right-10 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none border-r border-slate-100 pr-2.5 h-4 flex items-center">
                                <i class="fas fa-lock text-[11px]"></i>
                            </div>
                            <button type="button" @click="show = !show" class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-300 hover:text-indigo-600 transition-colors">
                                <i class="fas" :class="show ? 'fa-eye-slash' : 'fa-eye'"></i>
                            </button>
                        </div>
                        <template x-if="errors.password">
                            <p class="modal-error-message" x-text="errors.password[0]"></p>
                        </template>
                    </div>

                    <!-- Status Selection (Edit Only) -->
                    <div class="space-y-2" x-show="editMode">
                        <label class="modal-label-premium">Account Status <span class="text-red-600 font-bold">*</span></label>
                        <div class="relative group">
                            <select x-model="formData.status" @change="clearError('status')" class="modal-input-premium appearance-none pr-10" :class="{'border-red-500 ring-red-500/10': errors.status}">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="suspended">Suspended</option>
                            </select>
                            <div class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none transition-colors group-focus-within:text-emerald-500">
                                <i class="fas fa-toggle-on text-sm"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <x-slot name="footer">
                <button type="button" @click="closeModal()" class="btn-premium-cancel px-10">Cancel</button>
                <button type="submit" form="user-form" :disabled="submitting" class="btn-premium-primary min-w-[180px] bg-indigo-600 hover:bg-indigo-700 shadow-indigo-200">
                    <template x-if="submitting"><span class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin mr-3 inline-block"></span></template>
                    <span x-text="editMode ? 'Update Changes' : 'Create Staff Account'"></span>
                </button>
            </x-slot>
        </form>
    </x-modal>
    
    <!-- Confirmation Modal -->
    <x-confirm-modal />
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
            window.addEventListener('open-edit-user', (e) => this.openEditModal(e.detail));
            window.addEventListener('open-delete-user', (e) => this.confirmDelete(e.detail));

            // Sync Select2 with Alpine state
            this.$nextTick(() => {
                if (typeof $ !== 'undefined') {
                    // Role select
                    $('select[name="role"]').on('change', (e) => {
                        this.formData.role = e.target.value;
                        if (this.errors.role) delete this.errors.role;
                    });

                    // Status select
                    $('select[name="status"]').on('change', (e) => {
                        this.formData.status = e.target.value;
                        if (this.errors.status) delete this.errors.status;
                    });
                }
            });
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
