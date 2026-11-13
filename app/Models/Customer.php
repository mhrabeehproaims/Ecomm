<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;


class Customer extends Authenticatable
{
    use HasFactory, Cachable, HasApiTokens, Notifiable;
    protected $primaryKey = 'customer_id';
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'address',
        'city',
        'state',
        'country',
        'zip',
        'password',
        'status',
        'profile_pic',
        'last_seen_at'
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function carts()
    {
        return $this->hasMany(Cart::class, 'user_id', 'customer_id');
    }
    public function wishlist()
    {
        return $this->hasMany(Wishlist::class, 'user_id', 'customer_id');
    }

    public function deliveryAddress()
    {
        return $this->hasMany(DeliveryAddress::class, 'customer_id', 'customer_id');
    }
    public function defaultDeliveryAddress()
    {
        return $this->hasOne(DeliveryAddress::class, 'customer_id', 'customer_id')
            ->where('is_default', 1);
    }

    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }
}
