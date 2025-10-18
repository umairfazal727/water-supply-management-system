<div class="space-y-4">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Start Date -->
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Start Date</label>
            <input type="date" 
                   wire:model="startDate"
                   class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
            @error('startDate') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <!-- End Date -->
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">End Date</label>
            <input type="date" 
                   wire:model="endDate"
                   class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
            @error('endDate') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <!-- Branch -->
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Branch</label>
            <select wire:model="branchId"
                    class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                <option value="">All Branches</option>
                @foreach($branches as $branch)
                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                @endforeach
            </select>
        </div>

        <!-- Report Type -->
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Report Type</label>
            <select wire:model.live="reportType"
                    class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                <option value="customer_base">Customer Base</option>
                <option value="expense_base">Expense Base</option>
            </select>
        </div>
    </div>

    <!-- Conditional Fields -->
    @if($reportType === 'customer_base')
        <div class="grid grid-cols-1 gap-4">
            <!-- Company Name -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Select Company</label>
                <select wire:model="companyName"
                        class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    <option value="">All Companies</option>
                    @foreach($companyNames as $company)
                        <option value="{{ $company }}">{{ $company }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    @endif

    @if($reportType === 'expense_base')
        <div class="grid grid-cols-1 gap-4">
            <!-- Expense Category -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Expense Category</label>
                <select wire:model="expenseCategoryId"
                        class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    <option value="">All Categories</option>
                    @foreach($expenseCategories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    @endif

    <!-- Submit Button -->
    <div class="flex justify-end">
        <button wire:click="submitReport" 
                wire:loading.attr="disabled"
                class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded disabled:opacity-50 disabled:cursor-not-allowed">
            <span wire:loading.remove wire:target="submitReport">Generate Report</span>
            <span wire:loading wire:target="submitReport">Generating...</span>
        </button>
    </div>
</div>