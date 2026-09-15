<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\Order;
use App\Models\OrderAssignmentHistory;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use LogicException;

class OrderAssignmentService
{
    public function __construct(private readonly OrderVisibilityService $visibility) {}

    public function assign(Order $order, User $technician, User $changedBy, ?string $note = null): Order
    {
        return $this->change($order, $technician, $changedBy, $note);
    }

    public function reassign(Order $order, User $technician, User $changedBy, ?string $note = null): Order
    {
        return $this->change($order, $technician, $changedBy, $note);
    }

    public function unassign(Order $order, User $changedBy, ?string $note = null): Order
    {
        return $this->change($order, null, $changedBy, $note);
    }

    private function change(Order $order, ?User $technician, User $changedBy, ?string $note): Order
    {
        $tenant = Tenant::current();
        if (! $tenant || (int) $order->tenant_id !== (int) $tenant->getKey()) {
            throw new LogicException('The order and current tenant must match.');
        }

        return DB::transaction(function () use ($order, $technician, $changedBy, $note, $tenant): Order {
            /** @var Order $lockedOrder */
            $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->getKey());

            if (! $this->visibility->canView($changedBy, $lockedOrder)) {
                throw new LogicException('The actor cannot access this order.');
            }

            $branch = Branch::withoutGlobalScopes()
                ->whereKey($lockedOrder->branch_id)
                ->where('tenant_id', $tenant->getKey())
                ->where('company_id', $lockedOrder->company_id)
                ->first();

            if (! $branch) {
                throw new LogicException('The order branch is invalid for its tenant and company.');
            }

            if ($technician !== null) {
                if ((int) $technician->tenant_id !== (int) $tenant->getKey() || ! $this->hasBranchAccess($technician, $branch->getKey(), $lockedOrder->company_id)) {
                    throw new LogicException('The technician has no compatible branch or company access.');
                }
            }

            $from = $lockedOrder->assigned_to;
            $to = $technician?->getKey();

            if ((int) $from === (int) $to || ($from === null && $to === null)) {
                return $lockedOrder;
            }

            $lockedOrder->assigned_to = $to;
            $lockedOrder->save();

            OrderAssignmentHistory::create([
                'tenant_id' => $tenant->getKey(),
                'order_id' => $lockedOrder->getKey(),
                'from_user_id' => $from,
                'to_user_id' => $to,
                'changed_by' => $changedBy->getKey(),
                'changed_at' => now(),
                'note' => $note,
            ]);

            return $lockedOrder->fresh();
        });
    }

    private function hasBranchAccess(User $user, int $branchId, int $companyId): bool
    {
        $tenantId = Tenant::current()->getKey();

        return DB::table('branch_user')
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->where('user_id', $user->getKey())
            ->exists()
            || DB::table('company_user')
                ->where('tenant_id', $tenantId)
                ->where('company_id', $companyId)
                ->where('user_id', $user->getKey())
                ->exists();
    }
}
