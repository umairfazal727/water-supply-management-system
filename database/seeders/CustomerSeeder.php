<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Customer;
use App\Models\Vehicle;
use App\Models\Driver;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $vehicle1 = Vehicle::where('vehicle_number', 'ABC-123')->first();
        $vehicle2 = Vehicle::where('vehicle_number', 'DEF-456')->first();
        $driver1 = Driver::where('name', 'Ahmed Al-Rashid')->first();
        $driver2 = Driver::where('name', 'Mohammed Al-Salem')->first();

        Customer::create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'phone' => '+966-501234567',
            'address' => 'Riyadh, Saudi Arabia',
            'vehicle_id' => $vehicle1->id,
            'driver_id' => $driver1->id,
            'company_name' => 'Al-Falaj Water Company',
            'tanker_size' => '5000 Gallons',
            'product_type' => 'sweet_water',
            'price' => 150.00,
        ]);

        Customer::create([
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'jane.smith@example.com',
            'phone' => '+966-502345678',
            'address' => 'Jeddah, Saudi Arabia',
            'vehicle_id' => $vehicle2->id,
            'driver_id' => $driver2->id,
            'company_name' => 'Desert Water Solutions',
            'tanker_size' => '3000 Gallons',
            'product_type' => 'salt_water',
            'price' => 80.00,
        ]);

        Customer::create([
            'first_name' => 'Ahmed',
            'last_name' => 'Al-Rashid',
            'email' => 'ahmed@example.com',
            'phone' => '+966-503456789',
            'address' => 'Dammam, Saudi Arabia',
            'vehicle_id' => $vehicle1->id,
            'driver_id' => $driver1->id,
            'company_name' => 'Gulf Water Services',
            'tanker_size' => '5000 Gallons',
            'product_type' => 'sweet_water',
            'price' => 150.00,
        ]);
    }
}