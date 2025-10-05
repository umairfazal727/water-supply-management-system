<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Vehicle;
use App\Models\Branch;

class VehicleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $mainBranch = Branch::where('code', 'MAIN')->first();
        $branch1 = Branch::where('code', 'BR1')->first();
        $waterBranch = Branch::where('code', 'WATER_TR')->first();

        Vehicle::create([
            'vehicle_number' => 'ABC-123',
            'tanker_size' => '5000 Gallons',
            'sweet_water_price' => 150.00,
            'salt_water_price' => 100.00,
            'branch_id' => $mainBranch->id,
            'is_active' => true,
        ]);

        Vehicle::create([
            'vehicle_number' => 'DEF-456',
            'tanker_size' => '3000 Gallons',
            'sweet_water_price' => 120.00,
            'salt_water_price' => 80.00,
            'branch_id' => $branch1->id,
            'is_active' => true,
        ]);

        Vehicle::create([
            'vehicle_number' => 'GHI-789',
            'tanker_size' => '8000 Gallons',
            'sweet_water_price' => 200.00,
            'salt_water_price' => 150.00,
            'branch_id' => $waterBranch->id,
            'is_active' => true,
        ]);
    }
}