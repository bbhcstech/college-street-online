<?php
namespace App\Http\Controllers\Publisher;

use App\Http\Controllers\Controller;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index()
    {
        $publisherId = auth()->user()->publisher->id;
        $items = OrderItem::whereHas('book', fn ($q) => $q->where('publisher_id', $publisherId))
            ->with(['book', 'order.customer'])->latest()->paginate(20);
        return view('publisher.orders', compact('items'));
    }

    public function updateStatus(Request $request, OrderItem $orderItem)
    {
        $orderItem->load(['book', 'order']);
        abort_unless($orderItem->book?->publisher_id === auth()->user()->publisher->id, 403);

        $data = $request->validate([
            'status' => 'required|in:processing,packed,shipped',
        ]);

        if (! in_array($orderItem->order->status, ['confirmed', 'processing', 'packed', 'shipped'], true)) {
            return back()->withErrors(['status' => 'This item cannot be updated in the current order state.']);
        }

        $nextStatus = [
            'pending' => 'processing',
            'processing' => 'packed',
            'packed' => 'shipped',
        ][$orderItem->fulfillment_status] ?? null;

        if ($data['status'] !== $nextStatus) {
            return back()->withErrors(['status' => 'Order item statuses must be updated in sequence.']);
        }

        DB::transaction(function () use ($orderItem, $data) {
            $oldStatus = $orderItem->fulfillment_status;
            $orderItem->update(['fulfillment_status' => $data['status']]);
            $orderItem->statusHistory()->create([
                'from_status' => $oldStatus,
                'to_status' => $data['status'],
                'changed_by' => auth()->id(),
            ]);

            $order = $orderItem->order()->with('items')->lockForUpdate()->first();
            $levels = ['pending' => 0, 'processing' => 1, 'packed' => 2, 'shipped' => 3];
            $lowestItemLevel = $order->items->min(fn ($item) => $levels[$item->fulfillment_status] ?? 0);
            $syncedStatus = [1 => 'processing', 2 => 'packed', 3 => 'shipped'][$lowestItemLevel] ?? null;
            $overallLevels = ['confirmed' => 0, 'processing' => 1, 'packed' => 2, 'shipped' => 3];

            if ($syncedStatus
                && isset($overallLevels[$order->status])
                && $overallLevels[$syncedStatus] > $overallLevels[$order->status]) {
                $order->transitionTo($syncedStatus, auth()->id());
            }
        });

        return back()->with('success', 'Item status updated.');
    }
}
