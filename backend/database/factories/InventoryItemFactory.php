<?php

namespace Database\Factories;

use App\Models\InventoryItem;
use App\Models\Shop;
use Illuminate\Database\Eloquent\Factories\Factory;

class InventoryItemFactory extends Factory
{
    protected $model = InventoryItem::class;

    public function definition(): array
    {
        $shop = Shop::inRandomOrder()->first() ?? Shop::factory()->create();

        return [
            'shop_id' => $shop->id,
            'name' => $this->faker->words(2, true),
            'sku' => 'SKU-' . strtoupper($this->faker->bothify('???###')),
            'category' => $this->faker->randomElement(['fabric', 'thread', 'button', 'accessory']),
            'quantity' => $this->faker->numberBetween(1, 100),
            'unit' => 'pcs',
            'cost' => $this->faker->numberBetween(10, 100),
            'selling_price' => $this->faker->numberBetween(20, 200),
            'min_stock' => 5,
            'status' => 'active',
        ];
    }
}
