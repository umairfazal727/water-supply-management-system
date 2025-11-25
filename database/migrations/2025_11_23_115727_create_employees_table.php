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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            
            // Basic Details
            $table->string('employee_number')->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->nullable();
            $table->string('phone');
            $table->string('alternate_phone')->nullable();
            $table->text('address')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            
            // Emergency Contacts
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone')->nullable();
            $table->string('emergency_contact_relationship')->nullable();
            
            // Documents
            $table->string('id_document_path')->nullable(); // ID document upload
            $table->string('id_document_number')->nullable();
            $table->json('other_documents')->nullable(); // For additional documents
            
            // Employment Details
            $table->string('job_title');
            $table->string('designation')->nullable();
            $table->date('date_of_joining');
            $table->enum('employment_type', ['full-time', 'part-time', 'contract'])->default('full-time');
            $table->foreignId('branch_id')->nullable()->constrained()->onDelete('set null');
            $table->string('department')->nullable();
            $table->text('job_description')->nullable();
            
            // Payroll & Salary Information
            $table->decimal('monthly_salary', 10, 2)->default(0);
            $table->decimal('hourly_rate', 8, 2)->nullable();
            $table->string('bank_name')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->string('iban')->nullable();
            
            // Balance Tracking
            $table->decimal('current_balance', 10, 2)->default(0); // Negative means advance taken
            $table->decimal('total_advance_taken', 10, 2)->default(0);
            $table->decimal('total_salary_paid', 10, 2)->default(0);
            
            // Status
            $table->boolean('is_active')->default(true);
            $table->date('date_of_leaving')->nullable();
            $table->text('notes')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
