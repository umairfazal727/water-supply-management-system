<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add 'on_account' to the payment_type enum
        DB::statement("ALTER TABLE `orders` MODIFY `payment_type` ENUM('cash', 'credit', 'bank_transfer', 'on_account') DEFAULT 'cash'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove 'on_account' from the payment_type enum
        // Note: This will fail if any records have 'on_account' as payment_type
        DB::statement("ALTER TABLE `orders` MODIFY `payment_type` ENUM('cash', 'credit', 'bank_transfer') DEFAULT 'cash'");
    }
};
