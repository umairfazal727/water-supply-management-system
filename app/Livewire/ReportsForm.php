<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Branch;
use App\Models\Driver;
use App\Models\Vehicle;
use App\Models\Customer;

class ReportsForm extends Component
{
    public $startDate;
    public $endDate;
    public $reportType = 'profit_loss';
    public $branchId;
    public $driverId;
    public $vehicleId;
    public $customerId;

    public $branches = [];
    public $drivers = [];
    public $vehicles = [];
    public $customers = [];

    public function mount()
    {
        $this->startDate = now()->startOfMonth()->format('Y-m-d');
        $this->endDate = now()->endOfMonth()->format('Y-m-d');
        $this->loadOptions();
    }

    public function loadOptions()
    {
        $this->branches = Branch::where('is_active', true)->get();
        $this->drivers = Driver::where('is_active', true)->get();
        $this->vehicles = Vehicle::where('is_active', true)->get();
        $this->customers = Customer::all();
    }

    public function updated()
    {
        $this->dispatch('filtersUpdated', [
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
            'reportType' => $this->reportType,
            'branchId' => $this->branchId,
            'driverId' => $this->driverId,
            'vehicleId' => $this->vehicleId,
            'customerId' => $this->customerId,
        ]);
    }

    public function render()
    {
        return view('livewire.reports-form');
    }
}