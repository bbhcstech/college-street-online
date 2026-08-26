<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->decimal('publisher_commission_rate', 5, 2)->nullable()->after('base_unit_price');
            $table->decimal('publisher_commission_amount', 10, 2)->default(0)->after('publisher_commission_rate');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(['publisher_commission_rate', 'publisher_commission_amount']);
        });
    }
};
