@extends('layouts.receptionist')

@section('title', 'Student Enquiries')

@section('content')
<div class="space-y-6" x-data="enquiryManagement()" x-init="init()">
    <!-- Success Message -->
    @if(session('success'))
    <div x-data="{ show: true }" x-show="show" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg relative">
        <span class="block sm:inline">{{ session('success') }}</span>
        <button @click="show = false" class="absolute top-0 right-0 px-4 py-3">
            <i class="fas fa-times"></i>
        </button>
    </div>
    @endif

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
                    $status = $row->form_status instanceof \App\Enums\EnquiryStatus 
                        ? $row->form_status->name 
                        : ($row->form_status ?? 'pending');
                    
                    $colors = [
                        'Pending' => 'bg-orange-100 text-orange-800',
                        'Completed' => 'bg-blue-100 text-blue-800',
                        'Cancelled' => 'bg-red-100 text-red-800',
                        'Admitted' => 'bg-green-100 text-green-800',
                    ];
                    $color = $colors[$status] ?? 'bg-gray-100 text-gray-800';
                    
                    // Get label from enum if available
                    $label = $row->form_status instanceof \App\Enums\EnquiryStatus 
                        ? $row->form_status->label() 
                        : ucfirst(strtolower($status));
                    
                    return '<span class="px-2 py-1 text-xs font-semibold rounded-full ' . $color . '">' 
                         . $label . '</span>';
                }
            ],
        ];

        $tableActions = [
            [
                'type' => 'link',
                'href' => function($row) {
                    return route('receptionist.student-enquiries.show', $row->id);
                },
                'icon' => 'fas fa-eye',
                'class' => 'text-green-600 hover:text-green-900',
                'title' => 'View',
            ],
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
        :data="$enquiries"
        :searchable="true"
        :actions="$tableActions"
        empty-message="No enquiries found"
        empty-icon="fas fa-clipboard-list"
    >
        Enquiry List
    </x-data-table>

    <!-- Add/Edit Enquiry Modal -->
    <div x-show="showModal" x-cloak 
         class="fixed inset-0 z-50 overflow-y-auto" 
         aria-labelledby="modal-title" role="dialog" aria-modal="true">
        
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-gray-900 bg-opacity-50 transition-opacity"></div>

        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-xl bg-white dark:bg-gray-800 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-6xl"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100">
            
            <!-- Modal Header -->
            <div class="bg-teal-500 px-6 py-4 flex items-center justify-between rounded-t-xl">
                <h3 class="text-xl font-bold text-white" x-text="editMode ? 'Edit Enquiry' : 'Add New Enquiry'"></h3>
                <button @click="closeModal()" class="text-white hover:text-teal-100 transition-colors">
                    <i class="fas fa-times text-2xl"></i>
                </button>
            </div>

            <!-- Modal Body -->
            <form :action="editMode ? `/receptionist/student-enquiries/${enquiryId}` : '{{ route('receptionist.student-enquiries.store') }}'" 
                  method="POST" 
                  enctype="multipart/form-data"
                  class="p-6">
                @csrf
                <input type="hidden" name="_method" x-bind:value="editMode ? 'PUT' : 'POST'">
                <input type="hidden" name="enquiry_id" :value="enquiryId" x-show="editMode">

                @include('school.student-enquiries.partials.form')

                <!-- Modal Footer -->
                <div class="flex items-center justify-end gap-3 mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                    <button type="button" @click="closeModal()"
                            class="px-6 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors font-semibold">
                        Cancel
                    </button>
                    <button type="submit"
                            class="px-6 py-2 bg-teal-500 text-white rounded-lg hover:bg-teal-600 transition-colors font-semibold shadow-md">
                        <span x-text="editMode ? 'Update Enquiry' : 'Submit Enquiry'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
</div>

<!-- Delete Confirmation Modal (Outside main component for proper layering) -->
<div x-data="{ showDeleteModal: false, deleteEnquiryId: null }" 
     @delete-enquiry.window="showDeleteModal = true; deleteEnquiryId = $event.detail.id; document.body.style.overflow = 'hidden'">
    <div x-show="showDeleteModal" x-cloak 
         @close="document.body.style.overflow = ''"
         class="fixed inset-0 bg-gray-900 bg-opacity-50 overflow-y-auto h-full w-full z-[60] flex items-center justify-center"
         @click.self="showDeleteModal = false; document.body.style.overflow = ''">
        <div class="relative mx-auto w-full max-w-md shadow-2xl rounded-xl bg-white dark:bg-gray-800 overflow-hidden"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform scale-95"
             x-transition:enter-end="opacity-100 transform scale-100">
            
            <div class="bg-red-500 px-6 py-4 flex items-center justify-between">
                <h3 class="text-xl font-bold text-white">Confirm Delete</h3>
                <button @click="showDeleteModal = false; document.body.style.overflow = ''" class="text-white hover:text-red-100 transition-colors">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>

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
                    Do you really want to delete this enquiry? This will permanently remove the enquiry data from the system.
                </p>
            </div>

            <div class="bg-gray-50 dark:bg-gray-700 px-6 py-4 flex items-center justify-end gap-3">
                <button @click="showDeleteModal = false; document.body.style.overflow = ''"
                        class="px-6 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors font-semibold">
                    Cancel
                </button>
                <form :action="`/receptionist/student-enquiries/${deleteEnquiryId}`" method="POST" class="inline">
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
    Alpine.data('enquiryManagement', () => ({
        showModal: false,
        editMode: false,
        enquiryId: null,
        
        init() {
            // Hide error banner when modal opens
            this.$watch('showModal', (value) => {
                if (value) {
                    const errorBanner = document.getElementById('error-banner-enquiry');
                    if (errorBanner) {
                        errorBanner.style.display = 'none';
                        if (errorBanner.__x) {
                            errorBanner.__x.$data.show = false;
                        }
                    }
                }
            });
            
            // Check if there are validation errors and reopen modal
            @if($errors->any())
                this.editMode = {{ old('_method') === 'PUT' || request()->routeIs('receptionist.student-enquiries.update') ? 'true' : 'false' }};
                this.enquiryId = '{{ old('enquiry_id', request()->route('studentEnquiry')?->id ?? request()->route('enquiry')?->id ?? '') }}';
                this.$nextTick(() => {
                    this.showModal = true;
                    document.body.style.overflow = 'hidden';
                    // Set select values after modal opens and ensure errors are visible
                    setTimeout(() => {
                        @php
                            $selectFields = ['academic_year_id', 'class_id', 'country_id', 'gender'];
                        @endphp
                        const selects = @json($selectFields);
                        const oldValues = {
                            @foreach($selectFields as $field)
                            '{{ $field }}': '{{ old($field, '') }}',
                            @endforeach
                        };
                        selects.forEach(selectName => {
                            const select = document.querySelector(`[name="${selectName}"]`);
                            if (select) {
                                // Set value if exists
                                if (oldValues[selectName]) {
                                    if ($(select).hasClass('select2-hidden-accessible')) {
                                        $(select).val(oldValues[selectName]).trigger('change');
                                    } else {
                                        select.value = oldValues[selectName];
                                    }
                                }
                                
                                // Ensure error styling is applied to Select2 if there's an error
                                if (select.classList.contains('border-red-500')) {
                                    const select2Container = $(select).next('.select2-container');
                                    if (select2Container.length) {
                                        select2Container.find('.select2-selection').addClass('border-red-500');
                                    }
                                }
                                
                                // Ensure error message is visible
                                let errorElement = select.nextElementSibling;
                                if (!errorElement || !errorElement.classList.contains('text-red-500')) {
                                    const parent = select.closest('div');
                                    if (parent) {
                                        errorElement = parent.querySelector('.text-red-500');
                                    }
                                }
                                if (errorElement && errorElement.classList.contains('text-red-500')) {
                                    errorElement.classList.remove('hidden');
                                }
                            }
                        });
                        
                        // Restore image previews from sessionStorage if they exist
                        const photoFields = [
                            { field: 'father_photo', previewId: 'father-photo-preview', iconId: 'father-photo-icon', removeBtnId: 'father-photo-remove' },
                            { field: 'mother_photo', previewId: 'mother-photo-preview', iconId: 'mother-photo-icon', removeBtnId: 'mother-photo-remove' },
                            { field: 'student_photo', previewId: 'student-photo-preview', iconId: 'student-photo-icon', removeBtnId: 'student-photo-remove' }
                        ];
                        
                        photoFields.forEach(photo => {
                            const storedImage = sessionStorage.getItem(`enquiry_${photo.field}`);
                            if (storedImage) {
                                const preview = document.getElementById(photo.previewId);
                                const icon = document.getElementById(photo.iconId);
                                const removeBtn = document.getElementById(photo.removeBtnId);
                                
                                if (preview) {
                                    preview.src = storedImage;
                                    preview.classList.remove('hidden');
                                }
                                if (icon) {
                                    icon.classList.add('hidden');
                                }
                                if (removeBtn) {
                                    removeBtn.classList.remove('hidden');
                                }
                            }
                        });
                    }, 200);
                });
            @endif
        },
        
        resetForm() {
            // Reset form
            const form = document.querySelector('form[action*="student-enquiries"]');
            if (form) {
                form.reset();
            }
            
            // Reset Select2 dropdowns
            setTimeout(() => {
                $('select[name="academic_year_id"]').val(null).trigger('change');
                $('select[name="class_id"]').val(null).trigger('change');
                $('select[name="country_id"]').val(null).trigger('change');
                $('select[name="gender"]').val(null).trigger('change');
            }, 50);
            
            // Reset image previews
            const photoFields = [
                { previewId: 'father-photo-preview', iconId: 'father-photo-icon', removeBtnId: 'father-photo-remove' },
                { previewId: 'mother-photo-preview', iconId: 'mother-photo-icon', removeBtnId: 'mother-photo-remove' },
                { previewId: 'student-photo-preview', iconId: 'student-photo-icon', removeBtnId: 'student-photo-remove' }
            ];
            
            photoFields.forEach(photo => {
                const preview = document.getElementById(photo.previewId);
                const icon = document.getElementById(photo.iconId);
                const removeBtn = document.getElementById(photo.removeBtnId);
                
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
            });
            
            // Clear sessionStorage for images
            sessionStorage.removeItem('enquiry_father_photo');
            sessionStorage.removeItem('enquiry_mother_photo');
            sessionStorage.removeItem('enquiry_student_photo');
            
            // Reset file inputs
            const fileInputs = document.querySelectorAll('input[type="file"]');
            fileInputs.forEach(input => {
                input.value = '';
            });
        },
        
        openAddModal() {
            // Hide error banner when opening modal
            const errorBanner = document.getElementById('error-banner-enquiry');
            if (errorBanner) {
                errorBanner.style.display = 'none';
                if (errorBanner.__x) {
                    errorBanner.__x.$data.show = false;
                }
            }
            
            // Reset form to ensure clean state
            this.resetForm();
            
            this.editMode = false;
            this.enquiryId = null;
            this.showModal = true;
            // Lock body scroll
            document.body.style.overflow = 'hidden';
        },
        
        openEditModal(enquiry) {
            // Hide error banner when opening modal
            const errorBanner = document.getElementById('error-banner-enquiry');
            if (errorBanner) {
                errorBanner.style.display = 'none';
                if (errorBanner.__x) {
                    errorBanner.__x.$data.show = false;
                }
            }
            
            this.editMode = true;
            this.enquiryId = enquiry.id;
            this.showModal = true;
            // Lock body scroll
            document.body.style.overflow = 'hidden';
            
            // Populate form fields
            this.$nextTick(() => {
                Object.keys(enquiry).forEach(key => {
                    let value = enquiry[key];
                    
                    // Skip null values and nested objects
                    if (value === null || typeof value === 'object') {
                        return;
                    }
                    
                    const input = document.querySelector(`[name="${key}"]`);
                    if (input) {
                        // Skip file inputs to prevent InvalidStateError
                        if (input.type === 'file') {
                            return;
                        }

                        // Check if it's a Select2 dropdown
                        if ($(input).hasClass('select2-hidden-accessible')) {
                            // Use Select2 API to set value
                            $(input).val(value).trigger('change');
                        } else if (input.tagName === 'SELECT') {
                            // Regular select
                            input.value = value;
                        } else if (input.type === 'date' && value) {
                            // Format date for input (YYYY-MM-DD)
                            // Handles both "2025-12-25 15:00:00" and "2025-12-25T00:00:00.000000Z"
                            input.value = value.substring(0, 10);
                        } else {
                            // Regular input/textarea
                            input.value = value;
                        }
                    }
                });
                
                // Handle nested objects manually
                if (enquiry.class_id) {
                    const classInput = document.querySelector('[name="class_id"]');
                    if (classInput) {
                        if ($(classInput).hasClass('select2-hidden-accessible')) {
                            $(classInput).val(enquiry.class_id).trigger('change');
                        } else {
                            classInput.value = enquiry.class_id;
                        }
                    }
                }
                
                if (enquiry.academic_year_id) {
                    const yearInput = document.querySelector('[name="academic_year_id"]');
                    if (yearInput) {
                        if ($(yearInput).hasClass('select2-hidden-accessible')) {
                            $(yearInput).val(enquiry.academic_year_id).trigger('change');
                        } else {
                            yearInput.value = enquiry.academic_year_id;
                        }
                    }
                }
                
                // Handle country_id dropdown
                if (enquiry.country_id) {
                    const countryInput = document.querySelector('[name="country_id"]');
                    if (countryInput) {
                        if ($(countryInput).hasClass('select2-hidden-accessible')) {
                            $(countryInput).val(enquiry.country_id).trigger('change');
                        } else {
                            countryInput.value = enquiry.country_id;
                        }
                    }
                }
                
                // Handle photo previews for existing images
                const photoFields = [
                    { field: 'father_photo', previewId: 'father-photo-preview', iconId: 'father-photo-icon', removeBtnId: 'father-photo-remove' },
                    { field: 'mother_photo', previewId: 'mother-photo-preview', iconId: 'mother-photo-icon', removeBtnId: 'mother-photo-remove' },
                    { field: 'student_photo', previewId: 'student-photo-preview', iconId: 'student-photo-icon', removeBtnId: 'student-photo-remove' }
                ];
                
                photoFields.forEach(photo => {
                    if (enquiry[photo.field]) {
                        const preview = document.getElementById(photo.previewId);
                        const icon = document.getElementById(photo.iconId);
                        const removeBtn = document.getElementById(photo.removeBtnId);
                        
                        if (preview) {
                            // Set the image source to the stored photo path
                            preview.src = `/storage/${enquiry[photo.field]}`;
                            preview.classList.remove('hidden');
                        }
                        if (icon) {
                            icon.classList.add('hidden');
                        }
                        if (removeBtn) {
                            removeBtn.classList.remove('hidden');
                        }
                    }
                });
            });
        },
        
        closeModal() {
            this.resetForm();
            this.showModal = false;
            // Unlock body scroll
            document.body.style.overflow = '';
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

window.confirmDelete = function(enquiryId) {
    // Dispatch custom event for delete modal
    window.dispatchEvent(new CustomEvent('delete-enquiry', {
        detail: { id: enquiryId }
    }));
};
</script>

<script>
// Global script to hide validation errors when user starts typing or selecting
document.addEventListener('DOMContentLoaded', function() {
    // Function to hide error banner
    const hideErrorBanner = function() {
        const errorBanner = document.getElementById('error-banner-enquiry');
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
    const modal = document.querySelector('[x-data*="enquiryManagement"]');
    if (modal) {
        // Handle regular inputs
        modal.addEventListener('input', function(e) {
            if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') {
                hideErrorBanner();
                // Find error element (could be next sibling or in parent)
                let errorElement = e.target.nextElementSibling;
                if (!errorElement || !errorElement.classList.contains('text-red-500')) {
                    const parent = e.target.closest('div');
                    if (parent) {
                        errorElement = parent.querySelector('.text-red-500');
                    }
                }
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
                // Find error element (could be next sibling or in parent)
                let errorElement = e.target.nextElementSibling;
                if (!errorElement || !errorElement.classList.contains('text-red-500')) {
                    const parent = e.target.closest('div');
                    if (parent) {
                        errorElement = parent.querySelector('.text-red-500');
                    }
                }
                if (errorElement && errorElement.classList.contains('text-red-500')) {
                    errorElement.classList.add('hidden');
                }
                // Also remove red border from select and Select2 container
                e.target.classList.remove('border-red-500');
                const select2Container = $(e.target).next('.select2-container');
                if (select2Container.length) {
                    select2Container.find('.select2-selection').removeClass('border-red-500');
                }
            }
        });
        
        // Handle Select2 changes (for academic_year_id, class_id, country_id)
        $(document).on('change', 'select.select2-hidden-accessible', function() {
            hideErrorBanner();
            const select = this;
            // Find error element
            let errorElement = select.nextElementSibling;
            if (!errorElement || !errorElement.classList.contains('text-red-500')) {
                const parent = select.closest('div');
                if (parent) {
                    errorElement = parent.querySelector('.text-red-500');
                }
            }
            if (errorElement && errorElement.classList.contains('text-red-500')) {
                errorElement.classList.add('hidden');
            }
            // Remove red border from select and Select2 container
            $(select).removeClass('border-red-500');
            const select2Container = $(select).next('.select2-container');
            if (select2Container.length) {
                select2Container.find('.select2-selection').removeClass('border-red-500');
            }
        });
    }
});
</script>
@endpush
@endsection
