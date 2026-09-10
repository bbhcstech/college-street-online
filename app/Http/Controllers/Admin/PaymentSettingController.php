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
            'commissionRate' => (float) (SiteSetting::where('key', 'publisher_commission_rate')->value('value') ?? 0),
            'upiId' => SiteSetting::valueFor('upi_id') ?? SiteSetting::valueFor('payment_upi_id', ''),
            'domesticBank' => [
                'account_name' => SiteSetting::valueFor('bank_account_name', ''),
                'account_number' => SiteSetting::valueFor('bank_account_number', ''),
                'bank_name' => SiteSetting::valueFor('bank_name', ''),
                'ifsc' => SiteSetting::valueFor('bank_ifsc', ''),
                'branch' => SiteSetting::valueFor('bank_branch', ''),
            ],
            'intlBank' => [
                'account_name' => SiteSetting::valueFor('intl_bank_account_name', ''),
                'account_number' => SiteSetting::valueFor('intl_bank_account_number', ''),
                'bank_name' => SiteSetting::valueFor('intl_bank_name', ''),
                'swift_bic' => SiteSetting::valueFor('intl_bank_swift', ''),
                'iban' => SiteSetting::valueFor('intl_bank_iban', ''),
                'routing_number' => SiteSetting::valueFor('intl_bank_routing', ''),
                'bank_address' => SiteSetting::valueFor('intl_bank_address', ''),
            ],
            'enabledMethods' => [
                'upi_qr' => (bool) SiteSetting::valueFor('method_upi_qr', true),
                'bank_transfer' => (bool) SiteSetting::valueFor('method_bank_transfer', true),
                'international_wire' => (bool) SiteSetting::valueFor('method_intl_wire', true),
            ],
        ]);
    }

    public function updateBankDetails(Request $request)
    {
        $data = $request->validate([
            'upi_id' => 'nullable|string|max:100',
            'bank_account_name' => 'nullable|string|max:150',
            'bank_account_number' => 'nullable|string|max:50',
            'bank_name' => 'nullable|string|max:150',
            'bank_ifsc' => 'nullable|string|max:20',
            'bank_branch' => 'nullable|string|max:150',
            'intl_bank_account_name' => 'nullable|string|max:150',
            'intl_bank_account_number' => 'nullable|string|max:50',
            'intl_bank_name' => 'nullable|string|max:150',
            'intl_bank_swift' => 'nullable|string|max:30',
            'intl_bank_iban' => 'nullable|string|max:50',
            'intl_bank_routing' => 'nullable|string|max:30',
            'intl_bank_address' => 'nullable|string|max:255',
        ]);

        foreach ($data as $key => $val) {
            SiteSetting::updateOrCreate(['key' => $key], ['value' => (string) $val]);
        }

        // Method Toggles
        SiteSetting::updateOrCreate(['key' => 'method_upi_qr'], ['value' => $request->has('method_upi_qr') ? '1' : '0']);
        SiteSetting::updateOrCreate(['key' => 'method_bank_transfer'], ['value' => $request->has('method_bank_transfer') ? '1' : '0']);
        SiteSetting::updateOrCreate(['key' => 'method_intl_wire'], ['value' => $request->has('method_intl_wire') ? '1' : '0']);

        return back()->with('success', 'Payment instructions & Bank details updated successfully.');
    }

    public function updateCommission(Request $request)
    {
        $data = $request->validate([
            'publisher_commission_rate' => 'required|numeric|min:0|max:100',
        ]);

        SiteSetting::updateOrCreate(['key' => 'publisher_commission_rate'], [
            'value' => number_format((float) $data['publisher_commission_rate'], 2, '.', ''),
            'meta' => ['type' => 'percentage'],
        ]);

        return back()->with('success', 'Publisher deduction rate updated. It will apply when future payments are verified.');
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
