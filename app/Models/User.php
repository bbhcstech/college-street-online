<?php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = ['name', 'email', 'profile_image_path', 'password', 'role', 'status'];
    protected $hidden = ['password', 'remember_token'];
    protected $casts = ['email_verified_at' => 'datetime', 'password' => 'hashed'];

    public function publisher() { return $this->hasOne(Publisher::class); }
    public function orders() { return $this->hasMany(Order::class, 'customer_id'); }
    public function cart() { return $this->hasMany(Cart::class, 'customer_id'); }

    public function isAdmin(): bool { return $this->role === 'admin'; }
    public function isPublisher(): bool { return $this->role === 'publisher'; }
    public function isCustomer(): bool { return $this->role === 'customer'; }
    public function getProfileImageUrlAttribute(): ?string
    {
        return $this->profile_image_path ? asset('storage/' . $this->profile_image_path) : null;
    }
}
