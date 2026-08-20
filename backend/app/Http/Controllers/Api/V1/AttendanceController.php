<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAttendanceRequest;
use App\Http\Resources\AttendanceResource;
use App\Models\Attendance;
use App\Support\ShopScope;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $shopIds = ShopScope::resolve($request);

        $attendance = Attendance::with('employee')
            ->whereIn('shop_id', $shopIds)
            ->when($request->query('date'), fn ($q, $d) => $q->whereDate('date', $d))
            ->when($request->query('status'), fn ($q, $s) => $q->where('status', $s))
            ->when($request->query('from'), fn ($q, $d) => $q->whereDate('date', '>=', $d))
            ->when($request->query('to'), fn ($q, $d) => $q->whereDate('date', '<=', $d))
            ->orderBy('date', 'desc')
            ->paginate($request->query('per_page', 15));

        return $this->success(AttendanceResource::collection($attendance), 'Success');
    }

    public function store(StoreAttendanceRequest $request)
    {
        $shopIds = ShopScope::resolve($request);
        $shopId = $request->user()->isAdmin() ? ($request->input('shop_id') ?? $shopIds[0]) : $request->user()->shop_id;

        if (!in_array($shopId, $shopIds)) {
            return $this->error('Unauthorized shop', 403);
        }

        $existing = Attendance::where('employee_id', $request->validated('employee_id'))
            ->whereDate('date', $request->validated('date'))
            ->first();

        if ($existing) {
            return $this->error('Attendance already recorded for this employee on this date', 422);
        }

        $attendance = Attendance::create(array_merge($request->validated(), ['shop_id' => $shopId]));

        return $this->success(new AttendanceResource($attendance->load('employee')), 'Attendance recorded', 201);
    }

    public function update(StoreAttendanceRequest $request, Attendance $attendance)
    {
        if (!$request->user()->canAccessShop($attendance->shop_id)) {
            return $this->error('Unauthorized', 403);
        }

        $attendance->update($request->validated());

        return $this->success(new AttendanceResource($attendance->load('employee')), 'Attendance updated');
    }

    public function monthly(Request $request)
    {
        $shopIds = ShopScope::resolve($request);

        $month = $request->query('month', now()->format('Y-m'));

        $summary = Attendance::with('employee')
            ->whereIn('shop_id', $shopIds)
            ->whereRaw("to_char(date, 'YYYY-MM') = ?", [$month])
            ->get()
            ->groupBy('employee_id')
            ->map(fn ($rows) => [
                'employee_id' => $rows->first()->employee_id,
                'employee_name' => $rows->first()->employee?->name,
                'present' => $rows->where('status', 'present')->count(),
                'absent' => $rows->where('status', 'absent')->count(),
                'late' => $rows->where('status', 'late')->count(),
            ])
            ->values();

        return $this->success($summary, 'Success');
    }
}
