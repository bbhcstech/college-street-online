<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasColumn('books', 'cover_image_public_id')) {
            Schema::table('books', fn (Blueprint $table) => $table->dropColumn('cover_image_public_id'));
        }
    }
    public function down(): void
    {
        if (! Schema::hasColumn('books', 'cover_image_public_id')) {
            Schema::table('books', fn (Blueprint $table) => $table->string('cover_image_public_id')->nullable()->after('cover_image_url'));
        }
    }
};
