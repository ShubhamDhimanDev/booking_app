@extends('layouts.app')

@section('title', 'Thank You')

@section('content')
<div class="container-fluid">
  <div class="row">
    <div class="col-md-6 mb-4">
      <div class="card h-100">
        <div class="card-body">
          <div class="d-flex align-items-center mb-2">
            <img src="{{ $booking->event->user->avatar ?? '' }}"
                 class="rounded-circle me-2"
                 width="32" height="32">
            <strong>{{ $booking->event->user->name }}</strong>
          </div>

          <h4>Thanks — your booking is confirmed</h4>

          <p class="text-muted">
            Confirmation sent to <strong>{{ $booking->booker_email }}</strong>
          </p>

          <hr>

          <h6>Event</h6>
          <p>{{ $booking->event->title }}</p>
          <small class="text-muted">{{ $booking->event->duration }} minutes</small>

          <h6 class="mt-3">When</h6>
          <p>{{ $booking->booked_at_date }} at {{ $booking->booked_at_time }}</p>
        </div>
      </div>
    </div>

    <div class="col-md-6">
      <div class="card h-100">
        <div class="card-body">
          <h5>Next steps</h5>

          @if($booking->meet_link)
            <a href="{{ $booking->meet_link }}"
               class="btn btn-success w-100 mb-2"
               target="_blank">
              Join Meeting
            </a>
          @endif

          @if($booking->calendar_link)
            <a href="{{ $booking->calendar_link }}"
               class="btn btn-primary w-100 mb-2"
               target="_blank">
              Open in Google Calendar
            </a>
          @endif

          <a href="/e/{{ $booking->event->slug }}"
             class="btn btn-outline-secondary w-100">
            Back to event page
          </a>

          <p class="text-muted small mt-4">
            Recommendation: mark this time busy in your calendar to avoid double-booking.
          </p>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
