<?php

namespace App\Support;

use App\Models\TrustedDevice;
use App\Models\User;
use App\Notifications\OtpNotification;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TwoFactor
{
    public const COOKIE = 'trusted_device';

    private const CODE_TTL_MINUTES = 10;

    private const DEVICE_TRUST_DAYS = 30;

    /**
     * Roles that must pass an OTP challenge on login.
     * Anyone not matching (e.g. pengemudi) logs in normally.
     */
    public static function required(User $user): bool
    {
        return ! $user->hasRole('pengemudi');
    }

    public static function codeTtlMinutes(): int
    {
        return self::CODE_TTL_MINUTES;
    }

    /**
     * Generate a fresh 6-digit code, cache its hash and email it to the user.
     */
    public static function sendCode(User $user): void
    {
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        Cache::put(
            self::cacheKey($user),
            Hash::make($code),
            now()->addMinutes(self::CODE_TTL_MINUTES),
        );

        $user->notify(new OtpNotification($code, self::CODE_TTL_MINUTES));
    }

    /**
     * Check a submitted code against the cached hash. Consumes it on success.
     */
    public static function verifyCode(User $user, string $code): bool
    {
        $hash = Cache::get(self::cacheKey($user));

        if (! $hash || ! Hash::check($code, $hash)) {
            return false;
        }

        Cache::forget(self::cacheKey($user));

        return true;
    }

    public static function deviceTrusted(User $user): bool
    {
        $token = request()->cookie(self::COOKIE);

        if (! is_string($token) || $token === '') {
            return false;
        }

        $device = TrustedDevice::query()
            ->where('user_id', $user->id)
            ->where('token', hash('sha256', $token))
            ->where('expires_at', '>', now())
            ->first();

        if (! $device) {
            return false;
        }

        $device->forceFill(['last_used_at' => now()])->save();

        return true;
    }

    /**
     * Remember the current browser so this user skips OTP for 30 days.
     */
    public static function trustDevice(User $user): void
    {
        $token = Str::random(64);

        TrustedDevice::create([
            'user_id' => $user->id,
            'token' => hash('sha256', $token),
            'user_agent' => Str::limit((string) request()->userAgent(), 255, ''),
            'last_used_at' => now(),
            'expires_at' => now()->addDays(self::DEVICE_TRUST_DAYS),
        ]);

        Cookie::queue(self::COOKIE, $token, 60 * 24 * self::DEVICE_TRUST_DAYS);
    }

    private static function cacheKey(User $user): string
    {
        return "login-otp:{$user->id}";
    }
}
