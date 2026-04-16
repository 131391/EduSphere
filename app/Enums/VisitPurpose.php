<?php

namespace App\Enums;

enum VisitPurpose: string
{
    case WalkIn = 'Walk in';
    case General = 'General';
    case Admission = 'Admission';
    case Vendor = 'Vendor';
    case FeeDeposit = 'Fee Deposit';
    case Enquiry = 'Enquiry';
    case ForDiscussion = 'For Discussion';
    case Complain = 'Complain';
    case Suggestion = 'Suggestion';
    case ForDocument = 'For Document';
    case TransferCertificate = 'Transfer Certificate';

    public function label(): string
    {
        return $this->value;
    }
}
