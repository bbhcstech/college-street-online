<?php
namespace App\Http\Controllers\Publisher;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\CouponRequest;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function index()
    {
        $publisher = auth()->user()->publisher;
        return view('publisher.coupons', [
            'requests' => CouponRequest::where('publisher_id',$publisher->id)->with('coupon')->latest()->paginate(10),
            'activeCoupons' => Coupon::where('publisher_id',$publisher->id)->where('is_active',true)->count(),
            'totalUses' => Coupon::where('publisher_id',$publisher->id)->sum('times_used'),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code'=>'required|string|max:40|unique:coupons,code|unique:coupon_requests,code',
            'discount_type'=>'required|in:percentage,fixed',
            'discount_value'=>'required|numeric|min:0.01',
            'min_order_value'=>'nullable|numeric|min:0',
            'valid_from'=>'nullable|date',
            'valid_to'=>'nullable|date|after_or_equal:valid_from',
            'usage_limit'=>'nullable|integer|min:1',
            'publisher_notes'=>'nullable|string|max:1000',
        ]);
        if ($data['discount_type']==='percentage' && $data['discount_value']>100) {
            return back()->withInput()->withErrors(['discount_value'=>'Percentage discount cannot exceed 100%.']);
        }
        $data['code']=strtoupper(trim($data['code']));
        $data['publisher_id']=auth()->user()->publisher->id;
        CouponRequest::create($data);
        return back()->with('success','Coupon request submitted for admin review.');
    }
}
