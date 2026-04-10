@extends('layouts.receptionist')

@section('title', 'Visitor Management - Receptionist')
@section('page-title', 'Visitor Entry')
@section('page-description', 'Manage visitor entries and appointments')

@section('content')
<div class="space-y-6" x-data="visitorManagement" x-init="init()" @close-modal.window="if ($event.detail === 'visitor-modal') { resetForm(); }">
    <!-- Success/Error Messages -->

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
        use App\Enums\VisitorPriority;
        use App\Enums\VisitorMode;
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
                    $meetingType = $row->meeting_type instanceof \App\Enums\VisitorMode 
                        ? $row->meeting_type->label() 
                        : ($row->meeting_type ?? 'N/A');
                    return '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">' 
                         . $meetingType . '</span>';
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
                'type' => 'link',
                'href' => function($row) {
                    return route('receptionist.visitors.show', $row->id);
                },
                'icon' => 'fas fa-eye',
                'class' => 'text-green-600 hover:text-green-900',
                'title' => 'View',
            ],
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

    <x-modal name="visitor-modal" alpineTitle="editMode ? 'Edit Visitor' : 'Add New Visitor'" maxWidth="4xl">
        <form @submit.prevent="submitForm"
              method="POST" 
              enctype="multipart/form-data" 
              class="p-6"
              >
            @csrf
            <template x-if="editMode">
                @method('PUT')
            </template>
            <input type="hidden" name="visitor_id" :value="visitorId" x-show="editMode">

            {{-- Centralized Validation Summary --}}
            <template x-if="Object.keys(errors).length > 0">
                <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded-r-xl animate-fade-in">
                    <div class="flex items-center gap-2 mb-2">
                        <i class="fas fa-exclamation-circle text-red-500"></i>
                        <span class="text-xs font-black text-red-700 uppercase tracking-widest">Validation Exceptions</span>
                    </div>
                    <ul class="list-disc list-inside space-y-1">
                        <template x-for="(messages, field) in errors" :key="field">
                            <template x-for="message in messages" :key="message">
                                <li class="text-[10px] text-red-600 font-bold uppercase" x-text="message"></li>
                            </template>
                        </template>
                    </ul>
                </div>
            </template>

            <div class="grid grid-cols-2 gap-6">
                <!-- Left Column -->
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Mobile No <span class="text-red-500">*</span></label>
                        <input type="tel" name="mobile" x-model="formData.mobile" @input="delete errors.mobile" pattern="[0-9]{10,15}" inputmode="numeric" oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                               class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white transition-all shadow-sm"
                               :class="errors.mobile ? 'border-red-500 ring-red-500/5 bg-red-50/20' : 'border-gray-300 dark:border-gray-600'">
                        <template x-if="errors.mobile">
                            <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.mobile[0]"></p>
                        </template>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Visit Purpose <span class="text-red-500">*</span></label>
                        <select name="visit_purpose" x-model="formData.visit_purpose" @change="delete errors.visit_purpose"
                                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white transition-all shadow-sm"
                                :class="errors.visit_purpose ? 'border-red-500 ring-red-500/5 bg-red-50/20' : 'border-gray-300 dark:border-gray-600'">
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
                        <template x-if="errors.visit_purpose">
                            <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.visit_purpose[0]"></p>
                        </template>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Email ID</label>
                        <input type="email" name="email" x-model="formData.email" @input="delete errors.email"
                               class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white transition-all shadow-sm"
                               :class="errors.email ? 'border-red-500 ring-red-500/5 bg-red-50/20' : 'border-gray-300 dark:border-gray-600'">
                        <template x-if="errors.email">
                            <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.email[0]"></p>
                        </template>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Meeting Purpose</label>
                        <input type="text" name="meeting_purpose" x-model="formData.meeting_purpose" @input="delete errors.meeting_purpose"
                               class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white transition-all shadow-sm"
                               :class="errors.meeting_purpose ? 'border-red-500 ring-red-500/5 bg-red-50/20' : 'border-gray-300 dark:border-gray-600'">
                        <template x-if="errors.meeting_purpose">
                            <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.meeting_purpose[0]"></p>
                        </template>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Priority <span class="text-red-500">*</span></label>
                        <select name="priority" x-model="formData.priority" @change="delete errors.priority"
                                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white transition-all shadow-sm"
                                :class="errors.priority ? 'border-red-500 ring-red-500/5 bg-red-50/20' : 'border-gray-300 dark:border-gray-600'">
                            <option value="">Select Priority</option>
                            @foreach($priorities as $priority)
                            <option value="{{ $priority->value }}">{{ $priority->label() }}</option>
                            @endforeach
                        </select>
                        <template x-if="errors.priority">
                            <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.priority[0]"></p>
                        </template>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Visitor's Name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" x-model="formData.name" @input="delete errors.name"
                               class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white transition-all shadow-sm"
                               :class="errors.name ? 'border-red-500 ring-red-500/5 bg-red-50/20' : 'border-gray-300 dark:border-gray-600'">
                        <template x-if="errors.name">
                            <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.name[0]"></p>
                        </template>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Visitor Type <span class="text-red-500">*</span></label>
                        <select name="visitor_type" x-model="formData.visitor_type" @change="delete errors.visitor_type"
                                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white transition-all shadow-sm"
                                :class="errors.visitor_type ? 'border-red-500 ring-red-500/5 bg-red-50/20' : 'border-gray-300 dark:border-gray-600'">
                            <option value="">Select Type</option>
                            <option value="Parent">Parent</option>
                            <option value="General Visitor">General Visitor</option>
                        </select>
                        <template x-if="errors.visitor_type">
                            <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.visitor_type[0]"></p>
                        </template>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Address</label>
                        <input type="text" name="address" x-model="formData.address" @input="delete errors.address"
                               class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white transition-all shadow-sm"
                               :class="errors.address ? 'border-red-500 ring-red-500/5 bg-red-50/20' : 'border-gray-300 dark:border-gray-600'">
                        <template x-if="errors.address">
                            <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.address[0]"></p>
                        </template>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Select Meeting with <span class="text-red-500">*</span></label>
                        <select name="meeting_with" x-model="formData.meeting_with" @change="delete errors.meeting_with"
                                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white transition-all shadow-sm"
                                :class="errors.meeting_with ? 'border-red-500 ring-red-500/5 bg-red-50/20' : 'border-gray-300 dark:border-gray-600'">
                            <option value="">Select Person</option>
                            <option value="Principal">Principal</option>
                            <option value="Teacher">Teacher</option>
                            <option value="Accountant">Accountant</option>
                            <option value="Student">Student</option>
                            <option value="Non Teaching">Non Teaching</option>
                        </select>
                        <template x-if="errors.meeting_with">
                            <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.meeting_with[0]"></p>
                        </template>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">No. of Guest(s)</label>
                        <input type="number" name="no_of_guests" x-model="formData.no_of_guests" min="1" @input="delete errors.no_of_guests"
                               class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white transition-all shadow-sm"
                               :class="errors.no_of_guests ? 'border-red-500 ring-red-500/5 bg-red-50/20' : 'border-gray-300 dark:border-gray-600'">
                        <template x-if="errors.no_of_guests">
                            <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.no_of_guests[0]"></p>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Meeting Type -->
            <div class="mt-4">
                <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Meeting Type <span class="text-red-500">*</span></label>
                <select name="meeting_type" x-model="formData.meeting_type" @change="delete errors.meeting_type"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white transition-all shadow-sm"
                        :class="errors.meeting_type ? 'border-red-500 ring-red-500/5 bg-red-50/20' : 'border-gray-300 dark:border-gray-600'">
                    <option value="">Select Meeting Type</option>
                    @foreach($meetingTypes as $meetingType)
                    <option value="{{ $meetingType->value }}">{{ $meetingType->label() }}</option>
                    @endforeach
                </select>
                <template x-if="errors.meeting_type">
                    <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.meeting_type[0]"></p>
                </template>
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
                        :disabled="submitting"
                        class="px-8 py-2 bg-teal-500 text-white rounded-lg hover:bg-teal-600 transition-colors font-semibold shadow-md disabled:opacity-50 flex items-center gap-2">
                    <template x-if="submitting">
                        <span class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                    </template>
                    <span x-text="submitting ? 'Processing...' : 'Submit'"></span>
                </button>
            </div>
        </form>
    </x-modal>

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
                <button @click="deleteVisitor"
                        :disabled="submitting"
                        class="px-6 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors font-semibold shadow-md disabled:opacity-50 flex items-center gap-2">
                    <template x-if="submitting">
                        <span class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                    </template>
                    <span x-text="submitting ? 'Deleting...' : 'Delete'"></span>
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('visitorManagement', () => ({
        submitting: false,
        errors: {},
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
            meeting_type: '{{ VisitorMode::Offline->value }}', // Offline = 2
            source: '',
            meeting_scheduled: '',
        },
        
        init() {
            // Listen for modal close event to reset form
            window.addEventListener('close-modal', (event) => {
                if (event.detail === 'visitor-modal') {
                    this.resetForm();
                }
            });
            
            // Hide error banner when modal opens
            this.$watch('showModal', (value) => {
                if (value) {
                    const errorBanner = document.getElementById('error-banner');
                    if (errorBanner) {
                        errorBanner.style.display = 'none';
                        if (errorBanner.__x) {
                            errorBanner.__x.$data.show = false;
                        }
                    }
                }
            });
            
            // Check if there are validation errors and reopen modal with old data
            @if($errors->any())
                this.editMode = {{ old('_method') === 'PUT' || request()->routeIs('receptionist.visitors.update') ? 'true' : 'false' }};
                this.visitorId = '{{ old('visitor_id', request()->route('visitor')?->id ?? '') }}';
                this.formData = {
                    name: '{{ old('name') }}',
                    mobile: '{{ old('mobile') }}',
                    email: '{{ old('email') }}',
                    address: '{{ old('address') }}',
                    visitor_type: '{{ old('visitor_type') }}',
                    visit_purpose: '{{ old('visit_purpose') }}',
                    meeting_purpose: '{{ old('meeting_purpose') }}',
                    meeting_with: '{{ old('meeting_with') }}',
                    priority: '{{ old('priority') }}',
                    no_of_guests: '{{ old('no_of_guests') }}',
                    meeting_type: '{{ old('meeting_type') }}',
                    source: '{{ old('source') }}',
                    meeting_scheduled: '{{ old('meeting_scheduled') }}',
                };
                this.$nextTick(() => {
                    this.$dispatch('open-modal', 'visitor-modal');
                    // Set Select2 values after modal opens to ensure they're displayed properly
                    setTimeout(() => {
                        // Set Select2 dropdowns using jQuery
                        if (this.formData.visit_purpose) {
                            $('select[name="visit_purpose"]').val(this.formData.visit_purpose).trigger('change');
                        }
                        if (this.formData.visitor_type) {
                            $('select[name="visitor_type"]').val(this.formData.visitor_type).trigger('change');
                        }
                        if (this.formData.meeting_with) {
                            $('select[name="meeting_with"]').val(this.formData.meeting_with).trigger('change');
                        }
                        if (this.formData.priority) {
                            $('select[name="priority"]').val(this.formData.priority).trigger('change');
                        }
                        if (this.formData.meeting_type) {
                            $('select[name="meeting_type"]').val(this.formData.meeting_type).trigger('change');
                        }
                    }, 200);
                });
            @endif
        },
        
        resetForm() {
            // Reset form data to defaults
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
                priority: '',
                no_of_guests: '',
                meeting_type: '',
                source: '',
                meeting_scheduled: '',
            };
            
            // Reset Select2 dropdowns
            setTimeout(() => {
                $('select[name="visit_purpose"]').val(null).trigger('change');
                $('select[name="visitor_type"]').val(null).trigger('change');
                $('select[name="meeting_with"]').val(null).trigger('change');
                $('select[name="priority"]').val(null).trigger('change');
                $('select[name="meeting_type"]').val(null).trigger('change');
            }, 50);
            
            // Reset image previews
            this.resetImagePreviews();
            
            // Reset file inputs
            const fileInputs = document.querySelectorAll('input[type="file"]');
            fileInputs.forEach(input => {
                input.value = '';
            });
        },
        
        resetImagePreviews() {
            // Reset visitor photo preview
            const visitorPhotoPreview = document.getElementById('visitor-photo-preview');
            const visitorPhotoIcon = document.getElementById('visitor-photo-icon');
            const visitorPhotoRemove = document.getElementById('visitor-photo-remove');
            
            if (visitorPhotoPreview) {
                visitorPhotoPreview.src = '#';
                visitorPhotoPreview.classList.add('hidden');
            }
            if (visitorPhotoIcon) {
                visitorPhotoIcon.classList.remove('hidden');
            }
            if (visitorPhotoRemove) {
                visitorPhotoRemove.classList.add('hidden');
            }
            
            // Reset ID proof preview
            const idProofPreview = document.getElementById('id-proof-preview');
            const idProofIcon = document.getElementById('id-proof-icon');
            const idProofRemove = document.getElementById('id-proof-remove');
            
            if (idProofPreview) {
                idProofPreview.src = '#';
                idProofPreview.classList.add('hidden');
            }
            if (idProofIcon) {
                idProofIcon.classList.remove('hidden');
            }
            if (idProofRemove) {
                idProofRemove.classList.add('hidden');
            }
        },
        
        openAddModal() {
            this.resetForm();
            this.$dispatch('open-modal', 'visitor-modal');
        },

        async submitForm() {
            this.submitting = true;
            this.errors = {};
            
            try {
                const url = this.editMode 
                    ? `/receptionist/visitors/${this.visitorId}` 
                    : '{{ route('receptionist.visitors.store') }}';
                
                const method = this.editMode ? 'POST' : 'POST'; // We use POST for both, adding _method for PUT
                const formData = new FormData(this.$el);
                
                if (this.editMode) {
                    formData.append('_method', 'PUT');
                }

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
                    if (window.Toast) {
                        window.Toast.fire({ icon: 'success', title: result.message });
                    }
                    setTimeout(() => window.location.reload(), 1000);
                } else if (response.status === 422) {
                    this.errors = result.errors || {};
                } else {
                    throw new Error(result.message || 'Operation failed');
                }
            } catch (error) {
                if (window.Toast) {
                    window.Toast.fire({ icon: 'error', title: error.message });
                }
            } finally {
                this.submitting = false;
            }
        },

        async deleteVisitor() {
            this.submitting = true;
            try {
                const response = await fetch(`/receptionist/visitors/${this.deleteVisitorId}`, {
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
                    if (window.Toast) {
                        window.Toast.fire({ icon: 'success', title: result.message });
                    }
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    throw new Error(result.message || 'Deletion failed');
                }
            } catch (error) {
                if (window.Toast) {
                    window.Toast.fire({ icon: 'error', title: error.message });
                }
            } finally {
                this.submitting = false;
            }
        },
        
        openEditModal(visitor) {
            // Hide error banner when opening modal
            const errorBanner = document.getElementById('error-banner');
            if (errorBanner) {
                errorBanner.style.display = 'none';
                if (errorBanner.__x) {
                    errorBanner.__x.$data.show = false;
                }
            }
            
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
                priority: String(visitor.priority?.value || visitor.priority || '{{ VisitorPriority::Medium->value }}'), // Convert enum to integer string
                no_of_guests: visitor.no_of_guests,
                meeting_type: String(visitor.meeting_type?.value || visitor.meeting_type || '{{ VisitorMode::Offline->value }}'), // Convert enum to integer string
            };
            this.$dispatch('open-modal', 'visitor-modal');
            
            // Set select values after modal opens
            this.$nextTick(() => {
                setTimeout(() => {
                    const selects = ['priority', 'visit_purpose', 'visitor_type', 'meeting_with', 'meeting_type'];
                    selects.forEach(selectName => {
                        const select = document.querySelector(`[name="${selectName}"]`);
                        if (select && this.formData[selectName]) {
                            select.value = this.formData[selectName];
                            // Trigger change event to update Alpine.js
                            select.dispatchEvent(new Event('change', { bubbles: true }));
                        }
                    });
                }, 100);
            });
            
            // Display existing photos after modal is shown
            setTimeout(() => {
                // Display visitor photo if exists
                if (visitor.visitor_photo) {
                    const visitorPhotoPreview = document.getElementById('visitor-photo-preview');
                    const visitorPhotoIcon = document.getElementById('visitor-photo-icon');
                    const visitorPhotoRemove = document.getElementById('visitor-photo-remove');
                    
                    if (visitorPhotoPreview) {
                        visitorPhotoPreview.src = '/storage/' + visitor.visitor_photo;
                        visitorPhotoPreview.classList.remove('hidden');
                    }
                    if (visitorPhotoIcon) {
                        visitorPhotoIcon.classList.add('hidden');
                    }
                    if (visitorPhotoRemove) {
                        visitorPhotoRemove.classList.remove('hidden');
                    }
                }
                
                // Display ID proof if exists
                if (visitor.id_proof) {
                    const idProofPreview = document.getElementById('id-proof-preview');
                    const idProofIcon = document.getElementById('id-proof-icon');
                    const idProofRemove = document.getElementById('id-proof-remove');
                    
                    if (idProofPreview) {
                        idProofPreview.src = '/storage/' + visitor.id_proof;
                        idProofPreview.classList.remove('hidden');
                    }
                    if (idProofIcon) {
                        idProofIcon.classList.add('hidden');
                    }
                    if (idProofRemove) {
                        idProofRemove.classList.remove('hidden');
                    }
                }
            }, 100);
        },
        
        closeModal() {
            this.resetForm();
            this.$dispatch('close-modal', 'visitor-modal');
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

<script>
// Global script to hide validation errors when user starts typing or selecting
document.addEventListener('DOMContentLoaded', function() {
    // Function to hide error banner
    const hideErrorBanner = function() {
        const errorBanner = document.getElementById('error-banner');
        if (errorBanner) {
            errorBanner.style.display = 'none';
            // Also update Alpine.js state if available
            if (errorBanner.__x) {
                errorBanner.__x.$data.show = false;
            }
        }
    };
    
    // Hide error banner when clicking on any form field
    document.addEventListener('click', function(e) {
        if (e.target.tagName === 'INPUT' || e.target.tagName === 'SELECT' || e.target.tagName === 'TEXTAREA') {
            hideErrorBanner();
        }
    });
    
    // Add event listeners to all inputs and selects in the modal
    const modal = document.querySelector('[x-data*="visitorManagement"]');
    if (modal) {
        // Handle regular inputs
        modal.addEventListener('input', function(e) {
            if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') {
                hideErrorBanner();
                const errorElement = e.target.nextElementSibling;
                if (errorElement && errorElement.classList.contains('text-red-500')) {
                    errorElement.classList.add('hidden');
                }
                // Also remove red border
                e.target.classList.remove('border-red-500');
            }
        });
        
        // Handle native selects - use change event
        modal.addEventListener('change', function(e) {
            if (e.target.tagName === 'SELECT') {
                hideErrorBanner();
                const errorElement = e.target.nextElementSibling;
                if (errorElement && errorElement.classList.contains('text-red-500')) {
                    errorElement.classList.add('hidden');
                }
                // Also remove red border
                e.target.classList.remove('border-red-500');
            }
        });
        
        // Also listen for input events on selects (some browsers fire input on select change)
        modal.addEventListener('input', function(e) {
            if (e.target.tagName === 'SELECT') {
                hideErrorBanner();
                const errorElement = e.target.nextElementSibling;
                if (errorElement && errorElement.classList.contains('text-red-500')) {
                    errorElement.classList.add('hidden');
                }
                // Also remove red border
                e.target.classList.remove('border-red-500');
            }
        });
    }
});
</script>
@endpush
@endsection
