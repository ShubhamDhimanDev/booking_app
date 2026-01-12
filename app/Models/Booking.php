<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Payment;

class Booking extends Model
{
  use HasFactory;

  protected $guarded = [];

  /**
   * Format booked_at_time in hours and minutes only (H:i)
   *
   * @return \Illuminate\Database\Eloquent\Casts\Attribute
   */
  protected function bookedAtTime(): Attribute
  {
    return Attribute::make(
      get: fn ($value) => Carbon::parse($value)->format('H:i'),
    );
  }

  /**
   * The event this bookin is associated with
   *
   * @return \App\Models\Event
   */
  public function event()
  {
    return $this->belongsTo(Event::class);
  }

  /**
   * Payment associated with this booking (if any).
   *
   * @return \Illuminate\Database\Eloquent\Relations\HasOne
   */
  public function payment()
  {
    return $this->hasOne(Payment::class);
  }

  public function booker()
  {
      return $this->belongsTo(User::class, 'user_id');
  }
}
