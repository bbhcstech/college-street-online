<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\Currency;
use Illuminate\Http\Request;

class CountryController extends Controller
{
    public function index()
    {
        return view('admin.countries', [
            'countries' => Country::orderBy('name')->get(),
            'currencies' => Currency::orderBy('code')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => 'required|string|size:2|unique:countries,code',
            'name' => 'required|string|max:100',
            'currency_code' => 'required|string|exists:currencies,code',
            'symbol' => 'nullable|string|max:10',
            'markup_percentage' => 'nullable|numeric|min:0|max:500',
            'base_shipping_fee' => 'required|numeric|min:0',
            'per_item_shipping_fee' => 'required|numeric|min:0',
            'free_shipping_threshold' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        $data['code'] = strtoupper($data['code']);
        $data['per_kg_shipping_fee'] = $data['per_item_shipping_fee'];
        $data['is_active'] = $request->has('is_active');

        Country::create($data);

        return back()->with('success', "Country {$data['name']} added successfully.");
    }

    public function update(Request $request, Country $country)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'currency_code' => 'required|string|exists:currencies,code',
            'symbol' => 'nullable|string|max:10',
            'markup_percentage' => 'nullable|numeric|min:0|max:500',
            'base_shipping_fee' => 'required|numeric|min:0',
            'per_item_shipping_fee' => 'required|numeric|min:0',
            'free_shipping_threshold' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        $data['per_kg_shipping_fee'] = $data['per_item_shipping_fee'];
        $data['is_active'] = $request->has('is_active');

        $country->update($data);

        return back()->with('success', "Country {$country->name} shipping & pricing updated successfully.");
    }

    public function destroy(Country $country)
    {
        if ($country->code === 'IN') {
            return back()->with('error', 'Base country India (IN) cannot be deleted.');
        }

        $country->delete();

        return back()->with('success', "Country {$country->name} removed successfully.");
    }
}
