<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Branch;

class BranchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Branch::create([
            'name' => 'Main Branch',
            'code' => 'MAIN',
            'address' => 'Main Office Location',
            'phone' => '+966-123456789',
            'is_active' => true,
        ]);

        Branch::create([
            'name' => 'Branch 1',
            'code' => 'BR1',
            'address' => 'Branch 1 Location',
            'phone' => '+966-123456790',
            'is_active' => true,
        ]);

        Branch::create([
            'name' => 'Water Transport',
            'code' => 'WATER_TR',
            'address' => 'Water Transport Location',
            'phone' => '+966-123456791',
            'is_active' => true,
        ]);
    }
}