{{-- Step 2: Student Details --}}
@php use App\Constants\Gender; @endphp

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">

    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">First Name <span class="text-red-500">*</span></label>
        <input type="text" name="first_name" x-model="formData.first_name" @input="clearError('first_name')" placeholder="First name"
               :class="errors.first_name ? 'border-red-500' : 'border-gray-300 dark:border-gray-600'"
               class="w-full px-4 py-2.5 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
        <template x-if="errors.first_name"><template x-if="errors.first_name[0]"><p class="modal-error-message" x-text="errors.first_name[0]"></p></template></template>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Middle Name</label>
        <input type="text" name="middle_name" x-model="formData.middle_name" placeholder="Middle name"
               class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Last Name <span class="text-red-500">*</span></label>
        <input type="text" name="last_name" x-model="formData.last_name" @input="clearError('last_name')" placeholder="Last name"
               :class="errors.last_name ? 'border-red-500' : 'border-gray-300 dark:border-gray-600'"
               class="w-full px-4 py-2.5 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
        <template x-if="errors.last_name"><template x-if="errors.last_name[0]"><p class="modal-error-message" x-text="errors.last_name[0]"></p></template></template>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Gender <span class="text-red-500">*</span></label>
        <select name="gender" x-model="formData.gender" @change="clearError('gender')"
                :class="errors.gender ? 'border-red-500' : 'border-gray-300 dark:border-gray-600'"
                class="no-select2 w-full px-4 py-2.5 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
            <option value="">Choose Gender</option>
            @foreach(\App\Enums\Gender::options() as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
            @endforeach
        </select>
        <template x-if="errors.gender"><template x-if="errors.gender[0]"><p class="modal-error-message" x-text="errors.gender[0]"></p></template></template>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Date of Birth</label>
        <input type="date" name="dob" x-model="formData.dob"
               class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Mobile No <span class="text-red-500">*</span></label>
        <input type="tel" name="mobile_no" x-model="formData.mobile_no" @input="clearError('mobile_no')"
               placeholder="Mobile number" inputmode="numeric" oninput="this.value=this.value.replace(/[^0-9]/g,'')"
               :class="errors.mobile_no ? 'border-red-500' : 'border-gray-300 dark:border-gray-600'"
               class="w-full px-4 py-2.5 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
        <template x-if="errors.mobile_no"><template x-if="errors.mobile_no[0]"><p class="modal-error-message" x-text="errors.mobile_no[0]"></p></template></template>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Email</label>
        <input type="email" name="email" x-model="formData.email" placeholder="Email address"
               class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Aadhaar No</label>
        <input type="text" name="aadhaar_no" x-model="formData.aadhaar_no" placeholder="12-digit Aadhaar"
               inputmode="numeric" maxlength="12" pattern="[0-9]{12}" oninput="this.value=this.value.replace(/[^0-9]/g,'')"
               class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Blood Group</label>
        <select name="blood_group_id" x-model="formData.blood_group_id"
                class="no-select2 w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
            <option value="">Choose Blood Group</option>
            @foreach($bloodGroups as $group)
                <option value="{{ $group->id }}">{{ $group->name }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Place of Birth</label>
        <input type="text" name="place_of_birth" x-model="formData.place_of_birth" placeholder="City / town"
               class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Nationality</label>
        <select name="nationality" x-model="formData.nationality"
                class="no-select2 w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
            <option value="Indian">Indian</option>
            <option value="Nepal">Nepal</option>
            <option value="Pakistan">Pakistan</option>
        </select>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Religion</label>
        <select name="religion_id" x-model="formData.religion_id"
                class="no-select2 w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
            <option value="">Choose Religion</option>
            @foreach($religions as $rel)
                <option value="{{ $rel->id }}">{{ $rel->name }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Category</label>
        <select name="category_id" x-model="formData.category_id"
                class="no-select2 w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
            <option value="">Choose Category</option>
            @foreach($categories as $cat)
                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Student Type</label>
        <select name="student_type_id" x-model="formData.student_type_id"
                class="no-select2 w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
            <option value="">Choose Student Type</option>
            @foreach($studentTypes as $type)
                <option value="{{ $type->id }}">{{ $type->name }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Boarding Type</label>
        <select name="boarding_type_id" x-model="formData.boarding_type_id"
                class="no-select2 w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
            <option value="">Choose Boarding Type</option>
            @foreach($boardingTypes as $type)
                <option value="{{ $type->id }}">{{ $type->name }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Corresponding Relative</label>
        <select name="corresponding_relative_id" x-model="formData.corresponding_relative_id"
                class="no-select2 w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
            <option value="">Choose Relative</option>
            @foreach($correspondingRelatives as $rel)
                <option value="{{ $rel->id }}">{{ $rel->name }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Transport Required</label>
        <select name="is_transport_required" x-model="formData.is_transport_required"
                class="no-select2 w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
            <option value="0">No</option>
            <option value="1">Yes</option>
        </select>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">No. of Brothers</label>
        <input type="number" min="0" step="1" name="number_of_brothers" x-model="formData.number_of_brothers"
               class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">No. of Sisters</label>
        <input type="number" min="0" step="1" name="number_of_sisters" x-model="formData.number_of_sisters"
               class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Single Parent</label>
        <select name="is_single_parent" x-model="formData.is_single_parent"
                class="no-select2 w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
            <option value="0">No</option>
            <option value="1">Yes</option>
        </select>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Mother Tongue</label>
        <input type="text" name="mother_tongue" x-model="formData.mother_tongue" placeholder="Mother tongue"
               class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Special Needs</label>
        <input type="text" name="special_needs" x-model="formData.special_needs" placeholder="Any special needs"
               class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Remarks</label>
        <input type="text" name="remarks" x-model="formData.remarks" placeholder="Any remarks"
               class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
    </div>

</div>
