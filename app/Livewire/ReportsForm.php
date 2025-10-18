<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\ExpenseCategory;
use Illuminate\Support\Facades\DB;

class ReportsForm extends Component
{
    public $startDate;
    public $endDate;
    public $reportType = 'customer_base';
    public $branchId;
    public $companyName;
    public $expenseCategoryId;

    public $branches = [];
    public $companyNames = [];
    public $expenseCategories = [];

    public function mount()
    {
        $this->startDate = now()->startOfMonth()->format('Y-m-d');
        $this->endDate = now()->endOfMonth()->format('Y-m-d');
        $this->loadOptions();
    }

    public function loadOptions()
    {
        $this->branches = Branch::where('is_active', true)->get();
        
        // Get distinct company names
        $this->companyNames = Customer::select('company_name')
            ->whereNotNull('company_name')
            ->where('company_name', '!=', '')
            ->distinct()
            ->orderBy('company_name')
            ->pluck('company_name');
        
        $this->expenseCategories = ExpenseCategory::all();
    }

    public function updatedReportType()
    {
        // Reset conditional fields when report type changes
        $this->companyName = '';
        $this->expenseCategoryId = '';
    }

    public function submitReport()
    {
        $this->validate([
            'startDate' => 'required|date',
            'endDate' => 'required|date|after_or_equal:startDate',
            'reportType' => 'required|in:customer_base,expense_base',
        ]);

        $this->dispatch('generateReport', [
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
            'reportType' => $this->reportType,
            'branchId' => $this->branchId,
            'companyName' => $this->companyName,
            'expenseCategoryId' => $this->expenseCategoryId,
        ]);
    }

    public function render()
    {
        return view('livewire.reports-form');
    }
}