<?php

namespace App\Enums;

enum MeetingWith: string
{
    case Principal = 'Principal';
    case Teacher = 'Teacher';
    case Accountant = 'Accountant';
    case Student = 'Student';
    case NonTeaching = 'Non Teaching';

    public function label(): string
    {
        return $this->value;
    }
}
