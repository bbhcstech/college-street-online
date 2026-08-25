<?php
namespace App\Http\Controllers;

use App\Models\BulkOrderRequest;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function about() { return view('pages.about'); }
    public function bulkOrders() { return view('pages.bulk-orders'); }
    public function storeBulkOrder(Request $request)
    {
        $data = $request->validate([
            'institution_name' => 'required|string|max:200',
            'contact_name' => 'required|string|max:150',
            'email' => 'required|email|max:150',
            'phone' => 'required|string|max:30',
            'requirements' => 'required|string|min:10|max:10000',
            'notes' => 'nullable|string|max:3000',
        ]);
        $bulkOrder = BulkOrderRequest::create($data + ['customer_id' => auth()->id()]);

        return redirect()->route('bulk-orders')->with('success', "Quote request #BOR{$bulkOrder->id} submitted. Our team will contact you soon.");
    }
    public function bookRights() { return view('pages.book-rights'); }
}
