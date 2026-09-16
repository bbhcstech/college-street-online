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
        Schema::table('countries', function (Blueprint $table) {
            $table->decimal('tax_rate', 5, 2)->default(0.00)->after('free_shipping_threshold');
            $table->boolean('is_tax_inclusive')->default(false)->after('tax_rate');
            $table->decimal('min_order_value', 10, 2)->default(0.00)->after('is_tax_inclusive');
            $table->text('payment_methods')->nullable()->after('min_order_value');
            $table->string('estimated_delivery_days', 100)->nullable()->after('payment_methods');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('countries', function (Blueprint $table) {
            $table->dropColumn([
                'tax_rate',
                'is_tax_inclusive',
                'min_order_value',
                'payment_methods',
                'estimated_delivery_days',
            ]);
        });
    }
};

