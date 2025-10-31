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
        Schema::create('delivery_ledgers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('delivery_customer_id');
            $table->unsignedBigInteger('delivery_id')->nullable(); // For deliveries that create ledger entries
            $table->string('entry_number')->unique(); // Entry No like 0185, 0299, etc.
            $table->date('transaction_date');
            $table->string('entry_origin')->nullable(); // JV: 3888, JV: 3951, etc.
            $table->decimal('debit_amount', 10, 2)->default(0.00);
            $table->decimal('credit_amount', 10, 2)->default(0.00);
            $table->decimal('balance', 10, 2); // Running balance after this transaction
            $table->text('description'); // Line-Item Description
            $table->string('transaction_type')->default('manual'); // 'delivery', 'payment', 'opening_balance', 'manual'
            $table->timestamps();

            $table->foreign('delivery_customer_id')->references('id')->on('delivery_customers')->onDelete('cascade');
            $table->foreign('delivery_id')->references('id')->on('deliveries')->onDelete('set null');
            
            $table->index(['delivery_customer_id', 'transaction_date']);
            $table->index('transaction_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_ledgers');
    }
};
