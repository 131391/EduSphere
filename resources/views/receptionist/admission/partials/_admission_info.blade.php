<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="bg-teal-500 px-6 py-3">
        <h3 class="text-white font-semibold">Admission Form Information</h3>
    </div>
    <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Registration No <span class="text-red-500">*</span></label>
            <input type="text" name="registration_no" class="w-full border-gray-300 rounded-md shadow-sm focus:border-teal-500 focus:ring-teal-500" placeholder="Enter Registration No">
        </div>
        
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Academic Year <span class="text-red-500">*</span></label>
                <select name="academic_year_id" class="w-full border-gray-300 rounded-md shadow-sm focus:border-teal-500 focus:ring-teal-500" required>
                    @foreach($academicYears as $year)
                        <option value="{{ $year->id }}">{{ $year->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Class <span class="text-red-500">*</span></label>
                <select name="class_id" class="w-full border-gray-300 rounded-md shadow-sm focus:border-teal-500 focus:ring-teal-500" required>
                    <option value="">Select Class</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Section <span class="text-red-500">*</span></label>
                <select name="section_id" class="w-full border-gray-300 rounded-md shadow-sm focus:border-teal-500 focus:ring-teal-500" required>
                    <option value="">Select Section</option>
                    @foreach($sections as $section)
                        <option value="{{ $section->id }}">{{ $section->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Roll No <span class="text-red-500">*</span></label>
                <input type="text" name="roll_no" class="w-full border-gray-300 rounded-md shadow-sm focus:border-teal-500 focus:ring-teal-500" placeholder="Enter Roll No">
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Receipt No <span class="text-red-500">*</span></label>
                <input type="text" name="receipt_no" class="w-full border-gray-300 rounded-md shadow-sm focus:border-teal-500 focus:ring-teal-500" placeholder="Enter Receipt No">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Registration Date <span class="text-red-500">*</span></label>
                <input type="date" name="admission_date" class="w-full border-gray-300 rounded-md shadow-sm focus:border-teal-500 focus:ring-teal-500" required>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Admission Fee <span class="text-red-500">*</span></label>
                <input type="number" step="0.01" name="admission_fee" class="w-full border-gray-300 rounded-md shadow-sm focus:border-teal-500 focus:ring-teal-500" placeholder="Admission Fee">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Referred by</label>
                <select name="referred_by" class="w-full border-gray-300 rounded-md shadow-sm focus:border-teal-500 focus:ring-teal-500">
                    <option value="">Choose Referred by</option>
                    <option value="Staff">Staff</option>
                    <option value="Parent">Parent</option>
                    <option value="Other">Other</option>
                </select>
            </div>
        </div>
    </div>
</div>
