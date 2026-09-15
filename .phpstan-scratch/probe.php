<?php

namespace App\Services;

use App\Models\Order;

class ProbeStatus
{
    public function probe(Order $order): void
    {
        $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->id);
        \PHPStan\dumpType($lockedOrder);
        \PHPStan\dumpType($lockedOrder->status);
        $from = $lockedOrder->status;
        \PHPStan\dumpType($from);
    }
}
