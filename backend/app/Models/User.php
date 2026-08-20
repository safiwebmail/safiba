<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'phone', 'password', 'role', 'shop_id',
        'avatar', 'address', 'is_active', 'email_verified_at',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public const ROLES = ['super_admin', 'admin', 'shop_manager', 'tailor', 'customer'];

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function assignedOrders()
    {
        return $this->hasMany(Order::class, 'tailor_id');
    }

    public function measurementProfiles()
    {
        return $this->hasMany(MeasurementProfile::class);
    }

    public function wishlist()
    {
        return $this->belongsToMany(Product::class, 'wishlists')->withTimestamps();
    }

    public function employee()
    {
        return $this->hasOne(Employee::class);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, ['super_admin', 'admin']);
    }

    public function isShopManager(): bool
    {
        return in_array($this->role, ['super_admin', 'admin', 'shop_manager']);
    }

    public function isTailor(): bool
    {
        return $this->role === 'tailor';
    }

    public function isCustomer(): bool
    {
        return $this->role === 'customer';
    }

    public function canAccessShop(?int $shopId): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        return $this->shop_id === $shopId;
    }

    public function accessibleShopIds(): array
    {
        if ($this->isAdmin()) {
            return Shop::pluck('id')->all();
        }

        return $this->shop_id ? [$this->shop_id] : [];
    }
}