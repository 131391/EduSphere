@extends('layouts.receptionist')

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

    {{-- Page Header with Actions --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 mb-6">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <h2 class="text-xl font-bold text-gray-800 dark:text-white">Enquiry List</h2>
            <div class="flex flex-wrap gap-2">
                <button @click="openAddModal()" 
                        class="inline-flex items-center px-4 py-2 bg-teal-500 hover:bg-teal-600 text-white text-sm font-medium rounded-md transition-colors">
                    <i class="fas fa-plus mr-2"></i>
                    Add Enquiry
                </button>
                <a href="{{ route('receptionist.student-enquiries.index', ['today' => 1]) }}" 
                   class="inline-flex items-center px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-white text-sm font-medium rounded-md transition-colors">
                    <i class="fas fa-calendar-day mr-2"></i>
                    Today Follow Up Data
                </a>
                <button class="inline-flex items-center px-4 py-2 bg-green-500 hover:bg-green-600 text-white text-sm font-medium rounded-md transition-colors">
                    <i class="fas fa-file-excel mr-2"></i>
                    Export To Excel
                </button>
            </div>
        </div>
    </div>

    <!-- Enquiries Table -->
    @php
        use App\Enums\EnquiryStatus;
        
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
                'render' => function($row) {
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
                'render' => function($row) {
                    return $row->enquiry_date->format('d M, Y');
                }
            ],
            [
                'key' => 'follow_up_date',
                'label' => 'Follow Up Date',
                'sortable' => true,
                'render' => function($row) {
                    return $row->follow_up_date ? $row->follow_up_date->format('d M, Y') : 'N/A';
                }
            ],
            [
                'key' => 'form_status',
                'label' => 'Form Status',
                'sortable' => true,
                'render' => function($row) {
                    // Convert enum to string for array key lookup
                    if ($row->form_status instanceof EnquiryStatus) {
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
                'onclick' => function($row) {
                    // Use data attribute to avoid JSON escaping issues
                    $encodedData = base64_encode(json_encode($row));
                    return "openEditModal(JSON.parse(atob(this.getAttribute('data-enquiry'))))";
                },
                'data-enquiry' => function($row) {
                    return base64_encode(json_encode($row));
                },
                'icon' => 'fas fa-edit',
                'class' => 'text-blue-600 hover:text-blue-900',
                'title' => 'Edit',
            ],
            [
                'type' => 'form',
                'action' => function($row) {
                    return route('receptionist.student-enquiries.destroy', $row->id);
                },
                'method' => 'DELETE',
                'icon' => 'fas fa-trash',
                'class' => 'text-red-600 hover:text-red-900',
                'title' => 'Delete',
                'confirm' => 'Are you sure you want to delete this enquiry?',
            ],
        ];
    @endphp

    <x-data-table 
        :columns="$tableColumns"
        :data="$enquiries"
        :searchable="true"
        :actions="$tableActions"
        empty-message="No enquiries found"
        empty-icon="fas fa-clipboard-list"
    >
        Enquiry List
    </x-data-table>

    <!-- Add/Edit Enquiry Modal -->
    <x-modal name="enquiry-modal" alpineTitle="editMode ? 'Edit Enquiry' : 'Add New Enquiry'" maxWidth="6xl">
        <form @submit.prevent="submitForm" 
              id="enquiryForm"
              method="POST" 
              enctype="multipart/form-data"
              novalidate
              class="p-6">
            @csrf
            <input type="hidden" name="enquiry_id" x-model="enquiryId">
 

            @include('receptionist.student-enquiries.partials.form')

            <!-- Modal Footer -->
            <div class="flex items-center justify-end gap-3 mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                <button type="button" @click="closeModal()"
                        class="px-6 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors font-semibold">
                    Cancel
                </button>
                <button type="submit"
                        :disabled="submitting"
                        class="px-6 py-2 bg-teal-500 text-white rounded-lg hover:bg-teal-600 transition-colors font-semibold shadow-md disabled:opacity-50 disabled:cursor-not-allowed">
                    <span x-show="!submitting" x-text="editMode ? 'Update Enquiry' : 'Submit Enquiry'"></span>
                    <span x-show="submitting" class="flex items-center">
                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Processing...
                    </span>
                </button>
            </div>
        </form>
    </x-modal>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('enquiryManagement', () => ({
        editMode: false,
        enquiryId: null,
        submitting: false,
        errors: {},
        
        clearError(field) {
            if (this.errors[field]) {
                delete this.errors[field];
            }
        },

        init() {
            // Robust error clearing for selects (including Select2)
            this.$nextTick(() => {
                $(this.$el).find('select').on('change', (e) => {
                    const fieldName = e.target.getAttribute('name');
                    if (fieldName) {
                        this.clearError(fieldName);
                    }
                });
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
                        if ($(input).hasClass('select2-hidden-accessible')) {
                            $(input).val(value).trigger('change');
                        } else if (input.type === 'date' && value) {
                            input.value = value.substring(0, 10);
                        } else if (input.type === 'checkbox') {
                            input.checked = !!value;
                        } else {
                            input.value = value;
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
            this.errors = {};

            const form = document.getElementById('enquiryForm');
            const formData = new FormData(form);
            
            // Add _method for PUT requests
            if (this.editMode) {
                formData.append('_method', 'PUT');
            }

            const url = this.editMode 
                ? `/school/student-enquiries/${this.enquiryId}`
                : '{{ route('receptionist.student-enquiries.store') }}';

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
                    this.errors = result.errors;
                    if (window.Toast) {
                        window.Toast.fire({
                            icon: 'error',
                            title: 'Please check the form for errors'
                        });
                    }
                    // Scroll to the first error or the summary
                    this.$nextTick(() => {
                        const firstError = document.querySelector('.border-red-500, .bg-red-50');
                        if (firstError) firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    });
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

        clearErrors() {
            this.errors = {};
        }
    }));
});

// Make functions globally accessible for datatable buttons
window.openEditModal = function(enquiry) {
    // Get the Alpine component instance
    const component = Alpine.$data(document.querySelector('[x-data="enquiryManagement()"]'));
    if (component) {
        component.openEditModal(enquiry);
    }
};
</script>
@endpush
@endsection
