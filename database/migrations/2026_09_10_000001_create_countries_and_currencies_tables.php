<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('currencies', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique();
            $table->string('name', 100);
            $table->string('symbol', 10);
            $table->decimal('exchange_rate_to_inr', 12, 4)->default(1.0000);
            $table->integer('decimal_places')->default(2);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->string('code', 5)->unique();
            $table->string('name', 100);
            $table->string('currency_code', 10);
            $table->decimal('markup_percentage', 5, 2)->default(0.00);
            $table->decimal('base_shipping_fee', 10, 2)->default(0.00);
            $table->decimal('per_kg_shipping_fee', 10, 2)->default(0.00);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        DB::table('currencies')->insert([
            ['code' => 'INR', 'name' => 'Indian Rupee', 'symbol' => '₹', 'exchange_rate_to_inr' => 1.0000, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'USD', 'name' => 'US Dollar', 'symbol' => '$', 'exchange_rate_to_inr' => 0.0120, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'GBP', 'name' => 'British Pound', 'symbol' => '£', 'exchange_rate_to_inr' => 0.0095, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'EUR', 'name' => 'Euro', 'symbol' => '€', 'exchange_rate_to_inr' => 0.0110, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'AED', 'name' => 'UAE Dirham', 'symbol' => 'AED ', 'exchange_rate_to_inr' => 0.0440, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('countries')->insert([
            ['code' => 'IN', 'name' => 'India', 'currency_code' => 'INR', 'markup_percentage' => 0.00, 'base_shipping_fee' => 50.00, 'per_kg_shipping_fee' => 20.00, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'US', 'name' => 'United States', 'currency_code' => 'USD', 'markup_percentage' => 10.00, 'base_shipping_fee' => 15.00, 'per_kg_shipping_fee' => 5.00, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'GB', 'name' => 'United Kingdom', 'currency_code' => 'GBP', 'markup_percentage' => 10.00, 'base_shipping_fee' => 12.00, 'per_kg_shipping_fee' => 4.00, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'AE', 'name' => 'United Arab Emirates', 'currency_code' => 'AED', 'markup_percentage' => 5.00, 'base_shipping_fee' => 40.00, 'per_kg_shipping_fee' => 10.00, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('countries');
        Schema::dropIfExists('currencies');
    }
};

