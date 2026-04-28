@extends('layouts.school')

@section('title', 'Receipt Notes - ' . $school->name)

@section('content')
<div class="w-full space-y-6 animate-in fade-in duration-500"
     x-data="receiptNoteSettings()">

    {{-- Page Header --}}
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
                            <span>Receipt Notes</span>
                        </div>
                    </li>
                </ol>
            </nav>
            <h1 class="text-3xl font-black text-gray-900 dark:text-white tracking-tight">Receipt Notes</h1>
            <p class="text-base text-gray-500 mt-1 flex items-center font-medium">
                <span class="w-2 h-2 rounded-full bg-indigo-500 mr-2"></span>
                Footer text printed on each receipt type
            </p>
        </div>
    </div>

    <form @submit.prevent="submitForm()" novalidate class="space-y-6">
        @csrf

        {{-- Three note cards --}}
        @php
            $notes = [
                [
                    'key'         => 'registration_receipt_note',
                    'label'       => 'Registration Receipt',
                    'description' => 'Printed on registration fee receipts.',
                    'icon'        => 'fas fa-user-plus',
                    'color'       => 'indigo',
                ],
                [
                    'key'         => 'admission_receipt_note',
                    'label'       => 'Admission Receipt',
                    'description' => 'Printed on admission fee receipts.',
                    'icon'        => 'fas fa-graduation-cap',
                    'color'       => 'blue',
                ],
                [
                    'key'         => 'fee_receipt_note',
                    'label'       => 'Tuition Fee Receipt',
                    'description' => 'Printed on regular tuition fee receipts.',
                    'icon'        => 'fas fa-wallet',
                    'color'       => 'teal',
                ],
            ];
        @endphp

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
            @foreach($notes as $note)
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden flex flex-col">
                <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0
                        @if($note['color'] === 'indigo') bg-indigo-50 @elseif($note['color'] === 'blue') bg-blue-50 @else bg-teal-50 @endif">
                        <i class="{{ $note['icon'] }} text-sm
                            @if($note['color'] === 'indigo') text-indigo-600 @elseif($note['color'] === 'blue') text-blue-600 @else text-teal-600 @endif"></i>
                    </div>
                    <div>
                        <h2 class="text-sm font-bold text-gray-900 dark:text-white">{{ $note['label'] }}</h2>
                        <p class="text-xs text-gray-400 mt-0.5">{{ $note['description'] }}</p>
                    </div>
                </div>

                <div class="p-5 flex-1 flex flex-col gap-2">
                    <textarea
                        name="{{ $note['key'] }}"
                        rows="5"
                        maxlength="1000"
                        x-model="formData.{{ $note['key'] }}"
                        @input="clearError('{{ $note['key'] }}')"
                        placeholder="e.g. This receipt is computer generated..."
                        class="w-full flex-1 px-4 py-3 bg-gray-50 dark:bg-gray-700 border rounded-xl text-sm text-gray-800 dark:text-gray-100 leading-relaxed focus:bg-white dark:focus:bg-gray-600 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all resize-none"
                        :class="errors.{{ $note['key'] }} ? 'border-red-500 bg-red-50' : 'border-gray-200'"></textarea>

                    <div class="flex items-center justify-between">
                        <template x-if="errors.{{ $note['key'] }}">
                            <p class="text-xs text-red-500 flex items-center gap-1">
                                <i class="fas fa-exclamation-circle"></i>
                                <span x-text="errors.{{ $note['key'] }}[0]"></span>
                            </p>
                        </template>
                        <span x-show="!errors.{{ $note['key'] }}"></span>
                        <span class="text-xs text-gray-400 ml-auto"
                              :class="formData.{{ $note['key'] }}.length > 950 ? 'text-amber-500 font-semibold' : ''">
                            <span x-text="formData.{{ $note['key'] }}.length"></span>/1000
                        </span>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Save --}}
        <div class="flex items-center justify-end pt-1">
            <button type="submit" :disabled="submitting"
                    class="inline-flex items-center gap-2 px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 disabled:opacity-60 text-white text-sm font-semibold rounded-xl transition-all shadow-sm shadow-indigo-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                <template x-if="submitting">
                    <span class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                </template>
                <template x-if="!submitting">
                    <i class="fas fa-save text-xs"></i>
                </template>
                <span x-text="submitting ? 'Saving...' : 'Save Notes'"></span>
            </button>
        </div>

    </form>
</div>

@push('scripts')
<script>
function receiptNoteSettings() {
    return {
        submitting: false,
        errors: {},
        formData: {
            registration_receipt_note: @js(old('registration_receipt_note', $settings['registration_receipt_note'] ?? '')),
            admission_receipt_note:    @js(old('admission_receipt_note', $settings['admission_receipt_note'] ?? '')),
            fee_receipt_note:          @js(old('fee_receipt_note', $settings['fee_receipt_note'] ?? '')),
        },

        clearError(field) {
            if (this.errors && this.errors[field]) {
                const e = { ...this.errors };
                delete e[field];
                this.errors = e;
            }
        },

        async submitForm() {
            if (this.submitting) return;
            this.submitting = true;
            this.errors = {};

            try {
                const response = await fetch('{{ route('school.settings.receipt-note.update') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ ...this.formData, _method: 'PUT' })
                });

                const result = await response.json();

                if (response.ok) {
                    window.dispatchEvent(new CustomEvent('show-toast', {
                        detail: { message: result.message || 'Receipt notes saved.', type: 'success' }
                    }));
                } else if (response.status === 422) {
                    this.errors = result.errors || {};
                } else {
                    throw new Error(result.message || 'Something went wrong.');
                }
            } catch (error) {
                window.dispatchEvent(new CustomEvent('show-toast', {
                    detail: { message: error.message, type: 'error' }
                }));
            } finally {
                this.submitting = false;
            }
        }
    }
}
</script>
@endpush
@endsection
