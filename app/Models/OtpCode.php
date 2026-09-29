<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OtpCode extends Model
{
    protected $fillable = [
        'email',
        'mobile',
        'code',
        'purpose',
        'expires_at',
        'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'verified_at' => 'datetime',
        ];
    }

    /**
     * Find the most recent valid OTP for an email address.
     */
    public static function findValid(string $email, string $code, string $purpose = 'login'): ?self
    {
        return static::where('email', $email)
            ->where('code', $code)
            ->where('purpose', $purpose)
            ->whereNull('verified_at')
            ->where('expires_at', '>', now())
            ->latest()
            ->first();
    }

    /**
     * Latest unverified, unexpired OTP for an email — used by resend
     * to re-send the same code while it's still valid.
     */
    public static function latestValid(string $email, string $purpose = 'login'): ?self
    {
        return static::where('email', $email)
            ->where('purpose', $purpose)
            ->whereNull('verified_at')
            ->where('expires_at', '>', now())
            ->latest()
            ->first();
    }

    /**
     * Delete all old OTP codes for an email.
     */
    public static function purgeOld(string $email): int
    {
        return static::where('email', $email)->delete();
    }
}
