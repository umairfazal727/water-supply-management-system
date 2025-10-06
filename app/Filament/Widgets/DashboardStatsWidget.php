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
        $totalBranches = Branch::count();
        // $totalCustomers = Customer::count();
        // $totalVehicles = Vehicle::count();
        // $totalDrivers = Driver::count();
        
        // $todayExpenses = Expense::whereDate('expense_date', today())->sum('amount');
        $todayDeliveries = Delivery::whereDate('delivery_date', today())->count();
        $todayOrders = Order::whereDate('created_at', today())->count();
        
        $pendingExpenses = Expense::where('is_approved', false)->count();
        $scheduledDeliveries = Delivery::where('status', 'scheduled')->count();
        // $activeVehicles = Vehicle::where('is_active', true)->count();
        
        return [
            Stat::make('Total Branches', $totalBranches)
                ->description('Active branches')
                ->descriptionIcon('heroicon-m-building-office-2')
                ->color('success'),
                
            // Stat::make('Total Customers', $totalCustomers)
            //     ->description('Registered customers')
            //     ->descriptionIcon('heroicon-m-users')
            //     ->color('info'),
                
            // Stat::make('Total Vehicles', $totalVehicles)
            //     ->description('Fleet vehicles')
            //     ->descriptionIcon('heroicon-m-truck')
            //     ->color('warning'),
                
            // Stat::make('Total Drivers', $totalDrivers)
            //     ->description('Active drivers')
            //     ->descriptionIcon('heroicon-m-user')
            //     ->color('primary'),
                
            // Stat::make('Today\'s Expenses', 'SAR ' . number_format($todayExpenses, 2))
            //     ->description('Daily expense total')
            //     ->descriptionIcon('heroicon-m-currency-dollar')
            //     ->color('danger'),
                
            Stat::make('Today\'s Deliveries', $todayDeliveries)
                ->description('Completed deliveries')
                ->descriptionIcon('heroicon-m-truck')
                ->color('success'),
                
            Stat::make('Today\'s Orders', $todayOrders)
                ->description('POS transactions')
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('info'),
                
            Stat::make('Pending Expenses', $pendingExpenses)
                ->description('Awaiting approval')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
                
            Stat::make('Scheduled Deliveries', $scheduledDeliveries)
                ->description('Upcoming deliveries')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('info'),
                
            // Stat::make('Active Vehicles', $activeVehicles)
            //     ->description('Operational vehicles')
            //     ->descriptionIcon('heroicon-m-truck')
            //     ->color('success'),
        ];
    }
}
