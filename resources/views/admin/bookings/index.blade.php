@extends('admin.layouts.app')

@section('title', 'Bookings List')

@section('content')

<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Bookings List</h4>
    </div>

    {{-- Success Alert --}}
    @if(session('alert_message'))
        <div class="alert alert-{{ session('alert_type') }} alert-dismissible fade show" role="alert">
            {{ session('alert_message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body p-0">

            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Event</th>
                            <th>Booker</th>
                            <th>Email</th>
                            <th>Booked Date</th>
                            <th>Booked Time</th>
                            <th>Meet Link</th>
                            <th>Calendar Link</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>

                        @php $i = ($bookings->currentPage() - 1) * $bookings->perPage() + 1; @endphp

                        @forelse ($bookings as $booking)
                            <tr>
                                <td>{{ $i++ }}</td>

                                {{-- Event --}}
                                <td>
                                    <strong>{{ $booking->event->title }}</strong>
                                    <br>
                                    <small class="text-muted">
                                        {{ \Carbon\Carbon::parse($booking->event->available_from_date)->format('d M Y') }}
                                        →
                                        {{ \Carbon\Carbon::parse($booking->event->available_to_date)->format('d M Y') }}
                                    </small>
                                </td>

                                {{-- Booker --}}
                                <td>{{ $booking->booker_name }}</td>

                                {{-- Email --}}
                                <td>{{ $booking->booker_email }}</td>

                                {{-- Booked date --}}
                                <td>
                                    {{ \Carbon\Carbon::parse($booking->booked_at_date)->format('d M Y') }}
                                </td>

                                {{-- booked time (from accessor) --}}
                                <td>{{ $booking->booked_at_time }}</td>

                                {{-- Meet link --}}
                                @php
                                    $bookingDateTime = \Carbon\Carbon::parse($booking->booked_at_date . ' ' . $booking->booked_at_time);
                                @endphp
                                <td>
                                    @if($booking->status === 'cancelled')
                                        <span class="text-warning">Cancelled</span>
                                    @elseif($booking->meet_link && $bookingDateTime->isFuture())
                                        <a href="{{ $booking->meet_link }}" target="_blank">
                                            <i class="fa fa-external-link"></i>
                                        </a>
                                    @elseif($booking->meet_link)
                                        <span class="text-muted">Completed</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>

                                {{-- calendar link --}}
                                <td>
                                    @if($booking->status === 'cancelled')
                                        <span class="text-warning">Cancelled</span>
                                    @elseif($booking->calendar_link && $bookingDateTime->isFuture())
                                        <a href="{{ $booking->calendar_link }}" target="_blank">
                                            <i class="fa fa-external-link"></i>
                                        </a>
                                    @elseif($booking->calendar_link)
                                        <span class="text-muted">Completed</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>

                                {{-- created at --}}
                                <td>{{ $booking->created_at->format('d M Y H:i') }}</td>

                                {{-- Actions --}}
                                <td>
                                    @if($booking->isCompleted() && !$booking->is_followup)
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-primary"
                                            data-bs-toggle="modal"
                                            data-bs-target="#followUpModal{{ $booking->id }}">
                                            <i class="fa fa-paper-plane"></i> Follow-up
                                        </button>
                                    @elseif($booking->is_followup)
                                        <span class="badge bg-info">Follow-up Session</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>

                            {{-- Follow-up Modal --}}
                            @if($booking->isCompleted() && !$booking->is_followup)
                            <div class="modal fade" id="followUpModal{{ $booking->id }}" tabindex="-1" aria-hidden="true" data-bs-theme="dark">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content bg-dark border-secondary">
                                        <div class="modal-header border-secondary">
                                            <h5 class="modal-title text-white">
                                                <i class="fa fa-paper-plane me-2"></i>Send Follow-up Invitation
                                            </h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <form method="POST" action="{{ route('admin.bookings.send-followup', $booking) }}">
                                            @csrf
                                            <div class="modal-body">
                                                <div class="alert alert-info bg-info bg-opacity-10 border-info text-info mb-4">
                                                    <i class="fa fa-info-circle me-2"></i>
                                                    Send a follow-up session invitation to <strong>{{ $booking->booker_name }}</strong>
                                                    ({{ $booking->booker_email }})
                                                </div>

                                                <div class="mb-4">
                                                    <label class="form-label text-white fw-semibold">
                                                        <i class="fa fa-indian-rupee-sign me-2"></i>Session Price (₹) *
                                                    </label>
                                                    <input
                                                        type="number"
                                                        name="custom_price"
                                                        class="form-control bg-dark text-white border-secondary"
                                                        min="0"
                                                        step="0.01"
                                                        value="{{ $booking->event->price ?? 0 }}"
                                                        required
                                                        placeholder="Enter price">
                                                    <small class="form-text text-muted">
                                                        <i class="fa fa-lightbulb me-1"></i>Set to 0 for a free session
                                                    </small>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label text-white fw-semibold">
                                                        <i class="fa fa-calendar-days me-2"></i>Invitation Expiry (Days)
                                                    </label>
                                                    <input
                                                        type="number"
                                                        name="expires_days"
                                                        class="form-control bg-dark text-white border-secondary"
                                                        min="1"
                                                        max="90"
                                                        value="30"
                                                        placeholder="30">
                                                    <small class="form-text text-muted">
                                                        <i class="fa fa-clock me-1"></i>Default: 30 days
                                                    </small>
                                                </div>
                                            </div>
                                            <div class="modal-footer border-secondary">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                    <i class="fa fa-times me-1"></i>Cancel
                                                </button>
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="fa fa-paper-plane"></i> Send Invitation
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            @endif
                        @empty
                        <tr>
                            <td colspan="10" class="text-center py-4 text-muted">
                                No bookings found.
                            </td>
                        </tr>
                        @endforelse

                    </tbody>

                </table>
            </div>

        </div>
    </div>

    {{-- Pagination --}}
    <div class="d-flex justify-content-center mt-4">
        {{ $bookings->links() }}
    </div>

</div>

@endsection


@push('scripts')
@endpush
