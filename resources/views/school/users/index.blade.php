@extends('layouts.school')

@section('title', 'User Management')

@section('content')
<div class="space-y-6" x-data="userManagement" x-init="init()">
    @if(session('success'))
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg relative">
        <span class="block sm:inline">{{ session('success') }}</span>
        <button @click="show = false" class="absolute top-0 right-0 px-4 py-3">
            <i class="fas fa-times"></i>
        </button>
    </div>
    @endif

    @if(session('error'))
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg relative">
        <span class="block sm:inline">{{ session('error') }}</span>
        <button @click="show = false" class="absolute top-0 right-0 px-4 py-3">
            <i class="fas fa-times"></i>
        </button>
    </div>
    @endif

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">User Management</h1>
            <p class="text-gray-600 mt-1">Manage school staff users (teachers, receptionists, etc.)</p>
        </div>
        <button 
            @click="openAddModal()" 
            class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 flex items-center transition-colors shadow-md"
        >
            <i class="fas fa-plus mr-2"></i>
            Add User
        </button>
    </div>

    @php
        $tableColumns = [
            [
                'key' => 'id',
                'label' => 'SR NO',
                'render' => function($row) use ($users) {
                    static $index = 0;
                    return $users->firstItem() + $index++;
                }
            ],
            [
                'key' => 'name',
                'label' => 'NAME',
                'sortable' => true,
                'render' => fn($row) => '<span class="font-medium text-gray-900">' . e($row->name) . '</span>'
            ],
            [
                'key' => 'email',
                'label' => 'EMAIL',
                'sortable' => true,
            ],
            [
                'key' => 'phone',
                'label' => 'PHONE',
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
                    $colors = [
                        \App\Models\User::STATUS_ACTIVE => 'bg-green-100 text-green-800',
                        \App\Models\User::STATUS_INACTIVE => 'bg-gray-100 text-gray-800',
                        \App\Models\User::STATUS_SUSPENDED => 'bg-red-100 text-red-800',
                        \App\Models\User::STATUS_PENDING => 'bg-yellow-100 text-yellow-800',
                    ];
                    $color = $colors[$row->status] ?? 'bg-gray-100 text-gray-800';
                    $label = \App\Models\User::STATUS_LABELS[$row->status] ?? 'Unknown';
                    return '<span class="px-2 py-1 rounded-full text-xs font-semibold ' . $color . '">' . ucfirst($label) . '</span>';
                }
            ],
        ];

        $tableActions = [
            [
                'type' => 'button',
                'icon' => 'fas fa-edit',
                'class' => 'text-blue-600 hover:text-blue-900',
                'title' => 'Edit',
                'onClick' => 'openEditModal(row)'
            ],
            [
                'type' => 'form',
                'url' => fn($row) => route('school.users.destroy', $row->id),
                'method' => 'DELETE',
                'icon' => 'fas fa-trash',
                'class' => 'text-red-600 hover:text-red-900',
                'title' => 'Delete',
                'dispatch' => [
                    'event' => 'open-confirm-modal',
                    'title' => 'Delete User',
                    'message' => 'Are you sure you want to delete this user?'
                ]
            ],
        ];
    @endphp

    <x-data-table 
        :columns="$tableColumns"
        :data="$users"
        :actions="$tableActions"
        empty-message="No users found"
        empty-icon="fas fa-users"
    >
        Users List
    </x-data-table>

    <!-- Add/Edit User Modal -->
    <x-modal name="user-modal" alpineTitle="editMode ? 'Edit User' : 'Add User'" maxWidth="2xl">
        <form :action="editMode ? `/school/users/${userId}` : '{{ route('school.users.store') }}'" method="POST" class="p-6" novalidate>
            @csrf
            <template x-if="editMode">
                @method('PUT')
            </template>

            <div class="grid grid-cols-2 gap-6">
                <!-- Name -->
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Full Name <span class="text-red-500">*</span></label>
                    <input 
                        type="text" 
                        name="name" 
                        x-model="formData.name"
                        placeholder="Enter full name"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all @error('name') border-red-500 @enderror"
                    >
                    @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email -->
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Email <span class="text-red-500">*</span></label>
                    <input 
                        type="email" 
                        name="email" 
                        x-model="formData.email"
                        placeholder="Enter email address"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all @error('email') border-red-500 @enderror"
                    >
                    @error('email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password -->
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">
                        Password <span class="text-red-500" x-show="!editMode">*</span>
                        <span class="text-xs text-gray-500 font-normal" x-show="editMode">(Leave blank to keep current)</span>
                    </label>
                    <input 
                        type="password" 
                        name="password" 
                        x-model="formData.password"
                        placeholder="Enter password"
                        minlength="8"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all @error('password') border-red-500 @enderror"
                    >
                    @error('password')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Phone -->
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Phone</label>
                    <input 
                        type="tel" 
                        name="phone" 
                        x-model="formData.phone"
                        placeholder="Enter phone number"
                        pattern="[0-9]{10,15}" 
                        inputmode="numeric"
                        oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all @error('phone') border-red-500 @enderror"
                    >
                    @error('phone')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Role -->
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Role <span class="text-red-500">*</span></label>
                    <select 
                        name="role" 
                        x-model="formData.role"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all @error('role') border-red-500 @enderror"
                    >
                        <option value="">Select Role</option>
                        @foreach($roles as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('role')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Status (only in edit mode) -->
                <div x-show="editMode">
                    <label class="block text-sm font-bold text-gray-700 mb-2">Status <span class="text-red-500">*</span></label>
                    <select 
                        name="status" 
                        x-model="formData.status"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all @error('status') border-red-500 @enderror"
                    >
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="suspended">Suspended</option>
                    </select>
                    @error('status')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="mt-8 flex items-center justify-end gap-4">
                <button 
                    type="button" 
                    @click="closeModal()"
                    class="px-6 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors font-semibold"
                >
                    Cancel
                </button>
                <button 
                    type="submit"
                    class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-semibold shadow-md"
                >
                    <span x-text="editMode ? 'Update User' : 'Create User'"></span>
                </button>
            </div>
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
        formData: {
            name: '',
            email: '',
            password: '',
            phone: '',
            role: '',
            status: 'active'
        },
        
        init() {
            @if($errors->any())
                this.formData = {
                    name: '{{ old('name') }}',
                    email: '{{ old('email') }}',
                    password: '',
                    phone: '{{ old('phone') }}',
                    role: '{{ old('role') }}',
                    status: '{{ old('status', 'active') }}'
                };
                this.editMode = {{ old('_method') === 'PUT' ? 'true' : 'false' }};
                this.userId = '{{ old('user_id') }}';
                this.$nextTick(() => {
                    this.$dispatch('open-modal', 'user-modal');
                });
            @endif
        },
        
        openAddModal() {
            this.editMode = false;
            this.userId = null;
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
<script>
// Global script to hide validation errors when user starts typing or selecting
document.addEventListener('DOMContentLoaded', function() {
    // Add event listeners to all inputs and selects in the modal
    const modal = document.querySelector('[x-data*="userManagement"]');
    if (modal) {
        // Handle regular inputs
        modal.addEventListener('input', function(e) {
            if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') {
                const errorElement = e.target.nextElementSibling;
                if (errorElement && errorElement.classList.contains('text-red-600')) {
                    errorElement.classList.add('hidden');
                }
                // Also remove red border
                e.target.classList.remove('border-red-500');
            }
        });
        
        // Handle native selects and Select2 selects
        modal.addEventListener('change', function(e) {
            if (e.target.tagName === 'SELECT') {
                const errorElement = e.target.nextElementSibling;
                if (errorElement && errorElement.classList.contains('text-red-600')) {
                    errorElement.classList.add('hidden');
                }
                // Also remove red border
                e.target.classList.remove('border-red-500');
            }
        });
        
        // Handle Select2 change events specifically
        if (typeof $ !== 'undefined') {
            $(modal).on('select2:select select2:clear', 'select', function(e) {
                const select = e.target;
                // Find the error message (it might be after the Select2 container)
                let errorElement = select.nextElementSibling;
                
                // If next sibling is Select2 container, look for error after it
                if (errorElement && errorElement.classList.contains('select2')) {
                    errorElement = errorElement.nextElementSibling;
                }
                
                if (errorElement && errorElement.classList.contains('text-red-600')) {
                    errorElement.classList.add('hidden');
                }
                
                // Remove red border from original select
                select.classList.remove('border-red-500');
                
                // Also remove red border from Select2 container
                const select2Container = $(select).next('.select2-container').find('.select2-selection');
                select2Container.removeClass('border-red-500');
            });
        }
    }
});
</script>
@endpush
@endsection
