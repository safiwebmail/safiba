<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        if (!$request->user()->isShopManager()) {
            return $this->error('Unauthorized', 403);
        }

        $customers = User::where('role', 'customer')
            ->when($request->query('search'), fn ($q, $s) => $q->where(fn ($q) => $q->where('name', 'ilike', "%{$s}%")->orWhere('email', 'ilike', "%{$s}%")->orWhere('phone', 'ilike', "%{$s}%")))
            ->withCount('orders')
            ->orderBy('name')
            ->paginate($request->query('per_page', 15));

        return $this->success($customers->through(fn ($c) => [
            'id' => $c->id,
            'name' => $c->name,
            'email' => $c->email,
            'phone' => $c->phone,
            'address' => $c->address,
            'orders_count' => $c->orders_count,
            'total_spent' => round((float) Order::where('user_id', $c->id)->where('status', '!=', 'cancelled')->sum('total'), 2),
            'created_at' => $c->created_at?->toISOString(),
        ]), 'Success');
    }

    public function show(Request $request, User $user)
    {
        if (!$request->user()->isShopManager()) {
            return $this->error('Unauthorized', 403);
        }

        if ($user->role !== 'customer') {
            return $this->error('Not a customer', 404);
        }

        $orders = Order::where('user_id', $user->id)
            ->with('shop')
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get()
            ->map(fn ($o) => [
                'id' => $o->id,
                'order_number' => $o->order_number,
                'status' => $o->status,
                'total' => (float) $o->total,
                'shop_name' => $o->shop?->name,
                'created_at' => $o->created_at?->toISOString(),
            ]);

        return $this->success([
            'customer' => new UserResource($user),
            'total_spent' => round((float) Order::where('user_id', $user->id)->where('status', '!=', 'cancelled')->sum('total'), 2),
            'orders' => $orders,
        ], 'Success');
    }
}
