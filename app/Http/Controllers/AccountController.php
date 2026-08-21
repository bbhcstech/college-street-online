<?php
namespace App\Http\Controllers;

use App\Models\Order;

class AccountController extends Controller
{
    public function orders()
    {
        $orders = Order::with('items.book')->where('customer_id', auth()->id())->latest()->paginate(10);
        return view('pages.account-orders', compact('orders'));
    }
}
