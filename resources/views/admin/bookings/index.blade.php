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

    {{-- Campaign Tracking Filters --}}
    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.bookings.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label for="search" class="form-label">Search</label>
                    <input type="text" name="search" id="search" class="form-control" placeholder="Name or email" value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <label for="utm_source" class="form-label">Traffic Source</label>
                    <select name="utm_source" id="utm_source" class="form-select">
                        <option value="">All Sources</option>
                        @foreach($utmSources as $source)
                            <option value="{{ $source }}" {{ request('utm_source') == $source ? 'selected' : '' }}>
                                {{ ucfirst($source) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="utm_medium" class="form-label">Medium</label>
                    <select name="utm_medium" id="utm_medium" class="form-select">
                        <option value="">All Mediums</option>
                        @foreach($utmMediums as $medium)
                            <option value="{{ $medium }}" {{ request('utm_medium') == $medium ? 'selected' : '' }}>
                                {{ ucfirst($medium) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="utm_campaign" class="form-label">Campaign</label>
                    <select name="utm_campaign" id="utm_campaign" class="form-select">
                        <option value="">All Campaigns</option>
                        @foreach($utmCampaigns as $campaign)
                            <option value="{{ $campaign }}" {{ request('utm_campaign') == $campaign ? 'selected' : '' }}>
                                {{ $campaign }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="status" class="form-label">Status</label>
                    <select name="status" id="status" class="form-select">
                        <option value="">All Statuses</option>
                        @foreach($bookingStatuses as $value => $label)
                            <option value="{{ $value }}" {{ request('status') == $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label for="date_from" class="form-label">Booked From</label>
                    <input type="date" name="date_from" id="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-3">
                    <label for="date_to" class="form-label">Booked To</label>
                    <input type="date" name="date_to" id="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-3">
                    <label for="sort" class="form-label">Sort By</label>
                    <select name="sort" id="sort" class="form-select">
                        <option value="created_at" {{ (request('sort', 'created_at') == 'created_at') ? 'selected' : '' }}>Created Date</option>
                        <option value="booked_at_date" {{ request('sort') == 'booked_at_date' ? 'selected' : '' }}>Booking Date</option>
                        <option value="status" {{ request('sort') == 'status' ? 'selected' : '' }}>Status</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label for="direction" class="form-label">Direction</label>
                    <select name="direction" id="direction" class="form-select">
                        <option value="desc" {{ (request('direction', 'desc') == 'desc') ? 'selected' : '' }}>Descending</option>
                        <option value="asc" {{ request('direction') == 'asc' ? 'selected' : '' }}>Ascending</option>
                    </select>
                </div>

                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="bi bi-funnel"></i> Filter
                    </button>
                    @if(request()->hasAny(['search', 'utm_source', 'utm_medium', 'utm_campaign', 'status', 'date_from', 'date_to', 'sort', 'direction']))
                        <a href="{{ route('admin.bookings.index') }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Clear
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">

            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        @php
                            $sortLink = function (string $column) use ($sort, $direction) {
                                $nextDirection = ($sort === $column && $direction === 'asc') ? 'desc' : 'asc';
                                $params = array_filter(array_merge(request()->except('page'), [
                                    'sort' => $column,
                                    'direction' => $nextDirection,
                                ]), fn ($v) => $v !== null && $v !== '');
                                return route('admin.bookings.index', $params);
                            };
                            $sortIcon = function (string $column) use ($sort, $direction) {
                                if ($sort !== $column) {
                                    return '<i class="fa fa-sort text-muted"></i>';
                                }
                                return $direction === 'asc'
                                    ? '<i class="fa fa-sort-up"></i>'
                                    : '<i class="fa fa-sort-down"></i>';
                            };
                        @endphp
                        <tr>
                            <th>#</th>
                            <th>Event</th>
                            <th>Booker</th>
                            <th>
                                <a href="{{ $sortLink('booked_at_date') }}" class="text-dark text-decoration-none">
                                    Booked Date {!! $sortIcon('booked_at_date') !!}
                                </a>
                            </th>
                            <th>Booked Time</th>
                            <th>
                                <a href="{{ $sortLink('status') }}" class="text-dark text-decoration-none">
                                    Status {!! $sortIcon('status') !!}
                                </a>
                            </th>
                            <th>
                                <a href="{{ $sortLink('created_at') }}" class="text-dark text-decoration-none">
                                    Created At {!! $sortIcon('created_at') !!}
                                </a>
                            </th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>

                        @php $i = ($bookings->currentPage() - 1) * $bookings->perPage() + 1; @endphp

                        @forelse ($bookings as $booking)
                            @php
                                $bookingDateTime = \Carbon\Carbon::parse($booking->booked_at_date . ' ' . $booking->booked_at_time);
                                $bookingStatus = $booking->status;
                                $statusLabel = ucfirst($bookingStatus);
                                $statusBadgeClass = 'bg-secondary text-white';

                                if ($bookingStatus === 'cancelled') {
                                    $statusLabel = 'Cancelled';
                                    $statusBadgeClass = 'bg-warning text-dark';
                                } elseif ($bookingStatus === 'confirmed') {
                                    $statusLabel = 'Scheduled';
                                    $statusBadgeClass = 'bg-primary text-white';
                                } elseif ($bookingStatus === 'pending') {
                                    $statusLabel = 'Pending';
                                    $statusBadgeClass = 'bg-secondary text-white';
                                }

                                if ($bookingStatus !== 'cancelled' && $bookingDateTime->isPast()) {
                                    $statusLabel = 'Completed';
                                    $statusBadgeClass = 'bg-success text-white';
                                }

                                $additionalNotes = trim((string) $booking->additional_notes);
                                $isCancelled = in_array($bookingStatus, ['cancelled', 'declined'], true);
                                $hasPaidAmount = $booking->payment && $booking->payment->amount > 0;
                                $eventHasRefund = $booking->event && ($booking->event->refund_enabled ?? false);
                            @endphp
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

                                {{-- Booked date --}}
                                <td>
                                    {{ \Carbon\Carbon::parse($booking->booked_at_date)->format('d M Y') }}
                                </td>

                                {{-- booked time (from accessor) --}}
                                <td>{{ $booking->booked_at_time }}</td>

                                {{-- status --}}
                                <td>
                                    <span class="badge {{ $statusBadgeClass }}">{{ $statusLabel }}</span>
                                </td>

                                {{-- Created At --}}
                                <td>
                                    <small class="text-muted">{{ $booking->created_at->format('d M Y H:i') }}</small>
                                </td>

                                {{-- Actions --}}
                                <td>
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-outline-primary me-1"
                                        data-bs-toggle="modal"
                                        data-bs-target="#bookingModal{{ $booking->id }}">
                                        <i class="fa fa-eye"></i> View
                                    </button>

                                    @if($bookingStatus === 'confirmed')
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-outline-danger me-1"
                                            data-bs-toggle="modal"
                                            data-bs-target="#cancelModal{{ $booking->id }}">
                                            <i class="fa fa-times"></i> Cancel
                                        </button>
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-outline-secondary me-1"
                                            data-bs-toggle="modal"
                                            data-bs-target="#rescheduleModal{{ $booking->id }}">
                                            <i class="fa fa-calendar-alt"></i> Re-Schedule
                                        </button>
                                    @endif
                                    {{-- @if($booking->isCompleted() && !$booking->is_followup) --}}
                                    @if($booking->calendar_link && !$bookingDateTime->isFuture())
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-primary"
                                            data-bs-toggle="modal"
                                            data-bs-target="#followUpModal{{ $booking->id }}">
                                            <i class="fa fa-paper-plane"></i> Follow-up
                                        </button>
                                    @elseif($booking->is_followup)
                                        <span class="badge bg-info text-white">Follow-up Session</span>
                                    @endif

                                    <button
                                          type="button"
                                          class="btn btn-sm btn-success"
                                          data-bs-toggle="modal"
                                          data-bs-target="#inviteModal{{ $booking->id }}">
                                          <i class="fa fa-envelope-open"></i> Invite (Free)
                                    </button>
                                </td>
                            </tr>

                            {{-- Details Modal --}}
                            <div class="modal fade" id="bookingModal{{ $booking->id }}" tabindex="-1" aria-hidden="true" data-bs-theme="dark">
                                <div class="modal-dialog modal-lg modal-dialog-centered">
                                    <div class="modal-content bg-dark border-secondary">
                                        <div class="modal-header border-secondary">
                                            <h5 class="modal-title text-white">Booking Details</h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <div class="border border-secondary rounded p-3 h-100">
                                                        <h6 class="text-uppercase text-muted small mb-3">Overview</h6>
                                                        <dl class="row mb-0">
                                                            <dt class="col-5 text-muted">Event</dt>
                                                            <dd class="col-7 text-white">{{ $booking->event->title }}</dd>
                                                            <dt class="col-5 text-muted">Booked Date</dt>
                                                            <dd class="col-7 text-white">{{ \Carbon\Carbon::parse($booking->booked_at_date)->format('d M Y') }}</dd>
                                                            <dt class="col-5 text-muted">Booked Time</dt>
                                                            <dd class="col-7 text-white">{{ $booking->booked_at_time }}</dd>
                                                            <dt class="col-5 text-muted">Status</dt>
                                                            <dd class="col-7"><span class="badge {{ $statusBadgeClass }}">{{ $statusLabel }}</span></dd>
                                                            <dt class="col-5 text-muted">Created At</dt>
                                                            <dd class="col-7 text-white">{{ $booking->created_at->format('d M Y H:i') }}</dd>
                                                        </dl>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="border border-secondary rounded p-3 h-100">
                                                        <h6 class="text-uppercase text-muted small mb-3">Contact</h6>
                                                        <dl class="row mb-0">
                                                            <dt class="col-5 text-muted">Booker</dt>
                                                            <dd class="col-7 text-white">{{ $booking->booker_name }}</dd>
                                                            <dt class="col-5 text-muted">Email</dt>
                                                            <dd class="col-7 text-white">{{ $booking->booker_email }}</dd>
                                                            <dt class="col-5 text-muted">Phone</dt>
                                                            <dd class="col-7 text-white">{{ $booking->phone ?? '-' }}</dd>
                                                        </dl>
                                                    </div>
                                                </div>
                                                <div class="col-12">
                                                    <div class="border border-secondary rounded p-3">
                                                        <h6 class="text-uppercase text-muted small mb-3">Links</h6>
                                                        <dl class="row mb-0">
                                                            <dt class="col-3 text-muted">Meet Link</dt>
                                                            <dd class="col-9 text-white">
                                                                @if($isCancelled)
                                                                    <span class="text-muted">-</span>
                                                                @else
                                                                    @if($booking->meet_link && $bookingDateTime->isFuture())
                                                                        <a href="{{ $booking->meet_link }}" target="_blank" rel="noopener">Open Meet Link</a>
                                                                    @elseif($booking->meet_link)
                                                                        <span class="text-muted">Completed</span>
                                                                    @else
                                                                        <span class="text-muted">-</span>
                                                                    @endif
                                                                @endif
                                                            </dd>
                                                            <dt class="col-3 text-muted">Calendar Link</dt>
                                                            <dd class="col-9 text-white">
                                                                @if($isCancelled)
                                                                    <span class="text-muted">-</span>
                                                                @else
                                                                    @if($booking->calendar_link && $bookingDateTime->isFuture())
                                                                        <a href="{{ $booking->calendar_link }}" target="_blank" rel="noopener">Open Calendar Link</a>
                                                                    @elseif($booking->calendar_link)
                                                                        <span class="text-muted">Completed</span>
                                                                    @else
                                                                        <span class="text-muted">-</span>
                                                                    @endif
                                                                @endif
                                                            </dd>
                                                        </dl>
                                                    </div>
                                                </div>
                                                <div class="col-12">
                                                    <div class="border border-secondary rounded p-3">
                                                        <h6 class="text-uppercase text-muted small mb-3">Notes</h6>
                                                        @if($additionalNotes !== '')
                                                            <p class="mb-0 text-white">{{ $additionalNotes }}</p>
                                                        @else
                                                            <p class="text-muted mb-0">-</p>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="col-12">
                                                    <div class="border border-secondary rounded p-3">
                                                        <h6 class="text-uppercase text-muted small mb-3">Tracking</h6>
                                                        <dl class="row mb-0">
                                                            <dt class="col-3 text-muted">UTM Source</dt>
                                                            <dd class="col-9 text-white">{{ optional($booking->tracking)->utm_source ?? '-' }}</dd>
                                                            <dt class="col-3 text-muted">UTM Medium</dt>
                                                            <dd class="col-9 text-white">{{ optional($booking->tracking)->utm_medium ?? '-' }}</dd>
                                                            <dt class="col-3 text-muted">UTM Campaign</dt>
                                                            <dd class="col-9 text-white">{{ optional($booking->tracking)->utm_campaign ?? '-' }}</dd>
                                                        </dl>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer border-secondary">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Cancel Modal --}}
                            @if($bookingStatus === 'confirmed')
                                <div class="modal fade" id="cancelModal{{ $booking->id }}" tabindex="-1" aria-hidden="true" data-bs-theme="dark">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content bg-dark border-secondary">
                                            <div class="modal-header border-secondary">
                                                <h5 class="modal-title text-white">
                                                    <i class="fa fa-times me-2"></i>Cancel Booking
                                                </h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <form method="POST" action="{{ route('admin.bookings.cancel', $booking) }}">
                                                @csrf
                                                <div class="modal-body">
                                                    <div class="alert alert-warning bg-warning bg-opacity-10 border-warning text-warning mb-4">
                                                        <i class="fa fa-triangle-exclamation me-2"></i>
                                                        This will cancel the booking, remove it from Google Calendar, and process a refund if applicable.
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label text-white fw-semibold">Reason <span class="text-danger">*</span></label>
                                                        <textarea
                                                            name="reason"
                                                            class="form-control bg-dark text-white border-secondary"
                                                            rows="3"
                                                            required
                                                            placeholder="Reason for cancellation..."></textarea>
                                                    </div>

                                                    @if($hasPaidAmount)
                                                        <div class="mb-3">
                                                            <label class="form-label text-white fw-semibold">Refund Option</label>
                                                            <select
                                                                name="refund_option"
                                                                class="form-select bg-dark text-white border-secondary refund-option"
                                                                data-target="refund-custom-{{ $booking->id }}">
                                                                @if($eventHasRefund)
                                                                    <option value="policy" selected>Use refund policy</option>
                                                                    <option value="full">100% refund</option>
                                                                @else
                                                                    <option value="full" selected>100% refund</option>
                                                                @endif
                                                                <option value="custom">Custom percentage</option>
                                                            </select>
                                                            <small class="form-text text-muted">Custom percentage overrides the event refund policy.</small>
                                                        </div>
                                                        <div id="refund-custom-{{ $booking->id }}" class="mb-3 d-none">
                                                            <label class="form-label text-white fw-semibold">Refund Percentage</label>
                                                            <input
                                                                type="number"
                                                                name="refund_percentage"
                                                                class="form-control bg-dark text-white border-secondary"
                                                                min="0"
                                                                max="100"
                                                                step="0.01"
                                                                placeholder="e.g. 75">
                                                        </div>
                                                        <div class="form-check form-switch mb-3">
                                                            <input class="form-check-input" type="checkbox" name="force" value="1" id="forceCancel{{ $booking->id }}">
                                                            <label class="form-check-label text-white" for="forceCancel{{ $booking->id }}">Force Cancel (override policy)</label>
                                                            <div class="form-text text-muted">Use this to cancel even if the event policy would prevent cancellation.</div>
                                                        </div>

                                                        @else
                                                            <input type="hidden" name="refund_option" value="policy">
                                                            <div class="alert alert-info bg-info bg-opacity-10 border-info text-info mb-3">
                                                                <i class="fa fa-info-circle me-2"></i>
                                                                This booking has no paid amount. No refund will be processed for free bookings.
                                                            </div>

                                                            <div class="form-check form-switch mb-3">
                                                                <input class="form-check-input" type="checkbox" role="switch" id="force-{{ $booking->id }}" name="force" value="1">
                                                                <label class="form-check-label text-white" for="force-{{ $booking->id }}">Force Cancel (override policy)</label>
                                                            </div>
                                                        @endif
                                                </div>
                                                <div class="modal-footer border-secondary">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                        <i class="fa fa-times me-1"></i>Close
                                                    </button>
                                                    <button type="submit" class="btn btn-danger">
                                                        <i class="fa fa-times"></i> Cancel Booking
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endif

                                    {{-- Reschedule Modal --}}
                                    @if($bookingStatus === 'confirmed')
                                        <div class="modal fade" id="rescheduleModal{{ $booking->id }}" tabindex="-1" aria-hidden="true" data-bs-theme="dark">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content bg-dark border-secondary">
                                                    <div class="modal-header border-secondary">
                                                        <h5 class="modal-title text-white">
                                                            <i class="fa fa-calendar-alt me-2"></i>Request Re-Schedule
                                                        </h5>
                                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <form method="POST" action="{{ route('admin.bookings.request-reschedule', $booking) }}">
                                                        @csrf
                                                        <div class="modal-body">
                                                            <div class="alert alert-warning bg-warning bg-opacity-10 border-warning text-warning mb-4">
                                                                <i class="fa fa-info-circle me-2"></i>
                                                                This will cancel the existing Google Calendar event and remove the meeting links. The booker will receive an email with a link to reschedule.
                                                            </div>

                                                            <div class="mb-3">
                                                                <label class="form-label text-white fw-semibold">Optional Note</label>
                                                                <textarea name="note" class="form-control bg-dark text-white border-secondary" rows="3" placeholder="Optional message to the booker (appears in email)"></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer border-secondary">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                                <i class="fa fa-times me-1"></i>Close
                                                            </button>
                                                            <button type="submit" class="btn btn-primary">
                                                                <i class="fa fa-paper-plane"></i> Notify Booker
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

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
                                                        <i class="fa fa-indian-rupee-sign me-2"></i>Session Price ({{ $booking->event->currency_symbol ?? '₹' }}) *
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
                            {{-- Invite Modal --}}

                            <div class="modal fade" id="inviteModal{{ $booking->id }}" tabindex="-1" aria-hidden="true" data-bs-theme="dark">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content bg-dark border-secondary">
                                        <div class="modal-header border-secondary">
                                            <h5 class="modal-title text-white">
                                                <i class="fa fa-envelope-open me-2"></i>Send Free Invitation
                                            </h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <form method="POST" action="{{ route('admin.bookings.send-followup', $booking) }}">
                                            @csrf
                                            <div class="modal-body">
                                                <div class="alert alert-info bg-info bg-opacity-10 border-info text-info mb-4">
                                                    <i class="fa fa-info-circle me-2"></i>
                                                    Send a free session invitation to <strong>{{ $booking->booker_name }}</strong>
                                                    ({{ $booking->booker_email }})
                                                </div>

                                                <div class="mb-4">
                                                    <label class="form-label text-white fw-semibold">
                                                        <i class="fa fa-indian-rupee-sign me-2"></i>Session Price ({{ $booking->event->currency_symbol ?? '₹' }})
                                                    </label>
                                                    <input
                                                        type="number"
                                                        name="custom_price"
                                                        class="form-control bg-dark text-white border-secondary"
                                                        min="0"
                                                        step="0.01"
                                                        value="0"
                                                        required
                                                        placeholder="0">
                                                    <input type="hidden" name="is_normal_invite" value="1">
                                                    <small class="form-text text-muted">
                                                        <i class="fa fa-lightbulb me-1"></i>This will be sent as a free invitation
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
                                                <button type="submit" class="btn btn-success">
                                                    <i class="fa fa-paper-plane"></i> Send Invitation
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
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
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.refund-option').forEach(function (select) {
            var targetId = select.getAttribute('data-target');
            var target = targetId ? document.getElementById(targetId) : null;

            var toggleCustom = function () {
                if (!target) {
                    return;
                }

                if (select.value === 'custom') {
                    target.classList.remove('d-none');
                } else {
                    target.classList.add('d-none');
                    var input = target.querySelector('input[name="refund_percentage"]');
                    if (input) {
                        input.value = '';
                    }
                }
            };

            select.addEventListener('change', toggleCustom);
            toggleCustom();
        });
    });
</script>
@endpush
