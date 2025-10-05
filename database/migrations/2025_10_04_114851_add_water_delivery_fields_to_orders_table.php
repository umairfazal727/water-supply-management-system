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
        Schema::table('orders', function (Blueprint $table) {
            $table->string('vehicle_number')->nullable();
            $table->string('driver_name')->nullable();
            $table->string('company_name')->nullable();
            $table->string('tanker_size')->nullable();
            $table->enum('product_type', ['sweet_water', 'salt_water'])->default('sweet_water');
            $table->decimal('price', 8, 2)->default(0);
            $table->enum('payment_type', ['cash', 'credit', 'bank_transfer'])->default('cash');
            $table->foreignId('branch_id')->nullable()->constrained()->onDelete('set null');
            $table->datetime('order_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropColumn(['vehicle_number', 'driver_name', 'company_name', 'tanker_size', 'product_type', 'price', 'payment_type', 'branch_id', 'order_date']);
        });
    }
};
