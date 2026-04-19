<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $renames = [
        'phone'              => 'mobile_no',
        'date_of_birth'      => 'dob',
        'aadhaar_no'         => 'aadhaar_no',
        'father_aadhaar'     => 'father_aadhaar_no',
        'mother_aadhaar'     => 'mother_aadhaar_no',
        'father_mobile'      => 'father_mobile_no',
        'mother_mobile'      => 'mother_mobile_no',
        'photo'              => 'student_photo',
        'signature'          => 'student_signature',
        'transport_required' => 'is_transport_required',
    ];

    public function up(): void
    {
        foreach ($this->renames as $from => $to) {
            DB::statement("ALTER TABLE students RENAME COLUMN `{$from}` TO `{$to}`");
        }
    }

    public function down(): void
    {
        foreach (array_flip($this->renames) as $from => $to) {
            DB::statement("ALTER TABLE students RENAME COLUMN `{$from}` TO `{$to}`");
        }
    }
};
