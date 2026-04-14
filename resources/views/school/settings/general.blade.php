@extends('layouts.school')

@section('title', 'General Configuration - ' . $school->name)

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
                            <span>General Settings</span>
                        </div>
                    </li>
                </ol>
            </nav>
            <h1 class="text-3xl font-black text-gray-900 tracking-tight">System Configuration</h1>
            <p class="text-base text-gray-500 mt-1 flex items-center font-medium">
                <span class="w-2 h-2 rounded-full bg-indigo-500 mr-2"></span>
                Define global institutional parameters
            </p>
        </div>
    </div>



    <form action="{{ route('school.settings.general.update') }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            <!-- Left: Fee Configuration (High Legibility) -->
            <div class="lg:col-span-8 space-y-6">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden relative p-8">
                    <div class="flex items-center mb-8">
                        <div class="w-12 h-12 rounded-2xl bg-indigo-50 flex items-center justify-center mr-4 flex-shrink-0">
                            <i class="fas fa-money-bill-wave text-indigo-600 text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold">Fee & Fine Structures</h3>
                            <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mt-1 opacity-75">Global monetary parameters</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-8">
                        <div class="space-y-2">
                            <label class="text-sm font-bold text-gray-600 ml-1 uppercase">Registration Fee</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 right-0 flex items-center pr-5 text-indigo-500 font-black text-lg">₹</span>
                                <input type="number" step="0.01" name="registration_fee" value="{{ old('registration_fee', $settings['registration_fee'] ?? '') }}" 
                                       class="w-full pr-10 pl-5 py-3.5 bg-gray-50 border-gray-100 rounded-xl focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all font-black text-lg">
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-bold text-gray-600 ml-1 uppercase">Admission Fee</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 right-0 flex items-center pr-5 text-indigo-500 font-black text-lg">₹</span>
                                <input type="number" step="0.01" name="admission_fee" value="{{ old('admission_fee', $settings['admission_fee'] ?? '') }}" 
                                       class="w-full pr-10 pl-5 py-3.5 bg-gray-50 border-gray-100 rounded-xl focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all font-black text-lg">
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-bold text-gray-600 ml-1 uppercase">Library Fine (Daily)</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 right-0 flex items-center pr-5 text-indigo-500 font-black text-lg">₹</span>
                                <input type="number" step="0.01" name="late_return_library_book_fine" value="{{ old('late_return_library_book_fine', $settings['late_return_library_book_fine'] ?? '') }}" 
                                       class="w-full pr-10 pl-5 py-3.5 bg-gray-50 border-gray-100 rounded-xl focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all font-black text-lg">
                            </div>
                        </div>

                        <div class="flex items-end">
                            <label class="relative flex items-center cursor-pointer group w-full bg-gray-50 p-4 rounded-xl border border-gray-100 hover:border-indigo-100 transition-all">
                                <div class="flex items-center h-5">
                                    <input type="checkbox" name="admission_fee_applicable" value="1" {{ !empty($settings['admission_fee_applicable']) ? 'checked' : '' }} 
                                           class="w-6 h-6 text-indigo-600 border-gray-300 rounded-lg focus:ring-indigo-500 transition-all">
                                </div>
                                <div class="ml-4">
                                    <span class="block text-sm font-black text-gray-900 uppercase tracking-tight">Apply Admission Fee</span>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Global Receipt Note Section (High Legibility) -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden p-8 group">
                    <div class="flex items-center mb-6">
                        <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center mr-4 group-hover:bg-indigo-600 group-hover:text-white transition-all duration-300">
                            <i class="fas fa-sticky-note text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold">Standard Receipt Footer</h3>
                            <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mt-1 opacity-75 leading-none">Global disclaimer text</p>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <textarea name="receipt_note" rows="4" class="w-full px-6 py-4 bg-gray-50 border-gray-100 rounded-2xl focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all font-medium text-base leading-relaxed text-gray-800">{{ old('receipt_note', $settings['receipt_note'] ?? '') }}</textarea>
                        <div class="flex items-center gap-3 text-xs font-bold text-indigo-400 uppercase tracking-widest opacity-70">
                            <i class="fas fa-info-circle"></i>
                            Standard printed footer (Max 1000 characters)
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Action Center (High Legibility) -->
            <div class="lg:col-span-4 space-y-8">
                <!-- Action Card -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 text-center group relative overflow-hidden">
                    <div class="absolute -top-12 -right-12 w-32 h-32 bg-indigo-50 rounded-full blur-2xl"></div>
                    
                    <div class="relative z-10 text-center">
                        <div class="w-16 h-16 bg-indigo-50 rounded-2xl flex items-center justify-center mx-auto mb-6 group-hover:rotate-12 transition-all shadow-indigo-100 shadow-xl">
                            <i class="fas fa-save text-indigo-600 text-2xl"></i>
                        </div>
                        <h4 class="text-lg font-black mb-2 tracking-tight">Store Configurations</h4>
                        <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mb-8 leading-tight px-4 opacity-75">Update institutional parameters system-wide.</p>
                        
                        <button type="submit" class="w-full py-4 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-black rounded-xl transition-all shadow-xl shadow-indigo-100 flex items-center justify-center gap-3 uppercase tracking-[0.1em]">
                            <i class="fas fa-check-double text-[10px]"></i>
                            Sync Settings
                        </button>
                    </div>
                </div>

                <!-- Guidance (High Legibility) -->
                <div class="bg-indigo-900 rounded-2xl p-8 text-white shadow-xl relative overflow-hidden">
                    <div class="absolute bottom-0 right-0 w-32 h-32 bg-white/5 rounded-full -mb-16 -mr-16 blur-2xl"></div>
                    <h5 class="text-base font-black mb-6 flex items-center uppercase tracking-tight">
                        <i class="fas fa-lightbulb text-amber-300 mr-3 text-sm"></i> Intelligence
                    </h5>
                    <ul class="space-y-6">
                        <li class="flex items-start gap-4">
                            <div class="w-1.5 h-1.5 rounded-full bg-indigo-400 mt-2 flex-shrink-0"></div>
                            <p class="text-xs text-indigo-100 font-medium leading-relaxed">
                                <span class="text-white font-black uppercase tracking-tighter">Precision:</span> Fees allow two decimal points (e.g., 500.00).
                            </p>
                        </li>
                        <li class="flex items-start gap-4">
                            <div class="w-1.5 h-1.5 rounded-full bg-indigo-400 mt-2 flex-shrink-0"></div>
                            <p class="text-xs text-indigo-100 font-medium leading-relaxed">
                                <span class="text-white font-black uppercase tracking-tighter">Receipts:</span> Notes appear at the footer of all official documents.
                            </p>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
