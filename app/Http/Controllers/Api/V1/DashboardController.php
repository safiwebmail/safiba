<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Income;
use App\Models\InventoryItem;
use App\Models\Order;
use App\Models\User;
use App\Services\ReportService;
use App\Support\ShopScope;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(private ReportService $reportService)
    {
    }

    public function admin(Request $request)
    {
        $shopIds = ShopScope::resolve($request);

        $totalSales = Order::forShop($shopIds)->where('status', '!=', 'cancelled')->sum('total');
        $totalOrders = Order::forShop($shopIds)->count();
        $pendingOrders = Order::forShop($shopIds)->whereIn('status', ['pending', 'confirmed'])->count();
        $customers = User::where('role', 'customer')->count();
        $lowStock = InventoryItem::whereIn('shop_id', $shopIds)->whereColumn('quantity', '<=', 'min_stock')->count();

        $monthlyIncome = (float) Income::whereIn('shop_id', $shopIds)->whereYear('date', now()->year)->whereMonth('date', now()->month)->sum('amount')
            + (float) Order::forShop($shopIds)->whereBetween('created_at', [now()->startOfMonth(), now()])->where('status', '!=', 'cancelled')->sum('total');
        $monthlyExpenses = (float) Expense::whereIn('shop_id', $shopIds)->whereYear('date', now()->year)->whereMonth('date', now()->month)->sum('amount');

        $recentOrders = Order::forShop($shopIds)->with('shop')->orderBy('created_at', 'desc')->limit(8)->get()
            ->map(fn ($o) => [
                'id' => $o->id,
                'order_number' => $o->order_number,
                'customer_name' => $o->customer_name,
                'status' => $o->status,
                'total' => (float) $o->total,
                'shop_name' => $o->shop?->name,
                'created_at' => $o->created_at?->toISOString(),
            ]);

        $trend = $this->reportService->trend($shopIds, 30);

        $todaySummary = $this->reportService->summary($shopIds, today()->toDateString(), today()->toDateString());

        return $this->success([
            'total_sales' => round((float) $totalSales, 2),
            'total_orders' => $totalOrders,
            'pending_orders' => $pendingOrders,
            'customers' => $customers,
            'low_stock' => $lowStock,
            'monthly_income' => round($monthlyIncome, 2),
            'monthly_expenses' => round($monthlyExpenses, 2),
            'estimated_profit' => round($monthlyIncome - $monthlyExpenses, 2),
            'today_income' => $todaySummary['income'],
            'today_expenses' => $todaySummary['expenses'],
            'recent_orders' => $recentOrders,
            'trend' => $trend,
        ], 'Success');
    }

    public function tailor(Request $request)
    {
        $user = $request->user();

        $today = $user->assignedOrders()->whereDate('expected_completion_date', today())->count();
        $pending = $user->assignedOrders()->whereIn('status', ['assigned', 'cutting', 'stitching'])->count();
        $inProgress = $user->assignedOrders()->whereIn('status', ['cutting', 'stitching', 'quality_check'])->count();
        $completed = $user->assignedOrders()->where('status', 'completed')->count();
        $ready = $user->assignedOrders()->where('status', 'ready')->count();

        $tasks = $user->assignedOrders()
            ->with('shop', 'items', 'user:id,name,phone')
            ->whereIn('status', ['assigned', 'cutting', 'stitching', 'quality_check', 'ready'])
            ->orderBy('expected_completion_date')
            ->limit(10)
            ->get()
            ->map(fn ($o) => [
                'id' => $o->id,
                'order_number' => $o->order_number,
                'customer_name' => $o->customer_name,
                'status' => $o->status,
                'expected_completion_date' => $o->expected_completion_date?->toDateString(),
                'shop_name' => $o->shop?->name,
                'items' => $o->items->map(fn ($i) => [
                    'name' => $i->name,
                    'garment_type' => $i->garment_type,
                    'fabric' => $i->fabric,
                    'quantity' => $i->quantity,
                ])->values(),
            ]);

        return $this->success([
            'today_tasks' => $today,
            'pending' => $pending,
            'in_progress' => $inProgress,
            'ready' => $ready,
            'completed' => $completed,
            'tasks' => $tasks,
        ], 'Success');
    }
}
