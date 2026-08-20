<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFinanceRequest;
use App\Http\Resources\ExpenseResource;
use App\Models\AuditLog;
use App\Models\Expense;
use App\Support\ShopScope;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $shopIds = ShopScope::resolve($request);

        $expenses = Expense::whereIn('shop_id', $shopIds)
            ->when($request->query('category'), fn ($q, $c) => $q->where('category', $c))
            ->when($request->query('from'), fn ($q, $d) => $q->whereDate('date', '>=', $d))
            ->when($request->query('to'), fn ($q, $d) => $q->whereDate('date', '<=', $d))
            ->orderBy('date', 'desc')
            ->paginate($request->query('per_page', 15));

        return $this->success(ExpenseResource::collection($expenses), 'Success');
    }

    public function store(StoreFinanceRequest $request)
    {
        $shopIds = ShopScope::resolve($request);
        $shopId = $request->user()->isAdmin() ? ($request->input('shop_id') ?? $shopIds[0]) : $request->user()->shop_id;

        if (!in_array($shopId, $shopIds)) {
            return $this->error('Unauthorized shop', 403);
        }

        $data = $request->validated();

        if ($request->hasFile('receipt')) {
            $data['receipt_path'] = $request->file('receipt')->store('receipts', 'public');
        }

        $expense = Expense::create(array_merge($data, [
            'shop_id' => $shopId,
            'added_by' => $request->user()->id,
        ]));

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'expense.create',
            'entity_type' => 'expense',
            'entity_id' => $expense->id,
            'description' => "Expense of {$expense->amount} recorded",
        ]);

        return $this->success(new ExpenseResource($expense), 'Expense recorded', 201);
    }

    public function update(StoreFinanceRequest $request, Expense $expense)
    {
        if (!$request->user()->canAccessShop($expense->shop_id)) {
            return $this->error('Unauthorized', 403);
        }

        $data = $request->validated();

        if ($request->hasFile('receipt')) {
            $data['receipt_path'] = $request->file('receipt')->store('receipts', 'public');
        }

        $expense->update($data);

        return $this->success(new ExpenseResource($expense), 'Expense updated');
    }

    public function destroy(Request $request, Expense $expense)
    {
        if (!$request->user()->isAdmin() && !$request->user()->canAccessShop($expense->shop_id)) {
            return $this->error('Unauthorized', 403);
        }

        $expense->delete();

        return $this->success(null, 'Expense deleted');
    }
}
