@extends('layouts.receptionist')

@section('content')
    <div class="space-y-6">
    {{-- Statistics Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
        <div class="bg-white p-4 rounded-lg shadow border-l-4 border-blue-500">
            <div class="flex justify-between items-center">
                <div>
                    <p class="text-xs text-gray-500 uppercase font-bold">Total Registration</p>
                    <h3 class="text-2xl font-bold text-gray-800">{{ $stats['total'] }}</h3>
                </div>
                <div class="text-blue-500">
                    <i class="fas fa-file-alt text-2xl"></i>
                </div>
            </div>
        </div>
        <div class="bg-white p-4 rounded-lg shadow border-l-4 border-green-500">
            <div class="flex justify-between items-center">
                <div>
                    <p class="text-xs text-gray-500 uppercase font-bold">Admission Done</p>
                    <h3 class="text-2xl font-bold text-gray-800">{{ $stats['admitted'] }}</h3>
                </div>
                <div class="text-green-500">
                    <i class="fas fa-user-check text-2xl"></i>
                </div>
            </div>
        </div>
        <div class="bg-white p-4 rounded-lg shadow border-l-4 border-yellow-500">
            <div class="flex justify-between items-center">
                <div>
                    <p class="text-xs text-gray-500 uppercase font-bold">Pending Registration</p>
                    <h3 class="text-2xl font-bold text-gray-800">{{ $stats['pending'] }}</h3>
                </div>
                <div class="text-yellow-500">
                    <i class="fas fa-clock text-2xl"></i>
                </div>
            </div>
        </div>
        <div class="bg-white p-4 rounded-lg shadow border-l-4 border-red-500">
            <div class="flex justify-between items-center">
                <div>
                    <p class="text-xs text-gray-500 uppercase font-bold">Cancelled Registration</p>
                    <h3 class="text-2xl font-bold text-gray-800">{{ $stats['cancelled'] }}</h3>
                </div>
                <div class="text-red-500">
                    <i class="fas fa-times-circle text-2xl"></i>
                </div>
            </div>
        </div>
        <div class="bg-white p-4 rounded-lg shadow border-l-4 border-purple-500">
            <div class="flex justify-between items-center">
                <div>
                    <p class="text-xs text-gray-500 uppercase font-bold">Total Enquiry</p>
                    <h3 class="text-2xl font-bold text-gray-800">{{ $stats['total_enquiry'] }}</h3>
                </div>
                <div class="text-purple-500">
                    <i class="fas fa-question-circle text-2xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions -->
    <div class="flex flex-wrap items-center justify-end gap-4" x-data="{ showImportModal: false }">
        <button @click="showImportModal = true" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 flex items-center gap-2 shadow-sm transition">
            <i class="fas fa-file-import"></i> Import Bulk
        </button>
        <a href="{{ route('receptionist.student-registrations.create') }}" class="bg-teal-500 text-white px-4 py-2 rounded hover:bg-teal-600 flex items-center gap-2 shadow-sm transition">
            <i class="fas fa-plus"></i> New Registration
        </a>
        <button class="bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600 flex items-center gap-2 shadow-sm transition">
            <i class="fas fa-sms"></i> Send SMS
        </button>
        <button class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 flex items-center gap-2 shadow-sm transition">
            <i class="fas fa-envelope"></i> Send Email
        </button>

        <!-- Import Modal -->
        <div x-show="showImportModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity" aria-hidden="true" @click="showImportModal = false">
                    <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                </div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <form action="{{ route('receptionist.registrations.import') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Import Registrations</h3>
                            <div class="space-y-4">
                                <div class="bg-blue-50 p-4 rounded-lg flex items-start space-x-3">
                                    <i class="fas fa-info-circle text-blue-500 mt-1"></i>
                                    <p class="text-sm text-blue-700">
                                        Please download the template, fill student details, and upload the CSV file here.
                                    </p>
                                </div>
                                <a href="{{ route('receptionist.registrations.download-template') }}" class="inline-flex items-center text-sm font-semibold text-blue-600 hover:text-blue-800">
                                    <i class="fas fa-download mr-2"></i> Download CSV Template
                                </a>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Select CSV File</label>
                                    <input type="file" name="file" accept=".csv" required class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                                Start Import
                            </button>
                            <button type="button" @click="showImportModal = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Search and Table Section --}}
    @php
        $tableColumns = [
            [
                'key' => 'registration_no',
                'label' => 'Registration No.',
                'sortable' => true,
            ],
            [
                'key' => 'full_name',
                'label' => 'Student\'s Name',
                'sortable' => true,
                'render' => function($row) {
                    $photoUrl = $row->student_photo ? asset('storage/' . $row->student_photo) : null;
                    $photoHtml = $photoUrl 
                        ? '<img src="'.$photoUrl.'" alt="Student" class="w-full h-full object-cover">'
                        : '<i class="fas fa-user text-gray-400"></i>';
                    
                    return '<div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center overflow-hidden">
                                    '.$photoHtml.'
                                </div>
                                <div>
                                    <div class="text-sm font-medium text-gray-800 dark:text-gray-200">'.$row->full_name.'</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">'.$row->mobile_no.'</div>
                                </div>
                            </div>';
                }
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
                'key' => 'registration_fee',
                'label' => 'Reg. Form Fee',
                'sortable' => true,
                'render' => function($row) {
                    return number_format($row->registration_fee, 2);
                }
            ],
            [
                'key' => 'registration_date',
                'label' => 'Registration Date',
                'sortable' => true,
                'render' => function($row) {
                    return $row->registration_date->format('d/m/Y');
                }
            ],
            [
                'key' => 'admission_status',
                'label' => 'Admission Status',
                'sortable' => true,
                'render' => function($row) {
                    $color = $row->admission_status->color();
                    $statusClass = "bg-{$color}-100 text-{$color}-600 dark:bg-{$color}-900/30 dark:text-{$color}-400";
                    return '<span class="px-3 py-1 rounded-full text-xs font-medium '.$statusClass.'">
                                '.$row->admission_status->label().'
                            </span>';
                }
            ],
        ];

        $tableFilters = [
            [
                'name' => 'class_id',
                'label' => 'Class',
                'options' => $classes->pluck('name', 'id')->toArray(),
            ],
            [
                'name' => 'admission_status',
                'label' => 'Status',
                'options' => collect(\App\Enums\AdmissionStatus::cases())->mapWithKeys(fn($s) => [$s->value => $s->label()])->toArray(),
            ],
        ];

        $tableActions = [
            [
                'type' => 'link',
                'url' => fn($row) => route('receptionist.student-registrations.show', $row->id),
                'icon' => 'fas fa-eye',
                'class' => 'text-blue-500 hover:text-blue-600',
                'title' => 'View',
            ],
            [
                'type' => 'link',
                'url' => fn($row) => route('receptionist.student-registrations.edit', $row->id),
                'icon' => 'fas fa-edit',
                'class' => 'text-teal-500 hover:text-teal-600',
                'title' => 'Edit',
            ],
            [
                'type' => 'form',
                'url' => fn($row) => route('receptionist.student-registrations.destroy', $row->id),
                'method' => 'DELETE',
                'icon' => 'fas fa-trash',
                'class' => 'text-red-500 hover:text-red-600',
                'title' => 'Delete',
                'confirm' => 'Are you sure you want to delete this registration?',
            ],
        ];
    @endphp

    <x-data-table 
        :columns="$tableColumns"
        :data="$registrations"
        :searchable="true"
        :filterable="true"
        :filters="$tableFilters"
        :actions="$tableActions"
        empty-message="No registrations found"
        empty-icon="fas fa-folder-open"
    >
        Registration List
    </x-data-table>
</div>
@endsection
