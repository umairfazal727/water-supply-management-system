<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Driver;
use App\Models\Branch;

class DriverSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $mainBranch = Branch::where('code', 'MAIN')->first();
        $branch1 = Branch::where('code', 'BR1')->first();
        $waterBranch = Branch::where('code', 'WATER_TR')->first();

        Driver::create([
            'name' => 'Ahmed Al-Rashid',
            'phone' => '+966-501234567',
            'license_number' => 'DL-123456',
            'branch_id' => $mainBranch->id,
            'is_active' => true,
        ]);

        Driver::create([
            'name' => 'Mohammed Al-Salem',
            'phone' => '+966-502345678',
            'license_number' => 'DL-234567',
            'branch_id' => $branch1->id,
            'is_active' => true,
        ]);

        Driver::create([
            'name' => 'Omar Al-Fahad',
            'phone' => '+966-503456789',
            'license_number' => 'DL-345678',
            'branch_id' => $waterBranch->id,
            'is_active' => true,
        ]);
    }
}