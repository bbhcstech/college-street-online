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
    public function index()
    {
        $publishers = Publisher::with('user')->withCount('books')->latest()->paginate(15);
        return view('admin.publishers.index', compact('publishers'));
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
        $data = $request->validate(['approval_status' => 'required|in:approved,rejected']);
        $publisher->update(['approval_status' => $data['approval_status']]);

        return back()->with('success', 'Publisher application ' . $data['approval_status'] . '.');
    }
}
