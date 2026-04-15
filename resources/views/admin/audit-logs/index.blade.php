@extends('layouts.admin')

@section('title', 'System Audit Registry')

@section('content')
<div x-data="{ searchOpen: true }">
    <!-- Header Section -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 mb-6 border border-blue-100/50">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-gray-800 dark:text-white flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center text-blue-600">
                        <i class="fas fa-history text-xs"></i>
                    </div>
                    System Audit Registry
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Monitoring application activity and historical state changes across the network.</p>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" @click="searchOpen = !searchOpen" 
                    class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 text-sm font-semibold rounded-xl hover:bg-gray-50 dark:hover:bg-gray-600 transition-all shadow-sm">
                    <i class="fas fa-filter mr-2 opacity-50"></i>
                    Advanced Filters
                </button>
            </div>
        </div>
    </div>

    <!-- Stats Section -->
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-8">
        <div class="bg-white p-4 rounded-2xl shadow-sm border border-blue-50 flex items-center gap-4 group hover:shadow-md transition-all duration-300">
            <div class="w-10 h-10 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                <i class="fas fa-database text-lg"></i>
            </div>
            <div>
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Global Events</p>
                <p class="text-xl font-black text-gray-800">{{ number_format($stats['total']) }}</p>
            </div>
        </div>
        <div class="bg-white p-4 rounded-2xl shadow-sm border border-blue-50 flex items-center gap-4 group hover:shadow-md transition-all duration-300">
            <div class="w-10 h-10 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                <i class="fas fa-bolt text-lg"></i>
            </div>
            <div>
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Today Activity</p>
                <p class="text-xl font-black text-emerald-600">{{ number_format($stats['today']) }}</p>
            </div>
        </div>
        <div class="bg-white p-4 rounded-2xl shadow-sm border border-blue-50 flex items-center gap-4 group hover:shadow-md transition-all duration-300">
            <div class="w-10 h-10 bg-green-50 text-green-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                <i class="fas fa-plus-circle text-lg"></i>
            </div>
            <div>
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Creations</p>
                <p class="text-xl font-black text-green-600">{{ number_format($stats['created']) }}</p>
            </div>
        </div>
        <div class="bg-white p-4 rounded-2xl shadow-sm border border-blue-50 flex items-center gap-4 group hover:shadow-md transition-all duration-300">
            <div class="w-10 h-10 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                <i class="fas fa-edit text-lg"></i>
            </div>
            <div>
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Updates</p>
                <p class="text-xl font-black text-blue-600">{{ number_format($stats['updated']) }}</p>
            </div>
        </div>
        <div class="bg-white p-4 rounded-2xl shadow-sm border border-blue-50 flex items-center gap-4 group hover:shadow-md transition-all duration-300">
            <div class="w-10 h-10 bg-rose-50 text-rose-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                <i class="fas fa-trash-alt text-lg"></i>
            </div>
            <div>
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Deletions</p>
                <p class="text-xl font-black text-rose-600">{{ number_format($stats['deleted']) }}</p>
            </div>
        </div>
    </div>

    <!-- Filters Section -->
    <div x-show="searchOpen" x-collapse
         class="bg-white dark:bg-gray-800 p-4 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 mb-6">
        <form action="{{ route('admin.audit-logs.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 lg:grid-cols-6 gap-4">
            <div class="lg:col-span-2">
                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-wider mb-1">Search Activity</label>
                <div class="relative">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Action description, model name..." 
                           class="w-full h-10 pl-9 bg-gray-50 dark:bg-gray-700 border-gray-200 dark:border-gray-600 rounded-lg text-sm text-gray-700 dark:text-gray-200 focus:ring-blue-500 focus:border-blue-500 transition-all">
                    <div class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                        <i class="fas fa-search text-xs"></i>
                    </div>
                </div>
            </div>
            <div>
                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-wider mb-1">Event Start Date</label>
                <input type="date" name="from_date" value="{{ request('from_date') }}"
                       class="w-full h-10 px-3 bg-gray-50 dark:bg-gray-700 border-gray-200 dark:border-gray-600 rounded-lg text-sm text-gray-700 dark:text-gray-200 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div>
                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-wider mb-1">Event End Date</label>
                <input type="date" name="to_date" value="{{ request('to_date') }}"
                       class="w-full h-10 px-3 bg-gray-50 dark:bg-gray-700 border-gray-200 dark:border-gray-600 rounded-lg text-sm text-gray-700 dark:text-gray-200 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div class="flex items-end gap-2 lg:col-span-2">
                <button type="submit" 
                        class="flex-1 h-10 flex items-center justify-center gap-2 bg-gray-800 dark:bg-gray-700 hover:bg-black dark:hover:bg-gray-600 text-white font-bold text-xs uppercase tracking-widest rounded-lg transition-all duration-300 shadow-sm">
                    <i class="fas fa-filter text-[10px] opacity-50"></i>
                    Execute Filter
                </button>
                @if(request()->hasAny(['search', 'from_date', 'to_date']))
                <a href="{{ route('admin.audit-logs.index') }}" 
                   class="h-10 px-4 flex items-center justify-center bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400 rounded-lg hover:bg-gray-200 transition-colors">
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
                            <span class="text-[10px] font-black text-blue-500 tracking-wider">'.e($row->created_at->format('H:i:s')).' ('.e($row->created_at->diffForHumans()).')</span>
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
                            <div class="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center text-gray-500 border border-gray-200">
                                <i class="fas fa-user-shield text-[10px]"></i>
                            </div>
                            <div>
                                <div class="text-sm font-bold text-gray-800">'.e($name).'</div>
                                <div class="text-[10px] font-black text-gray-400 uppercase tracking-widest">'.e($role).'</div>
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
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-2xl '.$config['bg'].' '.$config['text'].' '.$config['border'].' border text-[10px] font-black uppercase tracking-widest">
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
                            <span class="text-xs font-bold text-gray-600">'.e($model).e($id).'</span>
                        </div>';
                }
            ],
            [
                'key' => 'properties',
                'label' => 'Delta Attributes',
                'sortable' => false,
                'render' => function($row) {
                    if (!$row->properties || !$row->properties->has('attributes')) {
                        return '<span class="text-[10px] font-bold text-gray-300 uppercase tracking-widest italic">No Data Delta</span>';
                    }
                    
                    return '
                        <div x-data="{ open: false }">
                            <button @click="open = !open" class="text-[10px] font-black text-blue-600 hover:text-blue-800 uppercase tracking-widest flex items-center gap-1.5 px-2 py-1 rounded bg-blue-50 border border-blue-100">
                                <i class="fas fa-eye text-[8px]"></i>
                                View Delta
                            </button>
                            <div x-show="open" x-cloak 
                                 class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
                                 @click.self="open = false">
                                <div class="bg-white dark:bg-gray-800 rounded-2xl w-full max-w-2xl max-h-[80vh] overflow-hidden flex flex-col shadow-2xl ring-1 ring-black/10">
                                    <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                                        <h3 class="text-sm font-black text-gray-800 dark:text-white uppercase tracking-widest">Activity Delta Data</h3>
                                        <button @click="open = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                    <div class="p-6 overflow-y-auto font-mono text-xs space-y-4">
                                        '.($row->properties->has('old') ? '
                                        <div>
                                            <div class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5">Original State</div>
                                            <pre class="bg-gray-50 dark:bg-gray-900/50 p-3 rounded-lg border border-gray-100 dark:border-gray-700 overflow-x-auto">'.json_encode($row->properties['old'], JSON_PRETTY_PRINT).'</pre>
                                        </div>' : '').'
                                        <div>
                                            <div class="text-[10px] font-black text-emerald-500 uppercase tracking-widest mb-1.5">Modified State</div>
                                            <pre class="bg-emerald-50/30 dark:bg-emerald-900/10 p-3 rounded-lg border border-emerald-100/50 dark:border-emerald-800/50 overflow-x-auto">'.json_encode($row->properties['attributes'], JSON_PRETTY_PRINT).'</pre>
                                        </div>
                                    </div>
                                    <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/20 text-right">
                                        <button @click="open = false" class="px-5 py-2 bg-gray-800 text-white text-xs font-black uppercase tracking-widest rounded-xl hover:bg-black transition-all shadow-sm">Close Window</button>
                                    </div>
                                </div>
                            </div>
                        </div>';
                }
            ],
        ];

        $tableActions = [];
    @endphp

    <div class="mt-4">
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

