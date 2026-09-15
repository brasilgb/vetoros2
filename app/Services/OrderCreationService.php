<?php

namespace App\Services;

use App\Models\ChecklistTemplate;
use App\Models\Order;
use App\Models\OrderChecklist;
use App\Models\OrderEquipmentAccessory;
use App\Models\OrderEquipmentCondition;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class OrderCreationService
{
    public function __construct(
        private readonly OrderSnapshotService $snapshots,
        private readonly OrderAssignmentService $assignments,
        private readonly TenantSequenceService $sequences,
    ) {}

    /** @param array{order: array<string, mixed>, accessories?: array<int, array<string, mixed>>, conditions?: array<int, array<string, mixed>>, checklist_template?: ChecklistTemplate|null, assigned_to?: User|null} $data */
    public function create(array $data, User $actor): Order
    {
        $tenant = Tenant::current();
        if (! $tenant || (int) $actor->tenant_id !== (int) $tenant->getKey()) {
            throw new \LogicException('The creator and current tenant must match.');
        }

        return DB::transaction(function () use ($data, $actor): Order {
            $attributes = $data['order'];
            if (($attributes['order_number'] ?? null) === null) {
                $attributes['order_number'] = $this->sequences->next('orders');
            }
            $initialTechnician = $data['assigned_to'] ?? null;
            $attributes['assigned_to'] = null;
            $order = Order::create($attributes);
            $this->snapshots->create($order);

            foreach ($data['accessories'] ?? [] as $accessory) {
                OrderEquipmentAccessory::create(['order_id' => $order->id, ...$accessory]);
            }
            foreach ($data['conditions'] ?? [] as $condition) {
                OrderEquipmentCondition::create(['order_id' => $order->id, ...$condition]);
            }
            if (($template = $data['checklist_template'] ?? null) !== null) {
                OrderChecklist::createFromTemplate($order, $template, $actor);
            }
            if ($initialTechnician !== null) {
                $this->assignments->assign($order, $initialTechnician, $actor, 'Initial assignment');
            }

            return $order->fresh();
        });
    }
}
