@extends('layouts.receptionist')

@section('title', 'Visitor Management - Receptionist')
@section('page-title', 'Visitor Entry')
@section('page-description', 'Manage visitor entries and appointments')

@section('content')
    <div class="space-y-6" x-data="visitorManagement" x-init="init()"
        @close-modal.window="if ($event.detail === 'visitor-modal') { resetForm(); }">
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
                    <button
                        class="inline-flex items-center px-4 py-2 bg-green-500 hover:bg-green-600 text-white text-sm font-medium rounded-md transition-colors">
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
                    'render' => function ($row) {
                        return $row->source ?? 'N/A';
                    }
                ],
                [
                    'key' => 'meeting_type',
                    'label' => 'Meeting Type',
                    'sortable' => true,
                    'render' => function ($row) {
                        $meetingType = $row->meeting_type instanceof VisitorMode
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
                    'render' => function ($row) {
                        return $row->meeting_with ?? 'N/A';
                    }
                ],
                [
                    'key' => 'check_in',
                    'label' => 'Check In',
                    'sortable' => true,
                    'render' => function ($row) {
                        return $row->check_in ? $row->check_in->format('d M, h:i A') : 'Not checked in';
                    }
                ],
                [
                    'key' => 'meeting_scheduled',
                    'label' => 'Meeting Scheduled',
                    'sortable' => true,
                    'render' => function ($row) {
                        return $row->meeting_scheduled ? $row->meeting_scheduled->format('d M, h:i A') : 'N/A';
                    }
                ],
            ];

            $tableActions = [
                [
                    'type' => 'link',
                    'href' => function ($row) {
                        return route('receptionist.visitors.show', $row->id);
                    },
                    'icon' => 'fas fa-eye',
                    'class' => 'text-green-600 hover:text-green-900',
                    'title' => 'View',
                ],
                [
                    'type' => 'button',
                    'onclick' => function ($row) {
                        $data = json_encode($row);
                        return "window.dispatchEvent(new CustomEvent('open-edit-visitor', { detail: $data }))";
                    },
                    'icon' => 'fas fa-edit',
                    'class' => 'text-blue-600 hover:text-blue-900',
                    'title' => 'Edit',
                ],
                [
                    'type' => 'button',
                    'onclick' => function ($row) {
                        return "window.dispatchEvent(new CustomEvent('open-delete-visitor', { detail: { id: " . $row->id . ", name: '" . addslashes($row->name) . "' } }))";
                    },
                    'icon' => 'fas fa-trash',
                    'class' => 'text-red-600 hover:text-red-900',
                    'title' => 'Delete',
                ],
            ];
        @endphp

        <x-data-table :columns="$tableColumns" :data="$visitors" :searchable="true" :actions="$tableActions"
            empty-message="No visitors found" empty-icon="fas fa-users">
            Visitor List
        </x-data-table>

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
                                <option value="Parent">Parent</option>
                                <option value="General Visitor">General Visitor</option>
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
                                <option value="Principal">Principal</option>
                                <option value="Teacher">Teacher</option>
                                <option value="Accountant">Accountant</option>
                                <option value="Student">Student</option>
                                <option value="Non Teaching">Non Teaching</option>
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

                    clearError(field) {
                        if (this.errors[field]) {
                            delete this.errors[field];
                        }
                    },

                    init() {
                        window.addEventListener('open-edit-visitor', (e) => this.openEditModal(e.detail));
                        window.addEventListener('open-delete-visitor', (e) => this.confirmDelete(e.detail));

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
                            const formData = new FormData(document.getElementById('visitorForm'));

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

                    confirmDelete(visitor) {
                        window.dispatchEvent(new CustomEvent('open-confirm-modal', {
                            detail: {
                                title: 'Delete Visitor Record',
                                message: `Are you sure you want to delete the visitor record for "${visitor.name}"? This action cannot be undone.`,
                                callback: async () => {
                                    this.submitting = true;
                                    try {
                                        const response = await fetch(`/receptionist/visitors/${visitor.id}`, {
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
                                }
                            }
                        }));
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
                        this.errors = {};
                        this.formData = {
                            name: visitor.name || '',
                            mobile: visitor.mobile || '',
                            email: visitor.email || '',
                            address: visitor.address || '',
                            visitor_type: visitor.visitor_type ? String(visitor.visitor_type) : '',
                            visit_purpose: visitor.visit_purpose ? String(visitor.visit_purpose) : '',
                            meeting_purpose: visitor.meeting_purpose || '',
                            meeting_with: visitor.meeting_with ? String(visitor.meeting_with) : '',
                            priority: String(visitor.priority?.value || visitor.priority || '{{ VisitorPriority::Medium->value }}'),
                            no_of_guests: visitor.no_of_guests || 1,
                            meeting_type: String(visitor.meeting_type?.value || visitor.meeting_type || '{{ VisitorMode::Offline->value }}'),
                        };
                        this.$dispatch('open-modal', 'visitor-modal');

                        // Set select values after modal opens
                        this.$nextTick(() => {
                            setTimeout(() => {
                                if (typeof $ !== 'undefined') {
                                    const selects = ['priority', 'visit_purpose', 'visitor_type', 'meeting_with', 'meeting_type'];
                                    selects.forEach(selectName => {
                                        if (this.formData[selectName]) {
                                            $(`select[name="${selectName}"]`).val(this.formData[selectName]).trigger('change');
                                        }
                                    });
                                }
                            }, 150);
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
                    reader.onload = function (e) {
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