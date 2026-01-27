@extends('admin.layouts.app')

@section('title', 'Tracking Settings')

@section('content')

<div class="container-fluid">

    <h4 class="mb-4">Analytics & Tracking Settings</h4>

    @if(session('alert_type'))
        <div class="alert alert-{{ session('alert_type') }} alert-dismissible fade show" role="alert">
            {{ session('alert_message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <form action="{{ route('admin.tracking.update') }}" method="POST">
        @csrf
        @method('PUT')

        {{-- Meta Pixel Section --}}
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-facebook"></i> Meta Pixel Tracking</h5>
            </div>
            <div class="card-body">

            <form action="{{ route('admin.tracking.update') }}" method="POST">
                @csrf
                @method('PUT')

                {{-- Enable Meta Pixel --}}
                <div class="mb-4">
                    <div class="form-check form-switch">
                        <input
                            class="form-check-input"
                            type="checkbox"
                            id="meta_pixel_enabled"
                            name="meta_pixel_enabled"
                            value="1"
                            {{ $settings['meta_pixel_enabled'] ? 'checked' : '' }}
                        >
                        <label class="form-check-label" for="meta_pixel_enabled">
                            <strong>Enable Meta Pixel Tracking</strong>
                        </label>
                    </div>
                    <small class="text-muted">Master switch to enable/disable all Meta Pixel tracking</small>
                </div>

                {{-- Meta Pixel ID --}}
                <div class="mb-4">
                    <label for="meta_pixel_id" class="form-label">Meta Pixel ID</label>
                    <input
                        type="text"
                        class="form-control @error('meta_pixel_id') is-invalid @enderror"
                        id="meta_pixel_id"
                        name="meta_pixel_id"
                        value="{{ old('meta_pixel_id', $settings['meta_pixel_id']) }}"
                        placeholder="e.g., 1234567890123456"
                    >
                    <small class="text-muted">Your Meta (Facebook) Pixel ID from Events Manager</small>
                    @error('meta_pixel_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <hr class="my-4">

                <h5 class="mb-3">Event Configuration</h5>
                <p class="text-muted mb-3">Select which events to track in the booking funnel:</p>

                {{-- PageView Event --}}
                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input
                            class="form-check-input"
                            type="checkbox"
                            id="meta_event_page_view"
                            name="meta_event_page_view"
                            value="1"
                            {{ $settings['meta_event_page_view'] ? 'checked' : '' }}
                        >
                        <label class="form-check-label" for="meta_event_page_view">
                            <strong>PageView</strong> - Track all page views
                        </label>
                    </div>
                    <small class="text-muted d-block ms-4">Fires on every page load (slot selection, details, payment, thank you)</small>
                </div>

                {{-- InitiateCheckout Event --}}
                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input
                            class="form-check-input"
                            type="checkbox"
                            id="meta_event_initiate_checkout"
                            name="meta_event_initiate_checkout"
                            value="1"
                            {{ $settings['meta_event_initiate_checkout'] ? 'checked' : '' }}
                        >
                        <label class="form-check-label" for="meta_event_initiate_checkout">
                            <strong>InitiateCheckout</strong> - User starts booking details form
                        </label>
                    </div>
                    <small class="text-muted d-block ms-4">Fires when user lands on the details form page</small>
                </div>

                {{-- AddPaymentInfo Event --}}
                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input
                            class="form-check-input"
                            type="checkbox"
                            id="meta_event_add_payment_info"
                            name="meta_event_add_payment_info"
                            value="1"
                            {{ $settings['meta_event_add_payment_info'] ? 'checked' : '' }}
                        >
                        <label class="form-check-label" for="meta_event_add_payment_info">
                            <strong>AddPaymentInfo</strong> - User reaches payment page
                        </label>
                    </div>
                    <small class="text-muted d-block ms-4">Fires when user is redirected to payment gateway</small>
                </div>

                {{-- Purchase Event --}}
                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input
                            class="form-check-input"
                            type="checkbox"
                            id="meta_event_purchase"
                            name="meta_event_purchase"
                            value="1"
                            {{ $settings['meta_event_purchase'] ? 'checked' : '' }}
                        >
                        <label class="form-check-label" for="meta_event_purchase">
                            <strong>Purchase</strong> - Payment successful (CONVERSION)
                        </label>
                    </div>
                    <small class="text-muted d-block ms-4">Fires on thank you page after successful payment - tracks conversions!</small>
                </div>

                <hr class="my-3">
                <h6 class="mb-3 text-primary">Custom Events</h6>

                {{-- View Bookings Event --}}
                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input
                            class="form-check-input"
                            type="checkbox"
                            id="meta_event_viewbookings"
                            name="meta_event_viewbookings"
                            value="1"
                            {{ $settings['meta_event_viewbookings'] ?? true ? 'checked' : '' }}
                        >
                        <label class="form-check-label" for="meta_event_viewbookings">
                            <strong>ViewBookings</strong> - User views their bookings list
                        </label>
                    </div>
                    <small class="text-muted d-block ms-4">Fires when user accesses their bookings dashboard</small>
                </div>

                {{-- Booking Rescheduled Event --}}
                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input
                            class="form-check-input"
                            type="checkbox"
                            id="meta_event_bookingrescheduled"
                            name="meta_event_bookingrescheduled"
                            value="1"
                            {{ $settings['meta_event_bookingrescheduled'] ?? true ? 'checked' : '' }}
                        >
                        <label class="form-check-label" for="meta_event_bookingrescheduled">
                            <strong>BookingRescheduled</strong> - User reschedules a booking
                        </label>
                    </div>
                    <small class="text-muted d-block ms-4">Fires when user submits reschedule form</small>
                </div>

                {{-- View Transactions Event --}}
                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input
                            class="form-check-input"
                            type="checkbox"
                            id="meta_event_viewtransactions"
                            name="meta_event_viewtransactions"
                            value="1"
                            {{ $settings['meta_event_viewtransactions'] ?? true ? 'checked' : '' }}
                        >
                        <label class="form-check-label" for="meta_event_viewtransactions">
                            <strong>ViewTransactions</strong> - User views transactions history
                        </label>
                    </div>
                    <small class="text-muted d-block ms-4">Fires when user accesses transactions page</small>
                </div>

                {{-- View Payment Page Event --}}
                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input
                            class="form-check-input"
                            type="checkbox"
                            id="meta_event_viewpaymentpage"
                            name="meta_event_viewpaymentpage"
                            value="1"
                            {{ $settings['meta_event_viewpaymentpage'] ?? true ? 'checked' : '' }}
                        >
                        <label class="form-check-label" for="meta_event_viewpaymentpage">
                            <strong>ViewPaymentPage</strong> - User lands on payment page
                        </label>
                    </div>
                    <small class="text-muted d-block ms-4">Fires when payment page loads</small>
                </div>

            </div>
        </div>

        {{-- Google Analytics Section --}}
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0"><i class="bi bi-google"></i> Google Analytics Tracking</h5>
            </div>
            <div class="card-body">

                {{-- Enable Google Analytics --}}
                <div class="mb-4">
                    <div class="form-check form-switch">
                        <input
                            class="form-check-input"
                            type="checkbox"
                            id="google_analytics_enabled"
                            name="google_analytics_enabled"
                            value="1"
                            {{ $settings['google_analytics_enabled'] ? 'checked' : '' }}
                        >
                        <label class="form-check-label" for="google_analytics_enabled">
                            <strong>Enable Google Analytics Tracking</strong>
                        </label>
                    </div>
                    <small class="text-muted">Master switch to enable/disable all Google Analytics tracking</small>
                </div>

                {{-- Google Analytics Measurement ID --}}
                <div class="mb-4">
                    <label for="google_analytics_id" class="form-label">Google Analytics Measurement ID</label>
                    <input
                        type="text"
                        class="form-control @error('google_analytics_id') is-invalid @enderror"
                        id="google_analytics_id"
                        name="google_analytics_id"
                        value="{{ old('google_analytics_id', $settings['google_analytics_id']) }}"
                        placeholder="e.g., G-XXXXXXXXXX or UA-XXXXXXXXX-X"
                    >
                    <small class="text-muted">Your Google Analytics Measurement ID from GA4 property</small>
                    @error('google_analytics_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <hr class="my-4">

                <h5 class="mb-3">Event Configuration</h5>
                <p class="text-muted mb-3">Select which events to track with Google Analytics:</p>

                {{-- PageView Event --}}
                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input
                            class="form-check-input"
                            type="checkbox"
                            id="google_event_page_view"
                            name="google_event_page_view"
                            value="1"
                            {{ $settings['google_event_page_view'] ? 'checked' : '' }}
                        >
                        <label class="form-check-label" for="google_event_page_view">
                            <strong>page_view</strong> - Track all page views
                        </label>
                    </div>
                    <small class="text-muted d-block ms-4">Automatic page view tracking</small>
                </div>

                {{-- Begin Checkout Event --}}
                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input
                            class="form-check-input"
                            type="checkbox"
                            id="google_event_begin_checkout"
                            name="google_event_begin_checkout"
                            value="1"
                            {{ $settings['google_event_begin_checkout'] ? 'checked' : '' }}
                        >
                        <label class="form-check-label" for="google_event_begin_checkout">
                            <strong>begin_checkout</strong> - User starts booking
                        </label>
                    </div>
                    <small class="text-muted d-block ms-4">GA4 recommended event for checkout start</small>
                </div>

                {{-- Add Payment Info Event --}}
                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input
                            class="form-check-input"
                            type="checkbox"
                            id="google_event_add_payment_info"
                            name="google_event_add_payment_info"
                            value="1"
                            {{ $settings['google_event_add_payment_info'] ? 'checked' : '' }}
                        >
                        <label class="form-check-label" for="google_event_add_payment_info">
                            <strong>add_payment_info</strong> - User reaches payment
                        </label>
                    </div>
                    <small class="text-muted d-block ms-4">GA4 recommended event for payment info</small>
                </div>

                {{-- Purchase Event --}}
                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input
                            class="form-check-input"
                            type="checkbox"
                            id="google_event_purchase"
                            name="google_event_purchase"
                            value="1"
                            {{ $settings['google_event_purchase'] ? 'checked' : '' }}
                        >
                        <label class="form-check-label" for="google_event_purchase">
                            <strong>purchase</strong> - Payment successful (CONVERSION)
                        </label>
                    </div>
                    <small class="text-muted d-block ms-4">GA4 recommended event - tracks conversions & revenue!</small>
                </div>

                <hr class="my-3">
                <h6 class="mb-3 text-danger">Custom Events</h6>

                {{-- View Bookings Event --}}
                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input
                            class="form-check-input"
                            type="checkbox"
                            id="google_event_view_bookings"
                            name="google_event_view_bookings"
                            value="1"
                            {{ $settings['google_event_view_bookings'] ?? true ? 'checked' : '' }}
                        >
                        <label class="form-check-label" for="google_event_view_bookings">
                            <strong>view_bookings</strong> - User views bookings list
                        </label>
                    </div>
                    <small class="text-muted d-block ms-4">Custom event for bookings dashboard</small>
                </div>

                {{-- Booking Rescheduled Event --}}
                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input
                            class="form-check-input"
                            type="checkbox"
                            id="google_event_booking_rescheduled"
                            name="google_event_booking_rescheduled"
                            value="1"
                            {{ $settings['google_event_booking_rescheduled'] ?? true ? 'checked' : '' }}
                        >
                        <label class="form-check-label" for="google_event_booking_rescheduled">
                            <strong>booking_rescheduled</strong> - Booking rescheduled
                        </label>
                    </div>
                    <small class="text-muted d-block ms-4">Custom event for reschedule action</small>
                </div>

                {{-- View Transactions Event --}}
                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input
                            class="form-check-input"
                            type="checkbox"
                            id="google_event_view_transactions"
                            name="google_event_view_transactions"
                            value="1"
                            {{ $settings['google_event_view_transactions'] ?? true ? 'checked' : '' }}
                        >
                        <label class="form-check-label" for="google_event_view_transactions">
                            <strong>view_transactions</strong> - User views transaction history
                        </label>
                    </div>
                    <small class="text-muted d-block ms-4">Custom event for transactions page</small>
                </div>

                {{-- View Payment Page Event --}}
                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input
                            class="form-check-input"
                            type="checkbox"
                            id="google_event_view_payment_page"
                            name="google_event_view_payment_page"
                            value="1"
                            {{ $settings['google_event_view_payment_page'] ?? true ? 'checked' : '' }}
                        >
                        <label class="form-check-label" for="google_event_view_payment_page">
                            <strong>view_payment_page</strong> - User lands on payment page
                        </label>
                    </div>
                    <small class="text-muted d-block ms-4">Custom event for payment page view</small>
                </div>

            </div>
        </div>

        {{-- Save Button --}}
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <a href="{{ route('admin.events.index') }}" class="btn btn-secondary text-white">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Save Settings
                    </button>
                </div>
            </div>
        </div>

    </form>

    {{-- Help Section for Meta Pixel --}}
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0"><i class="bi bi-info-circle"></i> How to Setup Meta Pixel</h5>
        </div>
        <div class="card-body">
            <ol class="mb-0">
                <li>Go to <a href="https://business.facebook.com/events_manager" target="_blank">Meta Events Manager</a></li>
                <li>Select your Pixel or create a new one</li>
                <li>Copy your Pixel ID (16-digit number)</li>
                <li>Paste it above and enable tracking</li>
                <li>Configure which events you want to track</li>
                <li>Test your setup using Meta Pixel Helper extension</li>
            </ol>
        </div>
    </div>

    {{-- Help Section for Google Analytics --}}
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0"><i class="bi bi-info-circle"></i> How to Setup Google Analytics</h5>
        </div>
        <div class="card-body">
            <ol class="mb-0">
                <li>Go to <a href="https://analytics.google.com/" target="_blank">Google Analytics</a></li>
                <li>Create a GA4 property (or use existing one)</li>
                <li>Navigate to Admin → Data Streams → Web</li>
                <li>Copy your Measurement ID (starts with G-XXXXXXXXXX)</li>
                <li>Paste it above and enable tracking</li>
                <li>Configure which events you want to track</li>
                <li>View real-time events in GA4 to test your setup</li>
            </ol>
        </div>
    </div>

</div>

@endsection
