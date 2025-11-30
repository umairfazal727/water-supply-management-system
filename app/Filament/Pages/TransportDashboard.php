<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Expense;
use App\Models\Delivery;
use App\Models\Branch;

class TransportDashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-line';
    protected static ?string $navigationLabel = 'Transp Dashboard';
    protected static ?string $navigationGroup = 'Water Transport';
    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.transport-dashboard';

    public $kpis = [
        'expense' => 0,
        'deliveries' => 0,
        'revenue' => 0,
        'profit' => 0,
    ];

    public function mount()
    {
        $this->loadKpis();
    }

    private function getTransportBranchId(): int
    {
        return (int) (Branch::where('code', 'WATER_TR')->value('id') ?? 3);
    }

    private function loadKpis(): void
    {
        $branchId = $this->getTransportBranchId();
        $today = today();

        $expense = Expense::where('branch_id', $branchId)
            ->whereDate('expense_date', $today)
            ->sum('amount');

        $deliveriesQuery = Delivery::whereDate('delivery_date', $today);

        $deliveries = (clone $deliveriesQuery)->count();
        $revenue = (clone $deliveriesQuery)->sum('total_amount');

        // As requested: profit = total_amount in deliveries (same as revenue)
        $profit = $revenue;

        $this->kpis = [
            'expense' => $expense,
            'deliveries' => $deliveries,
            'revenue' => $revenue,
            'profit' => $profit,
        ];
    }
}


