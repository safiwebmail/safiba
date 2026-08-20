<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\Shop;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmployeeFactory extends Factory
{
    protected $model = Employee::class;

    public function definition(): array
    {
        $shop = Shop::inRandomOrder()->first() ?? Shop::factory()->create();

        return [
            'shop_id' => $shop->id,
            'name' => $this->faker->name(),
            'phone' => $this->faker->phoneNumber(),
            'position' => $this->faker->randomElement(['tailor', 'manager', 'cashier', 'delivery']),
            'salary' => $this->faker->numberBetween(5000, 20000),
            'joining_date' => $this->faker->date(),
            'status' => 'active',
        ];
    }
}
