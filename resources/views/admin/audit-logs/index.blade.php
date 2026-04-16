@extends('layouts.admin')

@section('title', 'System Audit Registry')

@section('content')
<div x-data="{ searchOpen: true }" class="space-y-6">
    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
        <!-- Global Events -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 border-t-4 border-blue-500 transition-all duration-300 hover:shadow-md">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[13px] font-medium text-gray-600 dark:text-gray-400">Global Events</p>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ number_format($stats['total']) }}</h3>
                </div>
                <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/20 rounded-full flex items-center justify-center transition-transform duration-300 hover:scale-110">
                    <i class="fas fa-database text-blue-600 dark:text-blue-400 text-lg"></i>
                </div>
            </div>
        </div>

        <!-- Today Activity -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 border-t-4 border-emerald-500 transition-all duration-300 hover:shadow-md">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[13px] font-medium text-gray-600 dark:text-gray-400">Today Activity</p>
                    <h3 class="text-2xl font-bold text-emerald-600 mt-1">{{ number_format($stats['today']) }}</h3>
                </div>
                <div class="w-10 h-10 bg-emerald-100 dark:bg-emerald-900/20 rounded-full flex items-center justify-center transition-transform duration-300 hover:scale-110">
                    <i class="fas fa-bolt text-emerald-600 dark:text-emerald-400 text-lg"></i>
                </div>
            </div>
        </div>

        <!-- Creations -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 border-t-4 border-green-500 transition-all duration-300 hover:shadow-md">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[13px] font-medium text-gray-600 dark:text-gray-400">Creations</p>
                    <h3 class="text-2xl font-bold text-green-600 mt-1">{{ number_format($stats['created']) }}</h3>
                </div>
                <div class="w-10 h-10 bg-green-100 dark:bg-green-900/20 rounded-full flex items-center justify-center transition-transform duration-300 hover:scale-110">
                    <i class="fas fa-plus-circle text-green-600 dark:text-green-400 text-lg"></i>
                </div>
            </div>
        </div>

        <!-- Updates -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 border-t-4 border-indigo-500 transition-all duration-300 hover:shadow-md">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[13px] font-medium text-gray-600 dark:text-gray-400">Updates</p>
                    <h3 class="text-2xl font-bold text-indigo-600 mt-1">{{ number_format($stats['updated']) }}</h3>
                </div>
                <div class="w-10 h-10 bg-indigo-100 dark:bg-indigo-900/20 rounded-full flex items-center justify-center transition-transform duration-300 hover:scale-110">
                    <i class="fas fa-edit text-indigo-600 dark:text-indigo-400 text-lg"></i>
                </div>
            </div>
        </div>

        <!-- Deletions -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 border-t-4 border-rose-500 transition-all duration-300 hover:shadow-md">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[13px] font-medium text-gray-600 dark:text-gray-400">Deletions</p>
                    <h3 class="text-2xl font-bold text-rose-600 mt-1">{{ number_format($stats['deleted']) }}</h3>
                </div>
                <div class="w-10 h-10 bg-rose-100 dark:bg-rose-900/20 rounded-full flex items-center justify-center transition-transform duration-300 hover:scale-110">
                    <i class="fas fa-trash-alt text-rose-600 dark:text-rose-400 text-lg"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Header Section -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 border border-blue-100/50 dark:border-gray-700">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div class="flex items-center space-x-3">
                <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center text-blue-600">
                    <i class="fas fa-history text-xs"></i>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-800 dark:text-white">System Audit Registry</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Monitoring application activity and historical state changes across the network</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" @click="searchOpen = !searchOpen" 
                    class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 text-gray-700 dark:text-gray-200 text-xs font-semibold rounded-xl hover:bg-gray-50 dark:hover:bg-gray-600 transition-all shadow-sm">
                    <i class="fas fa-filter mr-2 text-blue-500"></i>
                    Advanced Filters
                </button>
            </div>
        </div>
    </div>

    <!-- Filters Section -->
    <div x-show="searchOpen" x-collapse x-cloak
         class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700">
        <form action="{{ route('admin.audit-logs.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 lg:grid-cols-6 gap-6">
            <div class="lg:col-span-2">
                <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1.5">Search Activity</label>
                <div class="relative group">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Action description, model name..." 
                           class="w-full h-11 pl-10 bg-gray-50 dark:bg-gray-700 border-gray-200 dark:border-gray-600 rounded-xl text-sm text-gray-700 dark:text-gray-200 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none">
                    <div class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-blue-500 transition-colors">
                        <i class="fas fa-search text-xs"></i>
                    </div>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1.5">Event Start Date</label>
                <input type="date" name="from_date" value="{{ request('from_date') }}"
                       class="w-full h-11 px-4 bg-gray-50 dark:bg-gray-700 border-gray-200 dark:border-gray-600 rounded-xl text-sm text-gray-700 dark:text-gray-200 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1.5">Event End Date</label>
                <input type="date" name="to_date" value="{{ request('to_date') }}"
                       class="w-full h-11 px-4 bg-gray-50 dark:bg-gray-700 border-gray-200 dark:border-gray-600 rounded-xl text-sm text-gray-700 dark:text-gray-200 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none">
            </div>
            <div class="flex items-end gap-2 lg:col-span-2">
                <button type="submit" 
                        class="flex-1 h-11 flex items-center justify-center gap-2 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-bold text-xs uppercase tracking-widest rounded-xl transition-all duration-300 shadow-md hover:shadow-lg active:scale-95">
                    <i class="fas fa-filter text-[10px]"></i>
                    Execute Filter
                </button>
                @if(request()->hasAny(['search', 'from_date', 'to_date']))
                <a href="{{ route('admin.audit-logs.index') }}" 
                   class="h-11 px-4 flex items-center justify-center bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400 rounded-xl hover:bg-gray-200 transition-all shadow-sm">
                    <i class="fas fa-times text-xs"></i>
                </a>
                @endif
            </div>
        </form>
    </div>

    @php
        $tableColumns = [
            [
                'key' => 'timestamp',
                'label' => 'Event Timestamp',
                'sortable' => true,
                'render' => function($row) {
                    return '
                        <div class="flex flex-col">
                            <span class="text-xs font-bold text-gray-800">'.e($row->created_at->format('M d, Y')).'</span>
                            <span class="text-[10px] font-semibold text-blue-500">'.e($row->created_at->format('H:i:s')).' ('.e($row->created_at->diffForHumans()).')</span>
                        </div>';
                }
            ],
            [
                'key' => 'user',
                'label' => 'Executing Agent',
                'sortable' => false,
                'render' => function($row) {
                    $name = $row->causer?->name ?? 'System Process';
                    $role = $row->causer?->role->name ?? 'Core';
                    return '
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-gray-50 dark:bg-gray-700 flex items-center justify-center text-gray-400 border border-gray-100 dark:border-gray-600">
                                <i class="fas fa-user-shield text-[10px]"></i>
                            </div>
                            <div>
                                <div class="text-sm font-bold text-gray-800">'.e($name).'</div>
                                <div class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">'.e($role).'</div>
                            </div>
                        </div>';
                }
            ],
            [
                'key' => 'action',
                'label' => 'Event Type',
                'sortable' => true,
                'render' => function($row) {
                    $desc = $row->description;
                    $config = match($desc) {
                        'created' => ['bg' => 'bg-green-50', 'text' => 'text-green-700', 'border' => 'border-green-100', 'icon' => 'fa-plus-circle'],
                        'updated' => ['bg' => 'bg-blue-50', 'text' => 'text-blue-700', 'border' => 'border-blue-100', 'icon' => 'fa-edit'],
                        'deleted' => ['bg' => 'bg-rose-50', 'text' => 'text-rose-700', 'border' => 'border-rose-100', 'icon' => 'fa-trash-alt'],
                        default   => ['bg' => 'bg-gray-50', 'text' => 'text-gray-700', 'border' => 'border-gray-100', 'icon' => 'fa-info-circle'],
                    };
                    return '
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full '.$config['bg'].' '.$config['text'].' '.$config['border'].' border text-[10px] font-bold uppercase tracking-wider shadow-sm">
                            <i class="fas '.$config['icon'].' text-[8px]"></i>
                            '.ucfirst($desc).'
                        </span>';
                }
            ],
            [
                'key' => 'entity',
                'label' => 'Target Entity',
                'sortable' => false,
                'render' => function($row) {
                    $model = class_basename($row->subject_type ?? 'N/A');
                    $id = $row->subject_id ? " #{$row->subject_id}" : "";
                    return '
                        <div class="flex items-center gap-2">
                            <i class="fas fa-link text-gray-300 text-[10px]"></i>
                            <span class="text-xs font-semibold text-gray-600">'.e($model).e($id).'</span>
                        </div>';
                }
            ],
            [
                'key' => 'properties',
                'label' => 'Delta Attributes',
                'sortable' => false,
                'render' => function($row) {
                    if (!$row->properties || !$row->properties->has('attributes')) {
                        return '<span class="text-[10px] font-semibold text-gray-300 uppercase tracking-widest italic">No Data Delta</span>';
                    }
                    
                    return '
                        <div x-data="{ open: false }">
                            <button @click="open = !open" class="text-[10px] font-bold text-blue-600 hover:text-blue-800 uppercase tracking-wider flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-blue-50 border border-blue-100 transition-all hover:shadow-sm active:scale-95">
                                <i class="fas fa-eye text-[8px]"></i>
                                View Delta
                            </button>
                            <div x-show="open" x-cloak 
                                 class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
                                 @click.self="open = false">
                                <div class="bg-white dark:bg-gray-800 rounded-3xl w-full max-w-2xl max-h-[85vh] overflow-hidden flex flex-col shadow-2xl ring-1 ring-black/10">
                                    <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between bg-gray-50/50 dark:bg-gray-900/50">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center text-blue-600">
                                                <i class="fas fa-project-diagram text-xs"></i>
                                            </div>
                                            <h3 class="text-sm font-bold text-gray-800 dark:text-white uppercase tracking-wider">Activity Delta Analysis</h3>
                                        </div>
                                        <button @click="open = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors">
                                            <i class="fas fa-times text-lg"></i>
                                        </button>
                                    </div>
                                    <div class="p-8 overflow-y-auto font-mono text-xs space-y-6">
                                        '.($row->properties->has('old') ? '
                                        <div>
                                            <div class="flex items-center gap-2 mb-3">
                                                <div class="w-2 h-2 rounded-full bg-gray-400"></div>
                                                <div class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Original State</div>
                                            </div>
                                            <pre class="bg-gray-50 dark:bg-gray-900/50 p-4 rounded-2xl border border-gray-100 dark:border-gray-700 overflow-x-auto text-[11px] leading-relaxed">'.json_encode($row->properties['old'], JSON_PRETTY_PRINT).'</pre>
                                        </div>' : '').'
                                        <div>
                                            <div class="flex items-center gap-2 mb-3">
                                                <div class="w-2 h-2 rounded-full bg-emerald-500"></div>
                                                <div class="text-[10px] font-bold text-emerald-500 uppercase tracking-wider">Modified State</div>
                                            </div>
                                            <pre class="bg-emerald-50/30 dark:bg-emerald-900/10 p-4 rounded-2xl border border-emerald-100/50 dark:border-emerald-800/50 overflow-x-auto text-[11px] leading-relaxed text-emerald-900">'.json_encode($row->properties['attributes'], JSON_PRETTY_PRINT).'</pre>
                                        </div>
                                    </div>
                                    <div class="px-8 py-5 border-t border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50 text-right">
                                        <button @click="open = false" class="px-8 py-2.5 bg-gray-900 text-white text-xs font-bold uppercase tracking-widest rounded-xl hover:bg-black transition-all shadow-lg active:scale-95">Dismiss View</button>
                                    </div>
                                </div>
                            </div>
                        </div>';
                }
            ],
        ];

        $tableActions = [];
    @endphp

    <div>
        <x-data-table 
            :columns="$tableColumns" 
            :data="$logs" 
            :actions="$tableActions"
            empty-message="No system activity events found matching the specified parameters." 
            empty-icon="fas fa-history"
        >
            Historical Activity Audit Trail
        </x-data-table>
    </div>
</div>
@endsection
