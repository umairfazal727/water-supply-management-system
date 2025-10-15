<?php

namespace App\Filament\Widgets;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\Delivery;
use App\Models\Driver;
use App\Models\Expense;
use App\Models\Order;
use App\Models\Vehicle;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DashboardStatsWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $stats = [];
        
        // Get all branches excluding transport
        $branches = Branch::where('is_active', true)
            ->where('name', 'not like', '%transport%')
            ->get();
        
        foreach ($branches as $branch) {
            // Orders stats for this branch
            $branchOrders = Order::where('branch_id', $branch->id)
                ->whereDate('created_at', today())
                ->count();
            
            $branchOrdersTotal = Order::where('branch_id', $branch->id)
                ->whereDate('created_at', today())
                ->sum('price');
            
            // Deliveries stats for this branch
            $branchDeliveries = Delivery::where('branch_id', $branch->id)
                ->whereDate('delivery_date', today())
                ->count();
            
            $branchExpenses = Expense::where('branch_id', $branch->id)
                ->where('expense_type', 'general')
                ->whereDate('expense_date', today())
                ->sum('amount');
            
            // Add branch header stat
            $stats[] = Stat::make($branch->name . ' - Orders', $branchOrders)
                ->description('Total: AED ' . number_format($branchOrdersTotal, 2))
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('primary')
                ->chart([7, 3, 4, 5, 6, 3, 5]);
            
            $stats[] = Stat::make($branch->name . ' - Deliveries', $branchDeliveries)
                ->description('Today\'s deliveries')
                ->descriptionIcon('heroicon-m-truck')
                ->color('success');
            
            $stats[] = Stat::make($branch->name . ' - Expenses', 'AED ' . number_format($branchExpenses, 2))
                ->description('Today\'s expenses')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('danger');
        }
        
        // Global stats
        $pendingExpenses = Expense::where('is_approved', false)
            ->where('expense_type', 'general')
            ->count();
        $scheduledDeliveries = Delivery::where('status', 'scheduled')->count();
        
        $stats[] = Stat::make('Pending Expenses', $pendingExpenses)
            ->description('Awaiting approval')
            ->descriptionIcon('heroicon-m-clock')
            ->color('warning');
            
        $stats[] = Stat::make('Scheduled Deliveries', $scheduledDeliveries)
            ->description('Upcoming deliveries')
            ->descriptionIcon('heroicon-m-calendar')
            ->color('info');
        
        return $stats;
    }
}
