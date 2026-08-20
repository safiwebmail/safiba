<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\AssignTailorRequest;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderStatusRequest;
use App\Http\Resources\OrderResource;
use App\Models\AuditLog;
use App\Models\Order;
use App\Models\User;
use App\Notifications\OrderPlacedNotification;
use App\Notifications\OrderStatusNotification;
use App\Services\OrderService;
use App\Support\ShopScope;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(private OrderService $orderService)
    {
    }

    public function index(Request $request)
    {
        $user = $request->user();

        $orders = Order::with(['shop', 'tailor', 'items', 'statusHistory.changedBy', 'payments', 'delivery'])
            ->when($user->isCustomer(), fn ($q) => $q->where('user_id', $user->id))
            ->when($user->isTailor(), fn ($q) => $q->where('tailor_id', $user->id))
            ->when(!$user->isCustomer() && !$user->isTailor(), fn ($q) => $q->forShop(ShopScope::resolve($request)))
            ->when($request->query('status'), fn ($q, $s) => $q->where('status', $s))
            ->when($request->query('type'), fn ($q, $t) => $q->where('type', $t))
            ->when($request->query('search'), fn ($q, $s) => $q->where(fn ($q) => $q->where('order_number', 'ilike', "%{$s}%")->orWhere('customer_name', 'ilike', "%{$s}%")->orWhere('customer_phone', 'ilike', "%{$s}%")))
            ->when($request->query('from'), fn ($q, $d) => $q->whereDate('created_at', '>=', $d))
            ->when($request->query('to'), fn ($q, $d) => $q->whereDate('created_at', '<=', $d))
            ->orderBy('created_at', 'desc')
            ->paginate($request->query('per_page', 15));

        return $this->success(OrderResource::collection($orders), 'Success');
    }

    public function store(StoreOrderRequest $request)
    {
        $data = $request->validated();

        $user = $request->user();

        if (!$user->isAdmin() && !$user->isCustomer() && !$user->canAccessShop($data['shop_id'])) {
            return $this->error('You are not authorized to place orders for this shop.', 403);
        }

        $items = $data['items'];

        $subtotal = 0;
        foreach ($items as &$item) {
            $item['total'] = $item['price'] * $item['quantity'];
            $subtotal += $item['total'];
        }

        if ($request->hasFile('design_image')) {
            $items[0]['design_image'] = $request->file('design_image')->store('designs', 'public');
        }

        $deliveryFee = $data['delivery_method'] === 'delivery'
            ? ($data['delivery_fee'] ?? (float) (optional(\App\Models\Business::first())->default_delivery_fee ?? 0))
            : 0;

        $data['subtotal'] = $subtotal;
        $data['delivery_fee'] = $deliveryFee;
        $data['total'] = $subtotal + $deliveryFee;
        $data['items'] = $items;

        $isCustom = ($data['type'] ?? null) === 'custom'
            || collect($items)->contains(fn ($item) => !empty($item['measurements']) || !empty($item['garment_type']));

        $order = $this->orderService->createOrder($data, $user->id, $isCustom);

        // Notify shop managers
        $managers = User::whereIn('role', ['super_admin', 'admin'])
            ->orWhere(fn ($q) => $q->where('role', 'shop_manager')->where('shop_id', $order->shop_id))
            ->get();
        foreach ($managers as $manager) {
            $manager->notify(new OrderPlacedNotification($order));
        }

        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'order.create',
            'entity_type' => 'order',
            'entity_id' => $order->id,
            'description' => "Order {$order->order_number} created",
        ]);

        return $this->success(new OrderResource($order->load('shop', 'items', 'statusHistory', 'delivery')), 'Order placed successfully', 201);
    }

    public function show(Request $request, Order $order)
    {
        $this->authorize('view', $order);

        $order->load(['shop', 'tailor', 'items', 'statusHistory.changedBy', 'payments', 'delivery']);

        return $this->success(new OrderResource($order), 'Success');
    }

    public function updateStatus(UpdateOrderStatusRequest $request, Order $order)
    {
        $this->authorize('updateStatus', $order);

        $order = $this->orderService->updateStatus(
            $order,
            $request->validated('status'),
            $request->validated('note'),
            $request->user()->id
        );

        if ($order->user_id) {
            $message = "Your order {$order->order_number} is now {$order->status}.";
            if ($order->status === 'ready') {
                $message = "Your order {$order->order_number} is ready!";
            }
            if ($order->status === 'completed') {
                $message = "Your order {$order->order_number} is completed. Thank you!";
            }
            if ($order->status === 'cancelled') {
                $message = "Your order {$order->order_number} was cancelled.";
            }
            $order->user?->notify(new OrderStatusNotification($order, $message));
        }

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'order.status',
            'entity_type' => 'order',
            'entity_id' => $order->id,
            'description' => "Order {$order->order_number} status changed to {$order->status}",
        ]);

        return $this->success(new OrderResource($order->load('shop', 'tailor', 'items', 'statusHistory', 'payments', 'delivery')), 'Order status updated');
    }

    public function assignTailor(AssignTailorRequest $request, Order $order)
    {
        $this->authorize('update', $order);

        if ($order->type !== 'custom' && $order->status === 'pending') {
            $order = $this->orderService->updateStatus($order, 'confirmed', 'Confirmed by admin', $request->user()->id);
        }

        $order = $this->orderService->assignTailor($order, $request->validated('tailor_id'), $request->validated('note'));

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'order.assign',
            'entity_type' => 'order',
            'entity_id' => $order->id,
            'description' => "Order {$order->order_number} assigned to tailor #{$order->tailor_id}",
        ]);

        return $this->success(new OrderResource($order->load('shop', 'tailor', 'items', 'statusHistory', 'payments', 'delivery')), 'Tailor assigned');
    }

    public function dashboard(Request $request)
    {
        $shopIds = ShopScope::resolve($request);

        $byStatus = Order::forShop($shopIds)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $today = Order::forShop($shopIds)->whereDate('created_at', today())->count();
        $pending = Order::forShop($shopIds)->whereIn('status', ['pending', 'confirmed'])->count();
        $inProduction = Order::forShop($shopIds)->whereIn('status', ['assigned', 'cutting', 'stitching', 'quality_check'])->count();
        $ready = Order::forShop($shopIds)->where('status', 'ready')->count();
        $completed = Order::forShop($shopIds)->where('status', 'completed')->count();
        $monthlyRevenue = Order::forShop($shopIds)->whereBetween('created_at', [now()->startOfMonth(), now()])->sum('total');

        return $this->success([
            'today' => $today,
            'pending' => $pending,
            'in_production' => $inProduction,
            'ready' => $ready,
            'completed' => $completed,
            'monthly_revenue' => round((float) $monthlyRevenue, 2),
            'by_status' => $byStatus,
        ], 'Success');
    }
}
