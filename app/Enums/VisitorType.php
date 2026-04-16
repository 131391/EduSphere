<?php

namespace App\Enums;

enum VisitorType: string
{
    case Parent = 'Parent';
    case GeneralVisitor = 'General Visitor';

    public function label(): string
    {
        return $this->value;
    }
}
