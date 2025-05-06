<?php

namespace App\Enums;

enum SignatureRequestStatus : string
{
    case PENDING = 'pending';
    case COMPLETED = 'completed';
    case REJECTED = 'rejected';
}
