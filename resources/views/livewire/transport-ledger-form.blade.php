<div class="space-y-4">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Delivery Customer</label>
            <select wire:model="deliveryCustomerId" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                <option value="">All Delivery Customers</option>
                @foreach($deliveryCustomers as $c)
                    <option value="{{ $c->id }}">{{ $c->name }}{{ $c->company_name ? ' - ' . $c->company_name : '' }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Start Date</label>
            <input type="date" wire:model="startDate" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">End Date</label>
            <input type="date" wire:model="endDate" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
        </div>
    </div>
    <div class="flex justify-end">
        <button wire:click="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">Generate</button>
    </div>
</div>


