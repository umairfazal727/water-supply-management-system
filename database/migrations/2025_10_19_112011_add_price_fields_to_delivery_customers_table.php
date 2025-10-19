<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('delivery_customers', function (Blueprint $table) {
            $table->decimal('rate', 8, 2)->default(0.00)->after('opening_balance');
            $table->decimal('sweet_water_price', 8, 2)->default(0.00)->after('rate');
            $table->decimal('salt_water_price', 8, 2)->default(0.00)->after('sweet_water_price');
            $table->decimal('drinking_water_price', 8, 2)->default(0.00)->after('salt_water_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('delivery_customers', function (Blueprint $table) {
            $table->dropColumn(['rate', 'sweet_water_price', 'salt_water_price', 'drinking_water_price']);
        });
    }
};
