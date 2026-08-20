<?php

namespace Database\Factories;

use App\Models\Shop;
use Illuminate\Database\Eloquent\Factories\Factory;

class ShopFactory extends Factory
{
    protected $model = Shop::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->company() . ' Shop',
            'code' => strtoupper($this->faker->bothify('???')),
            'address' => $this->faker->address(),
            'phone' => $this->faker->phoneNumber(),
            'status' => 'active',
        ];
    }
}
