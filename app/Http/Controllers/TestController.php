<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\BookingReminderLog;
use App\Models\EventReminder;
use App\Notifications\BookingReminderNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class TestController extends Controller
{
  public function index()
  {
    return view('bookings');
  }

  public function welcome()
  {
    return view('welcome');
  }
}
