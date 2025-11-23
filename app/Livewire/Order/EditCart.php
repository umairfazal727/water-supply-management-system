<?php

namespace App\Livewire\Order;

use Livewire\Component;
use App\Models\Order;
use App\Models\Branch;

class EditCart extends Component
{
    public $orderId;
    public $order;
    
    public $vehicleNumber = '';
    public $driverName = '';
    public $companyName = '';
    public $tankerSize = '';
    public $productType = 'sweet_water';
    public $price = 0;
    public $orderDate;
    public $paymentType = 'cash';
    public $branchId;

    public $branches = [];
    private $currency_symbol;

    public function mount($orderId)
    {
        $this->orderId = $orderId;
        $this->order = Order::with(['customer', 'branch'])->findOrFail($orderId);
        $this->currency_symbol = config('settings.currency_symbol');
        
        // Load existing order data
        $this->vehicleNumber = $this->order->vehicle_number;
        $this->driverName = $this->order->driver_name;
        $this->companyName = $this->order->company_name;
        $this->tankerSize = $this->order->tanker_size;
        $this->productType = $this->order->product_type;
        $this->price = $this->order->total_price;
        $this->paymentType = $this->order->payment_type;
        $this->branchId = $this->order->branch_id;
        $this->orderDate = $this->order->order_date ? date('Y-m-d\TH:i', strtotime($this->order->order_date)) : now()->format('Y-m-d\TH:i');
        
        // Load branches
        $this->branches = Branch::where('is_active', true)->get();
    }

    public function updateOrder()
    {
        $this->validate([
            'vehicleNumber' => 'nullable|string|max:255',
            'driverName' => 'nullable|string|max:255',
            'companyName' => 'nullable|string|max:255',
            'tankerSize' => 'nullable|string|max:255',
            'productType' => 'required|in:sweet_water,salt_water',
            'price' => 'required|numeric|min:0',
            'paymentType' => 'required|in:cash,credit,bank_transfer,on_account',
            'branchId' => 'nullable|exists:branches,id',
            'orderDate' => 'required|date',
        ]);

        // Update the order
        $this->order->update([
            'vehicle_number' => $this->vehicleNumber,
            'driver_name' => $this->driverName,
            'company_name' => $this->companyName,
            'tanker_size' => $this->tankerSize,
            'product_type' => $this->productType,
            'price' => $this->price,
            'total_price' => $this->price,
            'payment_type' => $this->paymentType,
            'branch_id' => $this->branchId,
            'order_date' => $this->orderDate,
        ]);

        session()->flash('success', 'Order updated successfully!');
        
        // Refresh the order
        $this->order = Order::with(['customer', 'branch'])->findOrFail($this->orderId);
    }

    public function render()
    {
        return view('livewire.order.edit-cart', [
            'currency_symbol' => $this->currency_symbol
        ]);
    }
}

