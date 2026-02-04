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
 * Booking Model - SaaS Ready
 * 
 * Recommended Database Indexes:
 * - Index: (event_id, booked_at_date) - for event bookings list
 * - Index: (user_id, status) - for user bookings with status filter
 * - Index: (status, booked_at_date) - for dashboard queries
 * - Index: (booker_email, status) - for guest bookings lookup
 *
 * @property int $id
 * @property int $event_id
 * @property int|null $user_id
 * @property bool $is_followup
 * @property int|null $followup_invite_id
 * @property string $booker_email
 * @property string $booker_name
 * @property string|null $phone
 * @property string $status
 * @property \Carbon\Carbon $booked_at_date
 * @property string $booked_at_time
 * @property string|null $calendar_id
 * @property string|null $calendar_link
 * @property string|null $meet_link
 * @property \Carbon\Carbon|null $cancelled_at
 * @property int|null $cancelled_by
 * @property string|null $cancellation_reason
 * @property string $refund_status
 * @property float $refund_amount
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 * 
 * Virtual/Accessor Properties (for backward compatibility):
 * @property string $email Alias for booker_email
 * @property string $name Alias for booker_name
 * @property \Carbon\Carbon $scheduled_at Combines booked_at_date and booked_at_time
 * @property-read \App\Models\User|null $booker
 * @property-read \App\Models\User|null $cancelledBy
 * @property-read \App\Models\Event $event
 * @property-read \App\Models\FollowUpInvite|null $followUpInvite
 * @property-read \App\Models\Payment|null $payment
 * @property-read \App\Models\Refund|null $refund
 * @property-read \App\Models\BookingTracking|null $tracking
 * @method static \Illuminate\Database\Eloquent\Builder|Booking betweenDates($startDate, $endDate)
 * @method static \Illuminate\Database\Eloquent\Builder|Booking cancelled()
 * @method static \Illuminate\Database\Eloquent\Builder|Booking confirmed()
 * @method static \Database\Factories\BookingFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|Booking forEvent(int $eventId)
 * @method static \Illuminate\Database\Eloquent\Builder|Booking forUser(int $userId)
 * @method static \Illuminate\Database\Eloquent\Builder|Booking newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Booking newQuery()
 * @method static \Illuminate\Database\Query\Builder|Booking onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Booking past()
 * @method static \Illuminate\Database\Eloquent\Builder|Booking pending()
 * @method static \Illuminate\Database\Eloquent\Builder|Booking query()
 * @method static \Illuminate\Database\Eloquent\Builder|Booking refundable()
 * @method static \Illuminate\Database\Eloquent\Builder|Booking search(string $search)
 * @method static \Illuminate\Database\Eloquent\Builder|Booking upcoming()
 * @method static \Illuminate\Database\Eloquent\Builder|Booking whereBookedAtDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Booking whereBookedAtTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Booking whereBookerEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Booking whereBookerName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Booking whereCalendarId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Booking whereCalendarLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Booking whereCancellationReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Booking whereCancelledAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Booking whereCancelledBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Booking whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Booking whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Booking whereEventId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Booking whereFollowupInviteId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Booking whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Booking whereIsFollowup($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Booking whereMeetLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Booking wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Booking whereRefundAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Booking whereRefundStatus($value)
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
 * App\Models\BookingTracking
 *
 * @property int $id
 * @property int $booking_id
 * @property string|null $utm_source
 * @property string|null $utm_medium
 * @property string|null $utm_campaign
 * @property string|null $utm_content
 * @property string|null $utm_term
 * @property string|null $fbclid
 * @property string|null $gclid
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Booking $booking
 * @method static \Illuminate\Database\Eloquent\Builder|BookingTracking newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BookingTracking newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BookingTracking query()
 * @method static \Illuminate\Database\Eloquent\Builder|BookingTracking whereBookingId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BookingTracking whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BookingTracking whereFbclid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BookingTracking whereGclid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BookingTracking whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BookingTracking whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BookingTracking whereUtmCampaign($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BookingTracking whereUtmContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BookingTracking whereUtmMedium($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BookingTracking whereUtmSource($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BookingTracking whereUtmTerm($value)
 */
	class BookingTracking extends \Eloquent {}
}

namespace App\Models{
/**
 * Event Model - SaaS Ready
 * 
 * Recommended Database Indexes:
 * - Index: (user_id)
 * - Index: (slug) UNIQUE
 * - Index: (created_at)
 * 
 * NOTE: This model contains heavy business logic that should be extracted to services:
 * - calculateRefundAmount() → RefundService
 * - canBeCancelled() → BookingService
 * - Timeslot generation → EventService with caching
 *
 * @property int $id
 * @property int $user_id
 * @property string $title
 * @property string $slug
 * @property string|null $description
 * @property int $duration Duration in minutes
 * @property float|null $price
 * @property \Carbon\Carbon $available_from_date
 * @property \Carbon\Carbon $available_to_date
 * @property array|null $available_week_days
 * @property array|null $custom_timeslots
 * @property array|null $refund_rules
 * @property bool $refund_enabled
 * @property string $refund_policy_type
 * @property int $min_cancellation_hours
 * @property bool $deduct_gateway_charges
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 * 
 * Virtual/Accessor Properties (for backward compatibility):
 * @property string $name Alias for title
 * @property int $duration_minutes Alias for duration
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Booking[] $bookings
 * @property-read int|null $bookings_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\EventExclusion[] $exclusions
 * @property-read int|null $exclusions_count
 * @property-read array $timeslots
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\EventReminder[] $reminders
 * @property-read int|null $reminders_count
 * @property-read \App\Models\User $user
 * @method static \Database\Factories\EventFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|Event forUser(int $userId)
 * @method static \Illuminate\Database\Eloquent\Builder|Event free()
 * @method static \Illuminate\Database\Eloquent\Builder|Event newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Event newQuery()
 * @method static \Illuminate\Database\Query\Builder|Event onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Event paid()
 * @method static \Illuminate\Database\Eloquent\Builder|Event query()
 * @method static \Illuminate\Database\Eloquent\Builder|Event search(string $search)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereAvailableFromDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereAvailableToDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereAvailableWeekDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereCustomTimeslots($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereDeductGatewayCharges($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereDuration($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereMinCancellationHours($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereRefundEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereRefundPolicyType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereRefundRules($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|Event withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Event withoutTrashed()
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
 * EventReminder Model - SaaS Ready
 * 
 * Recommended Database Indexes:
 * - Index: (event_id, enabled)
 * - Index: (offset_minutes)
 *
 * @property int $id
 * @property int $event_id
 * @property int $offset_minutes Minutes before event to send reminder
 * @property string|null $name Human-friendly label for the reminder
 * @property bool $enabled
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read \App\Models\Event $event
 * @method static \Illuminate\Database\Eloquent\Builder|EventReminder byTime()
 * @method static \Illuminate\Database\Eloquent\Builder|EventReminder enabled()
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
 * FollowUpInvite Model - SaaS Ready
 * 
 * Recommended Database Indexes:
 * - UNIQUE Index: (token)
 * - Index: (booking_id)
 * - Index: (event_id)
 * - Index: (status, created_at)
 *
 * @property int $id
 * @property int $booking_id
 * @property int $user_id
 * @property float $custom_price
 * @property bool $is_normal_invite
 * @property int $event_id
 * @property string $token
 * @property string $status
 * @property \Carbon\Carbon|null $expires_at
 * @property \Carbon\Carbon|null $sent_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read \App\Models\Booking $booking
 * @property-read \App\Models\Event $event
 * @property-read \App\Models\User $inviter
 * @method static \Illuminate\Database\Eloquent\Builder|FollowUpInvite accepted()
 * @method static \Illuminate\Database\Eloquent\Builder|FollowUpInvite active()
 * @method static \Illuminate\Database\Eloquent\Builder|FollowUpInvite expired()
 * @method static \Illuminate\Database\Eloquent\Builder|FollowUpInvite newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FollowUpInvite newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FollowUpInvite pending()
 * @method static \Illuminate\Database\Eloquent\Builder|FollowUpInvite query()
 * @method static \Illuminate\Database\Eloquent\Builder|FollowUpInvite whereBookingId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FollowUpInvite whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FollowUpInvite whereCustomPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FollowUpInvite whereEventId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FollowUpInvite whereExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FollowUpInvite whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FollowUpInvite whereIsNormalInvite($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FollowUpInvite whereSentAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FollowUpInvite whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FollowUpInvite whereToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FollowUpInvite whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FollowUpInvite whereUserId($value)
 */
	class FollowUpInvite extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\HelpRequest
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $subject
 * @property string $message
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|HelpRequest newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|HelpRequest newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|HelpRequest query()
 * @method static \Illuminate\Database\Eloquent\Builder|HelpRequest whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HelpRequest whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HelpRequest whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HelpRequest whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HelpRequest whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HelpRequest whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HelpRequest whereSubject($value)
 * @method static \Illuminate\Database\Eloquent\Builder|HelpRequest whereUpdatedAt($value)
 */
	class HelpRequest extends \Eloquent {}
}

namespace App\Models{
/**
 * Payment Model - SaaS Ready
 * 
 * Recommended Database Indexes:
 * - Index: (transaction_id)
 * - Index: (booking_id)
 * - Index: (user_id, status)
 * - Index: (status, created_at)
 *
 * @property int $id
 * @property int $booking_id
 * @property int|null $user_id
 * @property string $transaction_id
 * @property int $amount Amount in smallest currency unit (paise for INR)
 * @property string $currency
 * @property string $status
 * @property string $provider Payment provider (razorpay, etc)
 * @property string|null $promo_code
 * @property array|null $metadata
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read \App\Models\Booking|null $booking
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Refund[] $refunds
 * @property-read int|null $refunds_count
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|Payment betweenDates($startDate, $endDate)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment byGateway(string $gateway)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment failed()
 * @method static \Illuminate\Database\Eloquent\Builder|Payment forBooking(int $bookingId)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment forUser(int $userId)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Payment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Payment pending()
 * @method static \Illuminate\Database\Eloquent\Builder|Payment query()
 * @method static \Illuminate\Database\Eloquent\Builder|Payment refunded()
 * @method static \Illuminate\Database\Eloquent\Builder|Payment successful()
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
 * PromoCode Model - SaaS Ready
 * 
 * Recommended Database Indexes:
 * - UNIQUE Index: (code)
 * - Index: (is_active, valid_until)
 * - Index: (valid_from, valid_until)
 *
 * @property int $id
 * @property string $code
 * @property string|null $description
 * @property string $discount_type
 * @property float $discount_value
 * @property float|null $min_booking_amount
 * @property float|null $max_discount_amount
 * @property int|null $usage_limit
 * @property int $usage_count
 * @property \Carbon\Carbon|null $valid_from
 * @property \Carbon\Carbon|null $valid_until
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|PromoCode active()
 * @method static \Illuminate\Database\Eloquent\Builder|PromoCode available()
 * @method static \Illuminate\Database\Eloquent\Builder|PromoCode byCode(string $code)
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
 * Refund Model - SaaS Ready
 * 
 * Recommended Database Indexes:
 * - Index: (booking_id)
 * - Index: (payment_id)
 * - Index: (status, created_at)
 * - Index: (initiated_by_user_id)
 *
 * @property int $id
 * @property int $booking_id
 * @property int $payment_id
 * @property float $amount
 * @property float $gateway_charges
 * @property float $net_refund_amount
 * @property string $status
 * @property string $gateway
 * @property string|null $gateway_refund_id
 * @property string $initiated_by
 * @property int|null $initiated_by_user_id
 * @property string|null $failure_reason
 * @property array|null $gateway_response
 * @property \Carbon\Carbon|null $processed_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Booking $booking
 * @property-read \App\Models\User|null $initiatedBy
 * @property-read \App\Models\Payment $payment
 * @method static \Illuminate\Database\Eloquent\Builder|Refund betweenDates($startDate, $endDate)
 * @method static \Illuminate\Database\Eloquent\Builder|Refund completed()
 * @method static \Illuminate\Database\Eloquent\Builder|Refund failed()
 * @method static \Illuminate\Database\Eloquent\Builder|Refund forBooking(int $bookingId)
 * @method static \Illuminate\Database\Eloquent\Builder|Refund forUser(int $userId)
 * @method static \Illuminate\Database\Eloquent\Builder|Refund newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Refund newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Refund pending()
 * @method static \Illuminate\Database\Eloquent\Builder|Refund processing()
 * @method static \Illuminate\Database\Eloquent\Builder|Refund query()
 * @method static \Illuminate\Database\Eloquent\Builder|Refund whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Refund whereBookingId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Refund whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Refund whereFailureReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Refund whereGateway($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Refund whereGatewayCharges($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Refund whereGatewayRefundId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Refund whereGatewayResponse($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Refund whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Refund whereInitiatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Refund whereInitiatedByUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Refund whereNetRefundAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Refund wherePaymentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Refund whereProcessedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Refund whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Refund whereUpdatedAt($value)
 */
	class Refund extends \Eloquent {}
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
 * @property string|null $remember_token
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Booking[] $bookings
 * @property-read int|null $bookings_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Event[] $events
 * @property-read int|null $events_count
 * @property-read string $avatar
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Refund[] $initiatedRefunds
 * @property-read int|null $initiated_refunds_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Booking[] $myBookings
 * @property-read int|null $my_bookings_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Payment[] $payments
 * @property-read int|null $payments_count
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
 * @method static \Illuminate\Database\Eloquent\Builder|User search(string $search)
 * @method static \Illuminate\Database\Eloquent\Builder|User verified()
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
 * @method static \Illuminate\Database\Eloquent\Builder|User withGoogleAuth()
 * @method static \Illuminate\Database\Eloquent\Builder|User withRole(string $role)
 * @method static \Illuminate\Database\Eloquent\Builder|User withoutPermission($permissions)
 * @method static \Illuminate\Database\Eloquent\Builder|User withoutRole($roles, $guard = null)
 */
	class User extends \Eloquent implements \Illuminate\Contracts\Auth\MustVerifyEmail {}
}

