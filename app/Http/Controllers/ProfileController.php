<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function customerEdit(Request $request)
    {
        return view('pages.profile', ['user' => $request->user()]);
    }

    public function adminEdit(Request $request)
    {
        return view('admin.profile', ['user' => $request->user()]);
    }

    public function publisherEdit(Request $request)
    {
        return view('publisher.profile', ['user' => $request->user()->load('publisher')]);
    }

    public function update(Request $request)
    {
        $user = $request->user();
        $data = $request->validate([
            'name' => 'required|string|max:150',
            'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($user->id)],
            'business_name' => Rule::when($user->isPublisher(), ['required', 'string', 'max:200'], ['nullable']),
            'contact_details' => Rule::when($user->isPublisher(), ['nullable', 'string', 'max:1000'], ['nullable']),
        ]);

        DB::transaction(function () use ($user, $data) {
            $user->update(['name' => trim($data['name']), 'email' => strtolower(trim($data['email']))]);
            if ($user->isPublisher()) {
                $user->publisher->update([
                    'business_name' => trim($data['business_name']),
                    'contact_details' => $data['contact_details'] ?? null,
                ]);
            }
        });

        return back()->with('success', 'Profile updated successfully.');
    }

    public function updateAdmin(Request $request)
    {
        $user = $request->user();
        abort_unless($user->isAdmin(), 403);
        $data = $request->validate([
            'name' => 'required|string|max:150',
            'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($user->id)],
            'profile_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $newPath = $request->hasFile('profile_image')
            ? $request->file('profile_image')->store('profile-images', 'public')
            : null;
        $oldPath = $user->profile_image_path;

        try {
            $user->update([
                'name' => trim($data['name']),
                'email' => strtolower(trim($data['email'])),
                'profile_image_path' => $newPath ?: $oldPath,
            ]);
        } catch (\Throwable $exception) {
            if ($newPath) Storage::disk('public')->delete($newPath);
            throw $exception;
        }

        if ($newPath && $oldPath) Storage::disk('public')->delete($oldPath);
        return back()->with('success', 'Admin profile updated successfully.');
    }

    public function destroyAdminImage(Request $request)
    {
        $user = $request->user();
        abort_unless($user->isAdmin(), 403);
        $oldPath = $user->profile_image_path;
        $user->update(['profile_image_path' => null]);
        if ($oldPath) Storage::disk('public')->delete($oldPath);

        return back()->with('success', 'Profile image removed.');
    }

    public function updatePassword(Request $request)
    {
        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);
        $request->user()->update(['password' => $data['password']]);

        return back()->with('success', 'Password changed successfully.');
    }
}
