@extends('layouts.receptionist')

@section('title', 'Student Enquiries')

@section('content')
<div class="space-y-6" x-data="enquiryManagement()">
    <!-- Success Message -->
    @if(session('success'))
    <div x-data="{ show: true }" x-show="show" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg relative">
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

    <!-- Action Buttons -->
    <div class="flex items-center justify-between bg-white dark:bg-gray-800 rounded-lg shadow p-4">
        <div class="flex items-center space-x-3">
            <button @click="openAddModal()" 
                    class="bg-teal-500 hover:bg-teal-600 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors">
                <i class="fas fa-plus"></i>
                <span>Add Enquiry</span>
            </button>
            <button @click="window.location.href='{{ route('receptionist.student-enquiries.index', ['today' => 1]) }}'" 
                    class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors">
                <i class="fas fa-calendar-day"></i>
                <span>Today Follow Up Data</span>
            </button>
        </div>
        <button class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors">
            <i class="fas fa-file-excel"></i>
            <span>Export To Excel</span>
        </button>
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
                    $colors = [
                        'pending' => 'bg-orange-100 text-orange-800',
                        'completed' => 'bg-blue-100 text-blue-800',
                        'cancelled' => 'bg-red-100 text-red-800',
                        'admitted' => 'bg-green-100 text-green-800',
                    ];
                    $color = $colors[$row->form_status] ?? 'bg-gray-100 text-gray-800';
                    return '<span class="px-2 py-1 text-xs font-semibold rounded-full ' . $color . '">' 
                         . ucfirst($row->form_status) . '</span>';
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
         class="fixed inset-0 bg-gray-900 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-10 mx-auto w-full max-w-6xl shadow-2xl rounded-xl bg-white dark:bg-gray-800 mb-10"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform scale-95"
             x-transition:enter-end="opacity-100 transform scale-100">
            
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
                  class="p-6 max-h-[calc(100vh-200px)] overflow-y-auto">
                @csrf
                <input type="hidden" name="_method" x-bind:value="editMode ? 'PUT' : 'POST'">

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

    <!-- Delete Confirmation Modal -->
    <div x-show="showDeleteModal" x-cloak 
         @close="document.body.style.overflow = ''"
         class="fixed inset-0 bg-gray-900 bg-opacity-50 overflow-y-auto h-full w-full z-50 flex items-center justify-center"
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
        showDeleteModal: false,
        editMode: false,
        enquiryId: null,
        deleteEnquiryId: null,
        
        openAddModal() {
            this.editMode = false;
            this.enquiryId = null;
            this.showModal = true;
            // Lock body scroll
            document.body.style.overflow = 'hidden';
        },
        
        openEditModal(enquiry) {
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
            });
        },
        
        closeModal() {
            this.showModal = false;
            // Unlock body scroll
            document.body.style.overflow = '';
        },

        confirmDelete(enquiryId) {
            this.deleteEnquiryId = enquiryId;
            this.showDeleteModal = true;
            // Lock body scroll
            document.body.style.overflow = 'hidden';
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
    // Get the Alpine component instance
    const component = Alpine.$data(document.querySelector('[x-data="enquiryManagement()"]'));
    if (component) {
        component.confirmDelete(enquiryId);
    }
};
</script>
@endpush
@endsection
