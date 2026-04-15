@extends('layouts.receptionist')

@section('title', 'Student Registration Matrix')

@section('content')
<div class="space-y-6">
    <!-- Premium Header Section -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-6 border border-teal-100/50">
        <div class="flex flex-col md:flex-row items-center justify-between gap-6">
            <div>
                <h2 class="text-xl font-black text-gray-800 dark:text-white flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-teal-100 flex items-center justify-center text-teal-600">
                        <i class="fas fa-file-signature text-xs"></i>
                    </div>
                    Registration Ledger
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 uppercase font-bold tracking-widest leading-tight">Institutional Enrollment Source & Registry Synchronization</p>
            </div>
            
            <div class="flex flex-wrap items-center gap-3">
                <button @click="$dispatch('open-modal', 'import-modal')" 
                    class="px-5 py-2.5 bg-indigo-50 text-indigo-600 border border-indigo-100 text-[10px] font-black uppercase tracking-widest rounded-xl hover:bg-indigo-600 hover:text-white transition-all shadow-sm flex items-center gap-2 group">
                    <i class="fas fa-upload text-[8px] group-hover:-translate-y-1 transition-transform"></i> Bulk Import
                </button>
                <div class="h-8 w-[1px] bg-gray-100 mx-1"></div>
                <button class="px-5 py-2.5 bg-amber-50 text-amber-600 border border-amber-100 text-[10px] font-black uppercase tracking-widest rounded-xl hover:bg-amber-600 hover:text-white transition-all shadow-sm flex items-center gap-2">
                    <i class="fas fa-sms text-[8px]"></i> SMS Protocol
                </button>
                <a href="{{ route('receptionist.student-registrations.create') }}" 
                    class="px-6 py-2.5 bg-gray-900 text-white text-[10px] font-black uppercase tracking-widest rounded-xl hover:bg-black transition-all shadow-xl flex items-center gap-2">
                    <i class="fas fa-plus text-[10px]"></i> New Registration
                </a>
            </div>
        </div>

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
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-1">CSV Data Segment <span class="text-red-500 font-bold">*</span></label>
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
                    </div>
                </div>

                <div class="px-8 py-6 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-4 rounded-b-3xl">
                    <button type="button" @click="$dispatch('close-modal', 'import-modal')"
                        class="px-6 py-3 text-[10px] font-black text-slate-400 hover:text-slate-600 uppercase tracking-widest transition-colors">
                        Cancel
                    </button>
                    <button type="submit" 
                        class="px-8 py-3 bg-gray-900 text-white text-[10px] font-black rounded-xl transition-all shadow-lg uppercase tracking-widest flex items-center gap-2 group hover:bg-black">
                        <i class="fas fa-upload text-[10px] group-hover:-translate-y-1 transition-transform"></i>
                        Initialize Import
                    </button>
                </div>
            </form>
        </x-modal>
    </div>

    <!-- Institutional Analytics Grid -->
    <div class="grid grid-cols-1 md:grid-cols-5 gap-6">
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-teal-50 group hover:shadow-md transition-all duration-300">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform shadow-sm">
                    <i class="fas fa-file-signature text-xl"></i>
                </div>
                <div>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest leading-tight">Total Registrations</p>
                    <p class="text-xl font-black text-gray-800">{{ number_format($stats['total']) }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-teal-50 group hover:shadow-md transition-all duration-300">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform shadow-sm">
                    <i class="fas fa-user-graduate text-xl"></i>
                </div>
                <div>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest leading-tight">Admitted</p>
                    <p class="text-xl font-black text-emerald-600">{{ number_format($stats['admitted']) }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-teal-50 group hover:shadow-md transition-all duration-300">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-amber-50 text-amber-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform shadow-sm">
                    <i class="fas fa-hourglass-half text-xl"></i>
                </div>
                <div>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest leading-tight">Pending Approval</p>
                    <p class="text-xl font-black text-amber-600">{{ number_format($stats['pending']) }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-teal-50 group hover:shadow-md transition-all duration-300">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-rose-50 text-rose-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform shadow-sm">
                    <i class="fas fa-ban text-xl"></i>
                </div>
                <div>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest leading-tight">Cancelled</p>
                    <p class="text-xl font-black text-rose-600">{{ number_format($stats['cancelled']) }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-teal-50 group hover:shadow-md transition-all duration-300">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-purple-50 text-purple-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform shadow-sm">
                    <i class="fas fa-search-dollar text-xl"></i>
                </div>
                <div>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest leading-tight">Leads Matrix</p>
                    <p class="text-xl font-black text-purple-600">{{ number_format($stats['total_enquiry']) }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Registration Ledger Section -->
    @php
        use App\Enums\AdmissionStatus;

        $tableColumns = [
            [
                'key' => 'registration_no',
                'label' => 'Registry ID',
                'sortable' => true,
                'render' => fn($row) => '<span class="font-black text-teal-600">#'.$row->registration_no.'</span>'
            ],
            [
                'key' => 'full_name',
                'label' => 'Student Identity',
                'sortable' => true,
                'render' => function($row) {
                    $photoUrl = $row->student_photo ? asset('storage/' . $row->student_photo) : null;
                    $photoHtml = $photoUrl 
                        ? '<img src="'.$photoUrl.'" alt="Student" class="w-full h-full object-cover">'
                        : '<i class="fas fa-user text-gray-300"></i>';
                    
                    return '<div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-gray-50 flex items-center justify-center overflow-hidden border border-gray-100">
                                    '.$photoHtml.'
                                </div>
                                <div>
                                    <div class="text-xs font-black text-gray-800 uppercase tracking-tight">'.$row->full_name.'</div>
                                    <div class="text-[10px] font-bold text-gray-400 uppercase tabular-nums mt-0.5">'.$row->mobile_no.'</div>
                                </div>
                            </div>';
                }
            ],
            [
                'key' => 'class_id',
                'label' => 'Academic Cluster',
                'sortable' => false,
                'render' => fn($row) => '<span class="text-xs font-black text-gray-700 uppercase tracking-tight">'.($row->class?->name ?? 'Institutional').'</span>'
            ],
            [
                'key' => 'registration_fee',
                'label' => 'Registry Fee',
                'sortable' => true,
                'render' => fn($row) => '<span class="text-xs font-bold text-gray-600 tabular-nums">₹'.number_format($row->registration_fee, 0).'</span>'
            ],
            [
                'key' => 'registration_date',
                'label' => 'Logged Date',
                'sortable' => true,
                'render' => fn($row) => '<span class="text-[10px] font-black text-gray-400 uppercase tabular-nums">'.$row->registration_date->format('d M, Y').'</span>'
            ],
            [
                'key' => 'admission_status',
                'label' => 'Protocol Stance',
                'sortable' => true,
                'render' => function($row) {
                    $color = $row->admission_status->color();
                    $statusClass = "bg-{$color}-50 text-{$color}-600 border-{$color}-100";
                    return '<div class="inline-flex items-center gap-2 px-3 py-1 rounded-xl border '.$statusClass.' shadow-sm">
                                <div class="w-1.5 h-1.5 rounded-full bg-current animate-pulse"></div>
                                <span class="text-[10px] font-black uppercase tracking-widest">
                                    '.$row->admission_status->label().'
                                </span>
                            </div>';
                }
            ],
        ];

        $tableFilters = [
            [
                'name' => 'class_id',
                'label' => 'Cluster',
                'options' => $classes->pluck('name', 'id')->toArray(),
            ],
            [
                'name' => 'admission_status',
                'label' => 'Stance',
                'options' => collect(AdmissionStatus::cases())->mapWithKeys(fn($s) => [$s->value => $s->label()])->toArray(),
            ],
        ];

        $tableActions = [
            [
                'type' => 'link',
                'url' => fn($row) => route('receptionist.student-registrations.show', $row->id),
                'icon' => 'fas fa-eye',
                'class' => 'text-blue-500 hover:text-blue-600',
                'title' => 'Analyze Identity',
            ],
            [
                'type' => 'link',
                'url' => fn($row) => route('receptionist.student-registrations.edit', $row->id),
                'icon' => 'fas fa-edit',
                'class' => 'text-teal-500 hover:text-teal-600',
                'title' => 'Modify Index',
            ],
            [
                'type' => 'form',
                'url' => fn($row) => route('receptionist.student-registrations.destroy', $row->id),
                'method' => 'DELETE',
                'icon' => 'fas fa-trash-alt',
                'class' => 'text-rose-500 hover:text-rose-600',
                'title' => 'Purge Node',
                'confirm' => 'This action will permanently purge the registration node. Continue?',
            ],
        ];
    @endphp

    <div class="bg-white/50 backdrop-blur-sm rounded-3xl border border-gray-100 overflow-hidden shadow-xl shadow-teal-500/5">
        <x-data-table 
            :columns="$tableColumns"
            :data="$registrations"
            :searchable="true"
            :filterable="true"
            :filters="$tableFilters"
            :actions="$tableActions"
            empty-message="No registration nodes found in the institutional matrix"
            empty-icon="fas fa-folder-open"
        >
            Registry Matrix
        </x-data-table>
    </div>
</div>
@endsection

