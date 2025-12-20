@extends('layouts.admin')

@php
use Illuminate\Support\Facades\Storage;
@endphp

@section('title', 'Logo Update')


@section('content')
<div class="space-y-6">
    <!-- Success Message -->
    @if(session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg relative" role="alert">
        <span class="block sm:inline">{{ session('success') }}</span>
    </div>
    @endif

    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-200">Logo Update</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Manage school logos and site icons</p>
        </div>
        <div class="flex items-center space-x-3">
            <button 
                onclick="document.getElementById('logo-upload-form').classList.toggle('hidden')"
                class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 flex items-center"
            >
                <i class="fas fa-image mr-2"></i>
                Logo
            </button>
            <button 
                onclick="document.getElementById('site-icon-upload-form').classList.toggle('hidden')"
                class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 flex items-center"
            >
                <i class="fas fa-favicon mr-2"></i>
                Site Icon
            </button>
        </div>
    </div>

    <!-- Upload Forms -->
    <div id="logo-upload-form" class="hidden bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">Upload Logo</h3>
        <form action="{{ route('admin.settings.logo.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="space-y-4">
                <div>
                    <label for="logo_school_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Select School</label>
                    <select name="school_id" id="logo_school_id" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Select a school</option>
                        @foreach(\App\Models\School::all() as $school)
                        <option value="{{ $school->id }}">{{ $school->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="logo" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Logo Image</label>
                    <input type="file" name="logo" id="logo" accept="image/*" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Max size: 2MB. Formats: JPEG, PNG, JPG, GIF, SVG</p>
                </div>
                <div class="flex items-center space-x-3">
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                        <i class="fas fa-upload mr-2"></i>Upload Logo
                    </button>
                    <button type="button" onclick="document.getElementById('logo-upload-form').classList.add('hidden')" class="bg-gray-300 dark:bg-gray-600 text-gray-700 dark:text-gray-200 px-4 py-2 rounded-lg hover:bg-gray-400 dark:hover:bg-gray-500">
                        Cancel
                    </button>
                </div>
            </div>
        </form>
    </div>

    <div id="site-icon-upload-form" class="hidden bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">Upload Site Icon</h3>
        <form action="{{ route('admin.settings.logo.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="space-y-4">
                <div>
                    <label for="icon_school_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Select School</label>
                    <select name="school_id" id="icon_school_id" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Select a school</option>
                        @foreach(\App\Models\School::all() as $school)
                        <option value="{{ $school->id }}">{{ $school->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="site_icon" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Site Icon</label>
                    <input type="file" name="site_icon" id="site_icon" accept="image/*,.ico" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Max size: 512KB. Formats: JPEG, PNG, JPG, GIF, SVG, ICO</p>
                </div>
                <div class="flex items-center space-x-3">
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                        <i class="fas fa-upload mr-2"></i>Upload Site Icon
                    </button>
                    <button type="button" onclick="document.getElementById('site-icon-upload-form').classList.add('hidden')" class="bg-gray-300 dark:bg-gray-600 text-gray-700 dark:text-gray-200 px-4 py-2 rounded-lg hover:bg-gray-400 dark:hover:bg-gray-500">
                        Cancel
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Logos Table -->
    @php
        // Define table columns
        $tableColumns = [
            [
                'key' => 'id',
                'label' => 'SR NO',
                'sortable' => false,
                'render' => function($school) use ($schools) {
                    $firstItem = $schools->firstItem() ?? 1;
                    $index = 0;
                    foreach ($schools as $idx => $s) {
                        if ($s->id === $school->id) {
                            $index = $idx;
                            break;
                        }
                    }
                    return $firstItem + $index;
                }
            ],
            [
                'key' => 'name',
                'label' => 'School Name',
                'sortable' => true,
            ],
            [
                'key' => 'logo',
                'label' => 'Logo',
                'sortable' => false,
                'render' => function($school) {
                    if ($school->logo) {
                        $logoUrl = asset('storage/' . $school->logo);
                        return '<div class="flex items-center">
                                    <img src="' . $logoUrl . '" 
                                         alt="' . e($school->name) . ' Logo" 
                                         class="w-16 h-16 object-contain rounded border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 p-1"
                                         onerror="this.onerror=null; this.src=\'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjQiIGhlaWdodD0iNjQiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PHJlY3Qgd2lkdGg9IjY0IiBoZWlnaHQ9IjY0IiBmaWxsPSIjZTBlNGU3Ii8+PHRleHQgeD0iNTAlIiB5PSI1MCUiIGZvbnQtc2l6ZT0iMTIiIGZpbGw9IiM5Y2EzYWYiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGR5PSIuM2VtIj5ObyBJbWFnZTwvdGV4dD48L3N2Zz4=\';">
                                </div>';
                    }
                    return '<span class="text-sm text-gray-400 dark:text-gray-500">No logo</span>';
                }
            ],
            [
                'key' => 'site_icon',
                'label' => 'Site Icon',
                'sortable' => false,
                'render' => function($school) {
                    if ($school->site_icon) {
                        $iconUrl = asset('storage/' . $school->site_icon);
                        return '<div class="flex items-center">
                                    <img src="' . $iconUrl . '" 
                                         alt="' . e($school->name) . ' Site Icon" 
                                         class="w-16 h-16 object-contain rounded border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 p-1"
                                         onerror="this.onerror=null; this.src=\'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjQiIGhlaWdodD0iNjQiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PHJlY3Qgd2lkdGg9IjY0IiBoZWlnaHQ9IjY0IiBmaWxsPSIjZTBlNGU3Ii8+PHRleHQgeD0iNTAlIiB5PSI1MCUiIGZvbnQtc2l6ZT0iMTIiIGZpbGw9IiM5Y2EzYWYiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGR5PSIuM2VtIj5ObyBJbWFnZTwvdGV4dD48L3N2Zz4=\';">
                                </div>';
                    }
                    return '<span class="text-sm text-gray-400 dark:text-gray-500">No site icon</span>';
                }
            ],
        ];

        // Define filters
        $tableFilters = [
            [
                'name' => 'status',
                'label' => 'Status',
                'options' => [
                    'active' => 'Active',
                    'inactive' => 'Inactive',
                    'suspended' => 'Suspended',
                ]
            ],
        ];

        // Define actions
        $tableActions = [
            [
                'type' => 'button',
                'onclick' => function($school) {
                    $logoUrl = $school->logo ? asset('storage/' . $school->logo) : '';
                    $iconUrl = $school->site_icon ? asset('storage/' . $school->site_icon) : '';
                    $name = addslashes($school->name);
                    return "openEditModal({$school->id}, '{$name}', '{$logoUrl}', '{$iconUrl}')";
                },
                'icon' => 'fas fa-edit',
                'class' => 'text-blue-600 dark:text-blue-400 hover:text-blue-900 dark:hover:text-blue-300',
                'title' => 'Edit',
            ],
            [
                'type' => 'button',
                'onclick' => function($school) {
                    return "confirmDeleteLogo({$school->id})";
                },
                'icon' => 'fas fa-trash',
                'class' => 'text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300',
                'title' => 'Delete',
                'condition' => function($school) {
                    return $school->logo || $school->site_icon;
                }
            ],
        ];
    @endphp

    <x-data-table 
        :columns="$tableColumns" 
        :data="$schools" 
        :filters="$tableFilters"
        :actions="$tableActions"
        searchable="true"
        filterable="true"
        route="admin.settings.logo"
        empty-message="No logos found"
        empty-icon="fas fa-image"
    >
        All Schools Logos
    </x-data-table>
</div>

<!-- Delete Confirmation Modal -->
<x-confirm-modal 
    id="delete-logo-modal"
    title="Delete Logo"
    message="Are you sure you want to delete the logo? This action cannot be undone."
    confirm-text="Delete"
    cancel-text="Cancel"
    confirm-class="bg-red-600 hover:bg-red-700"
/>

<!-- Edit Modal -->
<div id="edit-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
        <div class="mt-3">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">Update Logo</h3>
            <form id="edit-form" action="{{ route('admin.settings.logo.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="school_id" id="edit_school_id">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">School</label>
                        <p id="edit_school_name" class="text-sm text-gray-900 dark:text-gray-200"></p>
                    </div>
                    <div>
                        <label for="edit_logo" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Logo</label>
                        <div id="current_logo" class="mb-2"></div>
                        <input type="file" name="logo" id="edit_logo" accept="image/*" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg">
                    </div>
                    <div>
                        <label for="edit_site_icon" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Site Icon</label>
                        <div id="current_site_icon" class="mb-2"></div>
                        <input type="file" name="site_icon" id="edit_site_icon" accept="image/*,.ico" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg">
                    </div>
                    <div class="flex items-center space-x-3 pt-4">
                        <button type="submit" class="flex-1 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                            Update
                        </button>
                        <button type="button" onclick="closeEditModal()" class="flex-1 bg-gray-300 dark:bg-gray-600 text-gray-700 dark:text-gray-200 px-4 py-2 rounded-lg hover:bg-gray-400 dark:hover:bg-gray-500">
                            Cancel
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openEditModal(schoolId, schoolName, logoUrl, siteIconUrl) {
    document.getElementById('edit_school_id').value = schoolId;
    document.getElementById('edit_school_name').textContent = schoolName;
    
    const currentLogo = document.getElementById('current_logo');
    if (logoUrl) {
        currentLogo.innerHTML = `<img src="${logoUrl}" alt="Current Logo" class="w-16 h-16 object-contain rounded mb-2">`;
    } else {
        currentLogo.innerHTML = '<span class="text-sm text-gray-400">No logo</span>';
    }
    
    const currentSiteIcon = document.getElementById('current_site_icon');
    if (siteIconUrl) {
        currentSiteIcon.innerHTML = `<img src="${siteIconUrl}" alt="Current Site Icon" class="w-16 h-16 object-contain rounded mb-2">`;
    } else {
        currentSiteIcon.innerHTML = '<span class="text-sm text-gray-400">No site icon</span>';
    }
    
    document.getElementById('edit-modal').classList.remove('hidden');
}

function closeEditModal() {
    document.getElementById('edit-modal').classList.add('hidden');
    document.getElementById('edit-form').reset();
}

function confirmDeleteLogo(schoolId) {
    // Check if confirmModal is available
    if (typeof window.confirmModal === 'undefined' || !window.confirmModal.show) {
        // Fallback to browser confirm
        if (confirm('Are you sure you want to delete the logo? This action cannot be undone.')) {
            submitDeleteForm(schoolId);
        }
        return;
    }
    
    // Show the modal
    window.confirmModal.show('delete-logo-modal', function() {
        submitDeleteForm(schoolId);
    });
}

function submitDeleteForm(schoolId) {
    // Create and submit form
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route("admin.settings.logo.delete", ":id") }}'.replace(':id', schoolId);
    
    // Add CSRF token
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = '{{ csrf_token() }}';
    form.appendChild(csrfInput);
    
    // Add method spoofing
    const methodInput = document.createElement('input');
    methodInput.type = 'hidden';
    methodInput.name = '_method';
    methodInput.value = 'DELETE';
    form.appendChild(methodInput);
    
    document.body.appendChild(form);
    form.submit();
}
</script>
@endsection
