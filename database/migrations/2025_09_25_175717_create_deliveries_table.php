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
        Schema::create('deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->onDelete('cascade');
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->foreignId('order_id')->nullable()->constrained()->onDelete('set null');
            $table->string('delivery_number')->unique();
            $table->date('delivery_date');
            $table->time('delivery_time');
            $table->string('customer_site');
            $table->string('customer_location');
            $table->integer('trip_size'); // Size of delivery in gallons/liters
            $table->decimal('rate_per_gallon', 8, 2);
            $table->decimal('total_amount', 10, 2);
            $table->enum('payment_method', ['cash', 'credit', 'bank_transfer', 'check'])->default('cash');
            $table->enum('status', ['scheduled', 'in_progress', 'delivered', 'cancelled'])->default('scheduled');
            $table->text('notes')->nullable();
            $table->json('delivery_photos')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deliveries');
    }
};
