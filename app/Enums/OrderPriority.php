<?php

namespace App\Enums;

enum OrderPriority: string
{
    case LOW = 'low';
    case NORMAL = 'normal';
    case HIGH = 'high';
    case URGENT = 'urgent';
}
