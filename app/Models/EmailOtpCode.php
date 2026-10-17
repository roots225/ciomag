<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailOtpCode extends Model
{
    protected $fillable = [
        'email',
        'code',
        'user_id',
        'purpose',
        'expires_at',
        'verified_at',
        'ip_address',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'verified_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isVerified(): bool
    {
        return $this->verified_at !== null;
    }

    /**
     * Generate a new 6-digit OTP code for an email.
     */
    public static function generateFor(
        string $email,
        ?string $userId = null,
        string $purpose = 'subscription_verification',
        ?string $ipAddress = null,
        int $expiresInMinutes = 15
    ): self {
        $normalizedEmail = strtolower(trim($email));

        // Invalidate previous unverified codes for this email and purpose
        static::query()
            ->where('email', $normalizedEmail)
            ->where('purpose', $purpose)
            ->whereNull('verified_at')
            ->delete();

        $code = sprintf('%06d', random_int(100000, 999999));

        return static::create([
            'email' => $normalizedEmail,
            'code' => $code,
            'user_id' => $userId,
            'purpose' => $purpose,
            'expires_at' => Carbon::now()->addMinutes($expiresInMinutes),
            'ip_address' => $ipAddress,
        ]);
    }

    /**
     * Verify a 6-digit OTP code.
     */
    public static function verifyCode(
        string $email,
        string $code,
        string $purpose = 'subscription_verification'
    ): ?self {
        $normalizedEmail = strtolower(trim($email));
        $normalizedCode = trim($code);

        $record = static::query()
            ->where('email', $normalizedEmail)
            ->where('code', $normalizedCode)
            ->where('purpose', $purpose)
            ->whereNull('verified_at')
            ->where('expires_at', '>', Carbon::now())
            ->latest()
            ->first();

        if (! $record) {
            return null;
        }

        $record->update([
            'verified_at' => Carbon::now(),
        ]);

        return $record;
    }
}
