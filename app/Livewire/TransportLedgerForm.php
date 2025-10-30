<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\DeliveryCustomer;

class TransportLedgerForm extends Component
{
    public $startDate;
    public $endDate;
    public $deliveryCustomerId;

    public $deliveryCustomers = [];

    public function mount()
    {
        $this->startDate = now()->format('Y-m-d');
        $this->endDate = now()->format('Y-m-d');
        $this->deliveryCustomers = DeliveryCustomer::where('is_active', true)->orderBy('name')->get();
    }

    public function submit()
    {
        $this->validate([
            'startDate' => 'required|date',
            'endDate' => 'required|date|after_or_equal:startDate',
        ]);

        $this->dispatch('transportLedgerGenerate', [
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
            'deliveryCustomerId' => $this->deliveryCustomerId,
        ]);
    }

    public function render()
    {
        return view('livewire.transport-ledger-form');
    }
}


