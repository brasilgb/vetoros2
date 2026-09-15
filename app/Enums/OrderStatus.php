<?php

namespace App\Enums;

enum OrderStatus: string
{
    case OPEN = 'open';
    case CANCELLED = 'cancelled';
    case BUDGET_GENERATED = 'budget_generated';
    case BUDGET_APPROVED = 'budget_approved';
    case BUDGET_REJECTED = 'budget_rejected';
    case IN_PROGRESS = 'in_progress';
    case COMPLETED = 'completed';
    case NOT_EXECUTED = 'not_executed';
    case WAITING_CUSTOMER = 'waiting_customer';
    case DELIVERED = 'delivered';
}
