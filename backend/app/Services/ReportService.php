<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\Income;
use App\Models\Order;
use Carbon\CarbonImmutable;

class ReportService
{
    public function summary(array $shopIds, ?string $from = null, ?string $to = null): array
    {
        $from = $from ? CarbonImmutable::parse($from)->startOfDay() : CarbonImmutable::today()->startOfDay();
        $to = $to ? CarbonImmutable::parse($to)->endOfDay() : CarbonImmutable::today()->endOfDay();

        $orders = Order::forShop($shopIds)->whereBetween('created_at', [$from, $to]);
        $income = Income::whereIn('shop_id', $shopIds)->whereBetween('date', [$from->toDateString(), $to->toDateString()]);
        $expenses = Expense::whereIn('shop_id', $shopIds)->whereBetween('date', [$from->toDateString(), $to->toDateString()]);

        $orderTotal = (float) (clone $orders)->sum('total');
        $orderCount = (clone $orders)->count();

        $incomeTotal = (float) (clone $income)->sum('amount') + $orderTotal;
        $expenseTotal = (float) (clone $expenses)->sum('amount');

        return [
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'total_orders' => $orderCount,
            'order_revenue' => round($orderTotal, 2),
            'income' => round($incomeTotal, 2),
            'expenses' => round($expenseTotal, 2),
            'profit' => round($incomeTotal - $expenseTotal, 2),
        ];
    }

    public function trend(array $shopIds, int $days = 30): array
    {
        $start = CarbonImmutable::today()->subDays($days - 1);
        $income = Income::whereIn('shop_id', $shopIds)
            ->whereBetween('date', [$start->toDateString(), CarbonImmutable::today()->toDateString()])
            ->selectRaw('date, sum(amount) as total')
            ->groupBy('date')
            ->pluck('total', 'date');

        $expenses = Expense::whereIn('shop_id', $shopIds)
            ->whereBetween('date', [$start->toDateString(), CarbonImmutable::today()->toDateString()])
            ->selectRaw('date, sum(amount) as total')
            ->groupBy('date')
            ->pluck('total', 'date');

        $orders = Order::forShop($shopIds)
            ->whereBetween('created_at', [$start->startOfDay(), now()])
            ->selectRaw('date(created_at) as date, sum(total) as total')
            ->groupBy('date')
            ->pluck('total', 'date');

        $points = [];
        for ($i = 0; $i < $days; $i++) {
            $day = $start->addDays($i)->toDateString();
            $points[] = [
                'date' => $day,
                'income' => round((float) ($income[$day] ?? 0), 2),
                'expenses' => round((float) ($expenses[$day] ?? 0), 2),
                'orders' => round((float) ($orders[$day] ?? 0), 2),
            ];
        }

        return $points;
    }
}
