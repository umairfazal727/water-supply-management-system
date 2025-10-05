<div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
    <div class="px-4 py-5 sm:p-6">
        <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100 mb-4">
            Order Details
        </h3>

    @if (session()->has('error'))
            <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                {{ session('error') }}
            </div>
        @endif

        @if (session()->has('success'))
            <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                {{ session('success') }}
            </div>
    @endif

        @php
            $selectedCustomer = session('customer_id') ? \App\Models\Customer::with(['vehicle', 'driver'])->find(session('customer_id')) : null;
                @endphp

        @if($selectedCustomer)
            <!-- Order Form -->
            <form wire:submit.prevent="createOrder">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <!-- Customer & Vehicle Information -->
                    <div class="space-y-4">
                        <h4 class="font-medium text-gray-900 dark:text-white border-b pb-2">Customer & Vehicle Information</h4>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Customer Name</label>
                            <input type="text" 
                                   wire:model="customerName" 
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                   readonly>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Company Name</label>
                            <input type="text" 
                                   wire:model="companyName" 
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Vehicle Number</label>
                            <input type="text" 
                                   wire:model="vehicleNumber" 
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Driver Name</label>
                            <input type="text" 
                                   wire:model="driverName" 
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tanker Size</label>
                            <input type="text" 
                                   wire:model="tankerSize" 
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        </div>
                    </div>

                    <!-- Order Information -->
                    <div class="space-y-4">
                        <h4 class="font-medium text-gray-900 dark:text-white border-b pb-2">Order Information</h4>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Product Type</label>
                            <select wire:model="productType" 
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                <option value="sweet_water">Sweet Water</option>
                                <option value="salt_water">Salt Water</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Price</label>
                            <input type="number" 
                                   step="0.01"
                                   wire:model="price" 
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Payment Type</label>
                            <select wire:model="paymentType" 
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                <option value="cash">Cash</option>
                                <option value="credit">Credit</option>
                                <option value="bank_transfer">Bank Transfer</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Order Date & Time</label>
                            <input type="datetime-local" 
                                   wire:model="orderDate" 
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        </div>
                    </div>
                </div>

                <!-- Total Amount Display -->
                <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg mb-6">
                    <div class="flex justify-between items-center">
                        <span class="text-lg font-medium text-gray-900 dark:text-white">Total Amount:</span>
                        <span class="text-2xl font-bold text-green-600">{{ $currency_symbol }}{{ number_format($price, 2) }}</span>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex space-x-4">
                    <button type="submit" 
                            wire:loading.attr="disabled"
                            class="bg-green-500 hover:bg-green-600 disabled:bg-gray-400 text-white font-bold py-2 px-4 rounded flex items-center">
                        <span wire:loading.remove wire:target="createOrder">Save Order</span>
                        <span wire:loading wire:target="createOrder" class="flex items-center">
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Saving...
                        </span>
                    </button>
                    <button type="button" 
                            wire:click="clearCustomer"
                            class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded">
                        Clear Selection
                    </button>
                </div>
            </form>
        @else
            <div class="text-center py-8">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No customer selected</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Please select a customer from the list to create an order.</p>
            </div>
            @endif
    </div>
</div>
