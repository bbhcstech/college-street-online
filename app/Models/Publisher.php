<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Publisher extends Model
{
    protected $fillable = ['user_id', 'business_name', 'contact_details', 'approval_status'];

    public function user() { return $this->belongsTo(User::class); }
    public function books() { return $this->hasMany(Book::class); }
}
