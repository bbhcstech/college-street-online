<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdministratorController extends Controller
{
    public function index(Request $request)
    {
        $administrators = User::query()
            ->whereIn('role', ['super_admin', 'admin'])
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = trim($request->query('q'));
                $query->where(fn ($search) => $search->where('name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%"));
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.administrators.index', compact('administrators'));
    }

    public function create()
    {
        return view('admin.administrators.create');
    }

    public function store(Request $request)
    {
        $roleRules = auth()->user()->isSuperAdmin()
            ? ['required', Rule::in(['admin', 'super_admin'])]
            : ['nullable'];

        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:150', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => $roleRules,
        ]);

        $role = (auth()->user()->isSuperAdmin() && isset($data['role'])) ? $data['role'] : 'admin';

        User::create($data + [
            'role' => $role,
            'status' => 'active',
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);

        return redirect()->route('admin.administrators.index')->with('success', 'Administrator created successfully.');
    }

    public function edit(User $administrator)
    {
        $this->ensureAdministrator($administrator);

        return view('admin.administrators.edit', compact('administrator'));
    }

    public function update(Request $request, User $administrator)
    {
        $this->ensureAdministrator($administrator);

        $roleRules = auth()->user()->isSuperAdmin()
            ? ['required', Rule::in(['admin', 'super_admin'])]
            : ['nullable'];

        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($administrator->id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'role' => $roleRules,
        ]);

        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        if (!auth()->user()->isSuperAdmin() || !isset($data['role'])) {
            unset($data['role']);
        }

        $administrator->update($data + ['updated_by' => auth()->id()]);

        return redirect()->route('admin.administrators.index')->with('success', 'Administrator updated successfully.');
    }

    public function updateStatus(Request $request, User $administrator)
    {
        $this->ensureAdministrator($administrator);
        $data = $request->validate(['status' => ['required', Rule::in(['active', 'suspended'])]]);

        if ($administrator->is(auth()->user()) && $data['status'] === 'suspended') {
            return back()->withErrors(['status' => 'You cannot deactivate your own administrator account.']);
        }

        if ($data['status'] === 'suspended') {
            if ($administrator->isSuperAdmin() && User::where('role', 'super_admin')->where('status', 'active')->count() <= 1) {
                return back()->withErrors(['status' => 'The last active Super Administrator cannot be deactivated.']);
            }
            if (User::whereIn('role', ['super_admin', 'admin'])->where('status', 'active')->count() <= 1) {
                return back()->withErrors(['status' => 'The last active administrator cannot be deactivated.']);
            }
        }

        $administrator->update(['status' => $data['status'], 'updated_by' => auth()->id()]);

        return back()->with('success', $data['status'] === 'active' ? 'Administrator activated.' : 'Administrator deactivated.');
    }

    private function ensureAdministrator(User $administrator): void
    {
        abort_unless($administrator->isAdmin(), 404);

        if ($administrator->isSuperAdmin() && !auth()->user()->isSuperAdmin()) {
            abort(403, 'Unauthorized. Only Super Administrators can manage Super Admin accounts.');
        }
    }
}
