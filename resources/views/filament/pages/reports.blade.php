<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Report Filters -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Report Filters</h3>
            <livewire:reports-form />
        </div>

        <!-- Report Insights -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Report Insights</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h4 class="text-md font-medium text-gray-900 dark:text-white mb-2">Date Range</h4>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        From {{ $this->startDate->format('M d, Y') }} to {{ $this->endDate->format('M d, Y') }}
                        ({{ $this->startDate->diffInDays($this->endDate) + 1 }} days)
                    </p>
                </div>

                <div>
                    <h4 class="text-md font-medium text-gray-900 dark:text-white mb-2">Filters Applied</h4>
                    <div class="flex flex-wrap gap-2">
                        @if ($this->branchId)
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                Branch: {{ \App\Models\Branch::find($this->branchId)?->name }}
                            </span>
                        @endif
                        @if ($this->driverId)
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                Driver: {{ \App\Models\Driver::find($this->driverId)?->name }}
                            </span>
                        @endif
                        @if ($this->vehicleId)
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                Vehicle: {{ \App\Models\Vehicle::find($this->vehicleId)?->vehicle_number }}
                            </span>
                        @endif
                        @if ($this->customerId)
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                Customer: {{ \App\Models\Customer::find($this->customerId)?->company_name }}
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Report Results -->
        @if (!empty($reportData))
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        {{ ucwords(str_replace('_', ' ', $reportType)) }} Report
                    </h3>
                    <button wire:click="exportReport"
                        class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                        Export CSV
                    </button>
                </div>

                @switch($reportType)
                    @case('profit_loss')
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="bg-green-50 p-4 rounded-lg">
                                <h4 class="text-lg font-semibold text-green-800">Total Revenue</h4>
                                <p class="text-2xl font-bold text-green-600">
                                    ${{ number_format($reportData['total_revenue'], 2) }}</p>
                            </div>
                            <div class="bg-red-50 p-4 rounded-lg">
                                <h4 class="text-lg font-semibold text-red-800">Total Expenses</h4>
                                <p class="text-2xl font-bold text-red-600">
                                    ${{ number_format($reportData['total_expenses'], 2) }}</p>
                            </div>
                            <div class="bg-blue-50 p-4 rounded-lg">
                                <h4 class="text-lg font-semibold text-blue-800">Net Profit</h4>
                                <p
                                    class="text-2xl font-bold {{ $reportData['profit'] >= 0 ? 'text-blue-600' : 'text-red-600' }}">
                                    ${{ number_format($reportData['profit'], 2) }}
                                </p>
                                <p class="text-sm text-gray-600">Margin: {{ number_format($reportData['profit_margin'], 2) }}%
                                </p>
                            </div>
                        </div>
                    @break

                    @case('monthly_sales')
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Date</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Orders</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Total Sales</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($reportData as $sale)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ \Carbon\Carbon::parse($sale->date)->format('M d, Y') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $sale->orders }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                ${{ number_format($sale->total, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @break

                    @case('customer_statements')
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Order ID</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Date</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Customer/Driver</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Vehicle</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Product</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Amount</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Payment</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($reportData as $order)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">#{{ $order->id }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{-- {{ $order->order_date->format('M d, Y') }} --}}
                                                {{ \Carbon\Carbon::parse($order->order_date)->format('M d, Y') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $order->customer->first_name }} {{ $order->customer->last_name }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $order->vehicle_number }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $order->product_type === 'sweet_water' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                                    {{ $order->product_type === 'sweet_water' ? 'Sweet Water' : 'Salt Water' }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                ${{ number_format($order->total_price, 2) }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ ucfirst($order->payment_type) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @break

                    @case('all_expenses')
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Date</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Title</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Category</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Branch</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Amount</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Payment Method</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Status</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($reportData as $expense)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $expense->expense_date->format('M d, Y') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $expense->title }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $expense->category?->name ?? 'N/A' }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $expense->branch?->name ?? 'N/A' }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                ${{ number_format($expense->amount, 2) }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ ucfirst(str_replace('_', ' ', $expense->payment_method)) }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $expense->is_approved ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
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
                            <p class="text-gray-500">No data available for this report type.</p>
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
