<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Publisher;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function index(Request $request)
    {
        $coupons = Coupon::with('publisher')
            ->when($request->filled('q'), fn ($query) => $query->where('code', 'like', '%'.trim($request->q).'%'))
            ->when($request->filled('publisher_id'), fn ($query) => $query->where('publisher_id', $request->publisher_id))
            ->when($request->status === 'active', fn ($query) => $query->where('is_active', true))
            ->when($request->status === 'inactive', fn ($query) => $query->where('is_active', false))
            ->latest()->paginate(10)->withQueryString();

        return view('admin.coupons', [
            'coupons' => $coupons,
            'publishers' => Publisher::where('approval_status', 'approved')->orderBy('business_name')->get(),
            'activeCount' => Coupon::where('is_active', true)->count(),
            'totalUses' => Coupon::sum('times_used'),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'publisher_id' => 'nullable|exists:publishers,id',
            'code' => 'required|string|max:40|unique:coupons,code',
            'discount_type' => 'required|in:percentage,fixed',
            'discount_value' => 'required|numeric|min:0.01',
            'min_order_value' => 'nullable|numeric|min:0',
            'valid_from' => 'nullable|date',
            'valid_to' => 'nullable|date|after_or_equal:valid_from',
            'usage_limit' => 'nullable|integer|min:1',
        ]);

        if ($data['discount_type'] === 'percentage' && $data['discount_value'] > 100) {
            return back()->withInput()->withErrors(['discount_value' => 'Percentage discount cannot exceed 100%.']);
        }

        $data['code'] = strtoupper(trim($data['code']));
        Coupon::create($data + ['is_active' => true]);
        return back()->with('success', 'Coupon created successfully.');
    }

    public function updateStatus(Request $request, Coupon $coupon)
    {
        $data = $request->validate(['is_active' => 'required|boolean']);
        $coupon->update($data);
        return back()->with('success', $coupon->is_active ? 'Coupon activated.' : 'Coupon deactivated.');
    }

    public function destroy(Coupon $coupon)
    {
        $coupon->delete();
        return back()->with('success', 'Coupon removed.');
    }
}
