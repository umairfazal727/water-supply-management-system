<?php

namespace App\Livewire;

use App\Models\Customer;
use Livewire\Component;

class CustomerSearch extends Component
{
    public $query = '';  
    public $customers = [];
    public $selectedCustomer = null;
    public $showDropdown = false;  

    public function mount(){
        $customerId = session('customer_id');
        if( $customerId ){
            $this->selectedCustomer = Customer::with(['vehicle', 'driver'])->find( $customerId );
        }
        
        // Load all customers by default
        $this->customers = Customer::with(['vehicle', 'driver'])
                            ->orderBy('first_name')
                            ->get();
    }

    public function updatedQuery()
    {
        if(empty($this->query)) {
            // Show all customers if no search query
            $this->customers = Customer::with(['vehicle', 'driver'])
                                ->orderBy('first_name')
                                ->get();
        } else {
            // Filter customers based on search query
            $this->customers = Customer::where('first_name', 'like', '%' . $this->query . '%')
                                ->orWhere('last_name', 'like', '%' . $this->query . '%')
                                ->orWhere('company_name', 'like', '%' . $this->query . '%')
                                ->orWhere('phone', 'like', '%' . $this->query . '%')
                                ->orWhereHas('vehicle', function($q) {
                                    $q->where('vehicle_number', 'like', '%' . $this->query . '%');
                                })
                                ->orWhereHas('driver', function($q) {
                                    $q->where('name', 'like', '%' . $this->query . '%');
                                })
                                ->with(['vehicle', 'driver'])
                                ->orderBy('first_name')
                                ->get();
        }
    }

    public function selectCustomer($customerId)
    {
        $customer = Customer::with(['vehicle', 'driver'])->find($customerId);
        if ($customer) {  
            session(['customer_id' => $customer->id]);
            $this->selectedCustomer = $customer;
            $this->showDropdown = false;  
            $this->dispatch('customerSelected', $customerId);
        }
    }


    public function clear(){
        session(['customer_id' => null]);
        $this->selectedCustomer = null;
        $this->query = '';
        $this->dispatch('customerSelected', null);
    }

    public function render()
    {
        return view('livewire.customer-search');
    }
}
