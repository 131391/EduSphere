@extends('layouts.school')

@section('title', 'Bulk Fee Generation')

@section('content')
<div x-data="feeGenerator()">
    <!-- Header Section -->
    <div class="mb-8 flex flex-col sm:flex-row items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <a href="{{ route('school.fees.index') }}" class="w-10 h-10 rounded-xl bg-white border border-gray-200 flex items-center justify-center text-gray-400 hover:text-emerald-600 hover:border-emerald-200 transition-all shadow-sm group">
                <i class="fas fa-chevron-left group-hover:-translate-x-0.5 transition-transform"></i>
            </a>
            <div>
                <h1 class="text-2xl font-black text-gray-800 tracking-tight">Bulk Fee Generation</h1>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-0.5 ml-0.5">Automated Financial Allocation Engine</p>
            </div>
        </div>
    </div>

    <form @submit.prevent="generateFees" method="POST" class="space-y-8">
        @csrf
        
        <!-- Configuration Card -->
        <div class="bg-white rounded-[2rem] shadow-xl shadow-gray-200/50 border border-gray-100 overflow-hidden">
            <div class="bg-emerald-600 px-10 py-4 flex items-center justify-between">
                <h3 class="text-xs font-black text-white uppercase tracking-[0.2em]">Mandatory Parameters</h3>
                <i class="fas fa-cogs text-white/30 text-lg"></i>
            </div>
            
            <div class="p-10 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Class Selection -->
                <div>
                    <label class="block text-sm font-semibold text-gray-800 mb-2 ml-1">Target Class <span class="text-red-500">*</span></label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400 group-focus-within:text-emerald-600 transition-colors">
                            <i class="fas fa-school text-sm"></i>
                        </div>
                        <select name="class_id" x-model="formData.class_id" required 
                                @change="if(errors.class_id) delete errors.class_id"
                                class="w-full pl-10 pr-4 py-3 bg-gray-50 border border-gray-100 rounded-2xl focus:outline-none focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all font-medium text-gray-700 appearance-none shadow-sm"
                                :class="{'border-red-500 ring-red-500/10': errors.class_id}">
                            <option value="">-- Choose Class --</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}">{{ $class->name }}</option>
                            @endforeach
                        </select>
                        <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-gray-300">
                            <i class="fas fa-chevron-down text-[10px]"></i>
                        </div>
                    </div>
                    <template x-if="errors.class_id">
                        <p class="text-[10px] font-bold text-red-500 mt-1 ml-1" x-text="errors.class_id[0]"></p>
                    </template>
                </div>

                <!-- Academic Year -->
                <div>
                    <label class="block text-sm font-semibold text-gray-800 mb-2 ml-1">Academic Calendar <span class="text-red-500">*</span></label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400 group-focus-within:text-emerald-600 transition-colors">
                            <i class="fas fa-calendar-check text-sm"></i>
                        </div>
                        <select name="academic_year_id" x-model="formData.academic_year_id" required class="w-full pl-10 pr-4 py-3 bg-gray-50 border border-gray-100 rounded-2xl focus:outline-none focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all font-medium text-gray-700 appearance-none shadow-sm">
                            @foreach($academicYears as $year)
                                <option value="{{ $year->id }}">{{ $year->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Fee Type -->
                <div>
                    <label class="block text-sm font-semibold text-gray-800 mb-2 ml-1">Fee Category <span class="text-red-500">*</span></label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400 group-focus-within:text-emerald-600 transition-colors">
                            <i class="fas fa-tag text-sm"></i>
                        </div>
                        <select name="fee_type_id" x-model="formData.fee_type_id" required 
                                @change="if(errors.fee_type_id) delete errors.fee_type_id"
                                class="w-full pl-10 pr-4 py-3 bg-gray-50 border border-gray-100 rounded-2xl focus:outline-none focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all font-medium text-gray-700 appearance-none shadow-sm"
                                :class="{'border-red-500 ring-red-500/10': errors.fee_type_id}">
                            <option value="">-- Choose Type --</option>
                            @foreach($feeTypes as $type)
                                <option value="{{ $type->id }}">{{ $type->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <template x-if="errors.fee_type_id">
                        <p class="text-[10px] font-bold text-red-500 mt-1 ml-1" x-text="errors.fee_type_id[0]"></p>
                    </template>
                </div>

                <!-- Fee Period -->
                <div>
                    <label class="block text-sm font-semibold text-gray-800 mb-2 ml-1">Billing Period <span class="text-red-500">*</span></label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400 group-focus-within:text-emerald-600 transition-colors">
                            <i class="fas fa-clock text-sm"></i>
                        </div>
                        <input type="text" name="fee_period" x-model="formData.fee_period" placeholder="e.g. {{ date('F Y') }}" required class="w-full pl-10 pr-4 py-3 bg-gray-50 border border-gray-100 rounded-2xl focus:outline-none focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all font-bold text-gray-700 shadow-sm">
                    </div>
                </div>

                <!-- Due Date -->
                <div>
                    <label class="block text-sm font-semibold text-gray-800 mb-2 ml-1">Due Date <span class="text-red-500">*</span></label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400 group-focus-within:text-emerald-600 transition-colors">
                            <i class="fas fa-hourglass-half text-sm"></i>
                        </div>
                        <input type="date" name="due_date" x-model="formData.due_date" required class="w-full pl-10 pr-4 py-3 bg-gray-50 border border-gray-100 rounded-2xl focus:outline-none focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all font-bold text-gray-700 shadow-sm">
                    </div>
                </div>
            </div>
        </div>

        <!-- Selection Card -->
        <div class="bg-white rounded-[2rem] shadow-xl shadow-gray-200/50 border border-gray-100 overflow-hidden">
            <div class="bg-indigo-600 px-10 py-5 flex items-center justify-between">
                <div>
                    <h3 class="text-xs font-black text-white uppercase tracking-[0.2em]">Select Applicable Heads</h3>
                    <p class="text-[10px] text-white/60 font-medium uppercase mt-1">Check all fee components to include in this generation</p>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-[10px] font-bold text-white/50 uppercase tracking-widest">Select All</span>
                    <input type="checkbox" @change="toggleAll" x-model="allSelected" class="rounded-lg w-5 h-5 border-transparent text-indigo-400 focus:ring-indigo-500/50">
                </div>
            </div>
            
            <div class="p-10">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    @foreach($feeNames as $name)
                    <label class="relative group cursor-pointer">
                        <input type="checkbox" name="fee_name_ids[]" value="{{ $name->id }}" x-model="formData.fee_name_ids" 
                               class="peer hidden">
                        <div class="p-5 flex items-center gap-4 bg-gray-50 border border-gray-100 rounded-[1.5rem] transition-all duration-300 peer-checked:bg-indigo-50 peer-checked:border-indigo-200 peer-checked:ring-4 peer-checked:ring-indigo-500/5 group-hover:bg-white group-hover:shadow-lg group-hover:shadow-gray-100 group-hover:scale-[1.02]">
                            <div class="w-10 h-10 rounded-2xl bg-white border border-gray-100 flex items-center justify-center text-gray-400 transition-all peer-checked:bg-indigo-600 peer-checked:text-white peer-checked:border-indigo-600 shadow-sm">
                                <i class="fas fa-receipt text-sm"></i>
                            </div>
                            <div>
                                <div class="text-sm font-bold text-gray-700 transition-colors peer-checked:text-indigo-900">{{ $name->name }}</div>
                                <div class="text-[9px] font-bold text-gray-400 uppercase tracking-tighter">Applicable Head</div>
                            </div>
                        </div>
                    </label>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-6 pt-4">
            <p class="text-xs text-center text-gray-400 italic font-medium">This process will create records for all active students in the target class.</p>
            <button 
                type="submit" 
                :disabled="submitting || formData.fee_name_ids.length === 0"
                class="px-12 py-4 bg-gradient-to-r from-emerald-600 to-teal-700 text-white font-black rounded-2xl shadow-xl shadow-emerald-100 hover:from-emerald-700 hover:to-teal-800 transition-all active:scale-95 disabled:opacity-30 flex items-center gap-3 min-w-[240px] justify-center text-sm uppercase tracking-widest"
            >
                <template x-if="submitting">
                    <span class="w-5 h-5 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                </template>
                <i x-show="!submitting" class="fas fa-magic"></i>
                <span x-text="submitting ? 'Processing...' : 'Generate and Apply'"></span>
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('feeGenerator', () => ({
        submitting: false,
        allSelected: false,
        errors: {},
        formData: {
            class_id: '{{ old('class_id') }}',
            academic_year_id: '{{ $academicYears->where('is_active', true)->first()->id ?? '' }}',
            fee_type_id: '',
            fee_period: '{{ date('F Y') }}',
            due_date: '{{ date('Y-m-d', strtotime('+10 days')) }}',
            fee_name_ids: []
        },

        async generateFees() {
            if (!this.formData.class_id || this.formData.fee_name_ids.length === 0) return;
            
            this.submitting = true;
            this.errors = {};
            try {
                const response = await fetch('{{ route('school.fees.store') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(this.formData)
                });
                
                const result = await response.json();
                
                if (response.ok) {
                    if (window.Toast) {
                        window.Toast.fire({ icon: 'success', title: result.message });
                    }
                    setTimeout(() => window.location.href = '{{ route('school.fees.index') }}', 1000);
                } else if (response.status === 422) {
                    this.errors = result.errors || {};
                    if (window.Toast) {
                        window.Toast.fire({ icon: 'error', title: 'Please correct the highlighted errors.' });
                    }
                } else {
                    throw new Error(result.message || 'Generation failed');
                }
            } catch (err) {
                if (window.Toast) {
                    window.Toast.fire({ icon: 'error', title: err.message });
                }
            } finally {
                this.submitting = false;
            }
        },

        toggleAll() {
            if (this.allSelected) {
                this.formData.fee_name_ids = [@foreach($feeNames as $n)'{{$n->id}}',@endforeach];
            } else {
                this.formData.fee_name_ids = [];
            }
        }
    }));
});
</script>
@endpush
@endsection
