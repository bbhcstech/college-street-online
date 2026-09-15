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
        $recentOrders = \App\Models\Order::where('customer_id', $request->user()->id)
            ->withCount('items')
            ->with(['items.book'])
            ->latest()
            ->limit(3)
            ->get();

        $countries = \App\Models\Country::where('is_active', true)->orderBy('name')->get();
        $currencies = \App\Models\Currency::where('is_active', true)->orderBy('code')->get();

        $totalOrdersCount = \App\Models\Order::where('customer_id', $request->user()->id)->count();

        return view('pages.profile', [
            'user' => $request->user(),
            'recentOrders' => $recentOrders,
            'totalOrdersCount' => $totalOrdersCount,
            'countries' => $countries,
            'currencies' => $currencies,
        ]);
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
            'country_code' => ['nullable', 'string', Rule::exists('countries', 'code')],
            'phone_code' => ['nullable', 'string', 'max:10'],
            'phone_number' => ['nullable', 'string', 'max:30'],
            'preferred_currency' => ['nullable', 'string', Rule::exists('currencies', 'code')],
            'marketing_consent' => ['nullable'],
            'profile_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'business_name' => Rule::when($user->isPublisher(), ['required', 'string', 'max:200'], ['nullable']),
            'contact_details' => Rule::when($user->isPublisher(), ['nullable', 'string', 'max:1000'], ['nullable']),
        ]);

        $newPath = $request->hasFile('profile_image')
            ? $request->file('profile_image')->store('profile-images', 'public')
            : null;
        $oldPath = $user->profile_image_path;

        try {
            DB::transaction(function () use ($user, $data, $request, $newPath, $oldPath) {
                $updateData = [
                    'name' => trim($data['name']),
                    'email' => strtolower(trim($data['email'])),
                    'profile_image_path' => $newPath ?: $oldPath,
                ];

                if (array_key_exists('country_code', $data) && empty($user->country_code)) {
                    $updateData['country_code'] = $data['country_code'];
                }
                if (array_key_exists('phone_code', $data)) {
                    $updateData['phone_code'] = $data['phone_code'];
                }
                if (array_key_exists('phone_number', $data)) {
                    $updateData['phone_number'] = $data['phone_number'];
                }
                if (array_key_exists('preferred_currency', $data)) {
                    $updateData['preferred_currency'] = $data['preferred_currency'];
                }
                if ($request->has('marketing_consent_submitted')) {
                    $updateData['marketing_consent'] = $request->boolean('marketing_consent');
                }

                $user->update($updateData);

                if ($user->isPublisher()) {
                    $user->publisher->update([
                        'business_name' => trim($data['business_name']),
                        'contact_details' => $data['contact_details'] ?? null,
                    ]);
                }
            });
        } catch (\Throwable $exception) {
            if ($newPath) Storage::disk('public')->delete($newPath);
            throw $exception;
        }

        if ($newPath && $oldPath) {
            Storage::disk('public')->delete($oldPath);
        }

        if ($user->country_code) {
            session(['customer_country' => $user->country_code]);
        }
        if ($user->preferred_currency) {
            session(['customer_currency' => $user->preferred_currency]);
        }

        return back()->with('success', 'Profile details updated.');
    }

    public function updateAdmin(Request $request)
    {
        return $this->update($request);
    }

    public function destroyImage(Request $request)
    {
        $user = $request->user();
        abort_unless($user->isAdmin(), 403);
        $oldPath = $user->profile_image_path;
        $user->update(['profile_image_path' => null]);
        if ($oldPath) Storage::disk('public')->delete($oldPath);

        return back()->with('success', 'Profile image removed.');
    }

    public function destroyAdminImage(Request $request)
    {
        return $this->destroyImage($request);
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
