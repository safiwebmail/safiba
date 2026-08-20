<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\Business;
use App\Models\Category;
use App\Models\Employee;
use App\Models\Expense;
use App\Models\Income;
use App\Models\InventoryItem;
use App\Models\MeasurementProfile;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Shop;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\User;
use App\Services\OrderService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $business = Business::firstOrCreate(
            ['id' => 1],
            [
                'name' => 'Safi Tailoring',
                'phone' => '+93 700 123 456',
                'email' => 'info@safitailoring.com',
                'whatsapp' => '+93 700 123 456',
                'address' => 'Charahi Ansari, Kabul, Afghanistan',
                'currency' => 'AFN',
                'timezone' => 'Asia/Kabul',
                'default_delivery_fee' => 100,
                'order_prefix' => 'ORD',
                'invoice_prefix' => 'INV',
            ]
        );

        $kabul = Shop::firstOrCreate(['code' => 'KBL'], [
            'name' => 'Kabul Shop',
            'address' => 'Charahi Ansari, Kabul',
            'phone' => '+93 700 111 222',
            'email' => 'kabul@safitailoring.com',
            'opening_hours' => '9:00 AM - 9:00 PM',
            'status' => 'active',
        ]);

        $jalalabad = Shop::firstOrCreate(['code' => 'JBD'], [
            'name' => 'Jalalabad Shop',
            'address' => 'Main Bazaar, Jalalabad',
            'phone' => '+93 700 333 444',
            'email' => 'jalalabad@safitailoring.com',
            'opening_hours' => '9:00 AM - 8:00 PM',
            'status' => 'active',
        ]);

        $herat = Shop::firstOrCreate(['code' => 'HRT'], [
            'name' => 'Herat Shop',
            'address' => 'Bazaar-e-Mardan, Herat',
            'phone' => '+93 700 555 666',
            'email' => 'herat@safitailoring.com',
            'opening_hours' => '9:00 AM - 8:00 PM',
            'status' => 'active',
        ]);

        $kandahar = Shop::firstOrCreate(['code' => 'KDH'], [
            'name' => 'Kandahar Shop',
            'address' => 'Haji Wali Road, Kandahar',
            'phone' => '+93 700 777 888',
            'email' => 'kandahar@safitailoring.com',
            'opening_hours' => '9:00 AM - 8:00 PM',
            'status' => 'active',
        ]);

        $shops = [$kabul, $jalalabad, $herat, $kandahar];

        // ============ USERS ============
        $superAdmin = User::firstOrCreate(['email' => 'superadmin@safitailoring.com'], [
            'name' => 'Super Admin',
            'phone' => '+93 700 000 001',
            'password' => 'password',
            'role' => 'super_admin',
        ]);

        $admin = User::firstOrCreate(['email' => 'admin@safitailoring.com'], [
            'name' => 'Business Admin',
            'phone' => '+93 700 000 002',
            'password' => 'password',
            'role' => 'admin',
        ]);

        $manager = User::firstOrCreate(['email' => 'manager@safitailoring.com'], [
            'name' => 'Kabul Shop Manager',
            'phone' => '+93 700 000 003',
            'password' => 'password',
            'role' => 'shop_manager',
            'shop_id' => $kabul->id,
        ]);

        $tailorUser = User::firstOrCreate(['email' => 'tailor@safitailoring.com'], [
            'name' => 'Ahmad Tailor',
            'phone' => '+93 700 000 004',
            'password' => 'password',
            'role' => 'tailor',
            'shop_id' => $kabul->id,
        ]);

        $tailor2 = User::firstOrCreate(['email' => 'tailor2@safitailoring.com'], [
            'name' => 'Mohammad Tailor',
            'phone' => '+93 700 000 005',
            'password' => 'password',
            'role' => 'tailor',
            'shop_id' => $kabul->id,
        ]);

        $customer = User::firstOrCreate(['email' => 'customer@safitailoring.com'], [
            'name' => 'Karim Customer',
            'phone' => '+93 700 000 006',
            'password' => 'password',
            'role' => 'customer',
            'address' => 'Karte Parwan, Kabul',
        ]);

        $customer2 = User::firstOrCreate(['email' => 'customer2@safitailoring.com'], [
            'name' => 'Rahim Customer',
            'phone' => '+93 700 000 007',
            'password' => 'password',
            'role' => 'customer',
            'address' => 'Shahr-e-Naw, Kabul',
        ]);

        $kabul->update(['manager_id' => $manager->id, 'manager_name' => $manager->name]);

        // ============ CATEGORIES ============
        $categories = [
            ['name' => 'Kameez', 'slug' => 'kameez', 'description' => 'Traditional kameez shirts'],
            ['name' => 'Shalwar', 'slug' => 'shalwar', 'description' => 'Traditional shalwar trousers'],
            ['name' => 'Waistcoat', 'slug' => 'waistcoat', 'description' => 'Formal and casual waistcoats'],
            ['name' => 'Suit', 'slug' => 'suit', 'description' => 'Two and three piece suits'],
            ['name' => 'Shirt', 'slug' => 'shirt', 'description' => 'Casual and formal shirts'],
            ['name' => 'Coat', 'slug' => 'coat', 'description' => 'Winter and formal coats'],
            ['name' => 'Traditional Wear', 'slug' => 'traditional-wear', 'description' => 'Afghan traditional clothing'],
            ['name' => 'Accessories', 'slug' => 'accessories', 'description' => 'Belts, ties, caps and more'],
        ];

        foreach ($categories as $c) {
            Category::firstOrCreate(['slug' => $c['slug']], $c);
        }

        $kameez = Category::where('slug', 'kameez')->first();
        $shalwar = Category::where('slug', 'shalwar')->first();
        $waistcoat = Category::where('slug', 'waistcoat')->first();
        $suit = Category::where('slug', 'suit')->first();
        $shirt = Category::where('slug', 'shirt')->first();
        $coat = Category::where('slug', 'coat')->first();
        $traditional = Category::where('slug', 'traditional-wear')->first();
        $accessories = Category::where('slug', 'accessories')->first();

        // ============ PRODUCTS ============
        $products = [
            ['name' => 'Classic White Kameez', 'slug' => 'classic-white-kameez', 'category_id' => $kameez->id, 'price' => 1500, 'sale_price' => 1200, 'fabric' => 'Wash & Wear', 'color' => 'White', 'size' => 'M, L, XL', 'featured' => true, 'description' => 'Premium wash & wear kameez with clean collar stitching. Perfect for daily wear.'],
            ['name' => 'Navy Blue Shalwar Kameez Set', 'slug' => 'navy-blue-shalwar-kameez-set', 'category_id' => $traditional->id, 'price' => 3200, 'fabric' => 'Wash & Wear', 'color' => 'Navy Blue', 'size' => 'L, XL', 'featured' => true, 'description' => 'Complete two-piece shalwar kameez set in rich navy blue.'],
            ['name' => 'Black Formal Waistcoat', 'slug' => 'black-formal-waistcoat', 'category_id' => $waistcoat->id, 'price' => 2400, 'sale_price' => 2000, 'fabric' => 'Polyester Blend', 'color' => 'Black', 'size' => 'M, L, XL', 'featured' => false, 'description' => 'Classic black waistcoat with satin back lining.'],
            ['name' => 'Grey Three Piece Suit', 'slug' => 'grey-three-piece-suit', 'category_id' => $suit->id, 'price' => 12500, 'fabric' => 'Wool Blend', 'color' => 'Grey', 'size' => '42, 44, 46', 'featured' => true, 'description' => 'Elegant three piece suit for weddings and formal events.'],
            ['name' => 'Beige Casual Shirt', 'slug' => 'beige-casual-shirt', 'category_id' => $shirt->id, 'price' => 1100, 'fabric' => 'Cotton', 'color' => 'Beige', 'size' => 'M, L', 'featured' => false, 'description' => 'Soft cotton casual shirt, breathable and comfortable.'],
            ['name' => 'Brown Winter Coat', 'slug' => 'brown-winter-coat', 'category_id' => $coat->id, 'price' => 6800, 'fabric' => 'Wool', 'color' => 'Brown', 'size' => 'L, XL', 'featured' => false, 'description' => 'Heavy wool winter coat with premium lining.'],
            ['name' => 'Emerald Traditional Kameez', 'slug' => 'emerald-traditional-kameez', 'category_id' => $traditional->id, 'price' => 2200, 'fabric' => 'Silk Blend', 'color' => 'Emerald', 'size' => 'M, L', 'featured' => true, 'description' => 'Festive emerald kameez with embroidered collar.'],
            ['name' => 'Leather Belt', 'slug' => 'leather-belt', 'category_id' => $accessories->id, 'price' => 450, 'fabric' => 'Leather', 'color' => 'Black / Brown', 'size' => 'All', 'featured' => false, 'description' => 'Genuine leather belt with metal buckle.'],
            ['name' => 'White Shalwar', 'slug' => 'white-shalwar', 'category_id' => $shalwar->id, 'price' => 900, 'fabric' => 'Cotton', 'color' => 'White', 'size' => 'M, L, XL', 'featured' => false, 'description' => 'Comfortable cotton shalwar, pairs with any kameez.'],
            ['name' => 'Charcoal Wool Suit', 'slug' => 'charcoal-wool-suit', 'category_id' => $suit->id, 'price' => 14000, 'sale_price' => 12800, 'fabric' => 'Wool', 'color' => 'Charcoal', 'size' => '44, 46', 'featured' => false, 'description' => 'Two piece charcoal suit, tailored fit.'],
        ];

        foreach ($products as $p) {
            $product = Product::firstOrCreate(['slug' => $p['slug']], array_merge($p, ['status' => 'active']));

            if ($product->images()->count() === 0) {
                $colors = ['stone', 'zinc', 'neutral', 'slate'];
                $imgName = 'products/' . $product->slug . '.svg';
                $color = $colors[array_rand($colors)];
                $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="800" height="1000"><rect width="800" height="1000" fill="' . $color . '"/><text x="400" y="480" font-size="48" text-anchor="middle" fill="#ffffff" font-family="sans-serif">' . $product->name . '</text></svg>';
                file_put_contents(storage_path('app/public/' . $imgName), $svg);
                $product->images()->create(['path' => $imgName, 'is_primary' => true, 'sort_order' => 0]);
            }
        }

        // ============ MEASUREMENT PROFILES ============
        $profile = MeasurementProfile::firstOrCreate(
            ['user_id' => $customer->id, 'name' => 'My Measurements'],
            [
                'height' => 175, 'chest' => 40, 'waist' => 36, 'hip' => 38,
                'shoulder' => 18, 'sleeve' => 25, 'neck' => 15,
                'shirt_length' => 42, 'trouser_length' => 40, 'wrist' => 7,
            ]
        );

        MeasurementProfile::firstOrCreate(
            ['user_id' => $customer->id, 'name' => 'Brother'],
            ['height' => 180, 'chest' => 42, 'waist' => 37, 'hip' => 40, 'shoulder' => 19, 'sleeve' => 26, 'neck' => 16, 'shirt_length' => 43, 'trouser_length' => 41, 'wrist' => 7.5]
        );

        // ============ EMPLOYEES ============
        $employees = [
            ['name' => 'Ahmad Tailor', 'position' => 'tailor', 'salary' => 15000, 'shop_id' => $kabul->id, 'user_id' => $tailorUser->id],
            ['name' => 'Mohammad Tailor', 'position' => 'tailor', 'salary' => 14000, 'shop_id' => $kabul->id, 'user_id' => $tailor2->id],
            ['name' => 'Omar Cashier', 'position' => 'cashier', 'salary' => 9000, 'shop_id' => $kabul->id],
            ['name' => 'Karim Delivery', 'position' => 'delivery', 'salary' => 8000, 'shop_id' => $kabul->id],
            ['name' => 'Hamid Tailor', 'position' => 'tailor', 'salary' => 13000, 'shop_id' => $jalalabad->id],
            ['name' => 'Zabi Manager', 'position' => 'manager', 'salary' => 20000, 'shop_id' => $jalalabad->id],
            ['name' => 'Fazel Tailor', 'position' => 'tailor', 'salary' => 12000, 'shop_id' => $herat->id],
            ['name' => 'Wali Tailor', 'position' => 'tailor', 'salary' => 12000, 'shop_id' => $kandahar->id],
        ];

        foreach ($employees as $e) {
            Employee::firstOrCreate(['name' => $e['name']], array_merge($e, ['status' => 'active', 'phone' => '+93 700 9' . rand(10000, 99999), 'joining_date' => now()->subMonths(rand(3, 24))]));
        }

        // ============ INVENTORY ============
        $inventoryItems = [
            ['name' => 'Wash & Wear Fabric', 'sku' => 'FAB-WW-001', 'category' => 'fabric', 'quantity' => 200, 'unit' => 'meter', 'cost' => 150, 'selling_price' => 220, 'min_stock' => 30],
            ['name' => 'Cotton Fabric', 'sku' => 'FAB-CT-002', 'category' => 'fabric', 'quantity' => 80, 'unit' => 'meter', 'cost' => 120, 'selling_price' => 180, 'min_stock' => 30],
            ['name' => 'Silk Fabric', 'sku' => 'FAB-SK-003', 'category' => 'fabric', 'quantity' => 25, 'unit' => 'meter', 'cost' => 400, 'selling_price' => 550, 'min_stock' => 20],
            ['name' => 'White Thread', 'sku' => 'THR-WH-001', 'category' => 'thread', 'quantity' => 150, 'unit' => 'pcs', 'cost' => 30, 'selling_price' => 50, 'min_stock' => 40],
            ['name' => 'Black Thread', 'sku' => 'THR-BK-002', 'category' => 'thread', 'quantity' => 10, 'unit' => 'pcs', 'cost' => 30, 'selling_price' => 50, 'min_stock' => 40],
            ['name' => 'Shirt Buttons', 'sku' => 'BTN-SH-001', 'category' => 'button', 'quantity' => 500, 'unit' => 'pcs', 'cost' => 2, 'selling_price' => 5, 'min_stock' => 100],
            ['name' => 'Waistcoat Buttons', 'sku' => 'BTN-WC-002', 'category' => 'button', 'quantity' => 45, 'unit' => 'pcs', 'cost' => 8, 'selling_price' => 15, 'min_stock' => 50],
            ['name' => 'Zippers', 'sku' => 'ACC-ZP-001', 'category' => 'accessory', 'quantity' => 120, 'unit' => 'pcs', 'cost' => 25, 'selling_price' => 45, 'min_stock' => 30],
            ['name' => 'Interlining', 'sku' => 'FAB-IN-004', 'category' => 'fabric', 'quantity' => 60, 'unit' => 'meter', 'cost' => 90, 'selling_price' => 140, 'min_stock' => 20],
            ['name' => 'Finished Kameez', 'sku' => 'FIN-KM-001', 'category' => 'finished', 'quantity' => 15, 'unit' => 'pcs', 'cost' => 900, 'selling_price' => 1500, 'min_stock' => 5],
        ];

        foreach ($shops as $shop) {
            foreach ($inventoryItems as $item) {
                $inv = InventoryItem::firstOrCreate(['shop_id' => $shop->id, 'sku' => $item['sku']], array_merge($item, ['status' => 'active']));
                if ($inv->movements()->count() === 0) {
                    StockMovement::create([
                        'inventory_id' => $inv->id,
                        'shop_id' => $shop->id,
                        'type' => 'in',
                        'quantity' => $item['quantity'],
                        'balance' => $item['quantity'],
                        'reason' => 'Initial stock (seeded)',
                    ]);
                }
            }
        }

        // ============ SUPPLIERS ============
        $suppliers = [
            ['name' => 'Ahmad Fabric House', 'company' => 'Ahmad Textiles Ltd', 'phone' => '+93 700 111 111', 'whatsapp' => '+93 700 111 111', 'email' => 'ahmad@fabrics.com', 'address' => 'Mandawi Market, Kabul'],
            ['name' => 'Karim Threads', 'company' => 'Karim Sewing Supplies', 'phone' => '+93 700 222 222', 'whatsapp' => '+93 700 222 222', 'email' => 'karim@threads.com', 'address' => 'Char Asiab, Kabul'],
            ['name' => 'Herat Silk Traders', 'company' => 'Herat Silk Co', 'phone' => '+93 700 333 333', 'email' => 'sales@heratsilk.com', 'address' => 'Herat City'],
        ];

        foreach ($suppliers as $s) {
            Supplier::firstOrCreate(['name' => $s['name']], array_merge($s, ['shop_id' => $kabul->id, 'status' => 'active']));
        }

        // ============ ORDERS ============
        $orderService = app(OrderService::class);

        $sampleOrders = [
            [
                'shop' => $kabul, 'user' => $customer, 'customer_name' => 'Karim Customer', 'customer_phone' => '+93 700 000 006',
                'type' => 'product', 'status' => 'completed', 'payment_status' => 'paid',
                'items' => [['product_id' => Product::where('slug', 'classic-white-kameez')->first()->id, 'name' => 'Classic White Kameez', 'quantity' => 2, 'price' => 1200]],
                'days_ago' => 20,
            ],
            [
                'shop' => $kabul, 'user' => $customer, 'customer_name' => 'Karim Customer', 'customer_phone' => '+93 700 000 006',
                'type' => 'custom', 'status' => 'stitching', 'payment_status' => 'unpaid',
                'items' => [[
                    'name' => 'Custom Kameez', 'garment_type' => 'Kameez', 'fabric' => 'Wash & Wear', 'color' => 'Blue',
                    'quantity' => 1, 'price' => 2500, 'measurement_profile_id' => $profile->id,
                    'measurements' => ['chest' => 40, 'waist' => 38, 'sleeve' => 24, 'length' => 42],
                    'instructions' => 'Simple collar, side pockets',
                ]],
                'tailor' => $tailorUser, 'expected' => now()->addDays(5),
                'days_ago' => 8,
            ],
            [
                'shop' => $kabul, 'user' => $customer2, 'customer_name' => 'Rahim Customer', 'customer_phone' => '+93 700 000 007',
                'type' => 'product', 'status' => 'pending', 'payment_status' => 'unpaid',
                'items' => [['product_id' => Product::where('slug', 'grey-three-piece-suit')->first()->id, 'name' => 'Grey Three Piece Suit', 'quantity' => 1, 'price' => 12500]],
                'days_ago' => 1,
            ],
            [
                'shop' => $jalalabad, 'user' => $customer2, 'customer_name' => 'Rahim Customer', 'customer_phone' => '+93 700 000 007',
                'type' => 'custom', 'status' => 'quality_check', 'payment_status' => 'partial',
                'items' => [[
                    'name' => 'Custom Waistcoat', 'garment_type' => 'Waistcoat', 'fabric' => 'Polyester Blend', 'color' => 'Black',
                    'quantity' => 1, 'price' => 1800,
                    'measurements' => ['chest' => 42, 'waist' => 38, 'shoulder' => 18, 'length' => 24],
                    'instructions' => 'Single breasted, satin back',
                ]],
                'tailor' => $tailor2, 'expected' => now()->addDays(2),
                'days_ago' => 12,
            ],
            [
                'shop' => $herat, 'user' => $customer, 'customer_name' => 'Karim Customer', 'customer_phone' => '+93 700 000 006',
                'type' => 'product', 'status' => 'ready', 'payment_status' => 'paid',
                'items' => [['product_id' => Product::where('slug', 'emerald-traditional-kameez')->first()->id, 'name' => 'Emerald Traditional Kameez', 'quantity' => 1, 'price' => 2200]],
                'days_ago' => 3,
            ],
            [
                'shop' => $kandahar, 'user' => $customer, 'customer_name' => 'Karim Customer', 'customer_phone' => '+93 700 000 006',
                'type' => 'product', 'status' => 'cancelled', 'payment_status' => 'unpaid',
                'items' => [['product_id' => Product::where('slug', 'brown-winter-coat')->first()->id, 'name' => 'Brown Winter Coat', 'quantity' => 1, 'price' => 6800]],
                'days_ago' => 5,
            ],
            [
                'shop' => $kabul, 'user' => $customer, 'customer_name' => 'Karim Customer', 'customer_phone' => '+93 700 000 006',
                'type' => 'product', 'status' => 'delivered', 'payment_status' => 'paid',
                'items' => [['product_id' => Product::where('slug', 'black-formal-waistcoat')->first()->id, 'name' => 'Black Formal Waistcoat', 'quantity' => 1, 'price' => 2000]],
                'days_ago' => 15,
            ],
        ];

        foreach ($sampleOrders as $i => $sample) {
            $existing = Order::where('customer_name', $sample['customer_name'])
                ->whereDate('created_at', now()->subDays($sample['days_ago'])->toDateString())
                ->first();

            if ($existing) {
                continue;
            }

            $subtotal = array_sum(array_map(fn ($item) => $item['price'] * $item['quantity'], $sample['items']));

            $order = $orderService->createOrder([
                'shop_id' => $sample['shop']->id,
                'customer_name' => $sample['customer_name'],
                'customer_phone' => $sample['customer_phone'],
                'customer_address' => $sample['user']->address,
                'payment_method' => 'cod',
                'delivery_method' => 'pickup',
                'delivery_fee' => 0,
                'subtotal' => $subtotal,
                'total' => $subtotal,
                'notes' => null,
                'items' => $sample['items'],
                'expected_completion_date' => $sample['expected'] ?? null,
            ], $sample['user']->id);

            $order->update([
                'type' => $sample['type'],
                'status' => $sample['status'],
                'payment_status' => $sample['payment_status'],
                'tailor_id' => $sample['tailor']->id ?? null,
                'created_at' => now()->subDays($sample['days_ago']),
                'updated_at' => now()->subDays($sample['days_ago']),
            ]);

            if (isset($sample['tailor'])) {
                $order->update(['assigned_at' => now()->subDays($sample['days_ago'] - 1)]);
            }

            if (in_array($sample['status'], ['delivered', 'completed'])) {
                $order->update(['completed_at' => now()->subDays($sample['days_ago'] - 2)]);
            }

            if ($sample['payment_status'] === 'paid') {
                $orderService->recordPayment($order, $subtotal, 'cod');
            } elseif ($sample['payment_status'] === 'partial') {
                $orderService->recordPayment($order, round($subtotal / 2), 'pay_at_shop');
            }

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'status' => $sample['status'],
                'note' => 'Seeded demo order',
                'changed_by' => $admin->id,
                'created_at' => now()->subDays($sample['days_ago']),
            ]);
        }

        // ============ INCOME & EXPENSES ============
        foreach ($shops as $shop) {
            foreach (range(0, 29) as $day) {
                $date = today()->subDays($day);
                if (Income::where('shop_id', $shop->id)->whereDate('date', $date)->count() === 0) {
                    Income::create([
                        'shop_id' => $shop->id,
                        'category' => 'product_sales',
                        'amount' => rand(2000, 12000),
                        'date' => $date,
                        'description' => 'Daily sales income',
                        'added_by' => $admin->id,
                    ]);
                    if ($day % 3 === 0) {
                        Income::create([
                            'shop_id' => $shop->id,
                            'category' => 'tailoring_services',
                            'amount' => rand(1000, 5000),
                            'date' => $date,
                            'description' => 'Custom tailoring services',
                            'added_by' => $admin->id,
                        ]);
                    }
                }
                if (Expense::where('shop_id', $shop->id)->whereDate('date', $date)->count() === 0) {
                    Expense::create([
                        'shop_id' => $shop->id,
                        'category' => 'salary',
                        'amount' => rand(1000, 3000),
                        'date' => $date,
                        'description' => 'Daily staff expense',
                        'added_by' => $admin->id,
                    ]);
                    if ($day % 7 === 0) {
                        Expense::create([
                            'shop_id' => $shop->id,
                            'category' => 'rent',
                            'amount' => rand(2000, 5000),
                            'date' => $date,
                            'description' => 'Shop rent share',
                            'added_by' => $admin->id,
                        ]);
                    }
                }
            }
        }

        // ============ ATTENDANCE ============
        $kabulEmployees = Employee::where('shop_id', $kabul->id)->get();
        foreach ($kabulEmployees as $employee) {
            foreach (range(1, 10) as $day) {
                $date = today()->subDays($day);
                Attendance::firstOrCreate(
                    ['employee_id' => $employee->id, 'date' => $date],
                    [
                        'shop_id' => $kabul->id,
                        'status' => ['present', 'present', 'present', 'late', 'absent'][rand(0, 4)],
                        'check_in' => '09:00',
                        'check_out' => '18:00',
                    ]
                );
            }
        }

        $this->command?->info('Demo data seeded successfully.');
    }
}
