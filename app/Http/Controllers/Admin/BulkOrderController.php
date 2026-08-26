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
        $perPage = in_array((int) $request->query('per_page'), [10, 25, 50, 100], true) ? (int) $request->query('per_page') : 10;
        $requests = $this->filteredQuery($request)->latest()->paginate($perPage)->withQueryString();

        return view('admin.bulk-orders.index', [
            'requests' => $requests,
            'totalRequests' => BulkOrderRequest::count(),
            'newRequests' => BulkOrderRequest::where('status', 'new')->count(),
            'quotedValue' => BulkOrderRequest::whereIn('status', ['quoted', 'accepted', 'completed'])->sum('quoted_amount'),
        ]);
    }

    public function export(Request $request, string $type)
    {
        abort_unless(in_array($type, ['csv', 'excel', 'print', 'pdf'], true), 404);
        $requests = $this->filteredQuery($request)
            ->when($request->filled('ids'), fn ($query) => $query->whereIn('id', collect(explode(',', $request->query('ids')))->filter(fn ($id) => ctype_digit($id))))
            ->latest()->get();

        if ($type === 'csv') {
            return response()->streamDownload(function () use ($requests) {
                $output = fopen('php://output', 'w');
                fputcsv($output, ['Reference', 'Institution', 'Contact', 'Email', 'Phone', 'Submitted', 'Quoted Amount INR', 'Status']);
                foreach ($requests as $item) fputcsv($output, ['BOR'.$item->id, $item->institution_name, $item->contact_name, $item->email, $item->phone, $item->created_at->format('Y-m-d H:i'), $item->quoted_amount, $item->status]);
                fclose($output);
            }, 'bulk-requests-'.now()->format('Y-m-d').'.csv');
        }

        if ($type === 'excel') {
            return response()->view('admin.bulk-orders.report', compact('requests') + ['mode' => 'excel'])
                ->header('Content-Type', 'application/vnd.ms-excel')
                ->header('Content-Disposition', 'attachment; filename="bulk-requests-'.now()->format('Y-m-d').'.xls"');
        }

        return view('admin.bulk-orders.report', compact('requests') + ['mode' => $type]);
    }

    public function updateStatus(Request $request, BulkOrderRequest $bulkOrder)
    {
        $data = $request->validate(['status' => ['required', Rule::in(BulkOrderRequest::STATUSES)]]);
        if ($data['status'] === 'quoted' && ! $bulkOrder->quoted_amount) {
            return back()->withErrors(['status' => 'Open the request and enter an amount before marking it as quoted.']);
        }
        $bulkOrder->update($data);
        return back()->with('success', 'Bulk request status updated.');
    }

    private function filteredQuery(Request $request)
    {
        return BulkOrderRequest::query()
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = trim($request->query('q'));
                $id = preg_replace('/\D/', '', $term);
                $query->where(function ($search) use ($term, $id) {
                    if ($id !== '') $search->orWhere('id', (int) $id);
                    $search->orWhere('institution_name', 'like', "%{$term}%")
                        ->orWhere('contact_name', 'like', "%{$term}%")
                        ->orWhere('email', 'like', "%{$term}%")
                        ->orWhere('phone', 'like', "%{$term}%");
                });
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->query('status')))
            ->when($request->filled('date_from'), fn ($query) => $query->whereDate('created_at', '>=', $request->query('date_from')))
            ->when($request->filled('date_to'), fn ($query) => $query->whereDate('created_at', '<=', $request->query('date_to')));
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
