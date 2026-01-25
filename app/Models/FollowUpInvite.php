<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class FollowUpInvite extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'expires_at' => 'datetime',
        'sent_at' => 'datetime',
        'custom_price' => 'decimal:2',
    ];

    /**
     * Generate a unique token for the invite
     */
    public static function generateUniqueToken(): string
    {
        do {
            $token = Str::random(64);
        } while (self::where('token', $token)->exists());

        return $token;
    }

    /**
     * Check if the invite is expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Check if the invite is still valid
     */
    public function isValid(): bool
    {
        return $this->status === 'pending' && !$this->isExpired();
    }

    /**
     * The original booking this follow-up is for
     */
    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * The event for the follow-up session
     */
    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * The user (booker) who will receive the follow-up invite
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The follow-up booking created from this invite (if accepted)
     */
    public function followUpBooking()
    {
        return $this->hasOne(Booking::class, 'followup_invite_id');
    }
}
