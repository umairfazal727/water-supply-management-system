<div class="space-y-4">
    <!-- Search Input -->
    <div class="relative w-full">
        <input type="text" 
            wire:model.live.debounce.250ms="query"
            class="bg-white block w-full text-sm text-gray-900 border border-gray-300 rounded-md bg-gray-50 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
            placeholder="Search customers..." />
    </div>

    <!-- Customer List -->
    <div class="max-h-96 overflow-y-auto border border-gray-300 rounded-md">
        @if($customers->count() > 0)
            @foreach($customers as $customer)
                <div wire:click="selectCustomer({{ $customer->id }})"
                     class="p-3 border-b border-gray-200 cursor-pointer hover:bg-blue-50 dark:hover:bg-gray-700 {{ $selectedCustomer && $selectedCustomer->id === $customer->id ? 'bg-blue-100 dark:bg-blue-900' : '' }}">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <div class="font-medium text-gray-900 dark:text-white">
                                {{ $customer->first_name . ' ' . $customer->last_name }}
                            </div>
                            @if($customer->company_name)
                                <div class="text-sm text-gray-600 dark:text-gray-400">{{ $customer->company_name }}</div>
                            @endif
                            <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                @if($customer->vehicle)
                                    <span class="inline-flex items-center">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M8 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM15 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z"/>
                                            <path d="M3 4a1 1 0 00-1 1v10a1 1 0 001 1h1.05a2.5 2.5 0 014.9 0H10a1 1 0 001-1V5a1 1 0 00-1-1H3zM14 7a1 1 0 00-1 1v6.05A2.5 2.5 0 0115.95 16H17a1 1 0 001-1V8a1 1 0 00-1-1h-3z"/>
                                        </svg>
                                        {{ $customer->vehicle->vehicle_number }}
                                    </span>
                                @endif
                                @if($customer->driver)
                                    <span class="inline-flex items-center ml-2">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                                        </svg>
                                        {{ $customer->driver->name }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="font-semibold text-green-600 dark:text-green-400">
                                ${{ number_format($customer->price, 2) }}
                            </div>
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $customer->product_type === 'sweet_water' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                {{ $customer->product_type === 'sweet_water' ? 'Sweet' : 'Salt' }}
                            </span>
                        </div>
                    </div>
                </div>
            @endforeach
        @else
            <div class="p-4 text-center text-gray-500 dark:text-gray-400">
                @if($query)
                    No customers found for "{{ $query }}"
                @else
                    No customers available
                @endif
            </div>
        @endif
    </div>

    <!-- Selected Customer Indicator -->
    @if($selectedCustomer)
        <div class="bg-green-50 dark:bg-green-900 p-3 rounded-lg border border-green-200 dark:border-green-700">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-sm font-medium text-green-800 dark:text-green-200">Selected:</span>
                    <span class="text-sm text-green-700 dark:text-green-300">{{ $selectedCustomer->first_name . ' ' . $selectedCustomer->last_name }}</span>
                </div>
                <button wire:click="clear" class="text-green-600 hover:text-green-800 dark:text-green-400 dark:hover:text-green-200">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                    </svg>
                </button>
            </div>
        </div>
    @endif
</div>
