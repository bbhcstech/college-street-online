<?php

namespace App\Http\Controllers;

use App\Models\Country;
use Illuminate\Http\Request;

class CountryCurrencyController extends Controller
{
    public function switchCountry(Request $request)
    {
        $data = $request->validate([
            'country' => 'required|string|exists:countries,code',
        ]);

        $country = Country::where('code', $data['country'])->where('is_active', true)->firstOrFail();

        session()->put('customer_country', $country->code);
        session()->put('customer_currency', $country->currency_code);

        return back()->with('success', "Switched region to {$country->name} ({$country->currency_code}).");
    }
}

