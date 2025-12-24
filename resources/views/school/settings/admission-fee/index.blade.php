@extends('layouts.school')

@section('title', 'Admission Fee')

@section('content')
<div x-data="admissionFeeManager()">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Admission Fee</h1>
        <button @click="openAddModal()" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors flex items-center shadow-sm">
            <i class="fas fa-plus mr-2"></i> ADD
        </button>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg relative mb-6 shadow-sm" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    <!-- Table -->
    <x-data-table 
        :columns="[
            ['key' => 'id', 'label' => 'SR NO', 'render' => fn($row, $index) => $fees->firstItem() + $index],
            ['key' => 'class_name', 'label' => 'CLASS', 'render' => fn($row) => $row->class->name ?? 'N/A'],
            ['key' => 'amount', 'label' => 'FEE', 'render' => fn($row) => number_format($row->amount, 2)],
            ['key' => 'created_at', 'label' => 'DATE', 'render' => fn($row) => $row->created_at->format('M d, Y, g:i a')],
        ]"
        :data="$fees"
        :actions="[
            [
                'type' => 'button',
                'icon' => 'fas fa-edit',
                'class' => 'text-blue-600 hover:text-blue-900',
                'title' => 'Edit',
                'onClick' => 'openEditModal(row)'
            ],
            [
                'type' => 'form',
                'icon' => 'fas fa-trash',
                'class' => 'text-red-600 hover:text-red-900',
                'title' => 'Delete',
                'action' => fn($row) => route('school.settings.admission-fee.destroy', $row->id),
                'confirm' => 'Are you sure you want to delete this admission fee?'
            ]
        ]"
    >
        Admission Fee List
    </x-data-table>

    <!-- Modal -->
    <x-modal name="admission-fee-modal" alpineTitle="editMode ? 'Edit Admission Fee' : 'Add Admission Fee'">
        <form :action="editMode ? '{{ url('school/settings/admission-fee') }}/' + feeId : '{{ route('school.settings.admission-fee.store') }}'" 
              method="POST" 
              class="p-6 space-y-4">
            @csrf
            <template x-if="editMode">
                <input type="hidden" name="_method" value="PUT">
            </template>
            <input type="hidden" name="fee_id" x-model="feeId">
            
            <div>
                <label for="class_id" class="block text-sm font-medium text-gray-700 mb-1">Class</label>
                <select name="class_id" id="class_id" class="w-full border @error('class_id') border-red-500 @else border-gray-300 @enderror rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all">
                    <option value="">Select Class</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}" {{ old('class_id') == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                    @endforeach
                </select>
                @error('class_id')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="amount" class="block text-sm font-medium text-gray-700 mb-1">Fee</label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500">₹</span>
                    <input type="number" name="amount" id="amount" step="0.01" x-model="formData.amount" class="w-full border @error('amount') border-red-500 @else border-gray-300 @enderror rounded-lg pl-8 pr-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all" placeholder="0.00">
                </div>
                @error('amount')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex justify-end gap-3 pt-4">
                <button type="button" @click="closeModal()" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors font-medium">
                    Close
                </button>
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium shadow-md">
                    Submit
                </button>
            </div>
        </form>
    </x-modal>
</div>

@push('scripts')
<script>
function admissionFeeManager() {
    return {
        showModal: false,
        editMode: false,
        feeId: null,
        formData: {
            class_id: '{{ old('class_id') }}',
            amount: '{{ old('amount') }}'
        },

        init() {
            @if($errors->any())
                this.editMode = {{ old('_method') === 'PUT' ? 'true' : 'false' }};
                this.feeId = '{{ old('fee_id') }}';
                this.$nextTick(() => {
                    this.$dispatch('open-modal', 'admission-fee-modal');
                    this.updateSelect2('{{ old('class_id') }}');
                });
            @endif
        },

        openAddModal() {
            this.editMode = false;
            this.feeId = null;
            this.formData = {
                class_id: '',
                amount: ''
            };
            this.$dispatch('open-modal', 'admission-fee-modal');
            this.updateSelect2();
        },

        openEditModal(fee) {
            console.log('Opening edit modal for fee:', fee);
            this.editMode = true;
            this.feeId = fee.id;
            this.formData = {
                class_id: fee.class_id,
                amount: fee.amount
            };
            this.$dispatch('open-modal', 'admission-fee-modal');
            this.updateSelect2();
        },

        closeModal() {
            this.$dispatch('close-modal', 'admission-fee-modal');
        },

        updateSelect2() {
            this.$nextTick(() => {
                if (typeof $ !== 'undefined') {
                    const select = $('select[name="class_id"]');
                    if (select.length) {
                        select.val(this.formData.class_id).trigger('change');
                    }
                }
            });
            
            // Backup with setTimeout for slower rendering
            setTimeout(() => {
                if (typeof $ !== 'undefined') {
                    const select = $('select[name="class_id"]');
                    if (select.length) {
                        select.val(this.formData.class_id).trigger('change');
                    }
                }
            }, 100);
        }
    }
}
</script>
@endpush
@endsection
