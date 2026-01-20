<?php

// @formatter:off
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * App\Models\Booking
 *
 * @property int $id
 * @property int|null $user_id
 * @property string $booker_name
 * @property string $booker_email
 * @property string|null $phone
 * @property string|null $calendar_id
 * @property string|null $calendar_link
 * @property string|null $meet_link
 * @property string $status
 * @property string $booked_at_date
 * @property string $booked_at_time
 * @property int $event_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\User|null $booker
 * @property-read \App\Models\Event $event
 * @property-read \App\Models\Payment|null $payment
 * @method static \Database\Factories\BookingFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|Booking newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Booking newQuery()
 * @method static \Illuminate\Database\Query\Builder|Booking onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Booking query()
 * @method static \Illuminate\Database\Eloquent\Builder|Booking whereBookedAtDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Booking whereBookedAtTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Booking whereBookerEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Booking whereBookerName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Booking whereCalendarId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Booking whereCalendarLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Booking whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Booking whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Booking whereEventId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Booking whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Booking whereMeetLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Booking wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Booking whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Booking whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Booking whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|Booking withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Booking withoutTrashed()
 */
	class Booking extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\BookingReminderLog
 *
 * @property int $id
 * @property int $booking_id
 * @property string $reminder_key
 * @property \Illuminate\Support\Carbon|null $sent_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Booking $booking
 * @method static \Illuminate\Database\Eloquent\Builder|BookingReminderLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BookingReminderLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BookingReminderLog query()
 * @method static \Illuminate\Database\Eloquent\Builder|BookingReminderLog whereBookingId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BookingReminderLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BookingReminderLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BookingReminderLog whereReminderKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BookingReminderLog whereSentAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BookingReminderLog whereUpdatedAt($value)
 */
	class BookingReminderLog extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Event
 *
 * @property int $id
 * @property string $title
 * @property string|null $description
 * @property string $slug
 * @property string|null $color
 * @property string $price
 * @property int $duration
 * @property string $available_from_date
 * @property string $available_to_date
 * @property array|null $available_week_days
 * @property array|null $custom_timeslots
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $user_id
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Booking[] $bookings
 * @property-read int|null $bookings_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\EventExclusion[] $exclusions
 * @property-read int|null $exclusions_count
 * @property-read array $timeslots
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\EventReminder[] $reminders
 * @property-read int|null $reminders_count
 * @property-read \App\Models\User $user
 * @method static \Database\Factories\EventFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|Event newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Event newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Event query()
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereAvailableFromDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereAvailableToDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereAvailableWeekDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereCustomTimeslots($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereDuration($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereUserId($value)
 */
	class Event extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\EventExclusion
 *
 * @property int $id
 * @property int $event_id
 * @property \Illuminate\Support\Carbon $date
 * @property bool $exclude_all
 * @property array|null $times
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Event $event
 * @method static \Illuminate\Database\Eloquent\Builder|EventExclusion newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EventExclusion newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EventExclusion query()
 * @method static \Illuminate\Database\Eloquent\Builder|EventExclusion whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EventExclusion whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EventExclusion whereEventId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EventExclusion whereExcludeAll($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EventExclusion whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EventExclusion whereTimes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EventExclusion whereUpdatedAt($value)
 */
	class EventExclusion extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\EventReminder
 *
 * @property int $id
 * @property int $event_id
 * @property int $offset_minutes
 * @property string|null $name
 * @property bool $enabled
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Event $event
 * @method static \Illuminate\Database\Eloquent\Builder|EventReminder newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EventReminder newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EventReminder query()
 * @method static \Illuminate\Database\Eloquent\Builder|EventReminder whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EventReminder whereEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EventReminder whereEventId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EventReminder whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EventReminder whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EventReminder whereOffsetMinutes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EventReminder whereUpdatedAt($value)
 */
	class EventReminder extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Payment
 *
 * @property int $id
 * @property int|null $user_id
 * @property int|null $booking_id
 * @property string $provider
 * @property string|null $transaction_id
 * @property int $amount
 * @property string $currency
 * @property string $status
 * @property string|null $promo_code
 * @property array|null $metadata
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Booking|null $booking
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|Payment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Payment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Payment query()
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereBookingId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereMetadata($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment wherePromoCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereProvider($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereTransactionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereUserId($value)
 */
	class Payment extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\PromoCode
 *
 * @property int $id
 * @property string $code
 * @property string|null $description
 * @property string $discount_type
 * @property string $discount_value
 * @property string|null $min_booking_amount
 * @property string|null $max_discount_amount
 * @property int|null $usage_limit
 * @property int $usage_count
 * @property \Illuminate\Support\Carbon|null $valid_from
 * @property \Illuminate\Support\Carbon|null $valid_until
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|PromoCode active()
 * @method static \Illuminate\Database\Eloquent\Builder|PromoCode newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PromoCode newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PromoCode query()
 * @method static \Illuminate\Database\Eloquent\Builder|PromoCode validNow()
 * @method static \Illuminate\Database\Eloquent\Builder|PromoCode whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PromoCode whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PromoCode whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PromoCode whereDiscountType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PromoCode whereDiscountValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PromoCode whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PromoCode whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PromoCode whereMaxDiscountAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PromoCode whereMinBookingAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PromoCode whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PromoCode whereUsageCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PromoCode whereUsageLimit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PromoCode whereValidFrom($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PromoCode whereValidUntil($value)
 */
	class PromoCode extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Setting
 *
 * @property int $id
 * @property string $key
 * @property string $value
 * @property int $is_encrypted
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $decrypted_value
 * @method static \Illuminate\Database\Eloquent\Builder|Setting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Setting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Setting query()
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereIsEncrypted($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereValue($value)
 */
	class Setting extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\SystemSetting
 *
 * @property int $id
 * @property int|null $user_id
 * @property bool $dark_mode
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|SystemSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SystemSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SystemSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder|SystemSetting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemSetting whereDarkMode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemSetting whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemSetting whereUserId($value)
 */
	class SystemSetting extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\User
 *
 * @property int $id
 * @property string $name
 * @property string|null $username
 * @property string $email
 * @property string|null $phone
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property mixed|null $google_auth_metadata
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Booking[] $bookings
 * @property-read int|null $bookings_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Event[] $events
 * @property-read int|null $events_count
 * @property-read string $avatar
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\Spatie\Permission\Models\Permission[] $permissions
 * @property-read int|null $permissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\Spatie\Permission\Models\Role[] $roles
 * @property-read int|null $roles_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\Laravel\Sanctum\PersonalAccessToken[] $tokens
 * @property-read int|null $tokens_count
 * @method static \Database\Factories\UserFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User permission($permissions, $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder|User query()
 * @method static \Illuminate\Database\Eloquent\Builder|User role($roles, $guard = null, $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereGoogleAuthMetadata($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereUsername($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User withoutPermission($permissions)
 * @method static \Illuminate\Database\Eloquent\Builder|User withoutRole($roles, $guard = null)
 */
	class User extends \Eloquent implements \Illuminate\Contracts\Auth\MustVerifyEmail {}
}

