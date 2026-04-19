<?php

namespace App\Services\School;

use App\Enums\AdmissionStatus;
use App\Enums\EnquiryStatus;
use App\Enums\FeeStatus;
use App\Enums\GeneralStatus;
use App\Enums\ParentStatus;
use App\Enums\RelationType;
use App\Enums\UserStatus;
use App\Http\Requests\School\StoreAdmissionRequest;
use App\Models\Fee;
use App\Models\FeeName;
use App\Models\FeePayment;
use App\Models\HostelBedAssignment;
use App\Models\PaymentMethod;
use App\Models\Role;
use App\Models\Student;
use App\Models\StudentEnquiry;
use App\Models\StudentParent;
use App\Models\StudentRegistration;
use App\Models\StudentTransportAssignment;
use App\Models\User;
use App\Notifications\StudentAdmissionCredentials;
use App\Traits\HandlesFileCopies;
use App\Traits\HandlesFinancialNumbers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdmissionService
{
    use HandlesFileCopies, HandlesFinancialNumbers;

    /**
     * Run the full admission pipeline inside an already-open DB transaction.
     * Returns the newly created Student.
     */
    public function admit(StoreAdmissionRequest $request, $school): Student
    {
        $student = new Student();
        $student->school_id = $school->id;

        // 1. Generate admission number atomically
        $admissionNo = $request->admission_no ?: Cache::lock('admission_no_seq_' . $school->id, 10)
            ->block(5, function () use ($school) {
                $max = Student::where('school_id', $school->id)->max('admission_no');
                return $max ? (intval($max) + 1) : 100001;
            });

        if (Student::where('school_id', $school->id)->where('admission_no', $admissionNo)->exists()) {
            throw new \RuntimeException("Admission number [{$admissionNo}] is already allocated. Please refresh and try again.");
        }
        $student->admission_no = $admissionNo;

        // 2. Duplicate detection — Aadhaar and mobile
        $this->assertNoDuplicate($request, $school->id);

        // 3. Create student User account
        $tempPassword = Str::password(12);
        $user = $this->createStudentUser($request, $admissionNo, $school, $tempPassword);
        $student->user_id = $user->id;

        // 4. Fill student data
        $excluded = [
            'student_photo', 'father_photo', 'mother_photo',
            'student_signature', 'father_signature', 'mother_signature',
            'registration_id', 'student_photo_path', 'father_photo_path', 'mother_photo_path',
            'student_signature_path', 'father_signature_path', 'mother_signature_path',
            'admission_fee', 'transport_route_id', 'hostel_id', 'hostel_room_id', 'hostel_bed_no',
            'admission_payment_method_id',
        ];
        $student->fill($request->except($excluded));

        if (!$student->father_name) {
            $student->father_name = trim(implode(' ', array_filter([
                $request->father_first_name, $request->father_middle_name, $request->father_last_name,
            ])));
        }
        if (!$student->mother_name) {
            $student->mother_name = trim(implode(' ', array_filter([
                $request->mother_first_name, $request->mother_middle_name, $request->mother_last_name,
            ])));
        }

        // 5. Handle file uploads / copies
        $this->handleStudentFiles($request, $student, $school);

        $student->save();

        // 6. Create parent accounts and link to student
        $this->createParentAccounts($request, $student, $school);

        // 7. Financial integration
        $this->handleFinancialIntegration($request, $student, $school);

        // 8. Facility assignments
        $this->handleFacilityIntegration($request, $student, $school);

        // 9. Back-propagate status on registration / enquiry, store enquiry_id
        $this->updateLinkedRecords($request, $student, $school);

        // 10. Send credentials notification
        $user->notify(new StudentAdmissionCredentials($student, $tempPassword));

        return $student;
    }

    // ──────────────────────────────────────────────────────────────────────────

    private function assertNoDuplicate(StoreAdmissionRequest $request, int $schoolId): void
    {
        if ($request->aadhaar_no) {
            $exists = Student::where('school_id', $schoolId)
                ->where('aadhaar_no', $request->aadhaar_no)
                ->exists();
            if ($exists) {
                throw new \RuntimeException("A student with Aadhaar number [{$request->aadhaar_no}] is already admitted in this school.");
            }
        }

        if ($request->mobile_no) {
            $exists = Student::where('school_id', $schoolId)
                ->where('mobile_no', $request->mobile_no)
                ->exists();
            if ($exists) {
                throw new \RuntimeException("A student with mobile number [{$request->mobile_no}] is already admitted in this school.");
            }
        }
    }

    private function createStudentUser(StoreAdmissionRequest $request, $admissionNo, $school, string $tempPassword): User
    {
        $studentRole = Role::where('slug', Role::STUDENT)->first();
        $email = $request->email ?: 'student.' . $admissionNo . '@' . $school->subdomain . '.edusphere.local';

        return User::create([
            'name'                 => trim($request->first_name . ' ' . $request->last_name),
            'email'                => $email,
            'password'             => Hash::make($tempPassword),
            'role_id'              => $studentRole?->id,
            'school_id'            => $school->id,
            'phone'                => $request->mobile_no,
            'status'               => UserStatus::Active,
            'must_change_password' => true,
        ]);
    }

    private function handleStudentFiles(StoreAdmissionRequest $request, Student $student, $school): void
    {
        $student->student_photo     = $this->storeTenantFile($request->file('student_photo'),     "students/{$school->id}/photos",     $request->student_photo_path);
        $student->father_photo      = $this->storeTenantFile($request->file('father_photo'),      "parents/{$school->id}/photos",      $request->father_photo_path);
        $student->mother_photo      = $this->storeTenantFile($request->file('mother_photo'),      "parents/{$school->id}/photos",      $request->mother_photo_path);
        $student->student_signature = $this->storeTenantFile($request->file('student_signature'), "students/{$school->id}/signatures", $request->student_signature_path);
        $student->father_signature  = $this->storeTenantFile($request->file('father_signature'),  "parents/{$school->id}/signatures",  $request->father_signature_path);
        $student->mother_signature  = $this->storeTenantFile($request->file('mother_signature'),  "parents/{$school->id}/signatures",  $request->mother_signature_path);
    }

    /**
     * Create/reuse parent User + StudentParent records for father and mother,
     * then link them to the student via the student_parent pivot.
     */
    private function createParentAccounts(StoreAdmissionRequest $request, Student $student, $school): void
    {
        $parentRole = Role::where('slug', Role::PARENT)->first();

        $parents = [
            'father' => [
                'first_name' => $request->father_first_name,
                'last_name'  => $request->father_last_name,
                'mobile'     => $request->father_mobile_no,
                'email'      => $request->father_email,
                'relation'   => RelationType::Father,
                'is_primary' => true,
            ],
            'mother' => [
                'first_name' => $request->mother_first_name,
                'last_name'  => $request->mother_last_name,
                'mobile'     => $request->mother_mobile_no,
                'email'      => $request->mother_email,
                'relation'   => RelationType::Mother,
                'is_primary' => false,
            ],
        ];

        foreach ($parents as $data) {
            if (empty($data['first_name'])) {
                continue;
            }

            $email = $data['email'] ?: null;

            // Reuse existing parent User if same email exists in this school
            $parentUser = $email
                ? User::where('school_id', $school->id)->where('email', $email)->first()
                : null;

            if (!$parentUser) {
                $tempPassword = Str::password(10);
                $autoEmail    = $email ?: 'parent.' . Str::lower($data['first_name']) . '.' . $student->admission_no . '@' . $school->subdomain . '.edusphere.local';

                $parentUser = User::create([
                    'name'                 => trim($data['first_name'] . ' ' . $data['last_name']),
                    'email'                => $autoEmail,
                    'password'             => Hash::make($tempPassword),
                    'role_id'              => $parentRole?->id,
                    'school_id'            => $school->id,
                    'phone'                => $data['mobile'],
                    'status'               => UserStatus::Active,
                    'must_change_password' => true,
                ]);
            }

            // Reuse existing StudentParent record for this user
            $parentRecord = StudentParent::firstOrCreate(
                ['school_id' => $school->id, 'user_id' => $parentUser->id],
                [
                    'first_name' => $data['first_name'],
                    'last_name'  => $data['last_name'],
                    'phone'      => $data['mobile'],
                    'email'      => $parentUser->email,
                    'status'     => ParentStatus::Active,
                ]
            );

            // Link to student (avoid duplicate pivot rows)
            if (!$student->parents()->where('student_parent_id', $parentRecord->id)->exists()) {
                $student->parents()->attach($parentRecord->id, [
                    'relation'   => $data['relation'],
                    'is_primary' => $data['is_primary'],
                ]);
            }
        }
    }

    private function handleFinancialIntegration(StoreAdmissionRequest $request, Student $student, $school): void
    {
        if (!$request->admission_fee || $request->admission_fee <= 0) {
            return;
        }

        $admissionFeeName = FeeName::where('school_id', $school->id)->where('name', 'Admission Fee')->first();
        if (!$admissionFeeName) {
            return; // Fee name not configured — skip silently
        }

        // Use selected payment method, fall back to Cash
        $paymentMethod = $request->admission_payment_method_id
            ? PaymentMethod::where('school_id', $school->id)->find($request->admission_payment_method_id)
            : PaymentMethod::where('school_id', $school->id)->where('name', 'Cash')->first();

        if (!$paymentMethod) {
            throw new \RuntimeException('No payment method configured for this school. Please add one in Payment Methods settings.');
        }

        $fee = Fee::create([
            'school_id'      => $school->id,
            'student_id'     => $student->id,
            'academic_year_id' => $student->academic_year_id,
            'fee_type_id'    => $admissionFeeName->fee_type_id,
            'fee_name_id'    => $admissionFeeName->id,
            'class_id'       => $student->class_id,
            'bill_no'        => $this->generateBillNumber($school->id),
            'fee_period'     => 'Admission',
            'payable_amount' => $request->admission_fee,
            'paid_amount'    => $request->admission_fee,
            'due_amount'     => 0,
            'due_date'       => now(),
            'payment_date'   => now(),
            'payment_status' => FeeStatus::Paid,
            'payment_mode'   => strtolower($paymentMethod->name),
            'remarks'        => 'Admission Fee paid during intake',
        ]);

        FeePayment::create([
            'school_id'         => $school->id,
            'student_id'        => $student->id,
            'fee_id'            => $fee->id,
            'academic_year_id'  => $student->academic_year_id,
            'amount'            => $request->admission_fee,
            'payment_date'      => now(),
            'payment_method_id' => $paymentMethod->id,
            'receipt_no'        => $this->generateReceiptNumber($school->id),
            'created_by'        => Auth::id(),
        ]);

        // Store payment method on student for reference
        $student->admission_payment_method_id = $paymentMethod->id;
        $student->save();
    }

    private function handleFacilityIntegration(StoreAdmissionRequest $request, Student $student, $school): void
    {
        if ($request->transport_route_id) {
            StudentTransportAssignment::create([
                'school_id'  => $school->id,
                'student_id' => $student->id,
                'route_id'   => $request->transport_route_id,
                'status'     => GeneralStatus::Active,
                'start_date' => now(),
            ]);
        }

        if ($request->hostel_id && $request->hostel_room_id) {
            HostelBedAssignment::create([
                'school_id'       => $school->id,
                'student_id'      => $student->id,
                'hostel_id'       => $request->hostel_id,
                'hostel_room_id'  => $request->hostel_room_id,
                'bed_no'          => $request->hostel_bed_no,
                'status'          => GeneralStatus::Active,
                'start_date'      => now(),
                'hostel_assign_date' => now(),
            ]);
        }
    }

    private function updateLinkedRecords(StoreAdmissionRequest $request, Student $student, $school): void
    {
        if (!$request->registration_no) {
            return;
        }

        $registration = StudentRegistration::where('registration_no', $request->registration_no)
            ->where('school_id', $school->id)
            ->first();

        if (!$registration) {
            return;
        }

        $registration->update(['admission_status' => AdmissionStatus::Admitted]);

        // Re-assign orphaned registration fee records to the new student
        $regFeeIds = Fee::where('school_id', $school->id)
            ->where('registration_id', $registration->id)
            ->whereNull('student_id')
            ->pluck('id');

        if ($regFeeIds->isNotEmpty()) {
            Fee::whereIn('id', $regFeeIds)->update(['student_id' => $student->id]);
            FeePayment::whereIn('fee_id', $regFeeIds)->whereNull('student_id')->update(['student_id' => $student->id]);
        }

        // Store enquiry_id on student for direct traceability
        if ($registration->enquiry_id) {
            $student->enquiry_id = $registration->enquiry_id;
            $student->save();

            StudentEnquiry::where('school_id', $school->id)
                ->where('id', $registration->enquiry_id)
                ->update(['form_status' => EnquiryStatus::Admitted]);
        }
    }
}
