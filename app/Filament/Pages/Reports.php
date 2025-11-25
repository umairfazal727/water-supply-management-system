<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Order;
use App\Models\Expense;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\ExpenseCategory;
use App\Models\Vehicle;
use App\Models\Driver;
use Carbon\Carbon;
use Illuminate\Support\Facades\Response;

class Reports extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Reports';
    protected static ?int $navigationSort = 5;

    protected static string $view = 'filament.pages.reports';

    public $startDate;
    public $endDate;
    public $reportType = 'customer_base';
    public $branchId;
    public $companyName;
    public $vehicleId;
    public $driverId;
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
        $this->startDate = \Carbon\Carbon::parse($filters['startDate'])->startOfDay();
        $this->endDate = \Carbon\Carbon::parse($filters['endDate'])->endOfDay();
        $this->reportType = $filters['reportType'];
        $this->branchId = $filters['branchId'];
        $this->companyName = $filters['companyName'];
        $this->vehicleId = $filters['vehicleId'];
        $this->driverId = $filters['driverId'];
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
                      ->whereBetween('order_date', [$this->startDate, $this->endDate]);
                    //   ->where('payment_type', 'credit');

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

        if ($this->vehicleId) {
            $vehicle = Vehicle::find($this->vehicleId);
            if ($vehicle) {
                $query->where('vehicle_number', $vehicle->vehicle_number);
            }
        }

        if ($this->driverId) {
            $driver = Driver::find($this->driverId);
            if ($driver) {
                $query->where('driver_name', $driver->name);
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
        // Generate the report if not already generated
        if (empty($this->reportData)) {
            $this->generateReport();
        }

        // Determine which template to use based on report type
        $template = $this->reportType === 'customer_base' ? 'invoices.customer-report' : 'invoices.expense-report';
        
        // Prepare view data based on report type
        $viewData = [
            'reportData' => $this->reportData,
            'insights' => $this->insights,
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
        ];

        // Add branch_name to insights if not present (for customer_base reports)
        if ($this->reportType === 'customer_base' && !isset($this->insights['branch_name'])) {
            $viewData['insights']['branch_name'] = $this->branchId 
                ? Branch::find($this->branchId)?->name 
                : 'All Branches';
        }

        $html = view($template, $viewData)->render();

        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4-L', // Landscape for better table display
            'orientation' => 'L',
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 10,
            'margin_bottom' => 10,
        ]);

        $mpdf->WriteHTML($html);
        
        $filename = $this->reportType . '_report_' . $this->startDate->format('Y-m-d') . '_to_' . $this->endDate->format('Y-m-d') . '.pdf';
        
        return response()->streamDownload(function() use ($mpdf) {
            echo $mpdf->Output('', 'S');
        }, $filename);
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
