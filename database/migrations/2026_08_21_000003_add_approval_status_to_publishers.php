<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('publishers', function (Blueprint $table) {
            $table->enum('approval_status', ['pending', 'approved', 'rejected'])
                ->default('approved')
                ->after('contact_details');
        });
    }

    public function down(): void
    {
        Schema::table('publishers', function (Blueprint $table) {
            $table->dropColumn('approval_status');
        });
    }
};
