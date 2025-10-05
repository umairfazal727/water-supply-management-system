<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Expense;
use App\Models\Order;
use Carbon\Carbon;

class ExpenseStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $today = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();
        $lastMonth = Carbon::now()->subMonth()->startOfMonth();
        $lastMonthEnd = Carbon::now()->subMonth()->endOfMonth();

        // Today's expenses
        $todayExpenses = Expense::whereDate('expense_date', $today)->sum('amount');

        // This month's expenses
        $thisMonthExpenses = Expense::where('expense_date', '>=', $thisMonth)->sum('amount');

        // Last month's expenses
        $lastMonthExpenses = Expense::whereBetween('expense_date', [$lastMonth, $lastMonthEnd])->sum('amount');

        // This month's revenue
        $thisMonthRevenue = Order::where('order_date', '>=', $thisMonth)->sum('total_price');

        // Profit calculation
        $profit = $thisMonthRevenue - $thisMonthExpenses;

        // Expense growth percentage
        $expenseGrowth = $lastMonthExpenses > 0 ? (($thisMonthExpenses - $lastMonthExpenses) / $lastMonthExpenses) * 100 : 0;

        return [
            Stat::make('Today\'s Expenses', '$' . number_format($todayExpenses, 2))
                ->description('Expenses recorded today')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('danger'),

            Stat::make('Monthly Expenses', '$' . number_format($thisMonthExpenses, 2))
                ->description($expenseGrowth >= 0 ? '+' . number_format($expenseGrowth, 1) . '% from last month' : number_format($expenseGrowth, 1) . '% from last month')
                ->descriptionIcon($expenseGrowth >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($expenseGrowth >= 0 ? 'danger' : 'success'),

            Stat::make('Monthly Revenue', '$' . number_format($thisMonthRevenue, 2))
                ->description('Total revenue this month')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),

            Stat::make('Monthly Profit', '$' . number_format($profit, 2))
                ->description($profit >= 0 ? 'Profitable month' : 'Loss this month')
                ->descriptionIcon($profit >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($profit >= 0 ? 'success' : 'danger'),
        ];
    }
}
