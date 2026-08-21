<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function index() { return view('admin.coupons', ['coupons' => Coupon::latest()->get()]); }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => 'required|string|max:40|unique:coupons,code',
            'discount_type' => 'required|in:percentage,fixed',
            'discount_value' => 'required|numeric|min:0',
            'min_order_value' => 'nullable|numeric|min:0',
            'valid_from' => 'nullable|date',
            'valid_to' => 'nullable|date|after_or_equal:valid_from',
            'usage_limit' => 'nullable|integer|min:1',
        ]);
        Coupon::create($data + ['is_active' => true]);
        return back()->with('success', 'Coupon created.');
    }

    public function destroy(Coupon $coupon)
    {
        $coupon->delete();
        return back()->with('success', 'Coupon removed.');
    }
}
