<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use App\Services\CloudinaryImageService;
use Illuminate\Http\Request;

class PaymentSettingController extends Controller
{
    public function edit()
    {
        return view('admin.payment-settings', [
            'qr' => SiteSetting::where('key', 'payment_qr')->first(),
        ]);
    }

    public function update(Request $request, CloudinaryImageService $images)
    {
        $data = $request->validate([
            'payment_qr' => 'required|image|mimes:jpg,jpeg,png,webp|max:3072',
        ]);
        $current = SiteSetting::where('key', 'payment_qr')->first();
        $uploaded = $images->uploadPaymentQr($data['payment_qr']);

        SiteSetting::updateOrCreate(['key' => 'payment_qr'], [
            'value' => $uploaded['url'],
            'meta' => ['public_id' => $uploaded['public_id']],
        ]);

        try {
            $images->delete($current?->meta['public_id'] ?? null);
        } catch (\Throwable $exception) {
            report($exception);
        }

        return back()->with('success', 'Payment QR code updated.');
    }
}
