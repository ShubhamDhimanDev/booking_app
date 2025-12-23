@extends('layouts.app')

@section('title', 'Reschedule Booking')

@section('content')
<div class="container-fluid">
  <div class="card">
    <div class="card-body">
      <h4>Reschedule: {{ optional($booking->event)->title }}</h4>
      <p>Current: {{ $booking->booked_at_date }} at {{ $booking->booked_at_time }}</p>

      <form method="POST" action="{{ route('user.bookings.reschedule', $booking->id) }}">
        @csrf
        <div class="mb-3">
          <label class="form-label">New Date</label>
          <select name="booked_at_date" class="form-control">
            @foreach($availableSlots as $slot)
              <option value="{{ $slot['date'] }}">{{ \Carbon\Carbon::parse($slot['date'])->format('d M Y') }}</option>
            @endforeach
          </select>
        </div>

        <div class="mb-3">
          <label class="form-label">New Time</label>
          <select name="booked_at_time" class="form-control">
            {{-- We'll load times via JS when date changes in a simple way: show times for first date by default --}}
            @if(!empty($availableSlots))
              @foreach($availableSlots[0]['timeslots'] as $ts)
                <option value="{{ $ts['start'] }}">{{ $ts['start'] }}</option>
              @endforeach
            @endif
          </select>
        </div>

        <button class="btn btn-primary">Reschedule</button>
      </form>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  (function(){
    const slots = @json($availableSlots);
    const dateSel = document.querySelector('select[name="booked_at_date"]');
    const timeSel = document.querySelector('select[name="booked_at_time"]');
    if (!dateSel || !timeSel) return;
    dateSel.addEventListener('change', function(){
      const val = this.value;
      const found = slots.find(s => s.date === val);
      timeSel.innerHTML = '';
      if (found) {
        found.timeslots.forEach(t => {
          const o = document.createElement('option'); o.value = t.start; o.text = t.start; timeSel.appendChild(o);
        });
      }
    });
  })();
</script>
@endpush
