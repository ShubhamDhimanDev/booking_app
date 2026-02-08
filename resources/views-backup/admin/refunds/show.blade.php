@extends('admin.layouts.app')

@section('title', 'Refund Details')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>Refund Details #{{ $refund->id }}</h4>
        <div>
            @if(in_array($refund->status, ['failed', 'pending']))
            <form action="{{ route('admin.refunds.retry', $refund) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-warning btn-sm">
                    <i class="bi bi-arrow-clockwise"></i> Retry Refund
                </button>
            </form>
            @endif
            <a href="{{ route('admin.refunds.index') }}" class="btn btn-secondary btn-sm">
                <i class="bi bi-arrow-left"></i> Back to List
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Refund Information -->
        <div class="col-md-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Refund Information</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4"><strong>Refund ID:</strong></div>
                        <div class="col-md-8">#{{ $refund->id }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4"><strong>Booking ID:</strong></div>
                        <div class="col-md-8"><a href="{{ route('admin.bookings.index') }}?search={{ $refund->booking_id }}">BK-{{ $refund->booking_id }}</a></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4"><strong>Payment ID:</strong></div>
                        <div class="col-md-8">#{{ $refund->payment_id }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4"><strong>Status:</strong></div>
                        <div class="col-md-8">
                            @if($refund->status === 'completed')
                                <span class="badge bg-success">Completed</span>
                            @elseif($refund->status === 'processing')
                                <span class="badge bg-info">Processing</span>
                            @elseif($refund->status === 'pending')
                                <span class="badge bg-warning">Pending</span>
                            @else
                                <span class="badge bg-danger">Failed</span>
                            @endif
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4"><strong>Refund Amount:</strong></div>
                        <div class="col-md-8">₹{{ number_format($refund->amount, 2) }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4"><strong>Gateway Charges:</strong></div>
                        <div class="col-md-8">₹{{ number_format($refund->gateway_charges, 2) }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4"><strong>Net Refund Amount:</strong></div>
                        <div class="col-md-8"><strong class="text-success">₹{{ number_format($refund->net_refund_amount, 2) }}</strong></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4"><strong>Payment Gateway:</strong></div>
                        <div class="col-md-8"><span class="badge bg-secondary">{{ ucfirst($refund->gateway) }}</span></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4"><strong>Gateway Refund ID:</strong></div>
                        <div class="col-md-8">{{ $refund->gateway_refund_id ?? 'N/A' }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4"><strong>Initiated By:</strong></div>
                        <div class="col-md-8">
                            <span class="badge bg-light text-dark">{{ ucfirst($refund->initiated_by) }}</span>
                            @if($refund->initiatedBy)
                            <br><small>{{ $refund->initiatedBy->name }} ({{ $refund->initiatedBy->email }})</small>
                            @endif
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4"><strong>Created At:</strong></div>
                        <div class="col-md-8">{{ $refund->created_at->format('d M Y, h:i:s A') }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4"><strong>Processed At:</strong></div>
                        <div class="col-md-8">{{ $refund->processed_at ? $refund->processed_at->format('d M Y, h:i:s A') : 'Not yet processed' }}</div>
                    </div>
                    @if($refund->failure_reason)
                    <div class="row mb-3">
                        <div class="col-md-4"><strong>Failure Reason:</strong></div>
                        <div class="col-md-8"><span class="text-danger">{{ $refund->failure_reason }}</span></div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Gateway Response -->
            @if($refund->gateway_response && is_array($refund->gateway_response))
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Gateway Response</h5>
                </div>
                <div class="card-body">
                    <pre class="bg-light p-3 rounded"><code>{{ json_encode($refund->gateway_response, JSON_PRETTY_PRINT) }}</code></pre>
                </div>
            </div>
            @endif
        </div>

        <!-- Booking & Customer Details -->
        <div class="col-md-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Booking Details</h5>
                </div>
                <div class="card-body">
                    <p><strong>Event:</strong><br>{{ $refund->booking->event->title ?? 'N/A' }}</p>
                    <p><strong>Date:</strong><br>{{ \Carbon\Carbon::parse($refund->booking->booked_at_date)->format('d M Y') }}</p>
                    <p><strong>Time:</strong><br>{{ \Carbon\Carbon::parse($refund->booking->booked_at_time)->format('h:i A') }}</p>
                    <p><strong>Status:</strong><br>
                        <span class="badge bg-{{ $refund->booking->status === 'confirmed' ? 'success' : ($refund->booking->status === 'pending' ? 'warning' : 'danger') }}">
                            {{ ucfirst($refund->booking->status) }}
                        </span>
                    </p>
                    @if($refund->booking->cancellation_reason)
                    <p><strong>Cancellation Reason:</strong><br>{{ $refund->booking->cancellation_reason }}</p>
                    @endif
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">Customer Details</h5>
                </div>
                <div class="card-body">
                    <p><strong>Name:</strong><br>{{ $refund->booking->booker->name ?? 'N/A' }}</p>
                    <p><strong>Email:</strong><br>{{ $refund->booking->booker->email ?? 'N/A' }}</p>
                    <p><strong>Phone:</strong><br>{{ $refund->booking->booker_phone ?? 'N/A' }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
