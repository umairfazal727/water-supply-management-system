<x-filament-panels::page>
    <!-- Loading Overlay (shows during Livewire requests) -->
    <div wire:loading.delay 
         class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-50"
         style="transition: opacity 0.3s;">
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-xl">
            <div class="flex items-center space-x-4">
                <svg class="animate-spin h-8 w-8 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="text-lg font-medium text-gray-900 dark:text-white">Loading Report...</span>
            </div>
        </div>
    </div>

    <div class="space-y-6">

        <!-- Report Filters -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Report Filters</h3>
            <livewire:reports-form />
        </div>

        <!-- Report Insights -->
        @if(!empty($reportData))
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Report Summary</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div>
                    <h4 class="text-md font-medium text-gray-900 dark:text-white mb-2">Date Range</h4>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        From {{ $this->startDate->format('M d, Y') }} to {{ $this->endDate->format('M d, Y') }}
                        ({{ $this->startDate->diffInDays($this->endDate) + 1 }} days)
                    </p>
                </div>

                <div>
                    <h4 class="text-md font-medium text-gray-900 dark:text-white mb-2">Branch</h4>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        @if ($this->branchId)
                            {{ \App\Models\Branch::find($this->branchId)?->name }}
                        @else
                            All Branches
                        @endif
                    </p>
                </div>

                @if($reportType === 'customer_base')
                    <div>
                        <h4 class="text-md font-medium text-gray-900 dark:text-white mb-2">Total Orders</h4>
                        <p class="text-2xl font-bold text-blue-600">
                            {{ $insights['total_orders'] ?? 0 }}
                        </p>
                    </div>
                    <div>
                        <h4 class="text-md font-medium text-gray-900 dark:text-white mb-2">Total Amount</h4>
                        <p class="text-2xl font-bold text-green-600">
                            ${{ number_format($insights['total_amount'] ?? 0, 2) }}
                        </p>
                    </div>
                @endif

                @if($reportType === 'expense_base')
                    <div>
                        <h4 class="text-md font-medium text-gray-900 dark:text-white mb-2">Total Expenses</h4>
                        <p class="text-2xl font-bold text-red-600">
                            {{ $insights['total_expenses'] ?? 0 }}
                        </p>
                    </div>
                    <div>
                        <h4 class="text-md font-medium text-gray-900 dark:text-white mb-2">Total Amount</h4>
                        <p class="text-2xl font-bold text-orange-600">
                            ${{ number_format($insights['total_amount'] ?? 0, 2) }}
                        </p>
                    </div>
                @endif
            </div>
        </div>
        @endif

        <!-- Report Results -->
        @if (!empty($reportData))
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        {{ ucwords(str_replace('_', ' ', $reportType)) }} Report
                    </h3>
                    <div class="flex space-x-2">
                        @if($reportType === 'customer_base' && $companyName)
                            <button wire:click="downloadStatement"
                               class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded">
                                Download Statement
                            </button>
                        @endif
                        <button wire:click="exportReport"
                            class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                            Export CSV
                        </button>
                    </div>
                </div>

                @switch($reportType)
                    @case('customer_base')
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Order ID</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Date</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Transport</th>

                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Vehicle</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Driver</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Product</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Qty</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Amount</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Payment</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach ($reportData as $order)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">
                                                #{{ $order->id }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">
                                                {{ \Carbon\Carbon::parse($order->order_date)->format('M d, Y') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">
                                                {{ $order->company_name }}
                                            </td>
                                            
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">
                                                {{ $order->vehicle_number }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">
                                                {{ $order->driver_name }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $order->product_type === 'sweet_water' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                                    {{ $order->product_type === 'sweet_water' ? 'Sweet Water' : 'Salt Water' }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">
                                                {{ $order->quantity }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">
                                                ${{ number_format($order->total_price, 2) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">
                                                {{ ucfirst($order->payment_type) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @break

                    @case('expense_base')
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            ID</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Date</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Title</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Category</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Branch</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Amount</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Payment Method</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Status</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach ($reportData as $expense)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">
                                                #{{ $expense->id }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">
                                                {{ $expense->expense_date->format('M d, Y') }}
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-300">
                                                {{ $expense->title }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">
                                                {{ $expense->category?->name ?? 'N/A' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">
                                                {{ $expense->branch?->name ?? 'N/A' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">
                                                ${{ number_format($expense->amount, 2) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">
                                                {{ ucfirst(str_replace('_', ' ', $expense->payment_method)) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $expense->is_approved ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                                    {{ $expense->is_approved ? 'Approved' : 'Pending' }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @break

                    @default
                        <div class="text-center py-8">
                            <p class="text-gray-500 dark:text-gray-400">No data available for this report type.</p>
                        </div>
                @endswitch
            </div>
        @else
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <div class="text-center py-8">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No Report Data</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Select your filters and generate a report
                        to see data.</p>
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>
