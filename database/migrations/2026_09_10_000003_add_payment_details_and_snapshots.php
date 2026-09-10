<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            if (!Schema::hasColumn('payments', 'payment_method')) {
                $table->string('payment_method', 50)->nullable()->after('order_id');
            }
            if (!Schema::hasColumn('payments', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable()->after('verified_status');
            }
            if (!Schema::hasColumn('payments', 'admin_notes')) {
                $table->text('admin_notes')->nullable()->after('rejection_reason');
            }
        });

        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'tracking_number')) {
                $table->string('tracking_number', 100)->nullable()->after('status');
            }
            if (!Schema::hasColumn('orders', 'country_code')) {
                $table->string('country_code', 5)->default('IN')->after('shipping_address');
            }
            if (!Schema::hasColumn('orders', 'currency')) {
                $table->string('currency', 10)->default('INR')->after('country_code');
            }
            if (!Schema::hasColumn('orders', 'currency_symbol')) {
                $table->string('currency_symbol', 10)->default('₹')->after('currency');
            }
            if (!Schema::hasColumn('orders', 'exchange_rate')) {
                $table->decimal('exchange_rate', 12, 4)->default(1.0000)->after('currency_symbol');
            }
            if (!Schema::hasColumn('orders', 'base_subtotal')) {
                $table->decimal('base_subtotal', 10, 2)->default(0.00)->after('subtotal');
            }
            if (!Schema::hasColumn('orders', 'base_shipping_fee')) {
                $table->decimal('base_shipping_fee', 10, 2)->default(0.00)->after('shipping_fee');
            }
            if (!Schema::hasColumn('orders', 'base_total_amount')) {
                $table->decimal('base_total_amount', 10, 2)->default(0.00)->after('total_amount');
            }
        });

        Schema::table('order_items', function (Blueprint $table) {
            if (!Schema::hasColumn('order_items', 'base_unit_price')) {
                $table->decimal('base_unit_price', 10, 2)->nullable()->after('unit_price');
            }
            if (!Schema::hasColumn('order_items', 'currency_code')) {
                $table->string('currency_code', 10)->nullable()->after('base_unit_price');
            }
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['payment_method', 'rejection_reason', 'admin_notes']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['tracking_number', 'country_code', 'currency', 'currency_symbol', 'exchange_rate', 'base_subtotal', 'base_shipping_fee', 'base_total_amount']);
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(['base_unit_price', 'currency_code']);
        });
    }
};

