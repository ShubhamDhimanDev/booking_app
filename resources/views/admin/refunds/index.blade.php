@extends('admin.layouts.app')

@section('title', 'Refunds Management')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>Refunds Management</h4>
        <a href="{{ route('admin.refunds.export', request()->query()) }}" class="btn btn-success btn-sm">
            <i class="bi bi-download"></i> Export CSV
        </a>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-2">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h6 class="card-title">Total Refunds</h6>
                    <h3 class="mb-0">{{ $stats['total'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-warning text-dark">
                <div class="card-body">
                    <h6 class="card-title">Pending</h6>
                    <h3 class="mb-0">{{ $stats['pending'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h6 class="card-title">Processing</h6>
                    <h3 class="mb-0">{{ $stats['processing'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h6 class="card-title">Completed</h6>
                    <h3 class="mb-0">{{ $stats['completed'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <h6 class="card-title">Failed</h6>
                    <h3 class="mb-0">{{ $stats['failed'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-dark text-white">
                <div class="card-body">
                    <h6 class="card-title">Total Amount</h6>
                    <h3 class="mb-0">₹{{ number_format($stats['total_amount'], 2) }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.refunds.index') }}" class="row g-3">
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>All</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>Processing</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Gateway</label>
                    <select name="gateway" class="form-select form-select-sm">
                        <option value="all" {{ request('gateway') == 'all' ? 'selected' : '' }}>All</option>
                        <option value="razorpay" {{ request('gateway') == 'razorpay' ? 'selected' : '' }}>Razorpay</option>
                        <option value="payu" {{ request('gateway') == 'payu' ? 'selected' : '' }}>PayU</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Date From</label>
                    <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Date To</label>
                    <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Booking ID" value="{{ request('search') }}">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary btn-sm me-2">Filter</button>
                    <a href="{{ route('admin.refunds.index') }}" class="btn btn-secondary btn-sm text-white">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Refunds Table -->
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Booking</th>
                            <th>Event</th>
                            <th>Customer</th>
                            <th>Amount</th>
                            <th>Gateway</th>
                            <th>Status</th>
                            <th>Initiated By</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($refunds as $refund)
                        <tr>
                            <td><strong>#{{ $refund->id }}</strong></td>
                            <td><a href="{{ route('admin.bookings.index') }}?search={{ $refund->booking_id }}">BK-{{ $refund->booking_id }}</a></td>
                            <td>{{ $refund->booking->event->title ?? 'N/A' }}</td>
                            <td>{{ $refund->booking->booker->name ?? 'N/A' }}</td>
                            <td>
                                <div><strong>₹{{ number_format($refund->net_refund_amount, 2) }}</strong></div>
                                @if($refund->gateway_charges > 0)
                                <small class="text-muted">Charges: ₹{{ number_format($refund->gateway_charges, 2) }}</small>
                                @endif
                            </td>
                            <td><span class="badge bg-secondary">{{ ucfirst($refund->gateway) }}</span></td>
                            <td>
                                @if($refund->status === 'completed')
                                    <span class="badge bg-success">Completed</span>
                                @elseif($refund->status === 'processing')
                                    <span class="badge bg-info">Processing</span>
                                @elseif($refund->status === 'pending')
                                    <span class="badge bg-warning">Pending</span>
                                @else
                                    <span class="badge bg-danger">Failed</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-light text-dark">{{ ucfirst($refund->initiated_by) }}</span>
                                @if($refund->initiatedBy)
                                <br><small>{{ $refund->initiatedBy->name }}</small>
                                @endif
                            </td>
                            <td>{{ $refund->created_at->format('d M Y, h:i A') }}</td>
                            <td>
                                <a href="{{ route('admin.refunds.show', $refund) }}" class="btn btn-sm btn-outline-primary" title="View Details">
                                    <i class="bi bi-eye"></i>
                                </a>
                                @if(in_array($refund->status, ['failed', 'pending']))
                                <form action="{{ route('admin.refunds.retry', $refund) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-warning" title="Retry Refund">
                                        <i class="bi bi-arrow-clockwise"></i>
                                    </button>
                                </form>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center py-4">
                                <p class="text-muted mb-0">No refunds found</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $refunds->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
