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
    <div class="flex flex-wrap items-center justify-end gap-4">
        <a href="{{ route('receptionist.student-registrations.create') }}" class="bg-teal-500 text-white px-4 py-2 rounded hover:bg-teal-600 flex items-center gap-2">
            <i class="fas fa-plus"></i> New Registration
        </a>
        <button class="bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600 flex items-center gap-2">
            <i class="fas fa-sms"></i> Send SMS
        </button>
        <button class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 flex items-center gap-2">
            <i class="fas fa-envelope"></i> Send Email
        </button>
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
