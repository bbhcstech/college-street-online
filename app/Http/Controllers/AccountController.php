<?php
namespace App\Http\Controllers;

use App\Models\Order;

class AccountController extends Controller
{
    public function orders()
    {
        $orders = Order::with(['items.book', 'statusHistory'])
            ->where('customer_id', auth()->id())
            ->latest()
            ->paginate(10);
        return view('pages.account-orders', compact('orders'));
    }

    public function notifications()
    {
        $notifications = auth()->user()->notifications()->latest()->paginate(15);
        auth()->user()->unreadNotifications->markAsRead();

        return view('pages.account-notifications', compact('notifications'));
    }

    public function downloadInvoice(Order $order)
    {
        abort_unless($order->customer_id === auth()->id(), 403);
        abort_unless(in_array($order->status, ['delivered', 'completed'], true), 403, 'Invoice is only available for delivered or completed orders.');

        $order->load(['items.book.author', 'customer', 'payment']);

        return view('pages.customer-order-invoice', compact('order'));
    }
}
