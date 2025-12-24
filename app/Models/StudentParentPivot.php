<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use App\Enums\RelationType;

class StudentParentPivot extends Pivot
{
    protected $table = 'student_parent';

    protected $casts = [
        'relation' => RelationType::class,
        'is_primary' => 'boolean',
    ];
}
