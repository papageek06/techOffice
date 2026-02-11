<?php

namespace App\Enum;

enum InboundEmailStatus: string
{
    case NEW = 'NEW';
    case PROCESSED = 'PROCESSED';
    case ERROR = 'ERROR';
}
