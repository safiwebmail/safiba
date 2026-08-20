<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSupplierRequest;
use App\Http\Resources\SupplierResource;
use App\Models\Supplier;
use App\Support\ShopScope;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $shopIds = ShopScope::resolve($request);

        $suppliers = Supplier::whereIn('shop_id', $shopIds)
            ->when($request->query('search'), fn ($q, $s) => $q->where(fn ($q) => $q->where('name', 'ilike', "%{$s}%")->orWhere('company', 'ilike', "%{$s}%")->orWhere('phone', 'ilike', "%{$s}%")))
            ->when($request->query('status'), fn ($q, $s) => $q->where('status', $s))
            ->orderBy('name')
            ->paginate($request->query('per_page', 15));

        return $this->success(SupplierResource::collection($suppliers), 'Success');
    }

    public function store(StoreSupplierRequest $request)
    {
        $shopIds = ShopScope::resolve($request);
        $shopId = $request->user()->isAdmin() ? ($request->input('shop_id') ?? $shopIds[0]) : $request->user()->shop_id;

        if (!in_array($shopId, $shopIds)) {
            return $this->error('Unauthorized shop', 403);
        }

        $supplier = Supplier::create(array_merge($request->validated(), ['shop_id' => $shopId]));

        return $this->success(new SupplierResource($supplier), 'Supplier created', 201);
    }

    public function show(Request $request, Supplier $supplier)
    {
        $this->authorize('view', $supplier);

        return $this->success(new SupplierResource($supplier), 'Success');
    }

    public function update(StoreSupplierRequest $request, Supplier $supplier)
    {
        $this->authorize('update', $supplier);

        $supplier->update($request->validated());

        return $this->success(new SupplierResource($supplier), 'Supplier updated');
    }

    public function destroy(Request $request, Supplier $supplier)
    {
        $this->authorize('delete', $supplier);

        $supplier->delete();

        return $this->success(null, 'Supplier deleted');
    }
}
