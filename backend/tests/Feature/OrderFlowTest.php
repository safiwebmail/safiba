<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\InventoryItem;
use App\Models\Order;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    private function login(string $email): string
    {
        return $this->postJson('/api/v1/auth/login', [
            'email' => $email,
            'password' => 'password',
        ])->json('data.token');
    }

    public function test_customer_can_place_product_order(): void
    {
        $token = $this->login('customer@safitailoring.com');
        $kabul = Shop::where('code', 'KBL')->first();
        $product = \App\Models\Product::where('slug', 'classic-white-kameez')->first();

        $response = $this->withToken($token)->postJson('/api/v1/orders', [
            'shop_id' => $kabul->id,
            'customer_name' => 'Karim Customer',
            'customer_phone' => '+93 700 000 006',
            'payment_method' => 'cod',
            'delivery_method' => 'pickup',
            'items' => [
                ['product_id' => $product->id, 'name' => 'Classic White Kameez', 'quantity' => 2, 'price' => 1200],
            ],
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.type', 'product')
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('data.total', 2400);
    }

    public function test_customer_can_place_custom_order(): void
    {
        $token = $this->login('customer@safitailoring.com');
        $kabul = Shop::where('code', 'KBL')->first();

        $response = $this->withToken($token)->postJson('/api/v1/orders', [
            'shop_id' => $kabul->id,
            'customer_name' => 'Karim Customer',
            'customer_phone' => '+93 700 000 006',
            'payment_method' => 'cod',
            'delivery_method' => 'pickup',
            'expected_completion_date' => now()->addDays(7)->toDateString(),
            'items' => [
                [
                    'name' => 'Custom Kameez',
                    'garment_type' => 'Kameez',
                    'fabric' => 'Wash & Wear',
                    'color' => 'Blue',
                    'quantity' => 1,
                    'price' => 2500,
                    'measurements' => ['chest' => 40, 'waist' => 38, 'sleeve' => 24, 'length' => 42],
                    'instructions' => 'Simple collar',
                ],
            ],
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.type', 'custom')
            ->assertJsonPath('data.items.0.garment_type', 'Kameez')
            ->assertJsonPath('data.items.0.measurements.chest', 40);
    }

    public function test_admin_can_update_status_and_assign_tailor(): void
    {
        $adminToken = $this->login('admin@safitailoring.com');
        $tailor = User::where('email', 'tailor@safitailoring.com')->first();
        $kabul = Shop::where('code', 'KBL')->first();

        $order = Order::factory()->create([
            'shop_id' => $kabul->id,
            'user_id' => User::where('email', 'customer@safitailoring.com')->first()->id,
            'status' => 'pending',
            'total' => 1000,
        ]);

        $this->withToken($adminToken)->putJson("/api/v1/orders/{$order->id}/status", [
            'status' => 'confirmed',
        ])->assertOk()->assertJsonPath('data.status', 'confirmed');

        $this->withToken($adminToken)->postJson("/api/v1/orders/{$order->id}/assign-tailor", [
            'tailor_id' => $tailor->id,
        ])->assertOk()->assertJsonPath('data.tailor_id', $tailor->id);

        $this->withToken($adminToken)->putJson("/api/v1/orders/{$order->id}/status", [
            'status' => 'cutting',
        ])->assertOk();

        $this->assertDatabaseHas('order_status_histories', ['order_id' => $order->id, 'status' => 'cutting']);
    }

    public function test_invalid_status_transition_is_rejected(): void
    {
        $adminToken = $this->login('admin@safitailoring.com');
        $kabul = Shop::where('code', 'KBL')->first();

        $order = Order::factory()->create(['shop_id' => $kabul->id, 'status' => 'pending', 'total' => 100]);

        $this->withToken($adminToken)->putJson("/api/v1/orders/{$order->id}/status", [
            'status' => 'completed',
        ])->assertStatus(422);
    }

    public function test_customer_can_record_payment(): void
    {
        $adminToken = $this->login('admin@safitailoring.com');
        $kabul = Shop::where('code', 'KBL')->first();

        $order = Order::factory()->create(['shop_id' => $kabul->id, 'total' => 2000]);

        $this->withToken($adminToken)->postJson("/api/v1/orders/{$order->id}/payments", [
            'amount' => 2000,
            'method' => 'cod',
        ])->assertOk()->assertJsonPath('data.payment_status', 'paid');
    }

    public function test_inventory_adjustment_creates_movement(): void
    {
        $adminToken = $this->login('admin@safitailoring.com');
        $kabul = Shop::where('code', 'KBL')->first();

        $item = InventoryItem::factory()->create(['shop_id' => $kabul->id, 'quantity' => 100, 'min_stock' => 10]);

        $this->withToken($adminToken)->postJson("/api/v1/inventory/{$item->id}/adjust", [
            'type' => 'out',
            'quantity' => 30,
            'reason' => 'sale',
        ])->assertOk();

        $this->assertDatabaseHas('stock_movements', ['inventory_id' => $item->id, 'type' => 'out', 'quantity' => 30]);
        $this->assertEquals(70, $item->fresh()->quantity);
    }

    public function test_payroll_net_salary_calculation(): void
    {
        $adminToken = $this->login('admin@safitailoring.com');
        $kabul = Shop::where('code', 'KBL')->first();

        $employee = Employee::factory()->create(['shop_id' => $kabul->id, 'salary' => 15000]);

        $response = $this->withToken($adminToken)->postJson('/api/v1/payroll', [
            'employee_id' => $employee->id,
            'base_salary' => 15000,
            'bonus' => 1000,
            'deduction' => 500,
            'payment_date' => now()->toDateString(),
        ]);

        $response->assertStatus(201);
        $this->assertEquals(15500, $response->json('data.net_salary'));
    }

    public function test_income_and_expense_recording(): void
    {
        $adminToken = $this->login('admin@safitailoring.com');

        $this->withToken($adminToken)->postJson('/api/v1/income', [
            'category' => 'product_sales', 'amount' => 5000, 'date' => now()->toDateString(),
        ])->assertStatus(201);

        $this->withToken($adminToken)->postJson('/api/v1/expenses', [
            'category' => 'rent', 'amount' => 2000, 'date' => now()->toDateString(),
        ])->assertStatus(201);

        $this->withToken($adminToken)->getJson('/api/v1/reports/summary')
            ->assertOk()
            ->assertJsonStructure(['data' => ['income', 'expenses', 'profit']]);
    }

    public function test_unauthenticated_access_is_blocked(): void
    {
        $this->getJson('/api/v1/orders')->assertStatus(401);
        $this->getJson('/api/v1/inventory')->assertStatus(401);
        $this->getJson('/api/v1/income')->assertStatus(401);
    }
}