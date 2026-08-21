<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('cart') && ! Schema::hasTable('carts')) {
            Schema::rename('cart', 'carts');
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('carts') && ! Schema::hasTable('cart')) {
            Schema::rename('carts', 'cart');
        }
    }
};
