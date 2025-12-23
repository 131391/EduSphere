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

    {{-- Visitor Statistics --}}
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow-sm border-l-4 border-blue-500 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500 uppercase font-semibold mb-1">Total Visitors</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $stats['total'] }}</p>
                </div>
                <div class="bg-blue-100 p-3 rounded-full">
                    <i class="fas fa-users text-blue-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border-l-4 border-green-500 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500 uppercase font-semibold mb-1">Online Visitors</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $stats['online'] }}</p>
                </div>
                <div class="bg-green-100 p-3 rounded-full">
                    <i class="fas fa-video text-green-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border-l-4 border-yellow-500 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500 uppercase font-semibold mb-1">Offline/Office</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $stats['offline'] }}</p>
                </div>
                <div class="bg-yellow-100 p-3 rounded-full">
                    <i class="fas fa-building text-yellow-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border-l-4 border-red-500 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500 uppercase font-semibold mb-1">Cancelled</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $stats['cancelled'] }}</p>
                </div>
                <div class="bg-red-100 p-3 rounded-full">
                    <i class="fas fa-times-circle text-red-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border-l-4 border-purple-500 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500 uppercase font-semibold mb-1">Online Meetings</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $stats['office'] }}</p>
                </div>
                <div class="bg-purple-100 p-3 rounded-full">
                    <i class="fas fa-laptop text-purple-600"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Page Header with Actions --}}
    <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <h2 class="text-xl font-bold text-gray-800">Visitor List</h2>
            <div class="flex flex-wrap gap-2">
                <button @click="openAddModal()" 
                        class="inline-flex items-center px-4 py-2 bg-teal-500 hover:bg-teal-600 text-white text-sm font-medium rounded-md transition-colors">
                    <i class="fas fa-plus mr-2"></i>
                    New Visitor
                </button>
                <a href="{{ route('receptionist.visitors.index', ['today' => 1]) }}" 
                   class="inline-flex items-center px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-white text-sm font-medium rounded-md transition-colors">
                    <i class="fas fa-calendar-day mr-2"></i>
                    Today's Visitors
                </a>
                <button class="inline-flex items-center px-4 py-2 bg-green-500 hover:bg-green-600 text-white text-sm font-medium rounded-md transition-colors">
                    <i class="fas fa-file-excel mr-2"></i>
                    Export
                </button>
            </div>
        </div>
    </div>

    {{-- Visitors Table --}}
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
                    return "openEditModal(JSON.parse(atob(this.getAttribute('data-visitor'))))";
                },
                'data-visitor' => function($row) {
                    return base64_encode(json_encode($row));
                },
                'icon' => 'fas fa-edit',
                'class' => 'text-blue-600 hover:text-blue-900',
                'title' => 'Edit',
            ],
            [
                'type' => 'form',
                'action' => function($row) {
                    return route('receptionist.visitors.destroy', $row->id);
                },
                'method' => 'DELETE',
                'confirm' => 'Are you sure you want to delete this visitor?',
                'icon' => 'fas fa-trash',
                'class' => 'text-red-600 hover:text-red-900',
                'title' => 'Delete',
                'dispatch' => 'confirm-delete',
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
                            <div class="flex flex-col items-center">
                                <div class="w-32 h-32 bg-gray-200 dark:bg-gray-600 rounded-lg mb-2 flex items-center justify-center overflow-hidden relative">
                                    <img id="visitor-photo-preview" src="#" alt="Visitor's Photo" class="hidden w-full h-full object-cover">
                                    <i class="fas fa-user text-gray-400 text-4xl" id="visitor-photo-icon"></i>
                                    <button type="button" 
                                            id="visitor-photo-remove" 
                                            onclick="removeImage(event, 'visitor_photo', 'visitor-photo-preview', 'visitor-photo-icon', 'visitor-photo-remove')"
                                            class="hidden absolute top-1 right-1 bg-red-500 hover:bg-red-600 text-white rounded-full w-6 h-6 flex items-center justify-center transition-colors duration-200 shadow-lg">
                                        <i class="fas fa-times text-xs"></i>
                                    </button>
                                </div>
                                <input type="file" name="visitor_photo" accept="image/*"
                                       onchange="previewImage(event, 'visitor-photo-preview', 'visitor-photo-icon', 'visitor-photo-remove')"
                                       class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">ID proof</label>
                            <div class="flex flex-col items-center">
                                <div class="w-32 h-32 bg-gray-200 dark:bg-gray-600 rounded-lg mb-2 flex items-center justify-center overflow-hidden relative">
                                    <img id="id-proof-preview" src="#" alt="ID Proof" class="hidden w-full h-full object-cover">
                                    <i class="fas fa-id-card text-gray-400 text-4xl" id="id-proof-icon"></i>
                                    <button type="button" 
                                            id="id-proof-remove" 
                                            onclick="removeImage(event, 'id_proof', 'id-proof-preview', 'id-proof-icon', 'id-proof-remove')"
                                            class="hidden absolute top-1 right-1 bg-red-500 hover:bg-red-600 text-white rounded-full w-6 h-6 flex items-center justify-center transition-colors duration-200 shadow-lg">
                                        <i class="fas fa-times text-xs"></i>
                                    </button>
                                </div>
                                <input type="file" name="id_proof" accept="image/*,application/pdf"
                                       onchange="previewImage(event, 'id-proof-preview', 'id-proof-icon', 'id-proof-remove')"
                                       class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                            </div>
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
        
        init() {
            // Initialization code if needed
        },
        
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

// Global function to open edit modal (called from table action buttons)
function openEditModal(visitor) {
    const component = Alpine.$data(document.querySelector('[x-data*="visitorManagement"]'));
    if (component) {
        component.openEditModal(visitor);
    }
}

function previewImage(event, previewId, iconId, removeBtnId) {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById(previewId);
            const icon = document.getElementById(iconId);
            const removeBtn = document.getElementById(removeBtnId);
            
            preview.src = e.target.result;
            preview.classList.remove('hidden');
            if (icon) {
                icon.classList.add('hidden');
            }
            if (removeBtn) {
                removeBtn.classList.remove('hidden');
            }
        };
        reader.readAsDataURL(file);
    }
}

function removeImage(event, inputName, previewId, iconId, removeBtnId) {
    event.preventDefault();
    event.stopPropagation();
    
    const input = document.querySelector(`input[name="${inputName}"]`);
    const preview = document.getElementById(previewId);
    const icon = document.getElementById(iconId);
    const removeBtn = document.getElementById(removeBtnId);
    
    // Reset file input
    if (input) {
        input.value = '';
    }
    
    // Hide preview and show icon
    if (preview) {
        preview.src = '#';
        preview.classList.add('hidden');
    }
    if (icon) {
        icon.classList.remove('hidden');
    }
    if (removeBtn) {
        removeBtn.classList.add('hidden');
    }
}
</script>
@endpush
@endsection
