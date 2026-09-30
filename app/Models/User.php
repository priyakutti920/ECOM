<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    use \App\Traits\HasCustomAsset;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'mobile',
        'is_admin',
        'avatar',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'is_admin' => 'boolean',
        ];
    }

    /**
     * Find a user by their mobile number.
     */
    public static function findByMobile(string $mobile): ?self
    {
        return static::where('mobile', $mobile)->first();
    }

    /**
     * Scope: active (non-admin customers).
     */
    public function scopeCustomers($query)
    {
        return $query->where('is_admin', false);
    }

    /**
     * Saved delivery addresses for this customer.
     */
    public function addresses()
    {
        return $this->hasMany(CustomerAddress::class, 'customer_id');
    }

    public function supportTickets()
    {
        return $this->hasMany(SupportTicket::class, 'customer_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'customer_id');
    }

    public function wishlists()
    {
        return $this->hasMany(Wishlist::class, 'customer_id');
    }

    public function wishlistProducts()
    {
        return $this->belongsToMany(Product::class, 'wishlists', 'customer_id', 'product_id')->withTimestamps();
    }

    /**
     * Get user avatar URL or fall back to an initials avatar.
     */
    public function getAvatarUrlAttribute(): string
    {
        if (!empty($this->avatar)) {
            $url = self::resolveMediaUrl($this->avatar);
            if ($url) return $url;
        }

        $name = trim($this->name ?: 'User');
        return 'https://ui-avatars.com/api/?name=' . urlencode($name) . '&background=0068e1&color=ffffff&size=128&bold=true';
    }

    /**
     * Get 1-2 letters monogram initials.
     */
    public function getInitialsAttribute(): string
    {
        $name = trim($this->name ?: 'U');
        $words = preg_split('/\s+/', $name);
        if (count($words) >= 2) {
            return strtoupper(mb_substr($words[0], 0, 1) . mb_substr($words[1], 0, 1));
        }
        return strtoupper(mb_substr($name, 0, 2));
    }
}

