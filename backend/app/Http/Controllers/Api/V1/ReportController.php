<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\ReportService;
use App\Support\ShopScope;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct(private ReportService $reportService)
    {
    }

    public function summary(Request $request)
    {
        $shopIds = ShopScope::resolve($request);

        $summary = $this->reportService->summary(
            $shopIds,
            $request->query('from'),
            $request->query('to')
        );

        return $this->success($summary, 'Success');
    }

    public function trend(Request $request)
    {
        $shopIds = ShopScope::resolve($request);

        return $this->success(
            $this->reportService->trend($shopIds, (int) $request->query('days', 30)),
            'Success'
        );
    }

    public function sales(Request $request)
    {
        $shopIds = ShopScope::resolve($request);

        $period = $request->query('period', 'monthly');

        $groupBy = match ($period) {
            'daily' => 'to_char(date(created_at), \'YYYY-MM-DD\')',
            'weekly' => 'to_char(date(created_at), \'IYYY-IW\')',
            'yearly' => 'to_char(date(created_at), \'YYYY\')',
            default => 'to_char(date(created_at), \'YYYY-MM\')',
        };

        $sales = Order::forShop($shopIds)
            ->where('status', '!=', 'cancelled')
            ->when($request->query('from'), fn ($q, $d) => $q->whereDate('created_at', '>=', $d))
            ->when($request->query('to'), fn ($q, $d) => $q->whereDate('created_at', '<=', $d))
            ->selectRaw("{$groupBy} as period, sum(total) as total, count(*) as count")
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        return $this->success($sales, 'Success');
    }

    public function orders(Request $request)
    {
        $shopIds = ShopScope::resolve($request);

        $orders = Order::forShop($shopIds)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->get();

        return $this->success($orders, 'Success');
    }

    public function tailors(Request $request)
    {
        $shopIds = ShopScope::resolve($request);

        $tailors = Order::forShop($shopIds)
            ->whereNotNull('tailor_id')
            ->with('tailor:id,name')
            ->selectRaw('tailor_id, count(*) as total, sum(case when status in (\'assigned\',\'cutting\',\'stitching\',\'quality_check\') then 1 else 0 end) as in_progress')
            ->groupBy('tailor_id')
            ->get()
            ->map(fn ($row) => [
                'tailor' => $row->tailor?->name,
                'total' => $row->total,
                'in_progress' => $row->in_progress,
            ]);

        return $this->success($tailors, 'Success');
    }

    public function lowStock(Request $request)
    {
        $shopIds = ShopScope::resolve($request);

        $items = \App\Models\InventoryItem::whereIn('shop_id', $shopIds)
            ->whereColumn('quantity', '<=', 'min_stock')
            ->orderByRaw('quantity - min_stock asc')
            ->get();

        return $this->success($items, 'Success');
    }
}
