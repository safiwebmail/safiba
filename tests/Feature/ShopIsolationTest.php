<?php

namespace Tests\Feature;

use App\Models\InventoryItem;
use App\Models\Shop;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShopIsolationTest extends TestCase
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

    public function test_manager_only_sees_own_shop(): void
    {
        $token = $this->login('manager@safitailoring.com');

        $shops = $this->withToken($token)->getJson('/api/v1/shops')->json('data');

        $this->assertCount(1, $shops);
        $this->assertEquals('Kabul Shop', $shops[0]['name']);
    }

    public function test_manager_cannot_access_other_shop_inventory(): void
    {
        $token = $this->login('manager@safitailoring.com');

        $inventory = $this->withToken($token)->getJson('/api/v1/inventory')->json('data.data');

        foreach ($inventory as $item) {
            $this->assertEquals(Shop::where('code', 'KBL')->first()->id, $item['shop_id']);
        }

        $heratItem = InventoryItem::where('shop_id', Shop::where('code', 'HRT')->first()->id)->first();

        $response = $this->withToken($token)->getJson("/api/v1/inventory/{$heratItem->id}");
        $this->assertEquals(403, $response->status());
    }

    public function test_manager_cannot_create_order_for_other_shop(): void
    {
        $token = $this->login('manager@safitailoring.com');

        $response = $this->withToken($token)->postJson('/api/v1/orders', [
            'shop_id' => Shop::where('code', 'HRT')->first()->id,
            'customer_name' => 'Test',
            'customer_phone' => '123',
            'payment_method' => 'cod',
            'delivery_method' => 'pickup',
            'items' => [['name' => 'Test item', 'quantity' => 1, 'price' => 10]],
        ]);

        $response->assertStatus(403);
    }

    public function test_tailor_only_sees_assigned_orders(): void
    {
        $token = $this->login('tailor@safitailoring.com');

        $orders = $this->withToken($token)->getJson('/api/v1/orders')->json('data.data');

        $tailor = User::where('email', 'tailor@safitailoring.com')->first();
        foreach ($orders as $order) {
            $this->assertEquals($tailor->id, $order['tailor_id']);
        }
    }

    public function test_customer_only_sees_own_orders(): void
    {
        $token = $this->login('customer@safitailoring.com');

        $orders = $this->withToken($token)->getJson('/api/v1/orders')->json('data.data');

        $customer = User::where('email', 'customer@safitailoring.com')->first();
        foreach ($orders as $order) {
            $this->assertEquals($customer->id, $order['user_id']);
        }
    }

    public function test_admin_sees_all_shops(): void
    {
        $token = $this->login('admin@safitailoring.com');

        $shops = $this->withToken($token)->getJson('/api/v1/shops')->json('data');

        $this->assertGreaterThanOrEqual(4, count($shops));
    }

    public function test_customer_cannot_access_admin_inventory(): void
    {
        $token = $this->login('customer@safitailoring.com');

        $this->withToken($token)->getJson('/api/v1/inventory')->assertStatus(403);
    }
}