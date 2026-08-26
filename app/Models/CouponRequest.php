<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CouponRequest extends Model
{
    protected $fillable = ['publisher_id','coupon_id','code','discount_type','discount_value','min_order_value','valid_from','valid_to','usage_limit','publisher_notes','admin_notes','status','reviewed_by','reviewed_at'];
    protected $casts = ['valid_from'=>'date','valid_to'=>'date','reviewed_at'=>'datetime','discount_value'=>'decimal:2','min_order_value'=>'decimal:2'];
    public function publisher(){ return $this->belongsTo(Publisher::class); }
    public function coupon(){ return $this->belongsTo(Coupon::class); }
    public function reviewer(){ return $this->belongsTo(User::class,'reviewed_by'); }
}
