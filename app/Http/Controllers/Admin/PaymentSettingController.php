<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use App\Services\PublicImageStorageService;
use Illuminate\Http\Request;

class PaymentSettingController extends Controller
{
    public function edit()
    {
        return view('admin.payment-settings', [
            'qr' => SiteSetting::where('key', 'payment_qr')->first(),
            'qrUrl' => SiteSetting::valueFor('payment_qr'),
        ]);
    }

    public function update(Request $request, PublicImageStorageService $images)
    {
        $data = $request->validate([
            'payment_qr' => 'required|image|mimes:jpg,jpeg,png,webp|max:3072',
        ]);
        $current = SiteSetting::where('key', 'payment_qr')->first();
        $uploaded = $images->storePaymentQr($data['payment_qr']);

        SiteSetting::updateOrCreate(['key' => 'payment_qr'], [
            'value' => $uploaded,
            'meta' => ['disk' => 'public'],
        ]);

        try {
            $images->delete($current?->value);
        } catch (\Throwable $exception) {
            report($exception);
        }

        return back()->with('success', 'Payment QR code updated.');
    }
}
