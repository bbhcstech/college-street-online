<?php

namespace App\Services;

use App\Models\Order;
use App\Models\PublisherLedgerEntry;
use Illuminate\Support\Facades\DB;

class PublisherSettlementService
{
    public function recordVerifiedOrder(Order $order, float $commissionRate): void
    {
        $order->loadMissing(['items.book', 'coupon']);
        $baseDiscount = (float) $order->discount_amount / max((float) $order->exchange_rate, 0.000001);

        DB::transaction(function () use ($order, $commissionRate, $baseDiscount) {
            $eligible = $order->coupon?->publisher_id
                ? $order->items->filter(fn ($item) => $item->book?->publisher_id === $order->coupon->publisher_id)
                : collect();
            $eligibleGross = $eligible->sum(fn ($item) => $item->quantity * ($item->base_unit_price ?? $item->unit_price));

            foreach ($order->items as $item) {
                if (! $item->book) continue;
                $gross = round($item->quantity * ($item->base_unit_price ?? $item->unit_price), 2);
                $discount = $eligibleGross > 0 && $eligible->contains('id', $item->id)
                    ? round($baseDiscount * ($gross / $eligibleGross), 2) : 0;
                $commission = round(max(0, $gross - $discount) * $commissionRate / 100, 2);
                $net = round($gross - $discount - $commission, 2);

                $item->update(['publisher_commission_rate' => $commissionRate, 'publisher_commission_amount' => $commission]);
                PublisherLedgerEntry::updateOrCreate(
                    ['reference_key' => 'earning:'.$item->id],
                    ['publisher_id' => $item->book->publisher_id, 'order_id' => $order->id, 'order_item_id' => $item->id, 'type' => 'earning', 'gross_amount' => $gross, 'discount_amount' => $discount, 'commission_amount' => $commission, 'amount' => $net, 'description' => 'Verified sale for order #CSO'.$order->id, 'available_at' => null]
                );
            }
        });
    }

    public function scheduleRelease(Order $order): void
    {
        $availableAt = now()->addDays(7);
        PublisherLedgerEntry::where('order_id', $order->id)->where('type', 'earning')->whereNull('available_at')->update(['available_at' => $availableAt]);
        $order->loadMissing('items.book.publisher.user');
        $order->items->pluck('book.publisher.user')->filter()->unique('id')->each(fn ($user) => $user->notify(new \App\Notifications\PublisherFinanceNotification('Earnings scheduled', "Order #CSO{$order->id} earnings will be available on {$availableAt->format('d M Y')}.", route('publisher.payments.index'))));
    }

    public function recordReversal(Order $order, string $reason): void
    {
        PublisherLedgerEntry::where('order_id', $order->id)->where('type', 'earning')->get()->each(function ($entry) use ($reason) {
            PublisherLedgerEntry::firstOrCreate(['reference_key' => 'reversal:'.$entry->id], ['publisher_id' => $entry->publisher_id, 'order_id' => $entry->order_id, 'order_item_id' => $entry->order_item_id, 'type' => 'refund', 'amount' => -abs((float) $entry->amount), 'description' => $reason, 'available_at' => now()]);
        });
    }
}
