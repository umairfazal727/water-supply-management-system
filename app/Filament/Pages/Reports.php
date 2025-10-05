<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Order;
use App\Models\Expense;
use App\Models\Branch;
use App\Models\Driver;
use App\Models\Vehicle;
use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Support\Facades\Response;

class Reports extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Reports';
    protected static ?int $navigationSort = 3;

    protected static string $view = 'filament.pages.reports';

    public $startDate;
    public $endDate;
    public $reportType = 'profit_loss';
    public $branchId;
    public $driverId;
    public $vehicleId;
    public $customerId;
    
    public $reportData = [];
    public $insights = [];

    public function mount()
    {
        $this->startDate = now()->startOfMonth();
        $this->endDate = now()->endOfMonth();
    }

    protected function getListeners()
    {
        return [
            'filtersUpdated' => 'updateFilters',
        ];
    }

    public function updateFilters($filters)
    {
        $this->startDate = \Carbon\Carbon::parse($filters['startDate']);
        $this->endDate = \Carbon\Carbon::parse($filters['endDate']);
        $this->reportType = $filters['reportType'];
        $this->branchId = $filters['branchId'];
        $this->driverId = $filters['driverId'];
        $this->vehicleId = $filters['vehicleId'];
        $this->customerId = $filters['customerId'];
        
        $this->generateReport();
    }

    public function generateReport()
    {
        if (!$this->startDate || !$this->endDate) {
            return;
        }

        $this->reportData = [];
        $this->insights = [];

        switch ($this->reportType) {
            case 'profit_loss':
                $this->generateProfitLossReport();
                break;
            case 'monthly_sales':
                $this->generateMonthlySalesReport();
                break;
            case 'expense_driver':
                $this->generateExpenseDriverReport();
                break;
            case 'expense_vehicle':
                $this->generateExpenseVehicleReport();
                break;
            case 'customer_statements':
                $this->generateCustomerStatementsReport();
                break;
            case 'all_expenses':
                $this->generateAllExpensesReport();
                break;
        }
    }

    private function generateProfitLossReport()
    {
        $ordersQuery = Order::whereBetween('order_date', [$this->startDate, $this->endDate]);
        $expensesQuery = Expense::whereBetween('expense_date', [$this->startDate, $this->endDate]);

        if ($this->branchId) {
            $ordersQuery->where('branch_id', $this->branchId);
            $expensesQuery->where('branch_id', $this->branchId);
        }

        $totalRevenue = $ordersQuery->sum('total_price');
        $totalExpenses = $expensesQuery->sum('amount');
        $profit = $totalRevenue - $totalExpenses;

        $this->reportData = [
            'total_revenue' => $totalRevenue,
            'total_expenses' => $totalExpenses,
            'profit' => $profit,
            'profit_margin' => $totalRevenue > 0 ? ($profit / $totalRevenue) * 100 : 0,
            'order_count' => $ordersQuery->count(),
            'expense_count' => $expensesQuery->count()
        ];
    }

    private function generateMonthlySalesReport()
    {
        $query = Order::whereBetween('order_date', [$this->startDate, $this->endDate]);

        if ($this->branchId) {
            $query->where('branch_id', $this->branchId);
        }

        $sales = $query->selectRaw('DATE(order_date) as date, SUM(total_price) as total, COUNT(*) as orders')
                       ->groupBy('date')
                       ->orderBy('date')
                       ->get();

        $this->reportData = $sales;
    }

    private function generateExpenseDriverReport()
    {
        $query = Expense::with(['driver', 'category', 'branch'])
                        ->whereBetween('expense_date', [$this->startDate, $this->endDate]);

        if ($this->branchId) {
            $query->where('branch_id', $this->branchId);
        }

        if ($this->driverId) {
            $query->where('driver_id', $this->driverId);
        }

        $expenses = $query->get();

        $this->reportData = $expenses;
    }

    private function generateExpenseVehicleReport()
    {
        $query = Expense::with(['vehicle', 'category', 'branch'])
                        ->whereBetween('expense_date', [$this->startDate, $this->endDate]);

        if ($this->branchId) {
            $query->where('branch_id', $this->branchId);
        }

        if ($this->vehicleId) {
            $query->where('vehicle_id', $this->vehicleId);
        }

        $expenses = $query->get();

        $this->reportData = $expenses;
    }

    private function generateCustomerStatementsReport()
    {
        $query = Order::with(['customer', 'branch'])
                      ->whereBetween('order_date', [$this->startDate, $this->endDate]);

        if ($this->branchId) {
            $query->where('branch_id', $this->branchId);
        }

        if ($this->customerId) {
            $query->where('customer_id', $this->customerId);
        }

        $orders = $query->orderBy('order_date', 'desc')->get();

        $this->reportData = $orders;
    }

    private function generateAllExpensesReport()
    {
        $query = Expense::with(['category', 'branch', 'user'])
                        ->whereBetween('expense_date', [$this->startDate, $this->endDate]);

        if ($this->branchId) {
            $query->where('branch_id', $this->branchId);
        }

        $expenses = $query->orderBy('expense_date', 'desc')->get();

        $this->reportData = $expenses;
    }

    public function exportReport()
    {
        $filename = $this->reportType . '_' . $this->startDate->format('Y-m-d') . '_to_' . $this->endDate->format('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() {
            $file = fopen('php://output', 'w');
            
            switch ($this->reportType) {
                case 'customer_statements':
                    fputcsv($file, ['Order ID', 'Date', 'Customer', 'Company', 'Vehicle', 'Driver', 'Product Type', 'Amount', 'Payment Type']);
                    foreach ($this->reportData as $order) {
                        fputcsv($file, [
                            $order->id,
                            $order->order_date,
                            $order->customer->first_name . ' ' . $order->customer->last_name,
                            $order->company_name,
                            $order->vehicle_number,
                            $order->driver_name,
                            $order->product_type,
                            $order->total_price,
                            $order->payment_type
                        ]);
                    }
                    break;
                case 'all_expenses':
                    fputcsv($file, ['ID', 'Date', 'Title', 'Category', 'Branch', 'Amount', 'Payment Method', 'Approved']);
                    foreach ($this->reportData as $expense) {
                        fputcsv($file, [
                            $expense->id,
                            $expense->expense_date->format('Y-m-d'),
                            $expense->title,
                            $expense->category?->name ?? 'N/A',
                            $expense->branch?->name ?? 'N/A',
                            $expense->amount,
                            $expense->payment_method,
                            $expense->is_approved ? 'Yes' : 'No'
                        ]);
                    }
                    break;
                // Add more export cases as needed
            }
            
            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }
}
