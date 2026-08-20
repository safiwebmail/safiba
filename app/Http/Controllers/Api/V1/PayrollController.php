<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePayrollRequest;
use App\Http\Resources\PayrollResource;
use App\Models\AuditLog;
use App\Models\Payroll;
use App\Support\ShopScope;
use Illuminate\Http\Request;

class PayrollController extends Controller
{
    public function index(Request $request)
    {
        $shopIds = ShopScope::resolve($request);

        $payroll = Payroll::with('employee')
            ->whereIn('shop_id', $shopIds)
            ->when($request->query('from'), fn ($q, $d) => $q->whereDate('payment_date', '>=', $d))
            ->when($request->query('to'), fn ($q, $d) => $q->whereDate('payment_date', '<=', $d))
            ->orderBy('payment_date', 'desc')
            ->paginate($request->query('per_page', 15));

        return $this->success(PayrollResource::collection($payroll), 'Success');
    }

    public function store(StorePayrollRequest $request)
    {
        $shopIds = ShopScope::resolve($request);
        $shopId = $request->user()->isAdmin() ? ($request->input('shop_id') ?? $shopIds[0]) : $request->user()->shop_id;

        if (!in_array($shopId, $shopIds)) {
            return $this->error('Unauthorized shop', 403);
        }

        $validated = $request->validated();
        $validated['net_salary'] = $validated['base_salary'] + ($validated['bonus'] ?? 0) - ($validated['deduction'] ?? 0);

        $payroll = Payroll::create(array_merge($validated, [
            'shop_id' => $shopId,
            'added_by' => $request->user()->id,
        ]));

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'payroll.create',
            'entity_type' => 'payroll',
            'entity_id' => $payroll->id,
            'description' => "Payroll of {$validated['net_salary']} paid",
        ]);

        return $this->success(new PayrollResource($payroll->load('employee')), 'Payroll recorded', 201);
    }

    public function destroy(Request $request, Payroll $payroll)
    {
        if (!$request->user()->isAdmin() && !$request->user()->canAccessShop($payroll->shop_id)) {
            return $this->error('Unauthorized', 403);
        }

        $payroll->delete();

        return $this->success(null, 'Payroll deleted');
    }
}
