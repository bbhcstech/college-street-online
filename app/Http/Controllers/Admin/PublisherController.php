<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Publisher;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class PublisherController extends Controller
{
    public function index(Request $request)
    {
        $perPage = in_array((int) $request->query('per_page'), [10, 25, 50, 100], true) ? (int) $request->query('per_page') : 10;
        $publishers = $this->filteredQuery($request)->latest()->paginate($perPage)->withQueryString();
        return view('admin.publishers.index', compact('publishers'));
    }

    public function export(Request $request, string $type)
    {
        abort_unless(in_array($type, ['csv', 'excel', 'print', 'pdf'], true), 404);
        $publishers = $this->filteredQuery($request)
            ->when($request->filled('ids'), fn ($query) => $query->whereIn('id', collect(explode(',', $request->query('ids')))->filter(fn ($id) => ctype_digit($id))))
            ->orderBy('business_name')->get();

        if ($type === 'csv') {
            return response()->streamDownload(function () use ($publishers) {
                $output = fopen('php://output', 'w');
                fputcsv($output, ['Business Name', 'Contact Name', 'Email', 'Contact Details', 'Books', 'Status', 'Joined']);
                foreach ($publishers as $publisher) fputcsv($output, $this->exportRow($publisher));
                fclose($output);
            }, 'publishers-' . now()->format('Y-m-d') . '.csv');
        }

        if ($type === 'excel') {
            return response()->view('admin.publishers.report', compact('publishers') + ['mode' => 'excel'])
                ->header('Content-Type', 'application/vnd.ms-excel')
                ->header('Content-Disposition', 'attachment; filename="publishers-' . now()->format('Y-m-d') . '.xls"');
        }

        return view('admin.publishers.report', compact('publishers') + ['mode' => $type]);
    }

    private function filteredQuery(Request $request)
    {
        return Publisher::with('user')->withCount('books')
            ->when($request->query('q'), function ($query, $search) {
                $query->where(function ($nested) use ($search) {
                    $nested->where('business_name', 'like', "%{$search}%")
                        ->orWhere('contact_details', 'like', "%{$search}%")
                        ->orWhereHas('user', fn ($user) => $user->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"));
                });
            })
            ->when($request->query('status'), fn ($query, $status) => $query->where('approval_status', $status));
    }

    private function exportRow(Publisher $publisher): array
    {
        return [$publisher->business_name, $publisher->user?->name, $publisher->user?->email, $publisher->contact_details, $publisher->books_count, ucfirst($publisher->approval_status), $publisher->created_at->format('d M Y')];
    }

    public function create() { return view('admin.publishers.form'); }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:150',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'business_name' => 'required|string|max:200',
            'contact_details' => 'nullable|string',
        ]);
        $user = User::create([
            'name' => $data['name'], 'email' => $data['email'],
            'password' => Hash::make($data['password']), 'role' => 'publisher',
        ]);
        Publisher::create(['user_id' => $user->id, 'business_name' => $data['business_name'], 'contact_details' => $data['contact_details'] ?? null]);
        return redirect()->route('admin.publishers.index')->with('success', 'Publisher created.');
    }

    public function edit(Publisher $publisher)
    {
        $publisher->load('user');
        return view('admin.publishers.form', compact('publisher'));
    }

    public function update(Request $request, Publisher $publisher)
    {
        $data = $request->validate([
            'name' => 'required|string|max:150',
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($publisher->user_id)],
            'password' => 'nullable|string|min:8',
            'business_name' => 'required|string|max:200',
            'contact_details' => 'nullable|string',
        ]);

        $userData = ['name' => $data['name'], 'email' => $data['email']];
        if (! empty($data['password'])) {
            $userData['password'] = Hash::make($data['password']);
        }

        $publisher->user->update($userData);
        $publisher->update([
            'business_name' => $data['business_name'],
            'contact_details' => $data['contact_details'] ?? null,
        ]);

        return redirect()->route('admin.publishers.index')->with('success', 'Publisher updated.');
    }

    public function destroy(Publisher $publisher)
    {
        try {
            $publisher->user()->delete(); // cascades to publisher via FK
        } catch (QueryException $exception) {
            if ($exception->getCode() === '23000') {
                return back()->withErrors([
                    'publisher' => 'This publisher cannot be deleted because their books are linked to existing orders.',
                ]);
            }

            throw $exception;
        }

        return back()->with('success', 'Publisher removed.');
    }

    public function updateApproval(Request $request, Publisher $publisher)
    {
        $data = $request->validate(['approval_status' => 'required|in:pending,approved,rejected']);
        $publisher->update(['approval_status' => $data['approval_status']]);

        return back()->with('success', 'Publisher application ' . $data['approval_status'] . '.');
    }
}
