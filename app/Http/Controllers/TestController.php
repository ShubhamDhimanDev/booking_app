<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\BookingReminderLog;
use App\Models\EventReminder;
use App\Notifications\BookingReminderNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

class TestController extends Controller
{
  public function index()
  {
    return view('test');
  }

  public function welcome()
  {
    return view('welcome');
  }

  public function testEmailForm()
  {
    // return view('emails.tests.booking-confirmation');
    return view('emails.tests.booking-created-organizer');
    return view('emails.tests.booking-declined');
    return view('emails.tests.booking-reminder');
    return view('emails.tests.booking-rescheduled');
    return view('emails.tests.new-user-credentials');
    return view('emails.tests.refund-processed');
    return view('emails.tests.reset-password');
    return view('emails.tests.verify-email');



    return view('test-2');
  }

  public function sendTestEmail(Request $request)
  {
    $validated = $request->validate([
      'email' => 'required|email',
      'name' => 'nullable|string|max:255',
    ]);

    try {
      Mail::send('emails.tests.booking-confirmation', [
        'bookerName' => $validated['name'] ?? 'Alex',
        'eventTitle' => '30-Minute Consultation',
        'bookingDate' => 'January 30, 2026',
        'bookingTime' => '2:00 PM - 3:00 PM',
        'meetingLink' => 'https://meet.google.com/abc-defg-hij',
        'meetingLocation' => 'Google Meet',
        'bookingDetailsUrl' => url('/user/bookings'),
      ], function ($message) use ($validated) {
        $message->to($validated['email'])
                ->subject('Booking Confirmed - ' . config('app.name'));
      });

      return back()->with('success', 'Confirmation email sent successfully to ' . $validated['email'] . '! Check your inbox.');
    } catch (\Exception $e) {
      Log::error('Test email failed: ' . $e->getMessage());
      return back()->with('error', 'Failed to send email: ' . $e->getMessage());
    }
  }

  public function sendAllTestEmails(Request $request)
  {
    // Removed - no longer needed
    return redirect()->route('test.email.form');
  }
}
