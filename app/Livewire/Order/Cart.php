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
    public $paymentType = 'credit';
    public $branchId;
    public $createMultiple = false;
    public $orderCount = 2;

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
                // Enforce payment type based on customer's is_type_credit
                // is_type_credit = true means customer is on credit, false means cash only
                $this->paymentType = $customer->is_type_credit ? 'credit' : 'cash';
            }
        } else {
            $this->clearFields();
        }
    }
    
    public function updatedPaymentType($value)
    {
        // Enforce payment type based on customer's is_type_credit setting
        $customerId = session('customer_id');
        if ($customerId) {
            $customer = Customer::find($customerId);
            if ($customer) {
                // If customer is credit type but user tries to set cash, allow it
                // If customer is cash only (is_type_credit = false) but user tries credit, reset to cash
                if (!$customer->is_type_credit && in_array($value, ['credit', 'on_account', 'bank_transfer'])) {
                    $this->paymentType = 'cash';
                    session()->flash('error', 'This customer is cash-only and cannot use credit payment methods.');
                }
            }
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

        // Validate order count if multiple orders is enabled
        if ($this->createMultiple) {
            if ($this->orderCount < 2 || $this->orderCount > 10) {
                session()->flash('error', 'Order count must be between 2 and 10.');
                return;
            }
        }

        $orderData = [
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
        ];

        $ordersCreated = [];
        
        // Determine how many orders to create
        $totalOrders = $this->createMultiple ? $this->orderCount : 1;
        
        // Create orders
        for ($i = 0; $i < $totalOrders; $i++) {
            $order = Order::create($orderData);
            $ordersCreated[] = $order;
        }

        if ($this->createMultiple) {
            session()->flash('success', "{$totalOrders} orders created successfully!");
        } else {
            session()->flash('success', 'Order created successfully!');
        }
        
        // Check if order's company_name matches "REEM AL FALAJ TR."
        // For multiple orders, check the first order
        $firstOrder = $ordersCreated[0];
        if ($firstOrder->company_name == 'REEM AL FALAJ TR.') {
            // Clear the customer selection
            $this->clearCustomer();
            // Redirect to delivery creation with order_id (first order)
            return $this->redirect(url('admin/deliveries/create?order_id=' . $firstOrder->id));
        }
        
        // Clear the customer selection and stay on POS page
        $this->clearCustomer();
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
        $this->paymentType = 'credit';
        $this->branchId = null;
        $this->orderDate = now()->format('Y-m-d\TH:i');
        $this->createMultiple = false;
        $this->orderCount = 2;
    }

}
