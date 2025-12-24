@extends('layouts.school')

@section('title', 'School Banks')

@section('content')
<div class="space-y-6" x-data="schoolBankManagement">
    @if(session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg relative">
        <span class="block sm:inline">{{ session('success') }}</span>
    </div>
    @endif

    @if(session('error'))
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg relative">
        <span class="block sm:inline">{{ session('error') }}</span>
    </div>
    @endif

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">School Banks</h1>
            <p class="text-gray-600 mt-1">Manage bank accounts for your school</p>
        </div>
        <button 
            @click="openAddModal()" 
            class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 flex items-center"
        >
            <i class="fas fa-plus mr-2"></i>
            Add Bank Name
        </button>
    </div>

    @php
        $tableColumns = [
            [
                'key' => 'id',
                'label' => 'SR NO',
                'sortable' => true,
                'render' => function($row) use ($banks) {
                    static $index = 0;
                    return $banks->firstItem() + $index++;
                }
            ],
            [
                'key' => 'bank_name',
                'label' => 'BANK NAME',
                'sortable' => true,
                'render' => function($row) {
                    return '<span class="font-medium text-gray-900">' . e($row->bank_name) . '</span>';
                }
            ],
            [
                'key' => 'account_number',
                'label' => 'ACCOUNT NUMBER',
                'sortable' => true,
            ],
            [
                'key' => 'branch_name',
                'label' => 'BRANCH NAME',
                'sortable' => true,
                'render' => function($row) {
                    return $row->branch_name ?? '-';
                }
            ],
            [
                'key' => 'ifsc_code',
                'label' => 'IFSC CODE',
                'sortable' => true,
                'render' => function($row) {
                    return $row->ifsc_code ?? '-';
                }
            ],
        ];

        $tableActions = [
            [
                'type' => 'button',
                'icon' => 'fas fa-edit',
                'class' => 'text-blue-600 hover:text-blue-900',
                'title' => 'Edit',
                'onclick' => function($row) {
                    return "openEditModal(JSON.parse(atob(this.getAttribute('data-bank'))))";
                },
                'data-bank' => function($row) {
                    return base64_encode(json_encode([
                        'id' => $row->id,
                        'bank_name' => $row->bank_name,
                        'account_number' => $row->account_number,
                        'branch_name' => $row->branch_name,
                        'ifsc_code' => $row->ifsc_code,
                    ]));
                }
            ],
            [
                'type' => 'form',
                'url' => fn($row) => route('school.school-banks.destroy', $row->id),
                'method' => 'DELETE',
                'icon' => 'fas fa-trash',
                'class' => 'text-red-600 hover:text-red-900',
                'title' => 'Delete',
                'dispatch' => [
                    'event' => 'open-confirm-modal',
                    'title' => 'Delete School Bank',
                    'message' => 'Are you sure you want to delete this bank account?'
                ]
            ],
        ];
    @endphp

    <x-data-table 
        :columns="$tableColumns"
        :data="$banks"
        :actions="$tableActions"
        empty-message="No bank accounts found"
        empty-icon="fas fa-university"
    >
        School Banks List
    </x-data-table>

    <!-- Add/Edit Bank Modal -->
    <x-modal name="school-bank-modal" alpineTitle="editMode ? 'Edit School Bank' : 'Add School Bank'" maxWidth="md">
        <form :action="editMode ? `/school/school-banks/${bankId}` : '{{ route('school.school-banks.store') }}'" 
              method="POST" class="p-6 space-y-4" novalidate>
            @csrf
            <template x-if="editMode">
                @method('PUT')
            </template>
            <input type="hidden" name="bank_id" x-model="bankId">

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">Bank Name <span class="text-red-500">*</span></label>
                <input 
                    type="text" 
                    name="bank_name" 
                    x-model="formData.bank_name"
                    placeholder="Enter Bank Name"
                    class="w-full px-4 py-2 border @error('bank_name') border-red-500 @else border-gray-300 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all"
                >
                @error('bank_name')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">Account Number <span class="text-red-500">*</span></label>
                <input 
                    type="text" 
                    name="account_number" 
                    x-model="formData.account_number"
                    placeholder="Enter Account Number"
                    class="w-full px-4 py-2 border @error('account_number') border-red-500 @else border-gray-300 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all"
                >
                @error('account_number')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">Branch Name</label>
                <input 
                    type="text" 
                    name="branch_name" 
                    x-model="formData.branch_name"
                    placeholder="Enter Branch Name"
                    class="w-full px-4 py-2 border @error('branch_name') border-red-500 @else border-gray-300 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all"
                >
                @error('branch_name')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">IFSC Code</label>
                <input 
                    type="text" 
                    name="ifsc_code" 
                    x-model="formData.ifsc_code"
                    placeholder="Enter IFSC Code"
                    class="w-full px-4 py-2 border @error('ifsc_code') border-red-500 @else border-gray-300 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all"
                >
                @error('ifsc_code')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-center gap-4 mt-8">
                <button 
                    type="button" 
                    @click="closeModal()"
                    class="px-8 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors font-semibold"
                >
                    Close
                </button>
                <button 
                    type="submit"
                    class="px-8 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-semibold shadow-md"
                >
                    Submit
                </button>
            </div>
        </form>
    </x-modal>
</div>

<!-- Confirmation Modal -->
<x-confirm-modal />

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('schoolBankManagement', () => ({
        editMode: false,
        bankId: null,
        formData: {
            bank_name: '',
            account_number: '',
            branch_name: '',
            ifsc_code: ''
        },

        init() {
            @if($errors->any())
                this.editMode = {{ old('_method') === 'PUT' ? 'true' : 'false' }};
                this.bankId = '{{ old('bank_id') }}';
                this.formData = {
                    bank_name: '{{ old('bank_name') }}',
                    account_number: '{{ old('account_number') }}',
                    branch_name: '{{ old('branch_name') }}',
                    ifsc_code: '{{ old('ifsc_code') }}'
                };
                this.$nextTick(() => {
                    this.$dispatch('open-modal', 'school-bank-modal');
                });
            @endif
        },

        openAddModal() {
            this.editMode = false;
            this.bankId = null;
            this.formData = { bank_name: '', account_number: '', branch_name: '', ifsc_code: '' };
            this.$dispatch('open-modal', 'school-bank-modal');
        },
        
        openEditModal(bank) {
            this.editMode = true;
            this.bankId = bank.id;
            this.formData = {
                bank_name: bank.bank_name,
                account_number: bank.account_number,
                branch_name: bank.branch_name || '',
                ifsc_code: bank.ifsc_code || ''
            };
            this.$dispatch('open-modal', 'school-bank-modal');
        },

        closeModal() {
            this.$dispatch('close-modal', 'school-bank-modal');
        }
    }));
});

// Global function for table actions
function openEditModal(bank) {
    const component = Alpine.$data(document.querySelector('[x-data*="schoolBankManagement"]'));
    if (component) {
        component.openEditModal(bank);
    }
}
</script>
@endpush
@endsection
