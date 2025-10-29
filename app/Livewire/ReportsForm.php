<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\ExpenseCategory;
use App\Models\Vehicle;
use App\Models\Driver;
use Illuminate\Support\Facades\DB;

class ReportsForm extends Component
{
    public $startDate;
    public $endDate;
    public $reportType = 'customer_base';
    public $branchId;
    public $companyName;
    public $vehicleId;
    public $driverId;
    public $expenseCategoryId;

    public $branches = [];
    public $companyNames = [];
    public $vehicles = [];
    public $drivers = [];
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
        
        // Get distinct company names (trimmed and non-empty)
        $this->companyNames = Customer::selectRaw('TRIM(company_name) as company_name')
            ->whereNotNull('company_name')
            ->where('company_name', '!=', '')
            ->distinct()
            ->orderBy('company_name')
            ->pluck('company_name')
            ->filter() // Remove any empty values
            ->values(); // Re-index array
        
        $this->vehicles = Vehicle::where('is_active', true)->orderBy('vehicle_number')->get();
        $this->drivers = Driver::where('is_active', true)->orderBy('name')->get();
        $this->expenseCategories = ExpenseCategory::all();
    }

    public function updatedReportType()
    {
        // Reset conditional fields when report type changes
        $this->companyName = '';
        $this->vehicleId = '';
        $this->driverId = '';
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
            'vehicleId' => $this->vehicleId,
            'driverId' => $this->driverId,
            'expenseCategoryId' => $this->expenseCategoryId,
        ]);
    }

    public function render()
    {
        return view('livewire.reports-form');
    }
}