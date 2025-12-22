<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="bg-teal-500 px-6 py-3">
        <h3 class="text-white font-semibold">Personal Information</h3>
    </div>
    <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">First Name <span class="text-red-500">*</span></label>
            <input type="text" name="first_name" class="w-full border-gray-300 rounded-md shadow-sm focus:border-teal-500 focus:ring-teal-500" placeholder="Enter First Name" required>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Middle Name</label>
            <input type="text" name="middle_name" class="w-full border-gray-300 rounded-md shadow-sm focus:border-teal-500 focus:ring-teal-500" placeholder="Enter Middle Name">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Last Name</label>
            <input type="text" name="last_name" class="w-full border-gray-300 rounded-md shadow-sm focus:border-teal-500 focus:ring-teal-500" placeholder="Enter Last Name">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Gender <span class="text-red-500">*</span></label>
            <select name="gender" class="w-full border-gray-300 rounded-md shadow-sm focus:border-teal-500 focus:ring-teal-500" required>
                <option value="">Choose Gender</option>
                <option value="male">Male</option>
                <option value="female">Female</option>
                <option value="other">Other</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Date of Birth <span class="text-red-500">*</span></label>
            <input type="date" name="date_of_birth" class="w-full border-gray-300 rounded-md shadow-sm focus:border-teal-500 focus:ring-teal-500" required>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Email Id</label>
            <input type="email" name="email" class="w-full border-gray-300 rounded-md shadow-sm focus:border-teal-500 focus:ring-teal-500" placeholder="Enter Email Id">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Student Type</label>
            <select name="student_type" class="w-full border-gray-300 rounded-md shadow-sm focus:border-teal-500 focus:ring-teal-500">
                <option value="regular">Regular</option>
                <option value="boarding">Boarding</option>
                <option value="day_scholar">Day Scholar</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Mobile No <span class="text-red-500">*</span></label>
            <input type="text" name="phone" class="w-full border-gray-300 rounded-md shadow-sm focus:border-teal-500 focus:ring-teal-500" placeholder="Enter Mobile No" required>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Blood Group</label>
            <select name="blood_group" class="w-full border-gray-300 rounded-md shadow-sm focus:border-teal-500 focus:ring-teal-500">
                <option value="">Choose Blood Group</option>
                <option value="A+">A+</option>
                <option value="A-">A-</option>
                <option value="B+">B+</option>
                <option value="B-">B-</option>
                <option value="O+">O+</option>
                <option value="O-">O-</option>
                <option value="AB+">AB+</option>
                <option value="AB-">AB-</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">DOB Certificate No</label>
            <input type="text" name="dob_certificate_no" class="w-full border-gray-300 rounded-md shadow-sm focus:border-teal-500 focus:ring-teal-500" placeholder="Enter DOB Certificate No">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Place of Birth</label>
            <input type="text" name="place_of_birth" class="w-full border-gray-300 rounded-md shadow-sm focus:border-teal-500 focus:ring-teal-500" placeholder="Enter Place of Birth">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Aadhaar No</label>
            <input type="text" name="aadhaar_no" class="w-full border-gray-300 rounded-md shadow-sm focus:border-teal-500 focus:ring-teal-500" placeholder="Enter Aadhaar No">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nationality</label>
            <input type="text" name="nationality" value="India" class="w-full border-gray-300 rounded-md shadow-sm focus:border-teal-500 focus:ring-teal-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Religion</label>
            <select name="religion" class="w-full border-gray-300 rounded-md shadow-sm focus:border-teal-500 focus:ring-teal-500">
                <option value="">Choose Religion</option>
                <option value="Hindu">Hindu</option>
                <option value="Muslim">Muslim</option>
                <option value="Christian">Christian</option>
                <option value="Sikh">Sikh</option>
                <option value="Other">Other</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
            <select name="category" class="w-full border-gray-300 rounded-md shadow-sm focus:border-teal-500 focus:ring-teal-500">
                <option value="">Choose Category</option>
                <option value="General">General</option>
                <option value="OBC">OBC</option>
                <option value="SC">SC</option>
                <option value="ST">ST</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Special Needs if any</label>
            <input type="text" name="special_needs" class="w-full border-gray-300 rounded-md shadow-sm focus:border-teal-500 focus:ring-teal-500" placeholder="Enter Special Needs if any">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Mother Tongue</label>
            <input type="text" name="mother_tongue" class="w-full border-gray-300 rounded-md shadow-sm focus:border-teal-500 focus:ring-teal-500" placeholder="Enter Mother Tongue">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Remarks</label>
            <input type="text" name="remarks" class="w-full border-gray-300 rounded-md shadow-sm focus:border-teal-500 focus:ring-teal-500" placeholder="Enter Remarks">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Number of Brothers</label>
            <input type="number" step="0.01" name="number_of_brothers" class="w-full border-gray-300 rounded-md shadow-sm focus:border-teal-500 focus:ring-teal-500" placeholder="Enter Number of Brothers">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Is Single Parent</label>
            <select name="is_single_parent" class="w-full border-gray-300 rounded-md shadow-sm focus:border-teal-500 focus:ring-teal-500">
                <option value="0">No</option>
                <option value="1">Yes</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Number of Sister</label>
            <input type="number" step="0.01" name="number_of_sisters" class="w-full border-gray-300 rounded-md shadow-sm focus:border-teal-500 focus:ring-teal-500" placeholder="Enter Number of Sister">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Corresponding Relative</label>
            <select name="corresponding_relative" class="w-full border-gray-300 rounded-md shadow-sm focus:border-teal-500 focus:ring-teal-500">
                <option value="">Choose Corresponding Relative</option>
                <option value="Father">Father</option>
                <option value="Mother">Mother</option>
                <option value="Guardian">Guardian</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Transport Required</label>
            <select name="transport_required" class="w-full border-gray-300 rounded-md shadow-sm focus:border-teal-500 focus:ring-teal-500">
                <option value="0">No</option>
                <option value="1">Yes</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Bus Stop</label>
            <select name="bus_stop" class="w-full border-gray-300 rounded-md shadow-sm focus:border-teal-500 focus:ring-teal-500">
                <option value="">Choose Bus Stop</option>
                <!-- Populate dynamically -->
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Other Stop</label>
            <input type="text" name="other_stop" class="w-full border-gray-300 rounded-md shadow-sm focus:border-teal-500 focus:ring-teal-500" placeholder="Enter Other Stop">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Boarding Type</label>
            <select name="boarding_type" class="w-full border-gray-300 rounded-md shadow-sm focus:border-teal-500 focus:ring-teal-500">
                <option value="">Choose Boarding Type</option>
                <option value="Self">Self</option>
                <option value="School Bus">School Bus</option>
            </select>
        </div>
    </div>
</div>
