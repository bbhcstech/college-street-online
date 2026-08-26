<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('coupons', fn (Blueprint $table) => $table->foreignId('publisher_id')->nullable()->after('id')->constrained('publishers')->nullOnDelete());
    }
    public function down(): void
    {
        Schema::table('coupons', fn (Blueprint $table) => $table->dropConstrainedForeignId('publisher_id'));
    }
};
