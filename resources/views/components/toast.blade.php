@php
    $success = session('success');
    $error = session('error');
    $info = session('info');
    $warning = session('warning');

    $activeMessage = $success ?? $error ?? $info ?? $warning;
    $type = $success ? 'success' : ($error ? 'error' : ($info ? 'info' : 'warning'));

    $config = [
        'success' => [
            'icon' => 'fas fa-check',
            'bg' => 'bg-[#6ee7b7]',
            'edge' => 'border-[#059669]',
            'iconContainer' => 'bg-[#064e3b]',
            'iconColor' => 'text-white',
            'titleColor' => 'text-[#064e3b]',
            'textColor' => 'text-[#065f46]',
            'title' => 'Action Successful'
        ],
        'error' => [
            'icon' => 'fas fa-exclamation',
            'bg' => 'bg-[#fecaca]',
            'edge' => 'border-[#dc2626]',
            'iconContainer' => 'bg-[#b91c1c]',
            'iconColor' => 'text-white',
            'titleColor' => 'text-[#7f1d1d]',
            'textColor' => 'text-[#991b1b]',
            'title' => 'System Error'
        ],
        'info' => [
            'icon' => 'fas fa-info',
            'bg' => 'bg-[#93c5fd]',
            'edge' => 'border-[#2563eb]',
            'iconContainer' => 'bg-[#1e3a8a]',
            'iconColor' => 'text-white',
            'titleColor' => 'text-[#1e3a8a]',
            'textColor' => 'text-[#1e40af]',
            'title' => 'Information'
        ],
        'warning' => [
            'icon' => 'fas fa-exclamation-triangle',
            'bg' => 'bg-[#fcd34d]',
            'edge' => 'border-[#d97706]',
            'iconContainer' => 'bg-[#78350f]',
            'iconColor' => 'text-white',
            'titleColor' => 'text-[#78350f]',
            'textColor' => 'text-[#92400e]',
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
    
    <div class="relative {{ $c['bg'] }} {{ $c['edge'] }} border-l-[10px] border-b-[3px] p-5 rounded-2xl flex items-center shadow-xl">
        {{-- Icon Section --}}
        <div class="w-12 h-12 {{ $c['iconContainer'] }} rounded-full flex items-center justify-center mr-4 shrink-0 shadow-sm">
            <i class="{{ $c['icon'] }} {{ $c['iconColor'] }} text-lg"></i>
        </div>

        {{-- Content --}}
        <div class="flex-1 pr-6">
            <h4 class="{{ $c['titleColor'] }} text-base font-extrabold tracking-tight leading-none mb-1.5">{{ $c['title'] }}</h4>
            <p class="{{ $c['textColor'] }} text-xs font-bold leading-snug opacity-90">{{ $activeMessage }}</p>
        </div>

        {{-- Close Button --}}
        <button @click="show = false" class="absolute top-4 right-4 text-black/20 hover:text-black/40 transition-colors">
            <i class="fas fa-times text-xs"></i>
        </button>
    </div>
</div>
@endif
