@extends('layouts.school')

@section('title', 'Branding - ' . $school->name)

@section('content')
<div class="w-full space-y-6 animate-in fade-in duration-500">
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
                            <span>Logo Update</span>
                        </div>
                    </li>
                </ol>
            </nav>
            <h1 class="text-3xl font-black text-gray-900 tracking-tight">Institutional Branding</h1>
            <p class="text-base text-gray-500 mt-1 flex items-center font-medium">
                <span class="w-2 h-2 rounded-full bg-indigo-500 mr-2"></span>
                Official identity for headers and reports
            </p>
        </div>
    </div>



    <div class="w-full">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden relative">
            <div class="absolute top-0 right-0 -mr-16 -mt-16 w-48 h-48 bg-indigo-50/50 rounded-full blur-3xl"></div>
            
            <form action="{{ route('school.settings.logo.update') }}" method="POST" enctype="multipart/form-data" class="relative z-10 p-8 md:p-12">
                @csrf
                @method('PUT')

                <div class="flex flex-col md:flex-row items-center gap-12">
                    <!-- Smaller Logo Preview (Tighter but clear) -->
                    <div class="relative group">
                        <div class="absolute -inset-4 bg-indigo-500 rounded-3xl opacity-5 blur-xl transition duration-500 group-hover:opacity-10"></div>
                        <div class="relative w-40 h-40 md:w-56 md:h-56 bg-white rounded-2xl shadow-xl border-4 border-white flex items-center justify-center overflow-hidden">
                            @if($school->logo)
                                <img src="{{ asset('storage/' . $school->logo) }}" alt="School Logo" class="w-full h-full object-contain p-4 transition-transform duration-500 group-hover:scale-110">
                            @else
                                <div class="text-center p-6">
                                    <i class="fas fa-image text-gray-100 text-4xl mb-3"></i>
                                    <p class="text-[10px] font-black text-gray-300 uppercase tracking-widest leading-tight">No Identity Set</p>
                                </div>
                            @endif
                            <div class="absolute inset-x-0 bottom-0 bg-black/5 p-3 flex justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                <span class="text-[10px] font-black uppercase text-gray-500 tracking-widest">Active Brand</span>
                            </div>
                        </div>
                    </div>

                    <!-- Upload Controls (High Legibility) -->
                    <div class="flex-1 w-full space-y-6">
                        <div>
                            <h3 class="text-2xl font-black text-gray-900 mb-1 tracking-tight">Institutional Branding</h3>
                            <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mb-6 opacity-80 leading-relaxed">Select a high-resolution logo for reports and dashboard presentation.</p>
                        </div>

                        <div class="relative">
                            <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed border-indigo-100 rounded-3xl bg-indigo-50/20 hover:bg-indigo-50/40 hover:border-indigo-300 transition-all cursor-pointer group">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-xl bg-white shadow-sm flex items-center justify-center group-hover:scale-110 transition-transform">
                                        <i class="fas fa-cloud-upload-alt text-indigo-500 text-lg"></i>
                                    </div>
                                    <p class="text-sm text-gray-700 font-bold group-hover:text-indigo-600 transition-colors">Upload New Mark</p>
                                </div>
                                <p class="text-[11px] text-gray-400 mt-2 font-black uppercase tracking-widest opacity-60">PNG, JPG or GIF (MAX. 2MB)</p>
                                <input type="file" name="logo" class="hidden" accept="image/*" required onchange="this.form.submit()">
                            </label>
                            @error('logo') <p class="text-red-500 text-xs mt-3 font-bold text-center">{{ $message }}</p> @enderror
                        </div>

                        <div class="flex gap-6 pt-2">
                            <div class="flex items-center gap-2 text-xs font-bold text-gray-500 uppercase tracking-wide">
                                <i class="fas fa-check-circle text-indigo-500"></i>
                                HD Reports
                            </div>
                            <div class="flex items-center gap-2 text-xs font-bold text-gray-500 uppercase tracking-wide">
                                <i class="fas fa-check-circle text-indigo-500"></i>
                                Transparency
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Meta Info Card (High Legibility) -->
        <div class="mt-8 bg-indigo-900 rounded-3xl p-8 text-white relative overflow-hidden flex flex-col md:flex-row items-center justify-between gap-6 shadow-xl">
            <div class="absolute top-0 left-0 w-full h-full opacity-10 pointer-events-none" style="background-image: url('data:image/svg+xml,%3Csvg width=\"60\" height=\"60\" viewBox=\"0 0 60 60\" xmlns=\"http://www.w3.org/2000/svg\"%3E%3Cpath d=\"M54 48c-2.209 0-4 1.791-4 4s1.791 4 4 4 4-1.791 4-4-1.791-4-4-4zM6 48c-2.209 0-4 1.791-4 4s1.791 4 4 4 4-1.791 4-4-1.791-4-4-4z\" fill=\"%23ffffff\" fill-opacity=\"1\" fill-rule=\"evenodd\"/%3E%3C/svg%3E');"></div>
            
            <div class="relative z-10 flex items-center gap-6">
                <div class="w-14 h-14 bg-white/10 backdrop-blur-md rounded-2xl flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-lightbulb text-2xl text-amber-300"></i>
                </div>
                <div>
                    <h4 class="text-base font-black uppercase tracking-tight mb-1">Design Guideline</h4>
                    <p class="text-indigo-200 text-xs font-medium leading-relaxed max-w-md">For pixel-perfect results on ID cards and headers, use a square logo with a transparent background (PNG format).</p>
                </div>
            </div>
            
            <a href="{{ route('school.settings.basic-info') }}" class="relative z-10 px-8 py-3 bg-white/10 hover:bg-white/20 backdrop-blur-md text-white text-[11px] font-black rounded-xl border border-white/20 transition-all uppercase tracking-[0.2em]">
                Manage Records
            </a>
        </div>
    </div>
</div>
@endsection
