<?php

namespace App\Livewire\Order;

use Livewire\Component;
use App\Models\Cart as CartModel;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Setting;
use App\Models\Customer;
use App\Models\Branch;
use Livewire\Attributes\On; 

class Cart extends Component
{
    public $cartItems = [];
    public $customerName = '';
    public $vehicleNumber = '';
    public $driverName = '';
    public $companyName = '';
    public $tankerSize = '';
    public $productType = 'sweet_water';
    public $price = 0;
    public $orderDate;
    public $paymentType = 'cash';
    public $branchId;

    private $currency_symbol;

    public function mount()
    {
        $this->currency_symbol = config('settings.currency_symbol');
        $this->orderDate = now()->format('Y-m-d\TH:i');
        
        // Auto-fill if customer is already selected
        $customerId = session('customer_id');
        if ($customerId) {
            $this->updateCustomerDetails($customerId);
        }
    }

    
    public function render()
    {
        $this->currency_symbol = config('settings.currency_symbol');
        return view('livewire.order.cart', ['cartItems' => $this->cartItems, 'currency_symbol' => $this->currency_symbol]);
    }


    #[On('customerSelected')]
    public function updateCustomerDetails($customerId)
    {
        if ($customerId) {
            $customer = Customer::with(['vehicle', 'driver'])->find($customerId);
            if ($customer) {
                $this->customerName = $customer->first_name . ' ' . $customer->last_name;
                $this->vehicleNumber = $customer->vehicle ? $customer->vehicle->vehicle_number : '';
                $this->driverName = $customer->driver ? $customer->driver->name : '';
                $this->companyName = $customer->company_name;
                $this->tankerSize = $customer->tanker_size;
                $this->productType = $customer->product_type;
                $this->price = $customer->price;
                $this->branchId = $customer->vehicle && $customer->vehicle->branch_id ? $customer->vehicle->branch_id : null;
            }
        } else {
            $this->clearFields();
        }
    }

    public function createOrder()
    {
        $customerId = session('customer_id');
        
        if (!$customerId) {
            session()->flash('error', 'Please select a customer first.');
            return;
        }

        $customer = Customer::with(['vehicle', 'driver'])->find($customerId);
        
        if (!$customer) {
            session()->flash('error', 'Customer not found.');
            return;
        }

        // Create the order
        $order = Order::create([
            'customer_id' => $customerId,
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

        session()->flash('success', 'Order created successfully!');
        
        // Check if order's company_name matches "REEM AL FALAJ TR." (case-insensitive, trimmed)
        // $trimmedCompanyName = trim($order->company_name);
        if ($order->company_name == 'REEM AL FALAJ TR.') {
            // Clear the customer selection
            $this->clearCustomer();
            // Redirect to delivery creation with order_id
            return $this->redirect(url('admin/deliveries/create?order_id=' . $order->id));
        }
        
        // Clear the customer selection
        $this->clearCustomer();
        
        // Redirect to orders list
        return $this->redirect(url('admin/orders'));
    }

    public function clearCustomer()
    {
        session(['customer_id' => null]);
        $this->clearFields();
        $this->dispatch('customerSelected', null);
    }

    private function clearFields()
    {
        $this->customerName = '';
        $this->vehicleNumber = '';
        $this->driverName = '';
        $this->companyName = '';
        $this->tankerSize = '';
        $this->productType = 'sweet_water';
        $this->price = 0;
        $this->paymentType = 'cash';
        $this->branchId = null;
        $this->orderDate = now()->format('Y-m-d\TH:i');
    }

}
