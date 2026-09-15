<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderMedia;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderMedia>
 */
class OrderMediaFactory extends Factory
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
            'type' => 'entry',
            'path' => 'orders/example/image.jpg',
            'original_name' => 'image.jpg',
            'mime_type' => 'image/jpeg',
            'size' => 1024,
        ];
    }
}
