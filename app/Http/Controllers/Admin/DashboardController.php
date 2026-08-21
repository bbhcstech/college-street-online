<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Publisher;

class DashboardController extends Controller
{
    public function index()
    {
        return view('admin.dashboard', [
            'publisherCount' => Publisher::count(),
            'bookCount' => Book::count(),
            'orderCount' => Order::whereMonth('created_at', now()->month)->count(),
            'pendingPayments' => Payment::where('verified_status', 'pending')->count(),
            'recentPayments' => Payment::with('order.customer')->where('verified_status', 'pending')->latest()->limit(6)->get(),
            'statusMix' => Order::selectRaw('status, count(*) as c')->groupBy('status')->pluck('c', 'status'),
        ]);
    }
}
