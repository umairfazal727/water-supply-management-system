<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Order;
use App\Models\Expense;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\ExpenseCategory;
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
    public $reportType = 'customer_base';
    public $branchId;
    public $companyName;
    public $expenseCategoryId;
    
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
            'generateReport' => 'handleGenerateReport',
        ];
    }

    public function handleGenerateReport($filters)
    {
        $this->startDate = \Carbon\Carbon::parse($filters['startDate']);
        $this->endDate = \Carbon\Carbon::parse($filters['endDate']);
        $this->reportType = $filters['reportType'];
        $this->branchId = $filters['branchId'];
        $this->companyName = $filters['companyName'];
        $this->expenseCategoryId = $filters['expenseCategoryId'];
        
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
            case 'customer_base':
                $this->generateCustomerBaseReport();
                break;
            case 'expense_base':
                $this->generateExpenseBaseReport();
                break;
        }
    }

    private function generateCustomerBaseReport()
    {
        $query = Order::with(['customer', 'branch'])
                      ->whereBetween('order_date', [$this->startDate, $this->endDate])
                      ->where('payment_type', 'credit'); // Only credit/pending payments

        if ($this->branchId) {
            $query->where('branch_id', $this->branchId);
        }

        if ($this->companyName) {
            // Get all customers with this company name (trim and case-insensitive match)
            $trimmedCompanyName = trim($this->companyName);
            $customerIds = Customer::whereRaw('TRIM(company_name) = ?', [$trimmedCompanyName])
                ->pluck('id');
            
            if ($customerIds->isNotEmpty()) {
                $query->whereIn('customer_id', $customerIds);
            } else {
                // No customers found, return empty result
                $query->whereRaw('1 = 0'); // This makes the query return no results
            }
        }

        $orders = $query->orderBy('order_date', 'desc')->get();

        // Calculate insights
        $this->insights = [
            'total_orders' => $orders->count(),
            'total_amount' => $orders->sum('total_price'),
            'company_name' => $this->companyName ?: 'All Companies',
        ];

        $this->reportData = $orders;
    }

    private function generateExpenseBaseReport()
    {
        $query = Expense::with(['category', 'branch', 'user'])
                        ->whereBetween('expense_date', [$this->startDate, $this->endDate]);

        if ($this->branchId) {
            $query->where('branch_id', $this->branchId);
        }

        if ($this->expenseCategoryId) {
            $query->where('expense_category_id', $this->expenseCategoryId);
        }

        $expenses = $query->orderBy('expense_date', 'desc')->get();

        // Calculate insights
        $this->insights = [
            'total_expenses' => $expenses->count(),
            'total_amount' => $expenses->sum('amount'),
            'category_name' => $this->expenseCategoryId 
                ? ExpenseCategory::find($this->expenseCategoryId)?->name 
                : 'All Categories',
        ];

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
                case 'customer_base':
                    fputcsv($file, ['Order ID', 'Date', 'Company', 'Customer', 'Vehicle', 'Driver', 'Product Type', 'Quantity', 'Amount', 'Payment Type', 'Branch']);
                    foreach ($this->reportData as $order) {
                        fputcsv($file, [
                            $order->id,
                            \Carbon\Carbon::parse($order->order_date)->format('Y-m-d'),
                            $order->company_name,
                            $order->customer_name,
                            $order->vehicle_number,
                            $order->driver_name,
                            $order->product_type === 'sweet_water' ? 'Sweet Water' : 'Salt Water',
                            $order->quantity,
                            $order->total_price,
                            ucfirst($order->payment_type),
                            $order->branch?->name ?? 'N/A'
                        ]);
                    }
                    break;
                    
                case 'expense_base':
                    fputcsv($file, ['ID', 'Date', 'Title', 'Category', 'Branch', 'Amount', 'Payment Method', 'Approved', 'Created By']);
                    foreach ($this->reportData as $expense) {
                        fputcsv($file, [
                            $expense->id,
                            $expense->expense_date->format('Y-m-d'),
                            $expense->title,
                            $expense->category?->name ?? 'N/A',
                            $expense->branch?->name ?? 'N/A',
                            $expense->amount,
                            ucfirst(str_replace('_', ' ', $expense->payment_method)),
                            $expense->is_approved ? 'Yes' : 'No',
                            $expense->user?->name ?? 'N/A'
                        ]);
                    }
                    break;
            }
            
            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    public function downloadStatement()
    {
        if (!$this->companyName) {
            return;
        }

        // Get all customer IDs with this company name
        $customerIds = Customer::where('company_name', $this->companyName)->pluck('id');
        
        if ($customerIds->isEmpty()) {
            return;
        }

        // Redirect to statement download route
        return redirect()->to(
            url('/download-statement-by-company/' . urlencode($this->companyName) 
                . '?start_date=' . $this->startDate->format('Y-m-d') 
                . '&end_date=' . $this->endDate->format('Y-m-d'))
        );
    }
}
