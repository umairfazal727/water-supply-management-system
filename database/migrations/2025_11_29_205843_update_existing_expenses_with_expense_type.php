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
        // Check if expense_type column exists, if not add it first
        if (!Schema::hasColumn('expenses', 'expense_type')) {
            Schema::table('expenses', function (Blueprint $table) {
                $table->enum('expense_type', ['general', 'transport', 'operational'])->default('general')->after('vehicle_id');
            });
        } else {
            DB::statement("ALTER TABLE expenses MODIFY COLUMN expense_type ENUM('general', 'transport', 'operational') NOT NULL DEFAULT 'general'");
        }
        
        // Update all existing expenses that have null expense_type to 'general'
        DB::table('expenses')
            ->whereNull('expense_type')
            ->update(['expense_type' => 'general']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration doesn't need to be reversed
        // The expense_type column should remain
    }
};
