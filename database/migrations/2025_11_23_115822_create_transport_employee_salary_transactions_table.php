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
        Schema::create('transport_employee_salary_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transport_employee_id')->constrained()->onDelete('cascade');
            $table->string('transaction_number')->unique();
            $table->date('transaction_date');
            $table->enum('transaction_type', ['salary', 'advance', 'deduction', 'bonus', 'refund'])->default('salary');
            $table->decimal('amount', 10, 2);
            $table->string('payment_method')->default('cash'); // cash, bank_transfer, check
            $table->string('reference_number')->nullable();
            $table->text('description')->nullable();
            $table->string('month')->nullable(); // For salary: YYYY-MM format
            $table->integer('year')->nullable();
            $table->boolean('is_advance')->default(false);
            $table->decimal('balance_after', 10, 2)->default(0); // Balance after this transaction
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['transport_employee_id', 'transaction_date']);
            $table->index('transaction_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transport_employee_salary_transactions');
    }
};
