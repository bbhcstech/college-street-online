<?php
namespace App\Http\Controllers\Publisher;

use App\Http\Controllers\Controller;
use App\Models\OrderItem;

class OrderController extends Controller
{
    public function index()
    {
        $publisherId = auth()->user()->publisher->id;
        $items = OrderItem::whereHas('book', fn ($q) => $q->where('publisher_id', $publisherId))
            ->with(['book', 'order.customer'])->latest()->paginate(20);
        return view('publisher.orders', compact('items'));
    }
}
