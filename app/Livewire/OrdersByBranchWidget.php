<?php

namespace App\Livewire;

use App\Models\Branch;
use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OrdersByBranchWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $stats = [];
        $currency_symbol = config('settings.currency_symbol');
        
        // Get Main Branch and Branch 1 only
        $branches = Branch::whereIn('code', ['MAIN', 'BR1'])
            ->where('is_active', true)
            ->get();
        
        foreach ($branches as $branch) {
            // Get today's orders for this branch
            $todayOrders = Order::where('branch_id', $branch->id)
                ->whereDate('order_date', today())
                ->orWhere(function($query) use ($branch) {
                    $query->where('branch_id', $branch->id)
                          ->whereNull('order_date')
                          ->whereDate('created_at', today());
                });
            
            $todayOrdersCount = $todayOrders->count();
            $todayOrdersTotal = $todayOrders->sum('price') ?? $todayOrders->sum('total_price');
            
            $stats[] = Stat::make($branch->name . ' - Today\'s Orders', $todayOrdersCount)
                ->description('Total: ' . $currency_symbol . number_format($todayOrdersTotal, 2))
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color($branch->code === 'MAIN' ? 'primary' : 'success')
                ->chart([7, 3, 4, 5, 6, 3, 5]);
        }
        
        return $stats;
    }
}
