<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Expense;
use App\Models\Order;
use App\Models\Branch;
use Carbon\Carbon;

class ExpenseStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $stats = [];
        $currency_symbol = config('settings.currency_symbol', 'AED');
        
        $today = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();
        $lastMonth = Carbon::now()->subMonth()->startOfMonth();
        $lastMonthEnd = Carbon::now()->subMonth()->endOfMonth();

        // Get all active branches excluding transport
        $branches = Branch::where('is_active', true)
            ->where('name', 'not like', '%transport%')
            ->get();
        
        foreach ($branches as $branch) {
            // Today's expenses for this branch
            $todayExpenses = Expense::where('branch_id', $branch->id)
                ->where('is_approved', true)
                ->whereDate('expense_date', $today)
                ->sum('amount');

            // This month's expenses for this branch
            $thisMonthExpenses = Expense::where('branch_id', $branch->id)
                ->where('is_approved', true)
                ->where('expense_date', '>=', $thisMonth)
                ->sum('amount');

            // Last month's expenses for this branch
            $lastMonthExpenses = Expense::where('branch_id', $branch->id)
                ->where('is_approved', true)
                ->whereBetween('expense_date', [$lastMonth, $lastMonthEnd])
                ->sum('amount');

            // This month's revenue for this branch
            $thisMonthRevenue = Order::where('branch_id', $branch->id)
                ->where('order_date', '>=', $thisMonth)
                ->sum('price');

            // Profit calculation
            $profit = $thisMonthRevenue - $thisMonthExpenses;

            // Expense growth percentage
            $expenseGrowth = $lastMonthExpenses > 0 ? (($thisMonthExpenses - $lastMonthExpenses) / $lastMonthExpenses) * 100 : 0;

            // Today's Expenses Card
            // $stats[] = Stat::make($branch->name . ' - Today\'s Expenses', $currency_symbol . ' ' . number_format($todayExpenses, 2))
            //     ->description('Expenses recorded today')
            //     ->descriptionIcon('heroicon-m-arrow-trending-up')
            //     ->color('danger')
            //     ->chart([3, 5, 4, 6, 5, 7, 6]);

            // Monthly Expenses Card
            $stats[] = Stat::make($branch->name . ' - Monthly Expenses', $currency_symbol . ' ' . number_format($thisMonthExpenses, 2))
                ->description($expenseGrowth >= 0 ? '+' . number_format($expenseGrowth, 1) . '% from last month' : number_format($expenseGrowth, 1) . '% from last month')
                ->descriptionIcon($expenseGrowth >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($expenseGrowth >= 0 ? 'danger' : 'success')
                ->chart([7, 8, 6, 9, 8, 7, 9]);

            // Monthly Revenue Card
            $stats[] = Stat::make($branch->name . ' - Monthly Revenue', $currency_symbol . ' ' . number_format($thisMonthRevenue, 2))
                ->description('Total revenue this month')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success')
                ->chart([5, 7, 8, 10, 9, 11, 12]);

            // Monthly Profit Card
            $stats[] = Stat::make($branch->name . ' - Monthly Profit', $currency_symbol . ' ' . number_format($profit, 2))
                ->description($profit >= 0 ? 'Profitable month' : 'Loss this month')
                ->descriptionIcon($profit >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($profit >= 0 ? 'success' : 'danger')
                ->chart($profit >= 0 ? [4, 6, 7, 9, 10, 11, 12] : [12, 10, 8, 7, 6, 5, 4]);
        }

        return $stats;
    }
}
