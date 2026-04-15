@extends('layouts.parent')

@section('title', 'My Children')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-5 border border-indigo-100/50">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-gray-800 dark:text-white flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-indigo-100 flex items-center justify-center text-indigo-600">
                        <i class="fas fa-child text-xs"></i>
                    </div>
                    Family Dashboard
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Hello {{ explode(' ', Auth::user()->name)[0] }}, viewing academic profiles for your children.</p>
            </div>
        </div>
    </div>

    <!-- Stats Section -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-indigo-50 flex items-center gap-5 group hover:shadow-md transition-all duration-300">
            <div class="w-14 h-14 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform">
                <i class="fas fa-users text-2xl"></i>
            </div>
            <div>
                <p class="text-xs font-black text-gray-400 uppercase tracking-widest">Enrolled Children</p>
                <p class="text-3xl font-black text-gray-800">{{ $stats['total_children'] }}</p>
            </div>
        </div>
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-indigo-50 flex items-center gap-5 group hover:shadow-md transition-all duration-300">
            <div class="w-14 h-14 bg-rose-50 text-rose-600 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform">
                <i class="fas fa-wallet text-2xl"></i>
            </div>
            <div>
                <p class="text-xs font-black text-gray-400 uppercase tracking-widest">Balance Due</p>
                <p class="text-3xl font-black text-rose-600">₹{{ number_format($stats['total_due'], 2) }}</p>
            </div>
        </div>
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-indigo-50 flex items-center gap-5 group hover:shadow-md transition-all duration-300">
            <div class="w-14 h-14 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform">
                <i class="fas fa-calendar-check text-2xl"></i>
            </div>
            <div>
                <p class="text-xs font-black text-gray-400 uppercase tracking-widest">Avg. Attendance</p>
                <p class="text-3xl font-black text-emerald-600">{{ number_format($stats['avg_attendance'], 1) }}%</p>
            </div>
        </div>
    </div>

    @php
        $tableColumns = [
            [
                'key' => 'student',
                'label' => 'Student Profile',
                'sortable' => false,
                'render' => function($row) {
                    $initial = strtoupper(substr($row->first_name, 0, 1));
                    $img = $row->photo ? 
                        '<img src="'.asset('storage/'.$row->photo).'" class="w-full h-full object-cover rounded-xl">' :
                        '<div class="w-full h-full bg-indigo-50 text-indigo-600 flex items-center justify-center font-black rounded-xl">'.$initial.'</div>';
                    
                    return '
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-xl border-2 border-white shadow-sm ring-1 ring-gray-100 flex-shrink-0">
                                '.$img.'
                            </div>
                            <div>
                                <div class="text-sm font-bold text-gray-800">'.e($row->full_name).'</div>
                                <div class="text-[10px] font-bold text-gray-400 tracking-wider">Adm: '.e($row->admission_no).'</div>
                            </div>
                        </div>';
                }
            ],
            [
                'key' => 'class',
                'label' => 'Class & Section',
                'sortable' => false,
                'render' => function($row) {
                    $className = $row->class->name ?? 'N/A';
                    $sectionName = $row->section->name ?? '';
                    return '
                        <div>
                            <div class="text-sm font-semibold text-gray-700">'.e($className).'</div>
                            <div class="text-[10px] font-bold text-indigo-500 uppercase tracking-widest">'.e($sectionName).'</div>
                        </div>';
                }
            ],
            [
                'key' => 'attendance',
                'label' => 'Attendance',
                'sortable' => false,
                'render' => function($row) {
                    $att = $row->attendance ?? collect();
                    $total = $att->count();
                    $pres = $att->filter(fn($a) => $a->status?->value === 1)->count();
                    $pct = $total > 0 ? round($pres / $total * 100, 1) : 0;
                    $color = $pct >= 75 ? 'emerald' : ($pct >= 60 ? 'amber' : 'rose');
                    
                    return '
                        <div class="flex flex-col gap-1.5">
                            <div class="flex items-center justify-between gap-4">
                                <span class="text-[10px] font-black text-'.$color.'-600 uppercase tracking-widest">'.$pct.'%</span>
                                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">'.$pres.'/'.$total.' Days</span>
                            </div>
                            <div class="w-24 h-1.5 bg-gray-100 dark:bg-gray-700 rounded-full overflow-hidden">
                                <div class="h-full bg-'.$color.'-500 rounded-full" style="width: '.$pct.'%"></div>
                            </div>
                        </div>';
                }
            ],
            [
                'key' => 'fees',
                'label' => 'Financials',
                'sortable' => false,
                'render' => function($row) {
                    $due = $row->fees->sum('due_amount');
                    $paid = $row->fees->sum('paid_amount');
                    $color = $due > 0 ? 'rose' : 'emerald';
                    
                    return '
                        <div class="flex flex-col">
                            <span class="text-xs font-bold text-'.$color.'-600">₹'.number_format($due, 2).' Due</span>
                            <span class="text-[10px] font-bold text-gray-400">₹'.number_format($paid, 2).' Paid</span>
                        </div>';
                }
            ],
        ];

        $tableActions = [
            [
                'label' => 'Results',
                'icon' => 'fas fa-trophy',
                'url' => function($row) { return route('parent.results.index', ['student_id' => $row->id]); },
                'class' => 'bg-indigo-50 text-indigo-600 hover:bg-indigo-600 hover:text-white shadow-sm'
            ],
            [
                'label' => 'Fees',
                'icon' => 'fas fa-receipt',
                'url' => function($row) { return route('parent.fees.index', ['student_id' => $row->id]); },
                'class' => 'bg-emerald-50 text-emerald-600 hover:bg-emerald-600 hover:text-white shadow-sm'
            ],
            [
                'label' => 'Profile',
                'icon' => 'fas fa-eye',
                'url' => function($row) { return route('parent.children.show', $row->id); },
                'class' => 'bg-gray-50 text-gray-600 hover:bg-gray-800 hover:text-white shadow-sm'
            ],
        ];
    @endphp

    <div class="mt-4">
        <x-data-table 
            :columns="$tableColumns" 
            :data="$children" 
            :actions="$tableActions"
            empty-message="No children profiles found linked to your account." 
            empty-icon="fas fa-child"
        >
            Enrolled Children Profiles
        </x-data-table>
    </div>
</div>
@endsection

