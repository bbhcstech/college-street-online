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
        Schema::table('users', function (Blueprint $table) {
            $table->string('country_code', 5)->nullable()->after('email');
            $table->string('phone_code', 10)->nullable()->after('country_code');
            $table->string('phone_number', 30)->nullable()->after('phone_code');
            $table->string('preferred_currency', 5)->nullable()->after('phone_number');
            $table->boolean('marketing_consent')->default(false)->after('preferred_currency');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['country_code', 'phone_code', 'phone_number', 'preferred_currency', 'marketing_consent']);
        });
    }
};

