<?php

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderStatusHistory>
 */
class OrderStatusHistoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'from_status' => null,
            'to_status' => OrderStatus::OPEN,
            'changed_by' => fn () => User::factory()->create(['tenant_id' => Tenant::current()?->getKey()]),
            'changed_at' => now(),
            'note' => fake()->sentence(),
        ];
    }
}
