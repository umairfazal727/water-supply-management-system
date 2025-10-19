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
        Schema::table('deliveries', function (Blueprint $table) {
            // Make fields nullable
            $table->time('delivery_time')->nullable()->change();
            $table->string('customer_site')->nullable()->change();
            $table->string('customer_location')->nullable()->change();
            
            // Drop fields
            $table->dropColumn(['rate_per_gallon', 'delivered_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('deliveries', function (Blueprint $table) {
            // Revert nullable fields
            $table->time('delivery_time')->nullable(false)->change();
            $table->string('customer_site')->nullable(false)->change();
            $table->string('customer_location')->nullable(false)->change();
            
            // Restore dropped fields
            $table->decimal('rate_per_gallon', 8, 2)->after('trip_size');
            $table->timestamp('delivered_at')->nullable()->after('delivery_photos');
        });
    }
};
