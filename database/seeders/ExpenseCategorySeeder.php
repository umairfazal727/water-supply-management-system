<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ExpenseCategory;

class ExpenseCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Diesel Fuel',
                'code' => 'DIESEL',
                'description' => 'Fuel expenses for vehicles',
                'is_active' => true,
            ],
            [
                'name' => 'Generator Maintenance',
                'code' => 'GENERATOR',
                'description' => 'Generator operation and maintenance costs',
                'is_active' => true,
            ],
            [
                'name' => 'Water Filtration',
                'code' => 'FILTRATION',
                'description' => 'Water treatment and filtration expenses',
                'is_active' => true,
            ],
            [
                'name' => 'Employee Expenses',
                'code' => 'EMPLOYEE',
                'description' => 'Employee-related expenses and benefits',
                'is_active' => true,
            ],
            [
                'name' => 'Food & Meals',
                'code' => 'FOOD',
                'description' => 'Food and meal expenses for employees',
                'is_active' => true,
            ],
            [
                'name' => 'Vehicle Maintenance',
                'code' => 'VEHICLE_MAINT',
                'description' => 'Vehicle repair and maintenance costs',
                'is_active' => true,
            ],
            [
                'name' => 'Office Supplies',
                'code' => 'OFFICE',
                'description' => 'Office supplies and stationery',
                'is_active' => true,
            ],
            [
                'name' => 'Utilities',
                'code' => 'UTILITIES',
                'description' => 'Electricity, water, and other utilities',
                'is_active' => true,
            ],
        ];

        foreach ($categories as $category) {
            ExpenseCategory::create($category);
        }
    }
}