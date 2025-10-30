<x-filament-panels::page>
    <div class="space-y-6">
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Filters</h3>
            <livewire:transport-ledger-form />
        </div>

        @if (!empty($entries) && count($entries) > 0)
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <div class="flex justify-between items-center mb-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Transport Ledger</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">From {{ $this->startDate->format('M d, Y') }} to {{ $this->endDate->format('M d, Y') }}</p>
                    </div>
                    <div class="flex space-x-2">
                        <button wire:click="downloadSimple" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">Download Simple</button>
                        <button wire:click="downloadDetailed" class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded">Download Detailed</button>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Delivery No</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Customer</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Trip Size</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Amount</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach ($entries as $d)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $d->delivery_date?->format('M d, Y') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $d->delivery_number }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $d->deliveryCustomer?->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $d->trip_size }} gal</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">{{ config('settings.currency_symbol', 'AED') }}{{ number_format($d->total_amount, 2) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">{{ ucfirst(str_replace('_',' ', $d->status)) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <td colspan="3" class="px-6 py-3 text-right text-sm font-medium text-gray-900 dark:text-white">Total:</td>
                                <td class="px-6 py-3 text-left text-sm font-medium text-gray-900 dark:text-white">{{ $summary['count'] ?? 0 }} trips</td>
                                <td class="px-6 py-3 text-left text-sm font-bold text-green-600">{{ config('settings.currency_symbol', 'AED') }}{{ number_format($summary['total_amount'] ?? 0, 2) }}</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        @else
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <div class="text-center py-8">
                    <p class="text-gray-500 dark:text-gray-400">Select filters and generate to view ledger.</p>
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>


