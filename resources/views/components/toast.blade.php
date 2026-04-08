@php
    $success = session('success');
    $error = session('error');
    $info = session('info');
    $warning = session('warning');

    $activeMessage = $success ?? $error ?? $info ?? $warning;
    $type = $success ? 'success' : ($error ? 'error' : ($info ? 'info' : 'warning'));

    $config = [
        'success' => [
            'icon' => 'fas fa-check-circle',
            'bg' => 'bg-emerald-50',
            'border' => 'border-emerald-100',
            'gradient' => 'from-emerald-500 to-green-600',
            'text' => 'text-emerald-900',
            'subtext' => 'text-emerald-600',
            'iconBg' => 'bg-emerald-50',
            'title' => 'Success'
        ],
        'error' => [
            'icon' => 'fas fa-exclamation-circle',
            'bg' => 'bg-rose-50',
            'border' => 'border-rose-100',
            'gradient' => 'from-rose-500 to-red-600',
            'text' => 'text-rose-900',
            'subtext' => 'text-rose-600',
            'iconBg' => 'bg-rose-50',
            'title' => 'Error'
        ],
        'info' => [
            'icon' => 'fas fa-info-circle',
            'bg' => 'bg-blue-50',
            'border' => 'border-blue-100',
            'gradient' => 'from-blue-500 to-indigo-600',
            'text' => 'text-blue-900',
            'subtext' => 'text-blue-600',
            'iconBg' => 'bg-blue-50',
            'title' => 'Information'
        ],
        'warning' => [
            'icon' => 'fas fa-exclamation-triangle',
            'bg' => 'bg-amber-50',
            'border' => 'border-amber-100',
            'gradient' => 'from-amber-500 to-yellow-600',
            'text' => 'text-amber-900',
            'subtext' => 'text-amber-600',
            'iconBg' => 'bg-amber-50',
            'title' => 'Warning'
        ],
    ];

    $c = $config[$type];
@endphp

@if($activeMessage)
<div x-data="{ show: true }" 
     x-show="show" 
     x-init="setTimeout(() => show = false, 5000)"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 transform translate-x-12 scale-95"
     x-transition:enter-end="opacity-100 transform translate-x-0 scale-100"
     x-transition:leave="transition ease-in duration-300"
     x-transition:leave-start="opacity-100 transform translate-x-0 scale-100"
     x-transition:leave-end="opacity-0 transform translate-x-12 scale-95"
     class="fixed top-8 right-8 z-[9999] w-full max-w-sm"
     x-cloak>
    <div class="absolute -inset-2 bg-gradient-to-r {{ $c['gradient'] }} rounded-3xl blur-2xl opacity-15"></div>
    <div class="relative {{ $c['bg'] }} border {{ $c['border'] }} p-5 rounded-3xl flex items-center shadow-2xl backdrop-blur-sm">
        <div class="w-12 h-12 {{ $c['iconBg'] }} rounded-2xl flex items-center justify-center mr-5 shadow-inner">
            <i class="{{ $c['icon'] }} {{ str_replace('bg-', 'text-', $c['iconBg']) }} text-xl"></i>
        </div>
        <div class="flex-1">
            <h4 class="{{ $c['text'] }} text-base font-black tracking-tight leading-none">{{ $c['title'] }}</h4>
            <p class="{{ $c['subtext'] }} text-xs font-bold mt-1.5 opacity-80 leading-snug">{{ $activeMessage }}</p>
        </div>
        <button @click="show = false" class="ml-4 w-8 h-8 rounded-full flex items-center justify-center hover:bg-black/5 transition-colors text-gray-400 hover:text-gray-600">
            <i class="fas fa-times text-xs"></i>
        </button>
    </div>
</div>
@endif
