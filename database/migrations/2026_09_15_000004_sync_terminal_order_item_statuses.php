<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->whereIn('orders.status', ['delivered', 'completed'])
            ->whereColumn('order_items.fulfillment_status', '!=', 'orders.status')
            ->select('order_items.id', 'order_items.fulfillment_status', 'orders.status')
            ->orderBy('order_items.id')
            ->each(function ($item) {
                DB::table('order_item_status_histories')->insert([
                    'order_item_id' => $item->id,
                    'from_status' => $item->fulfillment_status,
                    'to_status' => $item->status,
                    'changed_by' => null,
                    'created_at' => now(),
                ]);

                DB::table('order_items')->where('id', $item->id)->update([
                    'fulfillment_status' => $item->status,
                    'updated_at' => now(),
                ]);
            });
    }

    public function down(): void
    {
        // Historical fulfillment corrections are intentionally not reversed.
    }
};
