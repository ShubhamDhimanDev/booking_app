@extends('admin.layout.app')

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
                            </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">
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
