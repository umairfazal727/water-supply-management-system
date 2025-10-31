<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Expense;
use App\Models\Branch;
use App\Models\ExpenseCategory;
use Carbon\Carbon;
use Illuminate\Support\Facades\Response;

class ExpenseView extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationLabel = 'Expense View';
    protected static ?int $navigationSort = 6;
    protected static bool $shouldRegisterNavigation = false;

    protected static string $view = 'filament.pages.expense-view';

    public $startDate;
    public $endDate;
    public $branchId;
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
            'generateExpenseReport' => 'handleGenerateReport',
        ];
    }

    public function handleGenerateReport($filters)
    {
        $this->startDate = \Carbon\Carbon::parse($filters['startDate'])->startOfDay();
        $this->endDate = \Carbon\Carbon::parse($filters['endDate'])->endOfDay();
        $this->branchId = $filters['branchId'] ?? null;
        $this->expenseCategoryId = $filters['expenseCategoryId'] ?? null;
        
        $this->generateReport();
    }

    public function generateReport()
    {
        if (!$this->startDate || !$this->endDate) {
            return;
        }

        $this->reportData = [];
        $this->insights = [];

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
            'branch_name' => $this->branchId 
                ? Branch::find($this->branchId)?->name 
                : 'All Branches',
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

        $html = view('invoices.expense-report', [
            'reportData' => $this->reportData,
            'insights' => $this->insights,
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
        ])->render();

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
        
        $filename = 'expense_report_' . $this->startDate->format('Y-m-d') . '_to_' . $this->endDate->format('Y-m-d') . '.pdf';
        
        return response()->streamDownload(function() use ($mpdf) {
            echo $mpdf->Output('', 'S');
        }, $filename);
    }
}

