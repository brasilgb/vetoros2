<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderSnapshot;

class OrderSnapshotService
{
    public function create(Order $order): OrderSnapshot
    {
        return OrderSnapshot::createForOrder($order);
    }
}
