<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('newsletter_subscribers', function (Blueprint $table) {
            $table->id();
            $table->string('email', 150)->unique();
            $table->string('unsubscribe_token', 64)->unique();
            $table->timestamp('subscribed_at')->useCurrent();
        });
        // FR-11, currently Pending in the SRS — table included so the schema
        // is ready when the feature is built.
        Schema::create('book_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained('books')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->unsignedTinyInteger('rating');
            $table->text('review')->nullable();
            $table->timestamps();
            $table->unique(['book_id', 'customer_id', 'order_id']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('book_reviews');
        Schema::dropIfExists('newsletter_subscribers');
    }
};
