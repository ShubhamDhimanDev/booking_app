<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Carbon\Carbon;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Notifications\QueuedVerifyEmail;
use App\Notifications\QueuedResetPassword;
use Illuminate\Database\Eloquent\Builder;

/**
 * User Model - SaaS Ready
 *
 * @property int $id
 * @property string $name
 * @property string|null $username
 * @property string $email
 * @property string|null $phone
 * @property string $password
 * @property array|null $google_auth_metadata
 * @property \Carbon\Carbon|null $email_verified_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class User extends Authenticatable implements MustVerifyEmail
{
  use HasApiTokens, HasFactory, Notifiable, HasRoles;

  /**
   * The attributes that are mass assignable.
   *
   * @var array<int, string>
   */
  protected $fillable = [
    'name',
    'username',
    'email',
    'phone',
    'password',
    'organization_id',
  ];

  /**
   * The attributes that should be hidden for serialization.
   *
   * @var array<int, string>
   */
  protected $hidden = [
    'password',
    'remember_token',
    'google_auth_metadata',
    'two_factor_secret',
    'two_factor_recovery_codes',
  ];

  /**
   * The attributes that should be cast.
   *
   * @var array<string, string>
   */
  protected $casts = [
    'email_verified_at' => 'datetime',
    'google_auth_metadata' => AsArrayObject::class,
    'created_at' => 'datetime',
    'updated_at' => 'datetime',
  ];

  /**
   * Attributes appended to the model
   *
   * @var array
   */
  protected $appends = ['avatar'];

  /**
   * The "booted" method of the model.
   */
  protected static function booted(): void
  {
    // Auto-scope queries to current organization (except for super admins)
    static::addGlobalScope('organization', function (Builder $builder) {
      // Skip during bootstrap or if not in web context
      if (!app()->isBooted() || !auth()->hasUser()) {
        return;
      }

      try {
        $user = auth()->user();
        if ($user && $user->organization_id && !$user->is_super_admin) {
          $builder->where('organization_id', $user->organization_id);
        }
      } catch (\Exception $e) {
        // Silently skip if user cannot be loaded
      }
    });

    // Auto-assign organization_id on create
    static::creating(function ($model) {
      // Skip during bootstrap
      if (!app()->isBooted() || !auth()->hasUser()) {
        return;
      }

      try {
        if (!$model->organization_id && auth()->user()->organization_id) {
          $model->organization_id = auth()->user()->organization_id;
        }
      } catch (\Exception $e) {
        // Silently skip if user cannot be loaded
      }
    });
  }

  // ==================== RELATIONSHIPS ====================

  /**
   * The organization this user belongs to
   */
  public function organization()
  {
    return $this->belongsTo(Organization::class);
  }

  /**
   * Get gravatar
   *
   * @return string
   */
  public function getAvatarAttribute()
  {
    return 'https://www.gravatar.com/avatar/' . md5($this->email) . "?d=retro";
  }


  /**
   * User's events
   * @return \Illuminate\Database\Eloquent\Relations\HasMany
   */
  public function events()
  {
    return $this->hasMany(Event::class)->latest();
  }

  /**
   * User's direct bookings (as booker)
   * @return \Illuminate\Database\Eloquent\Relations\HasMany
   */
  public function myBookings()
  {
    return $this->hasMany(Booking::class)->latest();
  }

  /**
   * Bookings on user's events (as event owner)
   * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
   */
  public function bookings()
  {
    return $this->hasManyThrough(Booking::class, Event::class);
  }

  /**
   * User's payments
   * @return \Illuminate\Database\Eloquent\Relations\HasMany
   */
  public function payments()
  {
    return $this->hasMany(Payment::class);
  }

  /**
   * Refunds initiated by this user
   * @return \Illuminate\Database\Eloquent\Relations\HasMany
   */
  public function initiatedRefunds()
  {
    return $this->hasMany(Refund::class, 'initiated_by_user_id');
  }

  // ==================== QUERY SCOPES ====================

  /**
   * Scope: Filter verified users
   */
  public function scopeVerified(Builder $query): Builder
  {
    return $query->whereNotNull('email_verified_at');
  }

  /**
   * Scope: Filter users with Google auth
   */
  public function scopeWithGoogleAuth(Builder $query): Builder
  {
    return $query->whereNotNull('google_auth_metadata');
  }

  /**
   * Scope: Filter by role
   */
  public function scopeWithRole(Builder $query, string $role): Builder
  {
    return $query->whereHas('roles', fn($q) => $q->where('name', $role));
  }

  /**
   * Scope: Search users by name or email
   */
  public function scopeSearch(Builder $query, string $search): Builder
  {
    return $query->where(function($q) use ($search) {
      $q->where('name', 'LIKE', "%{$search}%")
        ->orWhere('email', 'LIKE', "%{$search}%")
        ->orWhere('username', 'LIKE', "%{$search}%");
    });
  }


  /**
   * Set Google Auth Metadata
   *
   * @param string|null $google_uid
   * @param string $token
   * @param string $refresh_token
   * @param int $expires_in
   * @return void
   */
  public function setGoogleAuthMetadata($google_uid = null, $token, $refresh_token, $expires_in)
  {
    $this->google_auth_metadata = array_merge(
      [
        'token' => $token,
        'refresh_token' => $refresh_token,
        'token_expiry' => Carbon::now()->addSeconds($expires_in),
      ],
      $google_uid ? ['google_uid' => $google_uid] : []
    );
    $this->save();
  }

  /**
   * Send the email verification notification (queued).
   *
   * @return void
   */
  public function sendEmailVerificationNotification()
  {
    $this->notify(new QueuedVerifyEmail);
  }

  /**
   * Send the password reset notification (queued).
   *
   * @param  string  $token
   * @return void
   */
  public function sendPasswordResetNotification($token)
  {
    $this->notify(new QueuedResetPassword($token));
  }

  /**
   * Check if user has linked Google account
   *
   * @return bool
   */
  public function hasGoogleAuth(): bool
  {
    return !empty($this->google_auth_metadata) &&
           isset($this->google_auth_metadata['token']) &&
           isset($this->google_auth_metadata['refresh_token']);
  }

  /**
   * Check if Google token is expired or will expire soon
   *
   * @param int $bufferMinutes Check if token expires within this many minutes
   * @return bool
   */
  public function isGoogleTokenExpired(int $bufferMinutes = 5): bool
  {
    if (!$this->hasGoogleAuth() || !isset($this->google_auth_metadata['token_expiry'])) {
      return true;
    }

    return Carbon::now()->addMinutes($bufferMinutes)->greaterThan(
      Carbon::parse($this->google_auth_metadata['token_expiry'])
    );
  }

  /**
   * Get Google Calendar Service instance with auto-refreshed token
   *
   * @return \App\Services\GoogleCalendarService
   */
  public function googleCalendar(): \App\Services\GoogleCalendarService
  {
    return app(\App\Services\GoogleCalendarService::class);
  }
}
