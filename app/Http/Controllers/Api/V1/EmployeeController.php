<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Resources\EmployeeResource;
use App\Models\Employee;
use App\Support\ShopScope;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $shopIds = ShopScope::resolve($request);

        $employees = Employee::whereIn('shop_id', $shopIds)
            ->when($request->query('search'), fn ($q, $s) => $q->where(fn ($q) => $q->where('name', 'ilike', "%{$s}%")->orWhere('phone', 'ilike', "%{$s}%")))
            ->when($request->query('position'), fn ($q, $p) => $q->where('position', $p))
            ->when($request->query('status'), fn ($q, $s) => $q->where('status', $s))
            ->orderBy('name')
            ->paginate($request->query('per_page', 15));

        return $this->success(EmployeeResource::collection($employees), 'Success');
    }

    public function store(StoreEmployeeRequest $request)
    {
        $shopIds = ShopScope::resolve($request);
        $shopId = $request->user()->isAdmin() ? ($request->input('shop_id') ?? $shopIds[0]) : $request->user()->shop_id;

        if (!in_array($shopId, $shopIds)) {
            return $this->error('Unauthorized shop', 403);
        }

        $data = $request->validated();

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('employees', 'public');
        }

        $employee = Employee::create(array_merge($data, ['shop_id' => $shopId]));

        return $this->success(new EmployeeResource($employee), 'Employee created', 201);
    }

    public function show(Request $request, Employee $employee)
    {
        $this->authorize('view', $employee);

        return $this->success(new EmployeeResource($employee), 'Success');
    }

    public function update(StoreEmployeeRequest $request, Employee $employee)
    {
        $this->authorize('update', $employee);

        $data = $request->validated();

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('employees', 'public');
        }

        $employee->update($data);

        return $this->success(new EmployeeResource($employee), 'Employee updated');
    }

    public function destroy(Request $request, Employee $employee)
    {
        $this->authorize('delete', $employee);

        $employee->delete();

        return $this->success(null, 'Employee deleted');
    }

    public function tailors(Request $request)
    {
        $shopIds = ShopScope::resolve($request);

        $tailors = Employee::whereIn('shop_id', $shopIds)
            ->where('position', 'tailor')
            ->where('status', 'active')
            ->get(['id', 'name', 'shop_id', 'position']);

        return $this->success($tailors, 'Success');
    }
}
