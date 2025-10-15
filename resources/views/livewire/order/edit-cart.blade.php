<div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
    <div class="px-4 py-5 sm:p-6">
        <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100 mb-4">
            Edit Order #{{ $order->id }}
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

        <!-- Customer Information (Read-only) -->
        <div class="mb-6 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
            <h4 class="font-medium text-gray-900 dark:text-white mb-3">Customer Information</h4>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                <div>
                    <span class="text-gray-600 dark:text-gray-400">Customer:</span>
                    <span class="ml-2 font-medium text-gray-900 dark:text-white">
                        {{ $order->customer->first_name ?? '' }} {{ $order->customer->last_name ?? '' }}
                    </span>
                </div>
                <div>
                    <span class="text-gray-600 dark:text-gray-400">Phone:</span>
                    <span class="ml-2 font-medium text-gray-900 dark:text-white">
                        {{ $order->customer->phone ?? 'N/A' }}
                    </span>
                </div>
                <div>
                    <span class="text-gray-600 dark:text-gray-400">Email:</span>
                    <span class="ml-2 font-medium text-gray-900 dark:text-white">
                        {{ $order->customer->email ?? 'N/A' }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Order Edit Form -->
        <form wire:submit.prevent="updateOrder">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- Vehicle & Company Information -->
                <div class="space-y-4">
                    <h4 class="font-medium text-gray-900 dark:text-white border-b pb-2">Vehicle & Company Information</h4>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Transport Name <span class="text-xs text-blue-600 font-semibold">(HIGHLIGHTED)</span>
                        </label>
                        <input type="text" 
                               wire:model="companyName" 
                               class="mt-1 block w-full border-2 border-blue-500 bg-blue-50 dark:bg-blue-900/20 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white font-semibold">
                        @error('companyName') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Vehicle Number <span class="text-xs text-green-600 font-semibold">(HIGHLIGHTED)</span>
                        </label>
                        <input type="text" 
                               wire:model="vehicleNumber" 
                               class="mt-1 block w-full border-2 border-green-500 bg-green-50 dark:bg-green-900/20 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white font-semibold">
                        @error('vehicleNumber') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Driver Name <span class="text-xs text-purple-600 font-semibold">(HIGHLIGHTED)</span>
                        </label>
                        <input type="text" 
                               wire:model="driverName" 
                               class="mt-1 block w-full border-2 border-purple-500 bg-purple-50 dark:bg-purple-900/20 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white font-semibold">
                        @error('driverName') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tanker Size</label>
                        <input type="text" 
                               wire:model="tankerSize" 
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        @error('tankerSize') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Branch</label>
                        <select wire:model="branchId" 
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            <option value="">Select Branch</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                        @error('branchId') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
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
                        @error('productType') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Price</label>
                        <input type="number" 
                               step="0.01"
                               wire:model="price" 
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        @error('price') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Payment Type</label>
                        <select wire:model="paymentType" 
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            <option value="cash">Cash</option>
                            <option value="credit">Credit</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="on_account">On Account (Deferred)</option>
                        </select>
                        @error('paymentType') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Order Date & Time</label>
                        <input type="datetime-local" 
                               wire:model="orderDate" 
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        @error('orderDate') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
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
                        class="bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 text-white font-bold py-2 px-4 rounded flex items-center">
                    <span wire:loading.remove wire:target="updateOrder">Update Order</span>
                    <span wire:loading wire:target="updateOrder" class="flex items-center">
                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Updating...
                    </span>
                </button>
            </div>
        </form>
    </div>
</div>

