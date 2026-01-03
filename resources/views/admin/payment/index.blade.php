@extends('layouts.app')

@section('title', 'Transaction History')

@section('content')

<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Transaction History</h4>
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
                            <th>User</th>
                            <th>Booking</th>
                            <th>Provider</th>
                            <th>Transaction ID</th>
                            <th>Amount</th>
                            <th>Currency</th>
                            <th>Status</th>
                            <th>Created At</th>
                        </tr>
                    </thead>

                    <tbody>

                        @foreach ($payments as $index => $payment)
                        <tr>
                            <td>{{ $index + 1 }}</td>

                            {{-- User --}}
                            <td>
                                {{ $payment->user?->name ?? 'N/A' }}
                                <br>
                                <small class="text-muted">{{ $payment->user?->email }}</small>
                            </td>

                            {{-- Booking --}}
                            <td>
                                @if ($payment->booking)
                                    <strong>{{ $payment->booking->booker_name }}</strong><br>
                                    <small class="text-muted">
                                        {{ $payment->booking->booked_at_date }} {{ $payment->booking->booked_at_time }}
                                    </small>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>

                            <td>{{ ucfirst($payment->provider) }}</td>

                            <td>
                                @if($payment->transaction_id)
                                    <span class="text-primary">{{ $payment->transaction_id }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>

                            {{-- Amount --}}
                            <td>
                                ₹{{ $payment->amount  }}
                            </td>

                            <td>{{ $payment->currency }}</td>

                            {{-- Status Badge --}}
                            <td>
                                @php
                                    $color = match($payment->status) {
                                        'success' => 'success',
                                        'pending' => 'warning',
                                        'failed' => 'danger',
                                        default => 'secondary'
                                    };
                                @endphp

                                <span class="badge bg-{{ $color }}">
                                    {{ ucfirst($payment->status) }}
                                </span>
                            </td>

                            <td>
                                {{ \Carbon\Carbon::parse($payment->created_at)->format('d M Y H:i') }}
                            </td>
                        </tr>
                        @endforeach

                        @if($payments->isEmpty())
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">
                                No transactions found.
                            </td>
                        </tr>
                        @endif

                    </tbody>

                </table>
            </div>

        </div>
    </div>

</div>

@endsection

@push('scripts')
@endpush
