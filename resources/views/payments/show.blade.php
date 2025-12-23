@extends('admin.layout.app')

@section('title', 'Complete Payment')

@section('content')
<div class="container-fluid">
  <div class="row">
    {{-- LEFT --}}
    <div class="col-md-6 mb-4">
      <div class="card h-100">
        <div class="card-body">
          <div class="d-flex align-items-center mb-2">
            <img src="{{ $booking->event->user->avatar ?? '' }}"
                 class="rounded-circle me-2"
                 width="32" height="32">
            <strong>{{ $booking->event->user->name ?? '' }}</strong>
          </div>

          <h4>{{ $booking->event->title }}</h4>

          <div class="text-muted mb-2">
            ⏱ {{ $booking->event->duration }} minutes
          </div>

          <p>{{ $booking->event->description }}</p>
        </div>
      </div>
    </div>

    {{-- RIGHT --}}
    <div class="col-md-6">
      <div class="card h-100">
        <div class="card-body">
          <h5 class="mb-3">Complete Payment</h5>

          <div class="mb-3">
            <div class="text-muted small">Booking for</div>
            <strong>{{ $booking->booker_name }} — {{ $booking->booker_email }}</strong>
          </div>

          <div class="mb-3">
            <div class="text-muted small">Amount</div>
            <strong class="fs-5">₹{{ $booking->event->price ?? 500 }}</strong>
          </div>

          <div id="payment-error" class="alert alert-danger d-none"></div>

          <button
            id="payBtn"
            class="btn btn-primary w-100"
            {{ $booking->status === 'confirmed' ? 'disabled' : '' }}
          >
            {{ $booking->status === 'confirmed'
                ? 'Already confirmed'
                : 'Pay ₹' . ($booking->event->price ?? 500) }}
          </button>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- VERIFY MODAL --}}
<div id="verifyingOverlay" class="position-fixed top-0 start-0 w-100 h-100 d-none"
     style="background: rgba(0,0,0,.4); z-index:1050;">
  <div class="d-flex align-items-center justify-content-center h-100">
    <div class="bg-white p-4 rounded text-center" style="width:300px">
      <div class="spinner-border text-primary mb-3"></div>
      <div class="fw-semibold">Verifying payment…</div>
      <div class="text-muted small mt-1">Please do not close this window.</div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
const payBtn = document.getElementById('payBtn');
const errorBox = document.getElementById('payment-error');
const overlay = document.getElementById('verifyingOverlay');

payBtn?.addEventListener('click', async function () {
  payBtn.disabled = true;
  errorBox.classList.add('d-none');

  try {
    const resp = await fetch('/create-order', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': '{{ csrf_token() }}',
      },
      body: JSON.stringify({
        amount: {{ $booking->event->price ?? 500 }}
      })
    });

    const data = await resp.json();

    const options = {
      key: data.key,
      amount: data.amount * 100,
      currency: 'INR',
      name: '{{ $booking->event->title }}',
      description: 'Payment for booking #{{ $booking->id }}',
      order_id: data.order_id,
      handler: async function (response) {
        overlay.classList.remove('d-none');

        const verify = await fetch('/verify-payment', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
          },
          body: JSON.stringify({
            razorpay_order_id: response.razorpay_order_id,
            razorpay_payment_id: response.razorpay_payment_id,
            razorpay_signature: response.razorpay_signature,
            booking_id: {{ $booking->id }},
            amount: data.amount
          })
        });

        const res = await verify.json();
        if (res.success) {
          window.location.href = '/payment/thankyou/{{ $booking->id }}';
        } else {
          throw new Error(res.message || 'Verification failed');
        }
      },
      theme: { color: '#0d6efd' }
    };

    new Razorpay(options).open();

  } catch (err) {
    errorBox.textContent = err.message || 'Payment failed';
    errorBox.classList.remove('d-none');
    payBtn.disabled = false;
  }
});
</script>
@endpush
