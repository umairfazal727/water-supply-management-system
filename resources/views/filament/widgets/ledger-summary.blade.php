<div class="space-y-6">
    <!-- Summary Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Debit Card -->
        <div class="bg-success-50 dark:bg-success-900/20 rounded-lg p-4 border-2 border-success-200 dark:border-success-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-success-600 dark:text-success-400">Total Debit</p>
                    <p class="text-2xl font-bold text-success-700 dark:text-success-300 mt-1">
                        {{ $summary['currency_symbol'] }} {{ number_format($summary['total_debit'], 2) }}
                    </p>
                </div>
                <svg class="w-12 h-12 text-success-300 dark:text-success-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
            </div>
        </div>

        <!-- Total Credit Card -->
        <div class="bg-danger-50 dark:bg-danger-900/20 rounded-lg p-4 border-2 border-danger-200 dark:border-danger-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-danger-600 dark:text-danger-400">Total Credit</p>
                    <p class="text-2xl font-bold text-danger-700 dark:text-danger-300 mt-1">
                        {{ $summary['currency_symbol'] }} {{ number_format($summary['total_credit'], 2) }}
                    </p>
                </div>
                <svg class="w-12 h-12 text-danger-300 dark:text-danger-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                </svg>
            </div>
        </div>

        <!-- Net Balance Card -->
        <div class="bg-{{ $summary['net_balance'] >= 0 ? 'primary' : 'warning' }}-50 dark:bg-{{ $summary['net_balance'] >= 0 ? 'primary' : 'warning' }}-900/20 rounded-lg p-4 border-2 border-{{ $summary['net_balance'] >= 0 ? 'primary' : 'warning' }}-200 dark:border-{{ $summary['net_balance'] >= 0 ? 'primary' : 'warning' }}-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-{{ $summary['net_balance'] >= 0 ? 'primary' : 'warning' }}-600 dark:text-{{ $summary['net_balance'] >= 0 ? 'primary' : 'warning' }}-400">Net Balance</p>
                    <p class="text-2xl font-bold text-{{ $summary['net_balance'] >= 0 ? 'primary' : 'warning' }}-700 dark:text-{{ $summary['net_balance'] >= 0 ? 'primary' : 'warning' }}-300 mt-1">
                        {{ $summary['currency_symbol'] }} {{ number_format($summary['net_balance'], 2) }}
                    </p>
                </div>
                <svg class="w-12 h-12 text-{{ $summary['net_balance'] >= 0 ? 'primary' : 'warning' }}-300 dark:text-{{ $summary['net_balance'] >= 0 ? 'primary' : 'warning' }}-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                </svg>
            </div>
        </div>

        <!-- Profit/Loss Card -->
        <div class="bg-{{ $summary['profit_loss'] >= 0 ? 'success' : 'danger' }}-50 dark:bg-{{ $summary['profit_loss'] >= 0 ? 'success' : 'danger' }}-900/20 rounded-lg p-4 border-2 border-{{ $summary['profit_loss'] >= 0 ? 'success' : 'danger' }}-200 dark:border-{{ $summary['profit_loss'] >= 0 ? 'success' : 'danger' }}-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-{{ $summary['profit_loss'] >= 0 ? 'success' : 'danger' }}-600 dark:text-{{ $summary['profit_loss'] >= 0 ? 'success' : 'danger' }}-400">
                        {{ $summary['profit_loss'] >= 0 ? 'Profit' : 'Loss' }}
                    </p>
                    <p class="text-2xl font-bold text-{{ $summary['profit_loss'] >= 0 ? 'success' : 'danger' }}-700 dark:text-{{ $summary['profit_loss'] >= 0 ? 'success' : 'danger' }}-300 mt-1">
                        {{ $summary['currency_symbol'] }} {{ number_format(abs($summary['profit_loss']), 2) }}
                    </p>
                </div>
                <svg class="w-12 h-12 text-{{ $summary['profit_loss'] >= 0 ? 'success' : 'danger' }}-300 dark:text-{{ $summary['profit_loss'] >= 0 ? 'success' : 'danger' }}-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    @if($summary['profit_loss'] >= 0)
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                    @else
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"></path>
                    @endif
                </svg>
            </div>
        </div>
    </div>

    <!-- Revenue & Expenses Section -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 border border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Revenue & Expenses</h3>
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-gray-600 dark:text-gray-400">Orders Total (Revenue)</span>
                    <span class="font-semibold text-success-600 dark:text-success-400">
                        {{ $summary['currency_symbol'] }} {{ number_format($summary['orders_total'], 2) }}
                    </span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600 dark:text-gray-400">Expenses Total</span>
                    <span class="font-semibold text-danger-600 dark:text-danger-400">
                        {{ $summary['currency_symbol'] }} {{ number_format($summary['expenses_total'], 2) }}
                    </span>
                </div>
                <div class="border-t border-gray-200 dark:border-gray-700 pt-3 mt-3">
                    <div class="flex justify-between items-center">
                        <span class="font-semibold text-gray-900 dark:text-white">Net Profit/Loss</span>
                        <span class="font-bold text-lg {{ $summary['profit_loss'] >= 0 ? 'text-success-600 dark:text-success-400' : 'text-danger-600 dark:text-danger-400' }}">
                            {{ $summary['currency_symbol'] }} {{ number_format($summary['profit_loss'], 2) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 border border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Deliveries Status</h3>
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-gray-600 dark:text-gray-400">Pending Deliveries</span>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-warning-100 text-warning-800 dark:bg-warning-800 dark:text-warning-100">
                        {{ $summary['pending_deliveries'] }}
                    </span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600 dark:text-gray-400">Scheduled Deliveries</span>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-info-100 text-info-800 dark:bg-info-800 dark:text-info-100">
                        {{ $summary['scheduled_deliveries'] }}
                    </span>
                </div>
                <div class="mt-4 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                    <p class="text-xs text-gray-600 dark:text-gray-400">
                        <strong>Note:</strong> Delivery amounts are not included in profit/loss calculations. Only pending and scheduled deliveries are listed here.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Info -->
    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
        <div class="flex">
            <svg class="w-5 h-5 text-blue-400 mr-3 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
            </svg>
            <div>
                <p class="text-sm font-medium text-blue-800 dark:text-blue-200">Financial Summary Information</p>
                <p class="text-sm text-blue-700 dark:text-blue-300 mt-1">
                    This summary shows the complete financial overview including all ledger entries, orders, expenses, and delivery status. 
                    Export options are available in CSV and PDF formats for detailed reporting.
                </p>
            </div>
        </div>
    </div>
</div>

