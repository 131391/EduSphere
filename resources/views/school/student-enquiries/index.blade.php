@extends('layouts.school')

@section('title', 'Student Enquiries')

@section('content')
    <div class="space-y-6" x-data="enquiryManagement()">
        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <!-- Total Enquiry -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border-t-4 border-teal-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Enquiry</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ $stats['total'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-teal-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-clipboard-list text-teal-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- Pending Enquiry -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border-t-4 border-orange-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Pending Enquiry</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ $stats['pending'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-orange-100 rounded-full flex items-center justify-between">
                        <i class="fas fa-clock text-orange-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- Cancelled Enquiry -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border-t-4 border-red-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Cancelled Enquiry</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ $stats['cancelled'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-times-circle text-red-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- Total Registration -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border-t-4 border-blue-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Registration</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ $stats['registration'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-user-check text-blue-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- Admission Done -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border-t-4 border-green-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Admission Done</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ $stats['admitted'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-graduation-cap text-green-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Header Section -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 mb-6 border border-teal-100/50">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl font-bold text-gray-800 dark:text-white flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-teal-100 flex items-center justify-center text-teal-600">
                            <i class="fas fa-clipboard-list text-xs"></i>
                        </div>
                        Enquiry Management
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Manage and track student admission enquiries and follow-ups.</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <button @click="openAddModal()"
                        class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-teal-600 to-emerald-600 hover:from-teal-700 hover:to-emerald-700 text-white text-sm font-semibold rounded-xl transition-all shadow-md hover:shadow-lg active:scale-95">
                        <i class="fas fa-plus mr-2"></i>
                        Add Enquiry
                    </button>
                    <a href="{{ route('school.student-enquiries.index', ['today' => 1]) }}"
                        class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-amber-500 to-orange-600 hover:from-amber-600 hover:to-orange-700 text-white text-sm font-semibold rounded-xl transition-all shadow-md hover:shadow-lg active:scale-95">
                        <i class="fas fa-calendar-day mr-2 text-xs"></i>
                        Today Follow Up
                    </a>
                    <button
                        class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-slate-700 to-slate-900 hover:from-black hover:to-slate-800 text-white text-sm font-semibold rounded-xl transition-all shadow-md hover:shadow-lg active:scale-95">
                        <i class="fas fa-file-excel mr-2 text-xs"></i>
                        Excel Export
                    </button>
                </div>
            </div>
        </div>

        <!-- Enquiries Table -->
        @php
            $tableColumns = [
                [
                    'key' => 'enquiry_no',
                    'label' => 'Enquiry No',
                    'sortable' => true,
                ],
                [
                    'key' => 'student_name',
                    'label' => 'Student\'s Name',
                    'sortable' => true,
                ],
                [
                    'key' => 'father_name',
                    'label' => 'Father\'s Name',
                    'sortable' => true,
                ],
                [
                    'key' => 'class_id',
                    'label' => 'Class',
                    'sortable' => false,
                    'render' => function ($row) {
                        return $row->class?->name ?? 'N/A';
                    }
                ],
                [
                    'key' => 'contact_no',
                    'label' => 'Primary Contact',
                    'sortable' => false,
                ],
                [
                    'key' => 'enquiry_date',
                    'label' => 'Enquiry Date',
                    'sortable' => true,
                    'render' => function ($row) {
                        return $row->enquiry_date->format('d M, Y');
                    }
                ],
                [
                    'key' => 'follow_up_date',
                    'label' => 'Follow Up Date',
                    'sortable' => true,
                    'render' => function ($row) {
                        return $row->follow_up_date ? $row->follow_up_date->format('d M, Y') : 'N/A';
                    }
                ],
                [
                    'key' => 'form_status',
                    'label' => 'Form Status',
                    'sortable' => true,
                    'render' => function ($row) {
                        // Convert enum to string for array key lookup
                        if ($row->form_status instanceof \App\Enums\EnquiryStatus) {
                            // Get the enum name (e.g., "PENDING") and convert to lowercase
                            $statusKey = strtolower($row->form_status->name);
                            $label = $row->form_status->label();
                        } else {
                            $statusKey = strtolower($row->form_status ?? 'pending');
                            $label = ucfirst($statusKey);
                        }

                        $colors = [
                            'pending' => 'bg-orange-100 text-orange-800',
                            'completed' => 'bg-blue-100 text-blue-800',
                            'cancelled' => 'bg-red-100 text-red-800',
                            'admitted' => 'bg-green-100 text-green-800',
                        ];
                        $color = $colors[$statusKey] ?? 'bg-gray-100 text-gray-800';

                        return '<span class="px-2 py-1 text-xs font-semibold rounded-full ' . $color . '"> '
                            . $label . '</span>';
                    }
                ],
            ];

            $tableActions = [
                [
                    'type' => 'button',
                    'onclick' => function ($row) {
                        $data = json_encode($row);
                        return "window.dispatchEvent(new CustomEvent('open-edit-enquiry', { detail: $data }))";
                    },
                    'icon' => 'fas fa-edit',
                    'class' => 'text-blue-600 hover:text-blue-900',
                    'title' => 'Edit',
                ],
                [
                    'type' => 'button',
                    'onclick' => function ($row) {
                        return "window.dispatchEvent(new CustomEvent('open-delete-enquiry', { detail: { id: " . $row->id . ", name: '" . addslashes($row->student_name) . "' } }))";
                    },
                    'icon' => 'fas fa-trash',
                    'class' => 'text-red-600 hover:text-red-900',
                    'title' => 'Delete',
                ],
            ];
        @endphp

        <x-data-table :columns="$tableColumns" :data="$enquiries" :searchable="true" :actions="$tableActions"
            empty-message="No enquiries found" empty-icon="fas fa-clipboard-list">
            Enquiry List
        </x-data-table>

        <!-- Add/Edit Enquiry Modal -->
        <x-modal name="enquiry-modal" alpineTitle="editMode ? 'Edit Enquiry' : 'Add New Enquiry'" maxWidth="6xl">
            <form @submit.prevent="submitForm" 
                  @input="clearError($event.target.name)"
                  @change="clearError($event.target.name)"
                  id="enquiryForm" method="POST" enctype="multipart/form-data" novalidate
                class="p-6">
                @csrf
                <input type="hidden" name="enquiry_id" x-model="enquiryId">

                @include('school.student-enquiries.partials.form')
            </form>

            <x-slot name="footer">
                <button type="button" @click="closeModal()" class="btn-premium-cancel px-10">Cancel</button>
                <button type="submit" form="enquiryForm" :disabled="submitting" class="btn-premium-primary min-w-[180px] bg-teal-600 hover:bg-teal-700 shadow-teal-200">
                    <template x-if="submitting">
                        <span class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin mr-3 inline-block"></span>
                    </template>
                    <span x-text="editMode ? 'Update Enquiry' : 'Submit Enquiry'"></span>
                </button>
            </x-slot>
        </x-modal>

        @push('scripts')
            <script>
                document.addEventListener('alpine:init', () => {
                    Alpine.data('enquiryManagement', () => ({
                        editMode: false,
                        enquiryId: null,
                        submitting: false,
                        errors: {},
                        showDeleteModal: false,
                        deleteEnquiryId: null,
                        fatherExpanded: false,
                        motherExpanded: false,
                        contactExpanded: false,

                        init() {
                            window.addEventListener('open-edit-enquiry', (e) => this.openEditModal(e.detail));
                            window.addEventListener('open-delete-enquiry', (e) => this.confirmDelete(e.detail));

                            // Specialized Select2 error clearing
                            this.$nextTick(() => {
                                if (typeof $ !== 'undefined') {
                                    $(document).on('change.select2', '#enquiryForm .select2-hidden-accessible', (e) => {
                                        this.clearError(e.target.name);
                                    });
                                }
                            });
                        },

                        closeModal() {
                            this.$dispatch('close-modal', 'enquiry-modal');
                            this.editMode = false;
                            this.enquiryId = null;
                            this.clearErrors();
                            document.getElementById('enquiryForm').reset();
                            // Reset Select2s
                            if (typeof $ !== 'undefined') {
                                $('.select2-hidden-accessible').val('').trigger('change');
                            }
                        },

                        openAddModal() {
                            this.editMode = false;
                            this.enquiryId = null;
                            this.clearErrors();
                            this.fatherExpanded = false;
                            this.motherExpanded = false;
                            this.contactExpanded = false;
                            document.getElementById('enquiryForm').reset();
                            if (typeof $ !== 'undefined') {
                                $('.select2-hidden-accessible').val('').trigger('change');
                            }
                            this.$dispatch('open-modal', 'enquiry-modal');
                        },

                        openEditModal(enquiry) {
                            this.editMode = true;
                            this.enquiryId = enquiry.id;
                            this.clearErrors();
                            this.fatherExpanded = true;
                            this.motherExpanded = true;
                            this.contactExpanded = true;
                            this.$dispatch('open-modal', 'enquiry-modal');

                            // Populate form fields
                            this.$nextTick(() => {
                                const form = document.getElementById('enquiryForm');
                                Object.keys(enquiry).forEach(key => {
                                    let value = enquiry[key];
                                    if (value === null || typeof value === 'object') return;

                                    const input = form.querySelector(`[name="${key}"]`);
                                    if (input) {
                                        if (input.type === 'file') return;
                                        // Cast to string for select elements to ensure type-safe matching
                                        const safeValue = (input.tagName === 'SELECT') ? String(value) : value;
                                        if ($(input).hasClass('select2-hidden-accessible')) {
                                            $(input).val(safeValue).trigger('change');
                                        } else if (input.type === 'date' && value) {
                                            input.value = value.substring(0, 10);
                                        } else if (input.type === 'checkbox') {
                                            input.checked = !!value;
                                        } else {
                                            input.value = safeValue;
                                        }
                                    }
                                });

                                // Special handling for photos
                                const photoFields = ['father_photo', 'mother_photo', 'student_photo'];
                                photoFields.forEach(field => {
                                    if (enquiry[field]) {
                                        const preview = document.getElementById(`${field.replace('_', '-')}-preview`);
                                        const icon = document.getElementById(`${field.replace('_', '-')}-icon`);
                                        const removeBtn = document.getElementById(`${field.replace('_', '-')}-remove`);
                                        if (preview) { preview.src = `/storage/${enquiry[field]}`; preview.classList.remove('hidden'); }
                                        if (icon) icon.classList.add('hidden');
                                        if (removeBtn) removeBtn.classList.remove('hidden');
                                    }
                                });
                            });
                        },

                        async submitForm() {
                            this.submitting = true;
                            this.clearErrors();

                            const form = document.getElementById('enquiryForm');
                            const formData = new FormData(form);

                            // Add _method for PUT requests
                            if (this.editMode) {
                                formData.append('_method', 'PUT');
                            }

                            const url = this.editMode
                                ? `/school/student-enquiries/${this.enquiryId}`
                                : '{{ route('school.student-enquiries.store') }}';

                            try {
                                const response = await fetch(url, {
                                    method: 'POST',
                                    body: formData,
                                    headers: {
                                        'X-Requested-With': 'XMLHttpRequest',
                                        'Accept': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                                    }
                                });

                                const result = await response.json();

                                if (response.status === 422) {
                                    this.displayErrors(result.errors);
                                } else if (response.ok) {
                                    // Success!
                                    if (window.Toast) {
                                        window.Toast.fire({
                                            icon: 'success',
                                            title: result.message || 'Enquiry saved successfully'
                                        });
                                    }
                                    setTimeout(() => window.location.reload(), 1000);
                                } else {
                                    throw new Error(result.message || 'Something went wrong');
                                }
                            } catch (error) {
                                console.error('Submission error:', error);
                                if (window.Toast) {
                                    window.Toast.fire({
                                        icon: 'error',
                                        title: error.message || 'Could not save enquiry'
                                    });
                                }
                            } finally {
                                this.submitting = false;
                            }
                        },

                        async confirmDelete(enquiry) {
                            window.dispatchEvent(new CustomEvent('open-confirm-modal', {
                                detail: {
                                    title: 'Delete Enquiry',
                                    message: `Are you sure you want to delete the enquiry for "${enquiry.name}"? This action cannot be undone.`,
                                    callback: async () => {
                                        this.submitting = true;
                                        try {
                                            const response = await fetch(`/school/student-enquiries/${enquiry.id}`, {
                                                method: 'POST',
                                                headers: {
                                                    'Content-Type': 'application/json',
                                                    'Accept': 'application/json',
                                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
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

                        displayErrors(errors) {
                            this.errors = errors;
                            
                            // Check if any error fields are in collapsed sections and expand them
                            const errorFields = Object.keys(errors);
                            
                            if (errorFields.some(field => field.startsWith('father_'))) {
                                this.fatherExpanded = true;
                            }
                            if (errorFields.some(field => field.startsWith('mother_'))) {
                                this.motherExpanded = true;
                            }
                            if (errorFields.some(field => field.startsWith('whatsapp_') || field.startsWith('contact_') || field === 'email_id')) {
                                this.contactExpanded = true;
                            }

                            this.$nextTick(() => {
                                // Focus the first error field
                                const firstErrorField = document.querySelector('.border-red-500');
                                if (firstErrorField) {
                                    firstErrorField.focus();
                                    firstErrorField.scrollIntoView({ behavior: 'smooth', block: 'center' });
                                }
                            });
                        },

                        clearError(field) {
                            if (this.errors && this.errors[field]) {
                                delete this.errors[field];
                                this.errors = Object.assign({}, this.errors);
                                
                                // Remove manual Select2 error highlights
                                if (typeof $ !== 'undefined') {
                                    const input = document.querySelector(`[name="${field}"]`);
                                    if (input && $(input).hasClass('select2-hidden-accessible')) {
                                        $(input).next('.select2-container').find('.select2-selection').removeClass('!border-red-500');
                                    }
                                }
                            }
                        },

                        clearErrors() {
                            this.errors = {};
                            document.querySelectorAll('.border-red-500').forEach(el => {
                                el.classList.remove('border-red-500');
                                el.classList.add('border-gray-300');
                            });
                            // Clear Select2 error borders
                            if (typeof $ !== 'undefined') {
                                $('.select2-selection').removeClass('!border-red-500');
                            }
                            document.querySelectorAll('.error-message').forEach(el => el.remove());
                        }
                    }));
                });

                // Make functions globally accessible for datatable buttons
                window.openEditModal = function (enquiry) {
                    // Get the Alpine component instance
                    const component = Alpine.$data(document.querySelector('[x-data="enquiryManagement()"]'));
                    if (component) {
                        component.openEditModal(enquiry);
                    }
                };
            </script>
        @endpush
@endsection