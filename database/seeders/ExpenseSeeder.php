<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Branch;
use App\Models\User;

class ExpenseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $mainBranch = Branch::where('code', 'MAIN')->first();
        $branch1 = Branch::where('code', 'BR1')->first();
        $waterBranch = Branch::where('code', 'WATER_TR')->first();
        
        $user = User::first();
        
        $dieselCategory = ExpenseCategory::where('code', 'DIESEL')->first();
        $generatorCategory = ExpenseCategory::where('code', 'GENERATOR')->first();
        $filtrationCategory = ExpenseCategory::where('code', 'FILTRATION')->first();
        $employeeCategory = ExpenseCategory::where('code', 'EMPLOYEE')->first();
        $foodCategory = ExpenseCategory::where('code', 'FOOD')->first();
        $vehicleCategory = ExpenseCategory::where('code', 'VEHICLE_MAINT')->first();

        // Sample expenses for this month
        $expenses = [
            [
                'branch_id' => $mainBranch->id,
                'expense_category_id' => $dieselCategory->id,
                'user_id' => $user->id,
                'title' => 'Diesel for Vehicle ABC-123',
                'description' => 'Daily diesel refill for main vehicle',
                'amount' => 150.00,
                'expense_date' => now()->subDays(1),
                'payment_method' => 'cash',
                'is_approved' => true,
            ],
            [
                'branch_id' => $mainBranch->id,
                'expense_category_id' => $generatorCategory->id,
                'user_id' => $user->id,
                'title' => 'Generator Maintenance',
                'description' => 'Monthly generator service and oil change',
                'amount' => 300.00,
                'expense_date' => now()->subDays(3),
                'payment_method' => 'bank_transfer',
                'is_approved' => true,
            ],
            [
                'branch_id' => $branch1->id,
                'expense_category_id' => $filtrationCategory->id,
                'user_id' => $user->id,
                'title' => 'Water Filtration System',
                'description' => 'Filter replacement and system maintenance',
                'amount' => 450.00,
                'expense_date' => now()->subDays(5),
                'payment_method' => 'credit',
                'is_approved' => false,
            ],
            [
                'branch_id' => $waterBranch->id,
                'expense_category_id' => $employeeCategory->id,
                'user_id' => $user->id,
                'title' => 'Driver Overtime',
                'description' => 'Overtime payment for weekend deliveries',
                'amount' => 200.00,
                'expense_date' => now()->subDays(2),
                'payment_method' => 'cash',
                'is_approved' => true,
            ],
            [
                'branch_id' => $mainBranch->id,
                'expense_category_id' => $foodCategory->id,
                'user_id' => $user->id,
                'title' => 'Employee Meals',
                'description' => 'Lunch expenses for field staff',
                'amount' => 75.00,
                'expense_date' => now()->subDays(1),
                'payment_method' => 'cash',
                'is_approved' => true,
            ],
            [
                'branch_id' => $branch1->id,
                'expense_category_id' => $vehicleCategory->id,
                'user_id' => $user->id,
                'title' => 'Vehicle Repair',
                'description' => 'Brake pad replacement for DEF-456',
                'amount' => 250.00,
                'expense_date' => now()->subDays(4),
                'payment_method' => 'bank_transfer',
                'is_approved' => false,
            ],
        ];

        foreach ($expenses as $expense) {
            Expense::create($expense);
        }
    }
}