<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('users')->cascadeOnDelete();
            $table->enum('status', [
                'pending_payment', 'confirmed', 'processing', 'packed', 'shipped',
                'delivered', 'completed', 'cancelled', 'return_requested', 'returned',
            ])->default('pending_payment');
            $table->string('country', 2)->default('IN');
            $table->string('currency', 3)->default('INR');
            $table->text('shipping_address');
            $table->string('shipping_phone', 30)->nullable();
            $table->decimal('subtotal', 10, 2);
            $table->decimal('shipping_fee', 10, 2)->default(0);
            $table->decimal('platform_fee', 10, 2)->default(0);
            $table->foreignId('coupon_id')->nullable()->constrained('coupons')->nullOnDelete();
            $table->decimal('discount_amount', 10, 2)->default(0);
            // total_amount is always computed server-side (pricing service), never trusted from client — FR-6
            $table->decimal('total_amount', 10, 2);
            $table->timestamps();
            $table->index('status');
        });
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('book_id')->constrained('books')->restrictOnDelete();
            $table->integer('quantity');
            $table->decimal('unit_price', 10, 2); // price snapshot at time of order
            $table->timestamps();
        });
        // FR-8 fix: every status transition is written here (actor, from, to,
        // timestamp) instead of only overwriting orders.status, so support
        // staff have an auditable timeline.
        Schema::create('order_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->string('from_status', 30)->nullable();
            $table->string('to_status', 30);
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();
        });
    }
    public function down(): void {
        Schema::dropIfExists('order_status_histories');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
