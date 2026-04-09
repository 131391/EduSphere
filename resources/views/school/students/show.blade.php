@extends('layouts.school')

@section('title', 'Student Profile - ' . $student->full_name)

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center space-x-4">
            <a href="{{ route('school.students.index') }}" class="p-2 bg-white border border-gray-200 rounded-lg text-gray-400 hover:text-blue-600 hover:border-blue-200 transition shadow-sm">
                <i class="fas fa-chevron-left"></i>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Student Profile</h1>
                <p class="text-sm text-gray-600">Managing record for {{ $student->admission_no }}</p>
            </div>
        </div>
        <div class="flex items-center space-x-2">
            <a href="{{ route('school.students.edit', $student->id) }}" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition flex items-center shadow-sm">
                <i class="fas fa-edit mr-2 text-sm"></i>
                Edit Profile
            </a>
            <button class="px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition flex items-center shadow-sm">
                <i class="fas fa-print mr-2 text-sm text-gray-400"></i>
                ID Card
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Sidebar: Basic Info & Photo -->
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="bg-gradient-to-r from-blue-600 to-indigo-700 h-24"></div>
                <div class="px-6 pb-6 text-center -mt-12">
                    <div class="inline-block relative">
                        @if($student->photo)
                            <img class="h-24 w-24 rounded-2xl object-cover ring-4 ring-white shadow-md mx-auto" src="{{ Storage::url($student->photo) }}" alt="">
                        @else
                            <div class="h-24 w-24 rounded-2xl bg-blue-100 ring-4 ring-white shadow-md mx-auto flex items-center justify-center text-blue-600 text-3xl font-bold">
                                {{ substr($student->first_name, 0, 1) }}{{ substr($student->last_name, 0, 1) }}
                            </div>
                        @endif
                        <span class="absolute bottom-0 right-0 block h-6 w-6 rounded-full ring-4 ring-white {{ $student->status == 'active' ? 'bg-green-500' : 'bg-red-500' }}" title="Status: {{ $student->status }}"></span>
                    </div>
                    <h2 class="mt-4 text-xl font-bold text-gray-900">{{ $student->full_name }}</h2>
                    <p class="text-sm text-blue-600 font-medium">{{ $student->class->name ?? 'N/A' }} - {{ $student->section->name ?? 'N/A' }}</p>
                    
                    <div class="mt-6 grid grid-cols-2 gap-4 border-t border-gray-100 pt-6">
                        <div class="text-left">
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Roll No</p>
                            <p class="text-sm font-semibold text-gray-900">{{ $student->roll_no ?? 'N/A' }}</p>
                        </div>
                        <div class="text-left">
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Admit Year</p>
                            <p class="text-sm font-semibold text-gray-900">{{ $student->academicYear->name ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact Details -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-sm font-bold text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-address-book text-blue-500 mr-2"></i>
                    Contact Information
                </h3>
                <div class="space-y-4">
                    <div class="flex items-start">
                        <div class="flex-shrink-0 mt-1">
                            <i class="fas fa-phone text-gray-400 text-sm"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-xs text-gray-500">Phone</p>
                            <p class="text-sm font-medium text-gray-900">{{ $student->phone ?? 'N/A' }}</p>
                        </div>
                    </div>
                    <div class="flex items-start">
                        <div class="flex-shrink-0 mt-1">
                            <i class="fas fa-envelope text-gray-400 text-sm"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-xs text-gray-500">Email</p>
                            <p class="text-sm font-medium text-gray-900 break-all">{{ $student->email ?? 'N/A' }}</p>
                        </div>
                    </div>
                    <div class="flex items-start">
                        <div class="flex-shrink-0 mt-1">
                            <i class="fas fa-map-marker-alt text-gray-400 text-sm"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-xs text-gray-500">Residence</p>
                            <p class="text-sm font-medium text-gray-900 leading-relaxed">{{ $student->address ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content: Tabs -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden" x-data="{ tab: 'details' }">
                <div class="border-b border-gray-100">
                    <nav class="flex -mb-px px-6" aria-label="Tabs">
                        <button @click="tab = 'details'" :class="tab === 'details' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="whitespace-nowrap py-4 px-4 border-b-2 font-medium text-sm transition-colors">
                            Personal Details
                        </button>
                        <button @click="tab = 'parent'" :class="tab === 'parent' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="whitespace-nowrap py-4 px-4 border-b-2 font-medium text-sm transition-colors">
                            Parents/Guardians
                        </button>
                        <button @click="tab = 'fees'" :class="tab === 'fees' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="whitespace-nowrap py-4 px-4 border-b-2 font-medium text-sm transition-colors">
                            Fee History
                        </button>
                        <button @click="tab = 'attendance'" :class="tab === 'attendance' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="whitespace-nowrap py-4 px-4 border-b-2 font-medium text-sm transition-colors">
                            Attendance
                        </button>
                    </nav>
                </div>

                <div class="p-6">
                    <!-- Tab: Personal Details -->
                    <div x-show="tab === 'details'" x-transition>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                            <div>
                                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Date of Birth</p>
                                <p class="text-sm font-medium text-gray-900">{{ $student->date_of_birth ? $student->date_of_birth->format('d M, Y') : 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Gender</p>
                                <p class="text-sm font-medium text-gray-900">{{ $student->gender_label }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Blood Group</p>
                                <p class="text-sm font-medium text-gray-900">{{ $student->blood_group ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Religion</p>
                                <p class="text-sm font-medium text-gray-900">{{ $student->religion ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Category</p>
                                <p class="text-sm font-medium text-gray-900">{{ $student->category ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Aadhaar No</p>
                                <p class="text-sm font-medium text-gray-900">{{ $student->aadhaar_no ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Tab: Parent Info -->
                    <div x-show="tab === 'parent'" x-transition style="display: none;">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="p-4 rounded-xl bg-gray-50 border border-gray-100">
                                <h4 class="text-xs font-bold text-blue-600 uppercase tracking-widest mb-4">Father / Guardian</h4>
                                <p class="text-lg font-bold text-gray-900 mb-1">{{ $student->father_name ?? 'N/A' }}</p>
                                <p class="text-sm text-gray-600 mb-4">{{ $student->father_occupation ?? 'Occupation N/A' }}</p>
                                <div class="space-y-2">
                                    <p class="text-xs flex items-center text-gray-500">
                                        <i class="fas fa-phone mr-2 w-4"></i> {{ $student->father_mobile ?? 'N/A' }}
                                    </p>
                                    <p class="text-xs flex items-center text-gray-500">
                                        <i class="fas fa-id-card mr-2 w-4"></i> {{ $student->father_aadhaar ?? 'Aadhaar N/A' }}
                                    </p>
                                </div>
                            </div>
                            <div class="p-4 rounded-xl bg-gray-50 border border-gray-100">
                                <h4 class="text-xs font-bold text-pink-600 uppercase tracking-widest mb-4">Mother Details</h4>
                                <p class="text-lg font-bold text-gray-900 mb-1">{{ $student->mother_name ?? 'N/A' }}</p>
                                <p class="text-sm text-gray-600 mb-4">{{ $student->mother_occupation ?? 'Occupation N/A' }}</p>
                                <div class="space-y-2">
                                    <p class="text-xs flex items-center text-gray-500">
                                        <i class="fas fa-phone mr-2 w-4"></i> {{ $student->mother_mobile ?? 'N/A' }}
                                    </p>
                                    <p class="text-xs flex items-center text-gray-500">
                                        <i class="fas fa-id-card mr-2 w-4"></i> {{ $student->mother_aadhaar ?? 'Aadhaar N/A' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tab: Fees -->
                    <div x-show="tab === 'fees'" x-transition style="display: none;">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">Bill No</th>
                                        <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">Amount</th>
                                        <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">Date</th>
                                        <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @forelse($student->fees as $fee)
                                        <tr>
                                            <td class="px-4 py-3 text-sm font-mono text-gray-600">{{ $fee->bill_no }}</td>
                                            <td class="px-4 py-3 text-sm font-bold text-gray-900">₹ {{ number_format($fee->paid_amount, 2) }}</td>
                                            <td class="px-4 py-3 text-xs text-gray-500">{{ $fee->payment_date ? $fee->payment_date->format('d M, Y') : 'N/A' }}</td>
                                            <td class="px-4 py-3 text-xs">
                                                <span class="px-2 py-0.5 rounded-full {{ $fee->payment_status->value == 'paid' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }} font-bold">
                                                    {{ strtoupper($fee->payment_status->value) }}
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="px-4 py-8 text-center text-gray-400 italic">No fee records found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-4 text-center">
                            <a href="{{ route('school.fee-payments.collect', $student->id) }}" class="text-sm font-bold text-blue-600 hover:text-blue-800">
                                Collect New Fee &rarr;
                            </a>
                        </div>
                    </div>

                    <!-- Tab: Attendance -->
                    <div x-show="tab === 'attendance'" x-transition style="display: none;">
                        <div class="flex flex-col items-center justify-center py-12 text-gray-400">
                            <i class="fas fa-calendar-alt text-4xl mb-4 opacity-20"></i>
                            <p class="text-sm italic">Attendance module integration pending...</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Documents Section -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-sm font-bold text-gray-900 mb-4">Uploaded Documents</h3>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <div class="p-3 border border-gray-100 rounded-xl bg-gray-50 text-center hover:bg-gray-100 transition cursor-pointer">
                        <i class="fas fa-file-pdf text-red-500 text-2xl mb-2"></i>
                        <p class="text-[10px] font-bold text-gray-600 uppercase">Birth Cert</p>
                    </div>
                    <div class="p-3 border border-gray-100 rounded-xl bg-gray-50 text-center hover:bg-gray-100 transition cursor-pointer">
                        <i class="fas fa-file-image text-blue-500 text-2xl mb-2"></i>
                        <p class="text-[10px] font-bold text-gray-600 uppercase">Aadhaar</p>
                    </div>
                    <div class="p-3 border border-gray-100 rounded-xl bg-gray-50 text-center hover:bg-gray-100 transition cursor-pointer opacity-40">
                        <i class="fas fa-plus text-gray-400 text-2xl mb-2"></i>
                        <p class="text-[10px] font-bold text-gray-500 uppercase">Add New</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
