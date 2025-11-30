<x-filament-panels::page>
    {{ $this->form }}

    @if($isLoading)
        <div class="mt-6">
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <div class="flex items-center justify-center">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600"></div>
                    <span class="ml-3 text-gray-600 dark:text-gray-400">Generating report...</span>
                </div>
            </div>
        </div>
    @elseif($results->count() > 0)
        <!-- Summary Cards -->
        <div class="mt-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4">
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-4">
                <div class="text-sm text-gray-500 dark:text-gray-400">Total Deliveries</div>
                <div class="text-2xl font-bold text-gray-900 dark:text-white mt-1">
                    {{ number_format($summary['total_deliveries']) }}
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-4">
                <div class="text-sm text-gray-500 dark:text-gray-400">Total Revenue</div>
                <div class="text-2xl font-bold text-green-600 mt-1">
                    AED {{ number_format($summary['total_revenue'], 2) }}
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-4">
                <div class="text-sm text-gray-500 dark:text-gray-400">Cash</div>
                <div class="text-2xl font-bold text-blue-600 mt-1">
                    AED {{ number_format($summary['total_cash'], 2) }}
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-4">
                <div class="text-sm text-gray-500 dark:text-gray-400">Credit</div>
                <div class="text-2xl font-bold text-orange-600 mt-1">
                    AED {{ number_format($summary['total_credit'], 2) }}
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-4">
                <div class="text-sm text-gray-500 dark:text-gray-400">Total Expenses</div>
                <div class="text-2xl font-bold text-red-600 mt-1">
                    AED {{ number_format($summary['total_expenses'], 2) }}
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-4">
                <div class="text-sm text-gray-500 dark:text-gray-400">Profit/Loss</div>
                <div class="text-2xl font-bold mt-1 {{ $summary['profit'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                    AED {{ number_format($summary['profit'], 2) }}
                </div>
            </div>
        </div>

        <!-- Date-wise Breakdown Table -->
        <div class="mt-6">
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                        Date-wise Breakdown
                    </h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Date
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Deliveries
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Revenue
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Cash
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Credit
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Expenses
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Profit/Loss
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($results as $result)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                        {{ \Carbon\Carbon::parse($result['date'])->format('d-m-Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-500 dark:text-gray-300">
                                        {{ number_format($result['deliveries']) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium text-green-600">
                                        AED {{ number_format($result['revenue'], 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium text-blue-600">
                                        AED {{ number_format($result['cash'], 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium text-orange-600">
                                        AED {{ number_format($result['credit'], 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium text-red-600">
                                        AED {{ number_format($result['expenses'], 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-bold {{ $result['profit'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                        AED {{ number_format($result['profit'], 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <td class="px-6 py-4 text-sm font-bold text-gray-900 dark:text-white">
                                    Totals
                                </td>
                                <td class="px-6 py-4 text-sm text-right font-bold text-gray-900 dark:text-white">
                                    {{ number_format($summary['total_deliveries']) }}
                                </td>
                                <td class="px-6 py-4 text-sm text-right font-bold text-green-600">
                                    AED {{ number_format($summary['total_revenue'], 2) }}
                                </td>
                                <td class="px-6 py-4 text-sm text-right font-bold text-blue-600">
                                    AED {{ number_format($summary['total_cash'], 2) }}
                                </td>
                                <td class="px-6 py-4 text-sm text-right font-bold text-orange-600">
                                    AED {{ number_format($summary['total_credit'], 2) }}
                                </td>
                                <td class="px-6 py-4 text-sm text-right font-bold text-red-600">
                                    AED {{ number_format($summary['total_expenses'], 2) }}
                                </td>
                                <td class="px-6 py-4 text-sm text-right font-bold {{ $summary['profit'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                    AED {{ number_format($summary['profit'], 2) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    @elseif(!$isLoading)
        <div class="mt-6">
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <div class="text-center">
                    <div class="text-gray-500 dark:text-gray-400">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No data found</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Select date range to view transport financial summary for the selected period.
                    </p>
                </div>
            </div>
        </div>
    @endif
</x-filament-panels::page>

