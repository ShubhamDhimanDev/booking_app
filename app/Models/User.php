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
    'google_auth_metadata'
  ];

  /**
   * The attributes that should be hidden for serialization.
   *
   * @var array<int, string>
   */
  protected $hidden = [
    'password',
    'remember_token',
    'google_auth_metadata'
  ];

  /**
   * The attributes that should be cast.
   *
   * @var array<string, string>
   */
  protected $casts = [
    'email_verified_at' => 'datetime',
    'google_auth_metadata' => AsArrayObject::class
  ];

  /**
   * Attributes appended to the model
   *
   * @var array
   */
  protected $appends = ['avatar'];


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
    return $this->hasMany(Event::class);
  }


  /**
   * Summary of bookings
   * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
   */
  public function bookings()
  {
    return $this->hasManyThrough(Booking::class, Event::class);
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
}
