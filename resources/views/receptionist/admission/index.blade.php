@extends('layouts.receptionist')

@section('title', 'Admission Confirmation Ledger')

@section('content')
<div class="space-y-6">
    <!-- Premium Header Section -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-6 border border-teal-100/50">
        <div class="flex flex-col md:flex-row items-center justify-between gap-6">
            <div>
                <h2 class="text-xl font-black text-gray-800 dark:text-white flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-teal-100 flex items-center justify-center text-teal-600">
                        <i class="fas fa-user-check text-xs"></i>
                    </div>
                    Admission Registry
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 uppercase font-bold tracking-widest leading-tight">Institutional Enrollment Hierarchy & Synchronization Ledger</p>
            </div>
            
            <div class="flex flex-wrap items-center gap-3">
                <button class="px-5 py-2.5 bg-amber-50 text-amber-600 border border-amber-100 text-[10px] font-black uppercase tracking-widest rounded-xl hover:bg-amber-600 hover:text-white transition-all shadow-sm flex items-center gap-2">
                    <i class="fas fa-sms text-[8px]"></i> Send SMS Protocol
                </button>
                <button class="px-5 py-2.5 bg-blue-50 text-blue-600 border border-blue-100 text-[10px] font-black uppercase tracking-widest rounded-xl hover:bg-blue-600 hover:text-white transition-all shadow-sm flex items-center gap-2">
                    <i class="fas fa-envelope text-[8px]"></i> Dispatch Email
                </button>
                <a href="{{ route('receptionist.admission.create') }}" 
                    class="px-6 py-2.5 bg-gray-900 text-white text-[10px] font-black uppercase tracking-widest rounded-xl hover:bg-black transition-all shadow-xl flex items-center gap-2">
                    <i class="fas fa-user-plus text-[10px]"></i> New Admission
                </a>
            </div>
        </div>
    </div>

    <!-- Institutional Analytics Grid -->
    <div class="grid grid-cols-1 md:grid-cols-5 gap-6">
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-teal-50 group hover:shadow-md transition-all duration-300">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform shadow-sm">
                    <i class="fas fa-file-alt text-xl"></i>
                </div>
                <div>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest leading-tight">Registrations</p>
                    <p class="text-xl font-black text-gray-800">{{ number_format($totalRegistration) }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-teal-50 group hover:shadow-md transition-all duration-300">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform shadow-sm">
                    <i class="fas fa-user-check text-xl"></i>
                </div>
                <div>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest leading-tight">Admitted</p>
                    <p class="text-xl font-black text-emerald-600">{{ number_format($admissionDone) }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-teal-50 group hover:shadow-md transition-all duration-300">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-amber-50 text-amber-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform shadow-sm">
                    <i class="fas fa-clock text-xl"></i>
                </div>
                <div>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest leading-tight">Pending</p>
                    <p class="text-xl font-black text-amber-600">{{ number_format($pendingRegistration) }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-teal-50 group hover:shadow-md transition-all duration-300">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-rose-50 text-rose-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform shadow-sm">
                    <i class="fas fa-times-circle text-xl"></i>
                </div>
                <div>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest leading-tight">Cancelled</p>
                    <p class="text-xl font-black text-rose-600">{{ number_format($cancelledRegistration) }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-teal-50 group hover:shadow-md transition-all duration-300">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-purple-50 text-purple-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform shadow-sm">
                    <i class="fas fa-question-circle text-xl"></i>
                </div>
                <div>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest leading-tight">Total Enquiries</p>
                    <p class="text-xl font-black text-purple-600">{{ number_format($totalEnquiry) }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Students Ledger Section -->
    @php
        $tableColumns = [
            [
                'key' => 'admission_no',
                'label' => 'Admission ID',
                'sortable' => true,
                'render' => fn($row) => '<span class="font-black text-teal-600">#'.$row->admission_no.'</span>'
            ],
            [
                'key' => 'full_name',
                'label' => 'Student Identity',
                'sortable' => true,
                'render' => function($row) {
                    $photoUrl = $row->photo ? asset('storage/' . $row->photo) : 'https://ui-avatars.com/api/?background=0D9488&color=fff&name='.urlencode($row->full_name);
                    return '<div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-gray-100 flex items-center justify-center overflow-hidden border border-gray-200">
                                    <img class="w-full h-full object-cover" src="'.$photoUrl.'" alt="">
                                </div>
                                <div>
                                    <div class="text-xs font-black text-gray-800 uppercase tracking-tight">'.$row->full_name.'</div>
                                    <div class="text-[10px] font-bold text-gray-400 uppercase leading-none mt-0.5">Primary Ward</div>
                                </div>
                            </div>';
                }
            ],
            [
                'key' => 'class_section',
                'label' => 'Academic Cluster',
                'sortable' => false,
                'render' => function($row) {
                    return '<div class="flex flex-col">
                                <span class="text-xs font-black text-gray-700 uppercase tracking-tight">'.($row->class->name ?? 'N/A').'</span>
                                <span class="text-[10px] font-bold text-gray-400 uppercase leading-none mt-0.5">Section '.($row->section->name ?? 'A').'</span>
                            </div>';
                }
            ],
            [
                'key' => 'father_name',
                'label' => 'Guardian Entity',
                'sortable' => true,
                'render' => fn($row) => '<span class="text-xs font-bold text-gray-600 uppercase">'.$row->father_name.'</span>'
            ],
            [
                'key' => 'registration_no',
                'label' => 'Registry ID',
                'sortable' => true,
                'render' => fn($row) => '<span class="text-[10px] font-black text-gray-400 tabular-nums">'.($row->registration_no ?? 'N/A').'</span>'
            ],
            [
                'key' => 'admission_date',
                'label' => 'Logged Date',
                'sortable' => true,
                'render' => function($row) {
                    return $row->admission_date ? '<span class="text-[10px] font-black text-gray-400 uppercase tabular-nums">'.$row->admission_date->format('d M, Y').'</span>' : '-';
                }
            ],
        ];

        $tableActions = [
            [
                'type' => 'link',
                'url' => fn($row) => route('receptionist.admission.show', $row->id),
                'icon' => 'fas fa-eye',
                'class' => 'text-blue-500 hover:text-blue-600',
                'title' => 'View Identity',
            ],
            [
                'type' => 'link',
                'url' => fn($row) => route('receptionist.admission.edit', $row->id),
                'icon' => 'fas fa-edit',
                'class' => 'text-indigo-500 hover:text-indigo-600',
                'title' => 'Modify Index',
            ],
            [
                'type' => 'form',
                'url' => fn($row) => route('receptionist.admission.destroy', $row->id),
                'method' => 'DELETE',
                'icon' => 'fas fa-trash-alt',
                'class' => 'text-rose-500 hover:text-rose-600',
                'title' => 'Purge Record',
                'confirm' => 'This action will permanently purge the student node from the institutional registry. Continue?',
            ],
        ];

        $tableFilters = [
            [
                'name' => 'class_id',
                'label' => 'Cluster',
                'options' => $classes->pluck('name', 'id')->toArray(),
            ],
        ];
    @endphp

    <div class="bg-white/50 backdrop-blur-sm rounded-3xl border border-gray-100 overflow-hidden shadow-xl shadow-teal-500/5">
        <x-data-table 
            :columns="$tableColumns"
            :data="$students"
            :searchable="true"
            :filterable="true"
            :filters="$tableFilters"
            :actions="$tableActions"
            empty-message="No student nodes found in the selected matrix"
            empty-icon="fas fa-user-graduate"
        >
            Admission Matrix
        </x-data-table>
    </div>
</div>
@endsection

