<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $overallLevels = ['confirmed' => 0, 'processing' => 1, 'packed' => 2, 'shipped' => 3];
        $statusByLevel = [1 => 'processing', 2 => 'packed', 3 => 'shipped'];

        DB::table('orders')
            ->whereIn('status', array_keys($overallLevels))
            ->orderBy('id')
            ->each(function ($order) use ($overallLevels, $statusByLevel) {
                $lowestLevel = DB::table('order_items')
                    ->where('order_id', $order->id)
                    ->selectRaw("MIN(CASE fulfillment_status WHEN 'shipped' THEN 3 WHEN 'packed' THEN 2 WHEN 'processing' THEN 1 ELSE 0 END) AS level")
                    ->value('level');

                $syncedStatus = $statusByLevel[(int) $lowestLevel] ?? null;
                if (! $syncedStatus || $overallLevels[$syncedStatus] <= $overallLevels[$order->status]) return;

                DB::table('orders')->where('id', $order->id)->update([
                    'status' => $syncedStatus,
                    'updated_at' => now(),
                ]);
                DB::table('order_status_histories')->insert([
                    'order_id' => $order->id,
                    'from_status' => $order->status,
                    'to_status' => $syncedStatus,
                    'changed_by' => null,
                    'created_at' => now(),
                ]);
            });

        DB::table('orders')
            ->whereIn('status', ['processing', 'packed', 'shipped'])
            ->whereNotExists(function ($query) {
                $query->selectRaw('1')
                    ->from('order_status_histories')
                    ->whereColumn('order_status_histories.order_id', 'orders.id')
                    ->whereColumn('order_status_histories.to_status', 'orders.status');
            })
            ->orderBy('id')
            ->each(function ($order) {
                DB::table('order_status_histories')->insert([
                    'order_id' => $order->id,
                    'from_status' => null,
                    'to_status' => $order->status,
                    'changed_by' => null,
                    'created_at' => now(),
                ]);
            });
    }

    public function down(): void
    {
        // Status history is intentionally preserved; operational state is not safely reversible.
    }
};
