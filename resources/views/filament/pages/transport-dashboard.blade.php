<x-filament-panels::page>
    <div class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <h4 class="text-sm text-gray-500 dark:text-gray-400">Expense (Today)</h4>
                <div class="mt-2 text-2xl font-bold text-red-600">{{ config('settings.currency_symbol','AED') }}{{ number_format($kpis['expense'] ?? 0, 2) }}</div>
            </div>
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <h4 class="text-sm text-gray-500 dark:text-gray-400">Deliveries (Today)</h4>
                <div class="mt-2 text-2xl font-bold text-blue-600">{{ $kpis['deliveries'] ?? 0 }}</div>
            </div>
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <h4 class="text-sm text-gray-500 dark:text-gray-400">Revenue (Today)</h4>
                <div class="mt-2 text-2xl font-bold text-green-600">{{ config('settings.currency_symbol','AED') }}{{ number_format($kpis['revenue'] ?? 0, 2) }}</div>
            </div>
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <h4 class="text-sm text-gray-500 dark:text-gray-400">Profit (Today)</h4>
                <div class="mt-2 text-2xl font-bold text-emerald-600">{{ config('settings.currency_symbol','AED') }}{{ number_format($kpis['profit'] ?? 0, 2) }}</div>
            </div>
        </div>
    </div>
</x-filament-panels::page>


