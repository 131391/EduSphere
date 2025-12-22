@extends('layouts.receptionist')

@section('title', 'Visitor Management - Receptionist')
@section('page-title', 'Visitor Entry')
@section('page-description', 'Manage visitor entries and appointments')

@section('content')
<div class="space-y-6" x-data="visitorManagement" x-init="init()">
    <!-- Success/Error Messages -->
    @if(session('success'))
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg relative">
        <span class="block sm:inline">{{ session('success') }}</span>
        <button @click="show = false" class="absolute top-0 right-0 px-4 py-3">
            <i class="fas fa-times"></i>
        </button>
    </div>
    @endif

    @if($errors->any())
    <div x-data="{ show: true }" x-show="show" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg relative">
        <strong class="font-bold">Whoops! Something went wrong.</strong>
        <ul class="mt-2 list-disc list-inside">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button @click="show = false" class="absolute top-0 right-0 px-4 py-3">
            <i class="fas fa-times"></i>
        </button>
    </div>
    @endif

    <!-- Visitor Statistics -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 flex items-center space-x-3">
            <div class="bg-teal-100 dark:bg-teal-900 p-3 rounded-lg">
                <i class="fas fa-users text-teal-600 dark:text-teal-400 text-xl"></i>
            </div>
            <div>
                <p class="text-2xl font-bold text-gray-800 dark:text-white">{{ $stats['total'] }}</p>
                <p class="text-xs text-gray-600 dark:text-gray-400">Total Visitor</p>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 flex items-center space-x-3">
            <div class="bg-blue-100 dark:bg-blue-900 p-3 rounded-lg">
                <i class="fas fa-video text-blue-600 dark:text-blue-400 text-xl"></i>
            </div>
            <div>
                <p class="text-2xl font-bold text-gray-800 dark:text-white">{{ $stats['online'] }}</p>
                <p class="text-xs text-gray-600 dark:text-gray-400">Online Visitor</p>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 flex items-center space-x-3">
            <div class="bg-green-100 dark:bg-green-900 p-3 rounded-lg">
                <i class="fas fa-building text-green-600 dark:text-green-400 text-xl"></i>
            </div>
            <div>
                <p class="text-2xl font-bold text-gray-800 dark:text-white">{{ $stats['offline'] }}</p>
                <p class="text-xs text-gray-600 dark:text-gray-400">Offline/Office</p>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 flex items-center space-x-3">
            <div class="bg-yellow-100 dark:bg-yellow-900 p-3 rounded-lg">
                <i class="fas fa-laptop text-yellow-600 dark:text-yellow-400 text-xl"></i>
            </div>
            <div>
                <p class="text-2xl font-bold text-gray-800 dark:text-white">{{ $stats['office'] }}</p>
                <p class="text-xs text-gray-600 dark:text-gray-400">Online Meeting</p>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 flex items-center space-x-3">
            <div class="bg-red-100 dark:bg-red-900 p-3 rounded-lg">
                <i class="fas fa-times-circle text-red-600 dark:text-red-400 text-xl"></i>
            </div>
            <div>
                <p class="text-2xl font-bold text-gray-800 dark:text-white">{{ $stats['cancelled'] }}</p>
                <p class="text-xs text-gray-600 dark:text-gray-400">Cancelled</p>
            </div>
        </div>
    </div>

    <!-- Actions Bar -->
    <div class="flex items-center justify-end bg-white dark:bg-gray-800 rounded-lg shadow p-4 gap-3">
        <button @click="openAddModal()" 
                class="bg-teal-500 hover:bg-teal-600 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors">
            <i class="fas fa-plus"></i>
            <span>Add Visitor</span>
        </button>
        <a href="{{ route('receptionist.visitors.index', ['today' => 1]) }}" 
           class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors">
            <i class="fas fa-calendar-day"></i>
            <span>Today's Visitor</span>
        </a>
        <button class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors">
            <i class="fas fa-file-excel"></i>
            <span>Export To Excel</span>
        </button>
    </div>

    <!-- Visitors Table -->
    @php
        $tableColumns = [
            [
                'key' => 'visitor_no',
                'label' => 'Visitor No',
                'sortable' => true,
            ],
            [
                'key' => 'name',
                'label' => 'Visitor Name',
                'sortable' => true,
            ],
            [
                'key' => 'mobile',
                'label' => 'Contact Number',
                'sortable' => false,
            ],
            [
                'key' => 'source',
                'label' => 'Sources',
                'sortable' => false,
                'render' => function($row) {
                    return $row->source ?? 'N/A';
                }
            ],
            [
                'key' => 'meeting_type',
                'label' => 'Meeting Type',
                'sortable' => true,
                'render' => function($row) {
                    return '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">' 
                         . ucfirst($row->meeting_type) . '</span>';
                }
            ],
            [
                'key' => 'meeting_with',
                'label' => 'Meeting With',
                'sortable' => false,
                'render' => function($row) {
                    return $row->meeting_with ?? 'N/A';
                }
            ],
            [
                'key' => 'check_in',
                'label' => 'Check In',
                'sortable' => true,
                'render' => function($row) {
                    return $row->check_in ? $row->check_in->format('d M, h:i A') : 'Not checked in';
                }
            ],
            [
                'key' => 'meeting_scheduled',
                'label' => 'Meeting Scheduled',
                'sortable' => true,
                'render' => function($row) {
                    return $row->meeting_scheduled ? $row->meeting_scheduled->format('d M, h:i A') : 'N/A';
                }
            ],
        ];

        $tableActions = [
            [
                'type' => 'button',
                'onclick' => function($row) {
                    return "openEditModal(" . htmlspecialchars(json_encode($row), ENT_QUOTES) . ")";
                },
                'icon' => 'fas fa-edit',
                'class' => 'text-blue-600 hover:text-blue-900',
                'title' => 'Edit',
            ],
            [
                'type' => 'button',
                'onclick' => function($row) {
                    return "confirmDelete({$row->id})";
                },
                'icon' => 'fas fa-trash',
                'class' => 'text-red-600 hover:text-red-900',
                'title' => 'Delete',
            ],
        ];
    @endphp

    <x-data-table 
        :columns="$tableColumns"
        :data="$visitors"
        :searchable="true"
        :actions="$tableActions"
        empty-message="No visitors found"
        empty-icon="fas fa-users"
    >
        Visitor List
    </x-data-table>

    <!-- Add/Edit Visitor Modal -->
    <div x-show="showModal" x-cloak 
         class="fixed inset-0 bg-gray-900 bg-opacity-50 overflow-y-auto h-full w-full z-50 flex items-center justify-center"
         @click.self="closeModal()">
        <div class="relative mx-auto w-full max-w-4xl shadow-2xl rounded-xl bg-white dark:bg-gray-800 overflow-hidden"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform scale-95"
             x-transition:enter-end="opacity-100 transform scale-100">
            
            <!-- Modal Header -->
            <div class="bg-teal-500 px-6 py-4 flex items-center justify-between">
                <h3 class="text-xl font-bold text-white" x-text="editMode ? 'Edit Visitor' : 'Add New Visitor'"></h3>
                <button @click="closeModal()" class="text-white hover:text-teal-100 transition-colors">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>

            <form :action="editMode ? `/receptionist/visitors/${visitorId}` : '{{ route('receptionist.visitors.store') }}'" 
                  method="POST" enctype="multipart/form-data" class="p-6">
                @csrf
                <template x-if="editMode">
                    @method('PUT')
                </template>

                <div class="grid grid-cols-2 gap-6">
                    <!-- Left Column -->
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Mobile Number *</label>
                            <input type="text" name="mobile" x-model="formData.mobile" required
                                   class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white @error('mobile') border-red-500 @enderror">
                            @error('mobile')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Visit Purpose *</label>
                            <select name="visit_purpose" x-model="formData.visit_purpose" required
                                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white @error('visit_purpose') border-red-500 @enderror">
                                <option value="">Select Purpose</option>
                                <option value="Walk in">Walk in</option>
                                <option value="General">General</option>
                                <option value="Admission">Admission</option>
                                <option value="Vendor">Vendor</option>
                                <option value="Fee Deposit">Fee Deposit</option>
                                <option value="Enquiry">Enquiry</option>
                                <option value="For Discussion">For Discussion</option>
                                <option value="Complain">Complain</option>
                                <option value="Suggestion">Suggestion</option>
                                <option value="For Document">For Document</option>
                                <option value="Transfer Certificate">Transfer Certificate</option>
                            </select>
                            @error('visit_purpose')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Email ID</label>
                            <input type="email" name="email" x-model="formData.email"
                                   class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Meeting Purpose</label>
                            <input type="text" name="meeting_purpose" x-model="formData.meeting_purpose"
                                   class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Priority *</label>
                            <select name="priority" x-model="formData.priority" required
                                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                                <option value="">Select Priority</option>
                                <option value="Urgent">Urgent</option>
                                <option value="High">High</option>
                                <option value="Medium">Medium</option>
                                <option value="Low">Low</option>
                            </select>
                        </div>
                    </div>

                    <!-- Right Column -->
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Visitor's Name *</label>
                            <input type="text" name="name" x-model="formData.name" required
                                   class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white @error('name') border-red-500 @enderror">
                            @error('name')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Visitor Type *</label>
                            <select name="visitor_type" x-model="formData.visitor_type" required
                                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                                <option value="">Select Type</option>
                                <option value="Parent">Parent</option>
                                <option value="General Visitor">General Visitor</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Address</label>
                            <input type="text" name="address" x-model="formData.address"
                                   class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Select Meeting with *</label>
                            <select name="meeting_with" x-model="formData.meeting_with" required
                                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                                <option value="">Select Person</option>
                                <option value="Principal">Principal</option>
                                <option value="Teacher">Teacher</option>
                                <option value="Accountant">Accountant</option>
                                <option value="Student">Student</option>
                                <option value="Non Teaching">Non Teaching</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">No. of Guest(s)</label>
                            <input type="number" name="no_of_guests" x-model="formData.no_of_guests" min="1" value="1"
                                   class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                        </div>
                    </div>
                </div>

                <!-- Meeting Type -->
                <div class="mt-4">
                    <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Meeting Type *</label>
                    <select name="meeting_type" x-model="formData.meeting_type" required
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                        <option value="offline">Offline</option>
                        <option value="online">Online</option>
                        <option value="office">Office</option>
                    </select>
                </div>

                <!-- Upload Section -->
                <div class="mt-6 bg-blue-50 dark:bg-blue-900 p-4 rounded-lg">
                    <h4 class="font-bold text-gray-800 dark:text-white mb-4">Upload Photo/ Document</h4>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Visitor's Photo</label>
                            <input type="file" name="visitor_photo" accept="image/*"
                                   class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">ID proof</label>
                            <input type="file" name="id_proof" accept="image/*,application/pdf"
                                   class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="mt-6 flex items-center justify-center gap-4">
                    <button type="button" @click="closeModal()"
                            class="px-8 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors font-semibold">
                        Close
                    </button>
                    <button type="submit"
                            class="px-8 py-2 bg-teal-500 text-white rounded-lg hover:bg-teal-600 transition-colors font-semibold shadow-md">
                        Submit
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div x-show="showDeleteModal" x-cloak 
         class="fixed inset-0 bg-gray-900 bg-opacity-50 overflow-y-auto h-full w-full z-50 flex items-center justify-center"
         @click.self="showDeleteModal = false">
        <div class="relative mx-auto w-full max-w-md shadow-2xl rounded-xl bg-white dark:bg-gray-800 overflow-hidden"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform scale-95"
             x-transition:enter-end="opacity-100 transform scale-100">
            
            <!-- Modal Header -->
            <div class="bg-red-500 px-6 py-4 flex items-center justify-between">
                <h3 class="text-xl font-bold text-white">Confirm Delete</h3>
                <button @click="showDeleteModal = false" class="text-white hover:text-red-100 transition-colors">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="p-6">
                <div class="flex items-center mb-4">
                    <div class="flex-shrink-0 w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-exclamation-triangle text-red-600 text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-lg font-semibold text-gray-800 dark:text-white">Are you sure?</p>
                        <p class="text-sm text-gray-600 dark:text-gray-400">This action cannot be undone.</p>
                    </div>
                </div>
                <p class="text-gray-700 dark:text-gray-300">
                    Do you really want to delete this visitor record? This will permanently remove the visitor data from the system.
                </p>
            </div>

            <!-- Modal Footer -->
            <div class="bg-gray-50 dark:bg-gray-700 px-6 py-4 flex items-center justify-end gap-3">
                <button @click="showDeleteModal = false"
                        class="px-6 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors font-semibold">
                    Cancel
                </button>
                <form :action="`/receptionist/visitors/${deleteVisitorId}`" method="POST" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="px-6 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors font-semibold shadow-md">
                        Delete
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('visitorManagement', () => ({
        showModal: false,
        showDeleteModal: false,
        editMode: false,
        visitorId: null,
        deleteVisitorId: null,
        formData: {
            name: '',
            mobile: '',
            email: '',
            address: '',
            visitor_type: '',
            visit_purpose: '',
            meeting_purpose: '',
            meeting_with: '',
            priority: 'Medium',
            no_of_guests: 1,
            meeting_type: 'offline',
        },
        
        init() {},
        
        openAddModal() {
            this.editMode = false;
            this.visitorId = null;
            this.formData = {
                name: '',
                mobile: '',
                email: '',
                address: '',
                visitor_type: '',
                visit_purpose: '',
                meeting_purpose: '',
                meeting_with: '',
                priority: 'Medium',
                no_of_guests: 1,
                meeting_type: 'offline',
            };
            this.showModal = true;
        },
        
        openEditModal(visitor) {
            this.editMode = true;
            this.visitorId = visitor.id;
            this.formData = {
                name: visitor.name,
                mobile: visitor.mobile,
                email: visitor.email || '',
                address: visitor.address || '',
                visitor_type: visitor.visitor_type || '',
                visit_purpose: visitor.visit_purpose || '',
                meeting_purpose: visitor.meeting_purpose || '',
                meeting_with: visitor.meeting_with || '',
                priority: visitor.priority,
                no_of_guests: visitor.no_of_guests,
                meeting_type: visitor.meeting_type,
            };
            this.showModal = true;
        },
        
        closeModal() {
            this.showModal = false;
        },

        confirmDelete(visitorId) {
            this.deleteVisitorId = visitorId;
            this.showDeleteModal = true;
        }
    }));
});
</script>
@endpush
@endsection
