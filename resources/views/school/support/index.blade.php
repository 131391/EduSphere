@extends('layouts.school')

@section('title', 'Support')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl p-8 border border-gray-100 dark:border-gray-700 shadow-sm relative overflow-hidden">
        <div class="absolute right-0 top-0 w-64 h-64 bg-teal-50 dark:bg-teal-900/10 rounded-full -mr-20 -mt-20 blur-3xl pointer-events-none"></div>
        <div class="relative">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-9 h-9 bg-teal-100 dark:bg-teal-900/30 text-teal-600 dark:text-teal-400 rounded-lg flex items-center justify-center">
                    <i class="fas fa-headset text-sm"></i>
                </div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Support Hub</h1>
            </div>
            <p class="text-sm text-gray-500 dark:text-gray-400 max-w-xl">
                Get help from our team, browse documentation, or check platform status.
            </p>
        </div>
    </div>

    {{-- Top Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">

        {{-- Contact Card --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm flex flex-col gap-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-teal-100 dark:bg-teal-900/30 text-teal-600 dark:text-teal-400 rounded-lg flex items-center justify-center">
                    <i class="fas fa-envelope text-sm"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-gray-800 dark:text-white uppercase tracking-wider">Contact Us</h3>
                    <p class="text-xs text-gray-400 dark:text-gray-500">Reach our support team</p>
                </div>
            </div>
            <div class="space-y-3">
                <a href="mailto:support@edusphere.com"
                   class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg border border-gray-100 dark:border-gray-700 hover:border-teal-300 dark:hover:border-teal-600 transition-colors group">
                    <div class="w-8 h-8 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg flex items-center justify-center text-teal-500 group-hover:scale-110 transition-transform">
                        <i class="fas fa-envelope text-xs"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest">Email</p>
                        <p class="text-xs font-semibold text-gray-700 dark:text-gray-200">support@edusphere.com</p>
                    </div>
                </a>
                <a href="tel:+18001234567"
                   class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg border border-gray-100 dark:border-gray-700 hover:border-teal-300 dark:hover:border-teal-600 transition-colors group">
                    <div class="w-8 h-8 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg flex items-center justify-center text-teal-500 group-hover:scale-110 transition-transform">
                        <i class="fas fa-phone-alt text-xs"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest">Phone</p>
                        <p class="text-xs font-semibold text-gray-700 dark:text-gray-200">+1 (800) 123-4567</p>
                    </div>
                </a>
            </div>
        </div>

        {{-- Documentation Card --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm flex flex-col gap-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-lg flex items-center justify-center">
                    <i class="fas fa-book-open text-sm"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-gray-800 dark:text-white uppercase tracking-wider">Documentation</h3>
                    <p class="text-xs text-gray-400 dark:text-gray-500">Guides & references</p>
                </div>
            </div>
            <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed">
                Browse our comprehensive documentation to learn about platform features, workflows, and best practices.
            </p>
            <div class="mt-auto space-y-2">
                <a href="#" class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg border border-gray-100 dark:border-gray-700 hover:border-blue-300 dark:hover:border-blue-600 transition-colors group">
                    <span class="text-xs font-semibold text-gray-700 dark:text-gray-200">Getting Started Guide</span>
                    <i class="fas fa-arrow-right text-[10px] text-gray-400 group-hover:text-blue-500 group-hover:translate-x-0.5 transition-all"></i>
                </a>
                <a href="#" class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg border border-gray-100 dark:border-gray-700 hover:border-blue-300 dark:hover:border-blue-600 transition-colors group">
                    <span class="text-xs font-semibold text-gray-700 dark:text-gray-200">API Reference</span>
                    <i class="fas fa-arrow-right text-[10px] text-gray-400 group-hover:text-blue-500 group-hover:translate-x-0.5 transition-all"></i>
                </a>
            </div>
        </div>

        {{-- Platform Status Card --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm flex flex-col gap-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 rounded-lg flex items-center justify-center">
                    <i class="fas fa-signal text-sm"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-gray-800 dark:text-white uppercase tracking-wider">Platform Status</h3>
                    <p class="text-xs text-gray-400 dark:text-gray-500">All systems operational</p>
                </div>
            </div>
            <div class="space-y-3">
                @foreach([
                    ['label' => 'API Services',    'status' => 'Operational', 'color' => 'emerald'],
                    ['label' => 'Database',         'status' => 'Operational', 'color' => 'emerald'],
                    ['label' => 'File Storage',     'status' => 'Operational', 'color' => 'emerald'],
                    ['label' => 'Email Delivery',   'status' => 'Operational', 'color' => 'emerald'],
                ] as $service)
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-gray-600 dark:text-gray-300">{{ $service['label'] }}</span>
                    <span class="flex items-center gap-1.5 text-[10px] font-bold text-{{ $service['color'] }}-600 dark:text-{{ $service['color'] }}-400 uppercase tracking-wider">
                        <span class="w-1.5 h-1.5 rounded-full bg-{{ $service['color'] }}-500 animate-pulse inline-block"></span>
                        {{ $service['status'] }}
                    </span>
                </div>
                @endforeach
            </div>
            <a href="#" class="mt-auto text-[10px] font-bold text-gray-400 dark:text-gray-500 hover:text-teal-500 dark:hover:text-teal-400 uppercase tracking-widest transition-colors">
                View full status page →
            </a>
        </div>
    </div>

    {{-- Bottom Row: Ticket Form + FAQ --}}
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-5">

        {{-- Submit a Ticket --}}
        <div class="lg:col-span-3 bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-700/30 flex items-center gap-3">
                <div class="w-8 h-8 bg-teal-100 dark:bg-teal-900/30 text-teal-600 dark:text-teal-400 rounded-lg flex items-center justify-center">
                    <i class="fas fa-ticket-alt text-xs"></i>
                </div>
                <h3 class="text-sm font-bold text-gray-800 dark:text-white uppercase tracking-wider">Submit a Ticket</h3>
            </div>
            <form class="p-6 space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-1.5">Subject</label>
                        <input type="text" placeholder="Brief description of the issue"
                               class="w-full px-3 py-2.5 text-sm bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-lg text-gray-800 dark:text-gray-200 placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent transition-all">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-1.5">Priority</label>
                        <select class="w-full px-3 py-2.5 text-sm bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-lg text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent transition-all appearance-none">
                            <option>Low</option>
                            <option>Medium</option>
                            <option>High</option>
                            <option>Critical</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-1.5">Category</label>
                    <select class="w-full px-3 py-2.5 text-sm bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-lg text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent transition-all appearance-none">
                        <option>Fee Management</option>
                        <option>Admissions</option>
                        <option>Attendance</option>
                        <option>Examinations</option>
                        <option>User Access</option>
                        <option>Other</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-1.5">Description</label>
                    <textarea rows="4" placeholder="Describe your issue in detail..."
                              class="w-full px-3 py-2.5 text-sm bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-lg text-gray-800 dark:text-gray-200 placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent transition-all resize-none"></textarea>
                </div>
                <div class="flex items-center justify-between pt-1">
                    <p class="text-[10px] text-gray-400 dark:text-gray-500">Average response time: <span class="font-bold text-teal-500">2–4 hours</span></p>
                    <button type="submit"
                            class="px-5 py-2.5 bg-teal-600 hover:bg-teal-700 text-white text-xs font-bold uppercase tracking-wider rounded-lg transition-colors active:scale-95 flex items-center gap-2">
                        <i class="fas fa-paper-plane text-[10px]"></i>
                        Submit Ticket
                    </button>
                </div>
            </form>
        </div>

        {{-- FAQ --}}
        <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-700/30 flex items-center gap-3">
                <div class="w-8 h-8 bg-orange-100 dark:bg-orange-900/30 text-orange-600 dark:text-orange-400 rounded-lg flex items-center justify-center">
                    <i class="fas fa-question-circle text-xs"></i>
                </div>
                <h3 class="text-sm font-bold text-gray-800 dark:text-white uppercase tracking-wider">Common Questions</h3>
            </div>
            <div class="divide-y divide-gray-100 dark:divide-gray-700">
                @foreach([
                    ['q' => 'How do I reset a student password?',       'a' => 'Go to Students → select the student → click Reset Password from the action menu.'],
                    ['q' => 'How do I generate a fee receipt?',          'a' => 'Navigate to Fee Payments, find the payment record, and click the Print Receipt button.'],
                    ['q' => 'Can I bulk import student data?',           'a' => 'Yes. Use the Import option under Admissions and download the Excel template first.'],
                    ['q' => 'How do I configure academic year?',         'a' => 'Go to Settings → Academic Years and create or activate the relevant year.'],
                    ['q' => 'Where can I view audit logs?',              'a' => 'Audit logs are available under Settings → Activity Log for all admin actions.'],
                ] as $faq)
                <details class="group px-5 py-4 cursor-pointer">
                    <summary class="flex items-center justify-between gap-3 list-none">
                        <span class="text-xs font-semibold text-gray-700 dark:text-gray-200 group-open:text-teal-600 dark:group-open:text-teal-400 transition-colors">{{ $faq['q'] }}</span>
                        <i class="fas fa-chevron-down text-[9px] text-gray-400 group-open:rotate-180 transition-transform flex-shrink-0"></i>
                    </summary>
                    <p class="mt-2 text-xs text-gray-500 dark:text-gray-400 leading-relaxed">{{ $faq['a'] }}</p>
                </details>
                @endforeach
            </div>
        </div>
    </div>

</div>
@endsection
