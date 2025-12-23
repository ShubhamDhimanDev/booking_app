<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// API route for reschedule data
Route::get('/events/{event:slug}/reschedule-data', function($slug) {
    $event = \App\Models\Event::where('slug', $slug)->with(['exclusions', 'bookings'])->firstOrFail();
    $event->append('timeslots');

    // Only confirmed bookings occupy slots
    $confirmedBookings = $event->bookings()
        ->where('status', 'confirmed')
        ->get()
        ->groupBy('booked_at_date');

    $bookedSlots = [];
    foreach ($confirmedBookings as $date => $collection) {
        $bookedSlots[$date] = $collection->pluck('booked_at_time')->values()->all();
    }

    $startDate = \Carbon\Carbon::parse($event->available_from_date);
    $endDate = \Carbon\Carbon::parse($event->available_to_date);
    $availableSlots = [];

    for ($date = $startDate->copy(); $date->lessThanOrEqualTo($endDate); $date->addDay()) {
        $dateStr = $date->toDateString();

        if (!empty($event->available_week_days) && is_array($event->available_week_days)) {
            $weekday = strtolower($date->format('l'));
            if (!in_array($weekday, $event->available_week_days)) {
                continue;
            }
        }

        $free = [];
        foreach ($event->timeslots as $ts) {
            $startTime = $ts['start'];
            $isBooked = isset($bookedSlots[$dateStr]) && in_array($startTime, $bookedSlots[$dateStr]);
            if (!$isBooked) {
                $free[] = $ts;
            }
        }

        if (count($free) > 0) {
            $availableSlots[] = [
                'date' => $dateStr,
                'timeslots' => $free,
            ];
        }
    }

    return response()->json([
        'availableSlots' => $availableSlots,
        'bookedSlots' => $bookedSlots,
        'exclusions' => $event->exclusions->map(function($e) {
            return [
                'date' => $e->date->toDateString(),
                'exclude_all' => (bool)$e->exclude_all,
                'times' => $e->times ?? []
            ];
        }),
        'allowedWeekDays' => $event->available_week_days ?? []
    ]);
});
