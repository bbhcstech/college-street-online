<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use Illuminate\Http\Request;

class CurrencyController extends Controller
{
    public function index()
    {
        return view('admin.currencies', [
            'currencies' => Currency::orderBy('code')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => 'required|string|size:3|unique:currencies,code',
            'name' => 'required|string|max:50',
            'symbol' => 'required|string|max:10',
            'exchange_rate' => 'required|numeric|min:0.000001',
            'is_active' => 'boolean',
        ]);

        Currency::create([
            'code' => strtoupper($data['code']),
            'name' => $data['name'],
            'symbol' => $data['symbol'],
            'exchange_rate_to_inr' => $data['exchange_rate'],
            'is_active' => $request->has('is_active'),
        ]);

        return back()->with('success', "Currency {$data['code']} created successfully.");
    }

    public function update(Request $request, Currency $currency)
    {
        $data = $request->validate([
            'name' => 'required|string|max:50',
            'symbol' => 'required|string|max:10',
            'exchange_rate' => 'required|numeric|min:0.000001',
            'is_active' => 'boolean',
        ]);

        $currency->update([
            'name' => $data['name'],
            'symbol' => $data['symbol'],
            'exchange_rate_to_inr' => $data['exchange_rate'],
            'is_active' => $request->has('is_active'),
        ]);

        return back()->with('success', "Currency {$currency->code} updated successfully.");
    }

    public function destroy(Currency $currency)
    {
        if ($currency->code === 'INR') {
            return back()->with('error', 'Base system currency INR cannot be deleted.');
        }

        $currency->delete();

        return back()->with('success', "Currency {$currency->code} removed successfully.");
    }
}
