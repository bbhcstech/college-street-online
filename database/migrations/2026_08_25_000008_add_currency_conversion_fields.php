<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('exchange_rate', 14, 6)->default(1)->after('currency');
            $table->decimal('base_total_amount', 12, 2)->nullable()->after('total_amount');
        });
        Schema::table('order_items', fn (Blueprint $table) => $table->decimal('base_unit_price', 10, 2)->nullable()->after('unit_price'));
        DB::table('orders')->update(['base_total_amount' => DB::raw('total_amount')]);
        DB::table('order_items')->update(['base_unit_price' => DB::raw('unit_price')]);
    }

    public function down(): void
    {
        Schema::table('order_items', fn (Blueprint $table) => $table->dropColumn('base_unit_price'));
        Schema::table('orders', fn (Blueprint $table) => $table->dropColumn(['exchange_rate', 'base_total_amount']));
    }
};
