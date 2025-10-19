<x-filament-panels::page>
    {{ $this->form }}

    <!-- Action Buttons -->
    <div class="mt-4 flex justify-end gap-3">
        @php
            $customerId = $data['customer_id'] ?? null;
            $fromDate = $data['from_date'] ?? null;
            $toDate = $data['to_date'] ?? null;
        @endphp

        @if($customerId)
            <button wire:click="refreshLedgerData" 
                    type="button"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-md transition duration-150 ease-in-out">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Generate Ledger
            </button>
        @endif

        @if($customer && $ledgerEntries->count() > 0)
            <a href="{{ url('/download-ledger', ['customer_id' => $customerId, 'from' => $fromDate ?? '', 'to' => $toDate ?? '']) }}" 
               target="_blank"
               class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg shadow-md transition duration-150 ease-in-out">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Export PDF
            </a>
        @endif
    </div>

    @if($isLoading)
        <div class="mt-6">
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <div class="flex items-center justify-center">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600"></div>
                    <span class="ml-3 text-gray-600 dark:text-gray-400">Loading ledger data...</span>
                </div>
            </div>
        </div>
    @elseif($customer && $ledgerEntries->count() > 0)
        <div class="mt-6">
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
                <!-- Header -->
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                                Ledger for {{ $customer->first_name }} {{ $customer->last_name }}
                            </h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                {{ $customer->company_name ?? 'No Company' }}
                            </p>
                        </div>
                        <div class="text-right">
                            <div class="text-sm text-gray-500 dark:text-gray-400">Current Balance</div>
                            <div class="text-lg font-bold {{ $finalBalance < 0 ? 'text-red-600' : 'text-green-600' }}">
                                AED {{ number_format($finalBalance, 2) }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Ledger Table -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Entry No
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Date
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Entry Origin
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Debit
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Credit
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Balance
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Description
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Type
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($ledgerEntries as $entry)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $entry->entry_number }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                        {{ $entry->transaction_date->format('d-m-Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                        {{ $entry->entry_origin ?? '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900 dark:text-white">
                                        @if($entry->debit_amount > 0)
                                            <span class="text-green-600 font-medium">AED {{ number_format($entry->debit_amount, 2) }}</span>
                                        @else
                                            <span class="text-gray-400">0.00</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900 dark:text-white">
                                        @if($entry->credit_amount > 0)
                                            <span class="text-orange-600 font-medium">AED {{ number_format($entry->credit_amount, 2) }}</span>
                                        @else
                                            <span class="text-gray-400">0.00</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-bold">
                                        <span class="{{ $entry->balance < 0 ? 'text-red-600' : 'text-green-600' }}">
                                            AED {{ number_format($entry->balance, 2) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-300">
                                        {{ Str::limit($entry->description, 50) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            @if($entry->transaction_type === 'order') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                                            @elseif($entry->transaction_type === 'payment') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                            @elseif($entry->transaction_type === 'opening_balance') bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200
                                            @else bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200 @endif">
                                            {{ ucfirst(str_replace('_', ' ', $entry->transaction_type)) }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <td colspan="3" class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">
                                    Totals
                                </td>
                                <td class="px-6 py-4 text-sm text-right font-medium text-green-600">
                                    AED {{ number_format($totalDebit, 2) }}
                                </td>
                                <td class="px-6 py-4 text-sm text-right font-medium text-orange-600">
                                    AED {{ number_format($totalCredit, 2) }}
                                </td>
                                <td class="px-6 py-4 text-sm text-right font-bold {{ $finalBalance < 0 ? 'text-red-600' : 'text-green-600' }}">
                                    AED {{ number_format($finalBalance, 2) }}
                                </td>
                                <td colspan="2"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Summary -->
                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700 border-t border-gray-200 dark:border-gray-600">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-sm">
                        <div>
                            <span class="text-gray-500 dark:text-gray-400">Previous Balance:</span>
                            <div class="font-medium text-gray-900 dark:text-white">
                                AED {{ number_format($previousBalance, 2) }}
                            </div>
                        </div>
                        <div>
                            <span class="text-gray-500 dark:text-gray-400">Total Debit:</span>
                            <div class="font-medium text-green-600">
                                AED {{ number_format($totalDebit, 2) }}
                            </div>
                        </div>
                        <div>
                            <span class="text-gray-500 dark:text-gray-400">Total Credit:</span>
                            <div class="font-medium text-orange-600">
                                AED {{ number_format($totalCredit, 2) }}
                            </div>
                        </div>
                        <div>
                            <span class="text-gray-500 dark:text-gray-400">Final Balance:</span>
                            <div class="font-bold {{ $finalBalance < 0 ? 'text-red-600' : 'text-green-600' }}">
                                AED {{ number_format($finalBalance, 2) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @elseif($customer)
        <div class="mt-6">
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <div class="text-center">
                    <div class="text-gray-500 dark:text-gray-400">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No ledger entries found</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        No transactions found for the selected customer and date range.
                    </p>
                </div>
            </div>
        </div>
    @endif
</x-filament-panels::page>