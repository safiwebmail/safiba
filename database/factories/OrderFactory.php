<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        $shop = Shop::inRandomOrder()->first() ?? Shop::factory()->create();

        return [
            'order_number' => 'ORD-' . date('Ymd') . '-' . strtoupper($this->faker->bothify('??????')),
            'shop_id' => $shop->id,
            'user_id' => User::inRandomOrder()->first()?->id,
            'customer_name' => $this->faker->name(),
            'customer_phone' => $this->faker->phoneNumber(),
            'type' => 'product',
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'payment_method' => 'cod',
            'subtotal' => 100,
            'discount' => 0,
            'delivery_fee' => 0,
            'total' => 100,
            'delivery_method' => 'pickup',
        ];
    }
}
