<?php
namespace App\Http\Controllers\Publisher;

use App\Http\Controllers\Controller;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $publisherId = auth()->user()->publisher->id;
        $perPage = in_array((int) $request->query('per_page'), [10, 25, 50, 100], true) ? (int) $request->query('per_page') : 10;
        $items = $this->filteredQuery($request)->latest('order_items.created_at')->paginate($perPage)->withQueryString();
        $base = OrderItem::whereHas('book', fn ($query) => $query->where('publisher_id', $publisherId));

        return view('publisher.orders', [
            'items' => $items,
            'totalItems' => (clone $base)->count(),
            'pendingItems' => (clone $base)->where('fulfillment_status', 'pending')->count(),
            'unitsOrdered' => (clone $base)->sum('quantity'),
        ]);
    }

    public function export(Request $request, string $type)
    {
        abort_unless(in_array($type, ['csv', 'excel', 'print', 'pdf'], true), 404);
        $items = $this->filteredQuery($request)
            ->when($request->filled('ids'), fn ($query) => $query->whereIn('order_items.id', collect(explode(',', $request->query('ids')))->filter(fn ($id) => ctype_digit($id))))
            ->latest('order_items.created_at')->get();

        if ($type === 'csv') {
            return response()->streamDownload(function () use ($items) {
                $output = fopen('php://output', 'w');
                fputcsv($output, ['Order', 'Date', 'Customer', 'Email', 'Book', 'Quantity', 'Unit Price INR', 'Gross INR', 'Payment', 'Overall Status', 'Fulfillment']);
                foreach ($items as $item) {
                    $price = $item->base_unit_price ?? $item->unit_price;
                    fputcsv($output, ['CSO'.$item->order_id, $item->order->created_at->format('Y-m-d H:i'), $item->order->customer?->name, $item->order->customer?->email, $item->book?->title, $item->quantity, $price, $item->quantity * $price, $item->order->payment?->verified_status ?? 'No payment', $item->order->status, $item->fulfillment_status]);
                }
                fclose($output);
            }, 'publisher-orders-'.now()->format('Y-m-d').'.csv');
        }

        if ($type === 'excel') {
            return response()->view('publisher.orders-report', compact('items') + ['mode' => 'excel'])
                ->header('Content-Type', 'application/vnd.ms-excel')
                ->header('Content-Disposition', 'attachment; filename="publisher-orders-'.now()->format('Y-m-d').'.xls"');
        }

        return view('publisher.orders-report', compact('items') + ['mode' => $type]);
    }

    private function filteredQuery(Request $request)
    {
        $publisherId = auth()->user()->publisher->id;

        return OrderItem::query()->whereHas('book', fn ($query) => $query->where('publisher_id', $publisherId))
            ->with(['book', 'order.customer', 'order.payment'])
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = trim($request->query('q'));
                $orderId = preg_replace('/\D/', '', $term);
                $query->where(function ($search) use ($term, $orderId) {
                    if ($orderId !== '') $search->orWhere('order_id', (int) $orderId);
                    $search->orWhereHas('book', fn ($book) => $book->where('title', 'like', "%{$term}%"))
                        ->orWhereHas('order.customer', fn ($customer) => $customer->where('name', 'like', "%{$term}%")->orWhere('email', 'like', "%{$term}%"));
                });
            })
            ->when($request->filled('status'), fn ($query) => $query->whereHas('order', fn ($order) => $order->where('status', $request->query('status'))))
            ->when($request->filled('fulfillment'), fn ($query) => $query->where('fulfillment_status', $request->query('fulfillment')))
            ->when($request->filled('payment'), fn ($query) => $query->whereHas('order.payment', fn ($payment) => $payment->where('verified_status', $request->query('payment'))))
            ->when($request->filled('date_from'), fn ($query) => $query->whereHas('order', fn ($order) => $order->whereDate('created_at', '>=', $request->query('date_from'))))
            ->when($request->filled('date_to'), fn ($query) => $query->whereHas('order', fn ($order) => $order->whereDate('created_at', '<=', $request->query('date_to'))));
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
