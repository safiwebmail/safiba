<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreShopRequest;
use App\Http\Requests\UpdateShopRequest;
use App\Http\Resources\ShopResource;
use App\Models\AuditLog;
use App\Models\Shop;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $shops = Shop::with('manager')
            ->when(!$user || !$user->isAdmin(), fn ($q) => $q->where('status', 'active'))
            ->when($user && !$user->isAdmin() && !$user->isCustomer(), fn ($q) => $q->whereIn('id', $user->accessibleShopIds()))
            ->when($request->query('status') && $user && $user->isAdmin(), fn ($q, $s) => $q->where('status', $s))
            ->orderBy('name')
            ->get();

        return $this->success(ShopResource::collection($shops), 'Success');
    }

    public function store(StoreShopRequest $request)
    {
        $shop = Shop::create($request->validated());

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'shop.create',
            'entity_type' => 'shop',
            'entity_id' => $shop->id,
            'description' => "Shop '{$shop->name}' created",
        ]);

        return $this->success(new ShopResource($shop), 'Shop created', 201);
    }

    public function show(Request $request, Shop $shop)
    {
        $this->authorize('view', $shop);

        $shop->load('manager');

        return $this->success(new ShopResource($shop), 'Success');
    }

    public function update(UpdateShopRequest $request, Shop $shop)
    {
        $this->authorize('update', $shop);

        $shop->update($request->validated());

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'shop.update',
            'entity_type' => 'shop',
            'entity_id' => $shop->id,
            'description' => "Shop '{$shop->name}' updated",
        ]);

        return $this->success(new ShopResource($shop), 'Shop updated');
    }

    public function destroy(Request $request, Shop $shop)
    {
        $this->authorize('delete', $shop);

        $shop->delete();

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'shop.delete',
            'entity_type' => 'shop',
            'entity_id' => $shop->id,
            'description' => "Shop '{$shop->name}' deleted",
        ]);

        return $this->success(null, 'Shop deleted');
    }
}
