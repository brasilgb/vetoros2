<?php

namespace App\Enums;

enum BudgetItemType: string
{
    case SERVICE = 'service';
    case PART = 'part';
    case OTHER = 'other';
}
