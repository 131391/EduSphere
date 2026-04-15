@extends('layouts.school')

@section('title', 'Institutional Support Matrix')

@section('content')
<div class="space-y-8">
    <!-- Premium Header -->
    <div class="bg-white dark:bg-gray-800 rounded-3xl p-10 border border-teal-100/50 shadow-xl shadow-teal-500/5 relative overflow-hidden">
        <div class="absolute right-0 top-0 -mr-16 -mt-16 w-80 h-80 bg-teal-50/50 rounded-full blur-3xl"></div>
        <div class="relative z-10">
            <h1 class="text-4xl font-black text-gray-800 dark:text-white tracking-tight">
                Support <span class="text-teal-600">Hub</span>
            </h1>
            <p class="text-gray-500 dark:text-gray-400 mt-3 font-medium text-lg max-w-2xl leading-relaxed">
                Connect with our technical intelligence units for seamless institutional orchestration and resolution of platform exceptions.
            </p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <!-- Direct Liaison Card -->
        <div class="bg-white p-8 rounded-[2rem] shadow-sm border border-teal-50 group hover:shadow-xl transition-all duration-500 flex flex-col justify-between">
            <div>
                <div class="w-16 h-16 bg-teal-50 text-teal-600 rounded-2xl flex items-center justify-center group-hover:scale-110 group-hover:rotate-3 transition-transform duration-500 shadow-sm mb-6">
                    <i class="fas fa-headset text-2xl"></i>
                </div>
                <h3 class="text-xl font-black text-gray-800 dark:text-gray-200 uppercase tracking-tight mb-2">Technical Liaison</h3>
                <p class="text-sm text-gray-500 leading-relaxed font-medium">Immediate synchronization with our dedicated support architects for mission-critical assistance.</p>
            </div>
            <div class="mt-8 space-y-4">
                <div class="p-4 bg-gray-50 rounded-2xl border border-gray-100 flex items-center gap-4 group/item hover:border-teal-200 transition-colors">
                    <div class="w-10 h-10 rounded-xl bg-white border border-gray-200 flex items-center justify-center text-teal-500 group-hover/item:scale-110 transition-transform">
                        <i class="fas fa-envelope text-xs"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest leading-none">Email Protocol</p>
                        <p class="text-sm font-black text-gray-700 mt-1">support@edusphere.com</p>
                    </div>
                </div>
                <div class="p-4 bg-gray-50 rounded-2xl border border-gray-100 flex items-center gap-4 group/item hover:border-teal-200 transition-colors">
                    <div class="w-10 h-10 rounded-xl bg-white border border-gray-200 flex items-center justify-center text-teal-500 group-hover/item:scale-110 transition-transform">
                        <i class="fas fa-phone-alt text-xs"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest leading-none">Voice Uplink</p>
                        <p class="text-sm font-black text-gray-700 mt-1">+1 (800) 123-4567</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Intelligence Matrix Card -->
        <div class="bg-white p-8 rounded-[2rem] shadow-sm border border-teal-50 group hover:shadow-xl transition-all duration-500 flex flex-col justify-between">
            <div>
                <div class="w-16 h-16 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center group-hover:scale-110 group-hover:rotate-3 transition-transform duration-500 shadow-sm mb-6">
                    <i class="fas fa-book-open-reader text-2xl"></i>
                </div>
                <h3 class="text-xl font-black text-gray-800 dark:text-gray-200 uppercase tracking-tight mb-2">Intelligence Base</h3>
                <p class="text-sm text-gray-500 leading-relaxed font-medium">Explore the comprehensive documentation matrix to master institutional orchestration workflows.</p>
            </div>
            <div class="mt-8">
                <a href="#" class="w-full h-14 bg-gray-900 hover:bg-black text-white rounded-2xl font-black text-[10px] uppercase tracking-[0.2em] flex items-center justify-center gap-3 transition-all shadow-xl active:scale-95 group/btn">
                    Access Matrix
                    <i class="fas fa-arrow-right text-[10px] group-hover/btn:translate-x-1 transition-transform"></i>
                </a>
            </div>
        </div>

        <!-- Operational Status Card -->
        <div class="bg-gray-900 p-8 rounded-[2rem] shadow-2xl shadow-gray-900/40 relative overflow-hidden group">
            <div class="absolute right-0 bottom-0 -mb-10 -mr-10 w-48 h-48 bg-teal-500/10 rounded-full blur-2xl group-hover:scale-125 transition-transform duration-700"></div>
            <div class="relative z-10">
                <div class="w-16 h-16 bg-white/5 border border-white/10 text-teal-400 rounded-2xl flex items-center justify-center mb-6">
                    <i class="fas fa-signal text-2xl"></i>
                </div>
                <h3 class="text-xl font-black text-white uppercase tracking-tight mb-6">Platform Pulse</h3>
                
                <div class="space-y-6">
                    <div class="flex items-center justify-between group/status">
                        <div class="flex flex-col">
                            <span class="text-[10px] font-black text-teal-400 uppercase tracking-[0.15em] mb-1">API Cluster</span>
                            <span class="text-white text-xs font-bold">Synchronized</span>
                        </div>
                        <div class="px-3 py-1 bg-emerald-500/10 border border-emerald-500/20 rounded-lg flex items-center gap-2">
                            <div class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></div>
                            <span class="text-[10px] font-black text-emerald-400 uppercase tracking-widest">Global</span>
                        </div>
                    </div>

                    <div class="flex items-center justify-between group/status">
                        <div class="flex flex-col">
                            <span class="text-[10px] font-black text-blue-400 uppercase tracking-[0.15em] mb-1">Database Shard</span>
                            <span class="text-white text-xs font-bold">Optimal Load</span>
                        </div>
                        <div class="px-3 py-1 bg-emerald-500/10 border border-emerald-500/20 rounded-lg flex items-center gap-2">
                            <div class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></div>
                            <span class="text-[10px] font-black text-emerald-400 uppercase tracking-widest">Active</span>
                        </div>
                    </div>

                    <div class="mt-6 pt-6 border-t border-white/5">
                        <p class="text-[10px] font-bold text-gray-500 leading-relaxed italic uppercase tracking-wider">All systems operational at peak efficiency levels across institutional boundaries.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

