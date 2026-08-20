<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFinanceRequest;
use App\Http\Resources\IncomeResource;
use App\Models\AuditLog;
use App\Models\Income;
use App\Support\ShopScope;
use Illuminate\Http\Request;

class IncomeController extends Controller
{
    public function index(Request $request)
    {
        $shopIds = ShopScope::resolve($request);

        $income = Income::whereIn('shop_id', $shopIds)
            ->when($request->query('category'), fn ($q, $c) => $q->where('category', $c))
            ->when($request->query('from'), fn ($q, $d) => $q->whereDate('date', '>=', $d))
            ->when($request->query('to'), fn ($q, $d) => $q->whereDate('date', '<=', $d))
            ->orderBy('date', 'desc')
            ->paginate($request->query('per_page', 15));

        return $this->success(IncomeResource::collection($income), 'Success');
    }

    public function store(StoreFinanceRequest $request)
    {
        $shopIds = ShopScope::resolve($request);
        $shopId = $request->user()->isAdmin() ? ($request->input('shop_id') ?? $shopIds[0]) : $request->user()->shop_id;

        if (!in_array($shopId, $shopIds)) {
            return $this->error('Unauthorized shop', 403);
        }

        $income = Income::create(array_merge($request->validated(), [
            'shop_id' => $shopId,
            'added_by' => $request->user()->id,
        ]));

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'income.create',
            'entity_type' => 'income',
            'entity_id' => $income->id,
            'description' => "Income of {$income->amount} recorded",
        ]);

        return $this->success(new IncomeResource($income), 'Income recorded', 201);
    }

    public function update(StoreFinanceRequest $request, Income $income)
    {
        if (!$request->user()->canAccessShop($income->shop_id)) {
            return $this->error('Unauthorized', 403);
        }

        $income->update($request->validated());

        return $this->success(new IncomeResource($income), 'Income updated');
    }

    public function destroy(Request $request, Income $income)
    {
        if (!$request->user()->isAdmin() && !$request->user()->canAccessShop($income->shop_id)) {
            return $this->error('Unauthorized', 403);
        }

        $income->delete();

        return $this->success(null, 'Income deleted');
    }
}
