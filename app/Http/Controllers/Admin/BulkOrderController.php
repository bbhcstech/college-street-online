<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BulkOrderRequest;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BulkOrderController extends Controller
{
    public function index(Request $request)
    {
        $requests = BulkOrderRequest::query()
            ->when($request->query('status'), fn ($query, $status) => $query->where('status', $status))
            ->latest()->paginate(20)->withQueryString();

        return view('admin.bulk-orders.index', compact('requests'));
    }

    public function show(BulkOrderRequest $bulkOrder)
    {
        return view('admin.bulk-orders.show', compact('bulkOrder'));
    }

    public function update(Request $request, BulkOrderRequest $bulkOrder)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(BulkOrderRequest::STATUSES)],
            'quoted_amount' => 'nullable|numeric|min:0|max:9999999999',
            'admin_notes' => 'nullable|string|max:5000',
        ]);
        if ($data['status'] === 'quoted' && empty($data['quoted_amount'])) {
            return back()->withErrors(['quoted_amount' => 'Enter a quote amount before marking this request as quoted.']);
        }
        $bulkOrder->update($data);

        return back()->with('success', 'Bulk order request updated.');
    }
}
