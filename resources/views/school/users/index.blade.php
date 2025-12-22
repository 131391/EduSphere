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
                'render' => fn($row) => '<span class="px-3 py-1 bg-indigo-100 text-indigo-800 rounded-full text-sm font-semibold">' . ucfirst($row->role) . '</span>'
            ],
            [
                'key' => 'status',
                'label' => 'STATUS',
                'sortable' => true,
                'render' => function($row) {
                    $colors = [
                        'active' => 'bg-green-100 text-green-800',
                        'inactive' => 'bg-gray-100 text-gray-800',
                        'suspended' => 'bg-red-100 text-red-800',
                    ];
                    $color = $colors[$row->status] ?? 'bg-gray-100 text-gray-800';
                    return '<span class="px-2 py-1 rounded-full text-xs font-semibold ' . $color . '">' . ucfirst($row->status) . '</span>';
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
    <div 
        x-show="showModal" 
        x-cloak
        class="fixed inset-0 bg-gray-900 bg-opacity-50 overflow-y-auto h-full w-full z-50 flex items-center justify-center"
        @click.self="closeModal()"
    >
        <div 
            class="relative mx-auto w-full max-w-2xl shadow-2xl rounded-xl bg-white overflow-hidden"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 transform scale-95"
            x-transition:enter-end="opacity-100 transform scale-100"
        >
            <!-- Modal Header -->
            <div class="bg-blue-600 px-6 py-4 flex items-center justify-between">
                <h3 class="text-xl font-bold text-white" x-text="editMode ? 'Edit User' : 'Add User'"></h3>
                <button @click="closeModal()" class="text-white hover:text-blue-100 transition-colors">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>
            
            <form :action="editMode ? `/school/users/${userId}` : '{{ route('school.users.store') }}'" method="POST" class="p-6">
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
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                        >
                    </div>

                    <!-- Email -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Email <span class="text-red-500">*</span></label>
                        <input 
                            type="email" 
                            name="email" 
                            x-model="formData.email"
                            placeholder="Enter email address"
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                        >
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
                            :required="!editMode"
                            minlength="8"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                        >
                    </div>

                    <!-- Phone -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Phone</label>
                        <input 
                            type="text" 
                            name="phone" 
                            x-model="formData.phone"
                            placeholder="Enter phone number"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                        >
                    </div>

                    <!-- Role -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Role <span class="text-red-500">*</span></label>
                        <select 
                            name="role" 
                            x-model="formData.role"
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                        >
                            <option value="">Select Role</option>
                            @foreach($roles as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Status (only in edit mode) -->
                    <div x-show="editMode">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Status <span class="text-red-500">*</span></label>
                        <select 
                            name="status" 
                            x-model="formData.status"
                            :required="editMode"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                        >
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="suspended">Suspended</option>
                        </select>
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
        </div>
    </div>

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
            // Initialization logic if needed
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
            this.showModal = true;
        },
        
        openEditModal(user) {
            this.editMode = true;
            this.userId = user.id;
            this.formData = {
                name: user.name,
                email: user.email,
                password: '',
                phone: user.phone || '',
                role: user.role,
                status: user.status
            };
            this.showModal = true;
        },
        
        closeModal() {
            this.showModal = false;
        }
    }));
});
</script>
@endpush
@endsection
