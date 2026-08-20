<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StockAdjustRequest;
use App\Http\Requests\StoreInventoryRequest;
use App\Http\Resources\InventoryResource;
use App\Models\AuditLog;
use App\Models\InventoryItem;
use App\Models\StockMovement;
use App\Models\User;
use App\Notifications\LowStockNotification;
use App\Services\InventoryService;
use App\Support\ShopScope;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function __construct(private InventoryService $inventoryService)
    {
    }

    public function index(Request $request)
    {
        if (!$request->user()->isShopManager()) {
            return $this->error('Unauthorized', 403);
        }

        $shopIds = ShopScope::resolve($request);

        $items = InventoryItem::whereIn('shop_id', $shopIds)
            ->when($request->query('search'), fn ($q, $s) => $q->where(fn ($q) => $q->where('name', 'ilike', "%{$s}%")->orWhere('sku', 'ilike', "%{$s}%")))
            ->when($request->query('category'), fn ($q, $c) => $q->where('category', $c))
            ->when($request->query('status'), fn ($q, $s) => $q->where('status', $s))
            ->when($request->query('low_stock'), fn ($q) => $q->whereColumn('quantity', '<=', 'min_stock'))
            ->orderBy('name')
            ->paginate($request->query('per_page', 15));

        return $this->success(InventoryResource::collection($items), 'Success');
    }

    public function store(StoreInventoryRequest $request)
    {
        $shopIds = ShopScope::resolve($request);
        $shopId = $request->user()->isAdmin() ? ($request->input('shop_id') ?? $shopIds[0]) : $request->user()->shop_id;

        if (!in_array($shopId, $shopIds)) {
            return $this->error('Unauthorized shop', 403);
        }

        $data = $request->validated();
        $data['shop_id'] = $shopId;
        $data['quantity'] = $data['quantity'] ?? 0;

        $item = InventoryItem::create($data);

        if ($item->quantity > 0) {
            $this->inventoryService->adjust($item, 'in', $item->quantity, 'Initial stock', null, $request->user()->id);
        }

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'inventory.create',
            'entity_type' => 'inventory',
            'entity_id' => $item->id,
            'description' => "Inventory item '{$item->name}' created",
        ]);

        return $this->success(new InventoryResource($item), 'Inventory item created', 201);
    }

    public function show(Request $request, InventoryItem $item)
    {
        $this->authorize('view', $item);

        $item->load('movements');

        return $this->success([
            'item' => new InventoryResource($item),
            'movements' => $item->movements->map(fn ($m) => [
                'type' => $m->type,
                'quantity' => (float) $m->quantity,
                'balance' => (float) $m->balance,
                'reason' => $m->reason,
                'reference' => $m->reference,
                'created_at' => $m->created_at?->toISOString(),
            ])->values(),
        ], 'Success');
    }

    public function update(StoreInventoryRequest $request, InventoryItem $item)
    {
        $this->authorize('update', $item);

        $item->update($request->validated());

        return $this->success(new InventoryResource($item), 'Inventory item updated');
    }

    public function adjust(StockAdjustRequest $request, InventoryItem $item)
    {
        $this->authorize('update', $item);

        $validated = $request->validated();

        $type = $validated['type'] === 'out' ? 'out' : 'in';

        $item = $this->inventoryService->adjust(
            $item,
            $type,
            $validated['quantity'],
            $validated['reason'] ?? null,
            $validated['reference'] ?? null,
            $request->user()->id
        );

        if ($item->is_low_stock) {
            User::whereIn('role', ['super_admin', 'admin'])->get()->each->notify(new LowStockNotification($item));
        }

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'inventory.adjust',
            'entity_type' => 'inventory',
            'entity_id' => $item->id,
            'description' => "Stock adjusted ({$type}) for '{$item->name}'",
        ]);

        return $this->success(new InventoryResource($item), 'Stock adjusted');
    }

    public function destroy(Request $request, InventoryItem $item)
    {
        $this->authorize('delete', $item);

        $item->delete();

        return $this->success(null, 'Inventory item deleted');
    }

    public function movements(Request $request)
    {
        $shopIds = ShopScope::resolve($request);

        $movements = StockMovement::with('inventory')
            ->whereIn('shop_id', $shopIds)
            ->when($request->query('type'), fn ($q, $t) => $q->where('type', $t))
            ->orderBy('created_at', 'desc')
            ->paginate($request->query('per_page', 15));

        return $this->success($movements->map(fn ($m) => [
            'id' => $m->id,
            'item' => $m->inventory?->name,
            'type' => $m->type,
            'quantity' => (float) $m->quantity,
            'balance' => (float) $m->balance,
            'reason' => $m->reason,
            'created_at' => $m->created_at?->toISOString(),
        ])->values(), 'Success');
    }
}
