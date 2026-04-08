@extends('layouts.school')

@section('title', 'Notes - ' . $school->name)

@section('content')
<div class="w-full space-y-6 animate-in fade-in duration-500 text-gray-900">
    <!-- Page Header (High Legibility) -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 px-1">
        <div>
            <nav class="flex mb-2" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3 text-sm font-bold uppercase tracking-wider text-gray-400">
                    <li class="inline-flex items-center">
                        <a href="{{ route('school.dashboard') }}" class="hover:text-indigo-600 transition-colors">Dashboard</a>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center text-indigo-600">
                            <i class="fas fa-chevron-right mx-2 text-[11px] text-gray-300"></i>
                            <span>Receipt Notes</span>
                        </div>
                    </li>
                </ol>
            </nav>
            <h1 class="text-3xl font-black text-gray-900 tracking-tight">Receipt Customization</h1>
            <p class="text-base text-gray-500 mt-1 flex items-center font-medium">
                <span class="w-2 h-2 rounded-full bg-indigo-500 mr-2"></span>
                Tailor legal disclaimers and footers
            </p>
        </div>
    </div>



    <form action="{{ route('school.settings.receipt-note.update') }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            <!-- Main Content: Documentation sections (High Legibility) -->
            <div class="lg:col-span-8 space-y-6">
                <!-- Registration Note -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden group">
                    <div class="p-8">
                        <div class="flex items-center mb-6">
                            <div class="w-11 h-11 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center mr-4 flex-shrink-0">
                                <i class="fas fa-user-plus text-lg"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-900 tracking-tight">Registration Fee Receipt Note</h3>
                                <p class="text-xs text-gray-400 font-bold uppercase tracking-widest leading-none mt-1.5 opacity-70">DISPLAYED ON INQUIRY DOCUMENTS</p>
                            </div>
                        </div>
                        <textarea name="registration_receipt_note" rows="4" class="w-full px-6 py-4 bg-gray-50 border-gray-100 rounded-2xl focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all font-medium text-base text-gray-800 leading-relaxed">{{ old('registration_receipt_note', $settings['registration_receipt_note'] ?? '') }}</textarea>
                    </div>
                </div>

                <!-- Admission Note -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden group">
                    <div class="p-8">
                        <div class="flex items-center mb-6">
                            <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center mr-4 flex-shrink-0">
                                <i class="fas fa-graduation-cap text-lg"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-900 tracking-tight">Admission Fee Receipt Note</h3>
                                <p class="text-xs text-gray-400 font-bold uppercase tracking-widest leading-none mt-1.5 opacity-70">DISPLAYED ON ENROLLMENT DOCUMENTS</p>
                            </div>
                        </div>
                        <textarea name="admission_receipt_note" rows="4" class="w-full px-6 py-4 bg-gray-50 border-gray-100 rounded-2xl focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all font-medium text-base text-gray-800 leading-relaxed">{{ old('admission_receipt_note', $settings['admission_receipt_note'] ?? '') }}</textarea>
                    </div>
                </div>

                <!-- Regular Fee Note -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden group">
                    <div class="p-8">
                        <div class="flex items-center mb-6">
                            <div class="w-11 h-11 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center mr-4 flex-shrink-0">
                                <i class="fas fa-wallet text-lg"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-900 tracking-tight">Tuition Fee Receipt Note</h3>
                                <p class="text-xs text-gray-400 font-bold uppercase tracking-widest leading-none mt-1.5 opacity-70">DISPLAYED ON RECURRING BILLING</p>
                            </div>
                        </div>
                        <textarea name="fee_receipt_note" rows="4" class="w-full px-6 py-4 bg-gray-50 border-gray-100 rounded-2xl focus:bg-white focus:border-teal-500 focus:ring-4 focus:ring-teal-500/10 transition-all font-medium text-base text-gray-800 leading-relaxed">{{ old('fee_receipt_note', $settings['fee_receipt_note'] ?? '') }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Right: Action Center (High Legibility) -->
            <div class="lg:col-span-4 space-y-8">
                <!-- Action Card -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 text-center group relative overflow-hidden">
                    <div class="absolute -top-12 -right-12 w-32 h-32 bg-teal-50 rounded-full blur-2xl opacity-50"></div>
                    
                    <div class="relative z-10">
                        <div class="w-16 h-16 bg-teal-50 rounded-2xl flex items-center justify-center mx-auto mb-6 group-hover:rotate-6 transition-all shadow-teal-100 shadow-xl">
                            <i class="fas fa-file-check text-teal-600 text-2xl"></i>
                        </div>
                        <h4 class="text-lg font-black mb-1 uppercase tracking-tight">Sync Documents</h4>
                        <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mt-2 mb-8 leading-tight opacity-70">Update standard documentation.</p>
                        
                        <button type="submit" class="w-full py-4 bg-teal-600 hover:bg-teal-700 text-white text-xs font-black rounded-xl transition-all shadow-xl shadow-teal-200 flex items-center justify-center gap-3 uppercase tracking-widest">
                            <i class="fas fa-save text-[10px]"></i>
                            Update Notes
                        </button>
                    </div>
                </div>

                <!-- Info Card (High Legibility) -->
                <div class="bg-indigo-900 rounded-2xl p-8 text-white shadow-xl relative overflow-hidden">
                    <h5 class="text-base font-black mb-6 flex items-center uppercase tracking-tight">
                        <i class="fas fa-info-circle text-indigo-300 mr-3 text-sm"></i> Note Strategy
                    </h5>
                    <p class="text-xs text-indigo-100 font-medium leading-relaxed mb-6">
                        These notes will reach parents at the <span class="text-white font-black underline decoration-indigo-400/50 underline-offset-4">footer</span> of the official receipts.
                    </p>
                    <div class="space-y-4 opacity-90">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-print text-indigo-300 text-sm"></i>
                            </div>
                            <span class="text-xs font-bold text-indigo-100 uppercase tracking-widest">Master PDF Output</span>
                        </div>
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-paper-plane text-indigo-300 text-sm"></i>
                            </div>
                            <span class="text-xs font-bold text-indigo-100 uppercase tracking-widest">Digital E-Receipts</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
