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
            if (!Schema::hasColumn('orders', 'tax_amount')) {
                $table->decimal('tax_amount', 10, 2)->default(0.00)->after('shipping_fee');
            }
            if (!Schema::hasColumn('orders', 'tax_rate')) {
                $table->decimal('tax_rate', 5, 2)->default(0.00)->after('tax_amount');
            }
            if (!Schema::hasColumn('orders', 'is_tax_inclusive')) {
                $table->boolean('is_tax_inclusive')->default(false)->after('tax_rate');
            }
            if (!Schema::hasColumn('orders', 'base_subtotal')) {
                $table->decimal('base_subtotal', 10, 2)->default(0.00)->after('base_total_amount');
            }
            if (!Schema::hasColumn('orders', 'base_shipping_fee')) {
                $table->decimal('base_shipping_fee', 10, 2)->default(0.00)->after('base_subtotal');
            }
            if (!Schema::hasColumn('orders', 'base_tax_amount')) {
                $table->decimal('base_tax_amount', 10, 2)->default(0.00)->after('base_shipping_fee');
            }
            if (!Schema::hasColumn('orders', 'base_discount_amount')) {
                $table->decimal('base_discount_amount', 10, 2)->default(0.00)->after('base_tax_amount');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(array_filter([
                Schema::hasColumn('orders', 'tax_amount') ? 'tax_amount' : null,
                Schema::hasColumn('orders', 'tax_rate') ? 'tax_rate' : null,
                Schema::hasColumn('orders', 'is_tax_inclusive') ? 'is_tax_inclusive' : null,
                Schema::hasColumn('orders', 'base_tax_amount') ? 'base_tax_amount' : null,
                Schema::hasColumn('orders', 'base_discount_amount') ? 'base_discount_amount' : null,
            ]));
        });
    }
};

