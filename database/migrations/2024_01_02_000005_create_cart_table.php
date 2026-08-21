<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        // DB-backed cart (not session-only) so it persists across logins, per FR-5.
        Schema::create('cart', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('book_id')->constrained('books')->cascadeOnDelete();
            $table->integer('quantity')->default(1);
            $table->timestamps();
            $table->unique(['customer_id', 'book_id']);
        });
    }
    public function down(): void { Schema::dropIfExists('cart'); }
};
