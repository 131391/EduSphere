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
        <button @click="$dispatch('open-modal', 'import-modal')" 
            class="inline-flex items-center px-6 py-3 bg-white border border-gray-100 text-indigo-600 text-sm font-black rounded-xl hover:bg-indigo-50 transition-all shadow-sm group">
            <i class="fas fa-file-import mr-2 group-hover:scale-110 transition-transform"></i>
            Import Bulk
        </button>
        <a href="{{ route('receptionist.student-registrations.create') }}" 
            class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-teal-600 to-teal-700 hover:from-teal-700 hover:to-teal-800 text-white text-sm font-black rounded-xl transition-all shadow-lg shadow-teal-100 group">
            <i class="fas fa-plus mr-2 group-hover:rotate-90 transition-transform duration-300"></i>
            New Registration
        </a>
        <button class="inline-flex items-center px-6 py-3 bg-white border border-gray-100 text-amber-600 text-sm font-black rounded-xl hover:bg-amber-50 transition-all shadow-sm group">
            <i class="fas fa-sms mr-2 group-hover:scale-110 transition-transform"></i>
            Send SMS
        </button>
        <button class="inline-flex items-center px-6 py-3 bg-white border border-gray-100 text-blue-600 text-sm font-black rounded-xl hover:bg-blue-50 transition-all shadow-sm group">
            <i class="fas fa-envelope mr-2 group-hover:scale-110 transition-transform"></i>
            Send Email
        </button>

        <!-- Import Modal -->
        <x-modal name="import-modal" title="Bulk Registration Interface" max-width="lg">
            <form action="{{ route('receptionist.registrations.import') }}" method="POST" enctype="multipart/form-data" class="p-0">
                @csrf
                <div class="p-8 space-y-8">
                    <div class="bg-indigo-50/50 border border-indigo-100 rounded-2xl p-6 flex flex-col gap-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-indigo-100 flex items-center justify-center text-indigo-600 shadow-sm">
                                <i class="fas fa-info-circle text-sm"></i>
                            </div>
                            <div>
                                <h4 class="text-xs font-black text-indigo-900 uppercase tracking-wider">Interface Guidelines</h4>
                                <p class="text-[10px] text-indigo-600 font-bold uppercase tracking-widest mt-0.5">Follow the established protocol</p>
                            </div>
                        </div>
                        <p class="text-xs text-indigo-700/70 font-medium leading-relaxed">
                            To ensure institutional data integrity, please utilize the standardized CSV template. Map all required student nodes before initiating the transmission.
                        </p>
                        <a href="{{ route('receptionist.registrations.download-template') }}" 
                            class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-white border border-indigo-100 text-indigo-600 text-[10px] font-black uppercase tracking-widest rounded-xl hover:bg-indigo-500 hover:text-white transition-all shadow-sm w-full">
                            <i class="fas fa-download text-[8px]"></i>
                            Download Registry Template
                        </a>
                    </div>

                    <div class="space-y-3">
                        <label class="modal-label-premium">CSV Data Segment <span class="text-red-500 font-bold">*</span></label>
                        <div class="relative group">
                            <input type="file" name="file" accept=".csv" required 
                                class="w-full text-xs text-slate-500 font-bold
                                file:mr-4 file:py-3 file:px-6
                                file:rounded-xl file:border-0
                                file:text-[10px] file:font-black file:uppercase file:tracking-widest
                                file:bg-slate-900 file:text-white
                                hover:file:bg-slate-800 transition-all
                                cursor-pointer bg-slate-50 border border-slate-100 rounded-2xl pr-4">
                            <div class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-300 pointer-events-none group-focus-within:text-indigo-500 transition-colors">
                                <i class="fas fa-file-csv text-[10px]"></i>
                            </div>
                        </div>
                        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest px-1">Ensure file encoding is set to UTF-8</p>
                    </div>
                </div>

                <div class="px-8 py-6 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-4 rounded-b-3xl">
                    <button type="button" @click="$dispatch('close-modal', 'import-modal')"
                        class="px-6 py-3 text-[10px] font-black text-slate-400 hover:text-slate-600 uppercase tracking-widest transition-colors">
                        Cancel
                    </button>
                    <button type="submit" 
                        class="px-8 py-3 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white text-[10px] font-black rounded-xl transition-all shadow-lg shadow-indigo-100 uppercase tracking-widest flex items-center gap-2 group">
                        <i class="fas fa-upload text-[10px] group-hover:-translate-y-1 transition-transform"></i>
                        Initialize Import
                    </button>
                </div>
            </form>
        </x-modal>
    </div>

    {{-- Search and Table Section --}}
    @php
        use App\Enums\AdmissionStatus;

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
                'options' => collect(AdmissionStatus::cases())->mapWithKeys(fn($s) => [$s->value => $s->label()])->toArray(),
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
