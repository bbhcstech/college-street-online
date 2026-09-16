<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('book_reviews', function (Blueprint $table) {
            $table->json('images')->nullable()->after('review');
            $table->text('admin_response')->nullable()->after('images');
            $table->timestamp('admin_responded_at')->nullable()->after('admin_response');
        });
        Schema::create('review_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_review_id')->constrained('book_reviews')->cascadeOnDelete();
            $table->foreignId('reporter_id')->constrained('users')->cascadeOnDelete();
            $table->string('reason', 500);
            $table->timestamps();
            $table->unique(['book_review_id', 'reporter_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('review_reports');
        Schema::table('book_reviews', fn (Blueprint $table) => $table->dropColumn(['images', 'admin_response', 'admin_responded_at']));
    }
};
