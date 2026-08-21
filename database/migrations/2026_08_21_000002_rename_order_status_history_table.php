<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('order_status_history') && ! Schema::hasTable('order_status_histories')) {
            Schema::rename('order_status_history', 'order_status_histories');
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('order_status_histories') && ! Schema::hasTable('order_status_history')) {
            Schema::rename('order_status_histories', 'order_status_history');
        }
    }
};
