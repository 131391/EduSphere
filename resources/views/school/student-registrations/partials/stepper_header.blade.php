{{-- Shared stepper header: progress bar + step indicators --}}
{{-- Variables expected: none (reads currentStep from parent Alpine scope) --}}

@php
$regSteps = [
    ['icon' => 'fa-file-alt',      'label' => 'Registration'],
    ['icon' => 'fa-user',          'label' => 'Student'],
    ['icon' => 'fa-users',         'label' => 'Parents'],
    ['icon' => 'fa-map-marker-alt','label' => 'Address'],
    ['icon' => 'fa-camera',        'label' => 'Photos'],
];
$totalSteps = count($regSteps);
@endphp

{{-- Progress bar --}}
<div class="mb-6">
    <div class="flex items-center justify-between mb-2">
        <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">
            Step <span x-text="currentStep">1</span> of {{ $totalSteps }}
        </span>
        <span class="text-xs font-semibold text-teal-600" x-text="stepLabels[currentStep - 1]">{{ $regSteps[0]['label'] }}</span>
    </div>
    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-1.5">
        <div class="bg-gradient-to-r from-teal-500 to-emerald-500 h-1.5 rounded-full transition-all duration-500"
             style="width: {{ 100 / $totalSteps }}%;"
             :style="`width: ${(currentStep / {{ $totalSteps }}) * 100}%`"></div>
    </div>
</div>

{{-- Step indicators --}}
<div class="mb-8">
    <div class="flex items-center gap-0">
        @foreach($regSteps as $i => $step)
        @php $n = $i + 1; @endphp
        <div class="flex items-center {{ $i < $totalSteps - 1 ? 'flex-1' : '' }}">
            <button type="button" @click="goToStep({{ $n }})"
                    class="flex flex-col items-center gap-1 group focus:outline-none">
                <div class="w-9 h-9 rounded-full flex items-center justify-center text-sm font-bold transition-all duration-200 border-2 {{ $n === 1 ? 'bg-teal-600 border-teal-600 text-white shadow-lg shadow-teal-200 dark:shadow-none' : 'bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-600 text-gray-400' }}"
                     :class="currentStep === {{ $n }}
                        ? 'bg-teal-600 border-teal-600 text-white shadow-lg shadow-teal-200 dark:shadow-none'
                        : currentStep > {{ $n }}
                            ? 'bg-emerald-500 border-emerald-500 text-white'
                            : 'bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-600 text-gray-400 group-hover:border-teal-400'">
                    <i class="fas fa-check text-xs hidden" :class="currentStep > {{ $n }} ? '!inline-block' : 'hidden'"></i>
                    <i class="fas {{ $step['icon'] }} text-xs" :class="currentStep > {{ $n }} ? 'hidden' : '!inline-block'"></i>
                </div>
                <span class="text-[10px] font-semibold hidden sm:block transition-colors {{ $n === 1 ? 'text-teal-600' : 'text-gray-400' }}"
                      :class="currentStep === {{ $n }} ? 'text-teal-600' : currentStep > {{ $n }} ? 'text-emerald-500' : 'text-gray-400'">
                    {{ $step['label'] }}
                </span>
            </button>
            @if($i < $totalSteps - 1)
            <div class="flex-1 h-0.5 mx-2 rounded transition-all duration-500 bg-gray-200 dark:bg-gray-700"
                 :class="currentStep > {{ $n }} ? 'bg-emerald-400' : 'bg-gray-200 dark:bg-gray-700'"></div>
            @endif
        </div>
        @endforeach
    </div>
</div>
