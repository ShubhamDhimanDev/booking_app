@extends('admin.layouts.app')

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

    {{-- Filters --}}
    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.payments.history') }}" class="row g-3">
                <div class="col-md-3">
                    <label for="status" class="form-label">Status</label>
                    <select name="status" id="status" class="form-select">
                        <option value="">All Statuses</option>
                        @foreach($statuses as $value => $label)
                            <option value="{{ $value }}" {{ request('status') == $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="provider" class="form-label">Provider</label>
                    <select name="provider" id="provider" class="form-select">
                        <option value="">All Providers</option>
                        @foreach($providers as $value => $label)
                            <option value="{{ $value }}" {{ request('provider') == $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="currency" class="form-label">Currency</label>
                    <select name="currency" id="currency" class="form-select">
                        <option value="">All Currencies</option>
                        @foreach($currencies as $value => $label)
                            <option value="{{ $value }}" {{ request('currency') == $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="date_from" class="form-label">Date From</label>
                    <input type="date" name="date_from" id="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-3">
                    <label for="date_to" class="form-label">Date To</label>
                    <input type="date" name="date_to" id="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-3">
                    <label for="sort" class="form-label">Sort By</label>
                    <select name="sort" id="sort" class="form-select">
                        <option value="created_at" {{ (request('sort', 'created_at') == 'created_at') ? 'selected' : '' }}>Date</option>
                        <option value="amount" {{ request('sort') == 'amount' ? 'selected' : '' }}>Amount</option>
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
                    @if(request()->hasAny(['status', 'provider', 'currency', 'date_from', 'date_to', 'sort', 'direction']))
                        <a href="{{ route('admin.payments.history') }}" class="btn btn-secondary">
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
                                return route('admin.payments.history', $params);
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
                            <th>User</th>
                            <th>Booking</th>
                            <th>Provider</th>
                            <th>Transaction ID</th>
                            <th>
                                <a href="{{ $sortLink('amount') }}" class="text-dark text-decoration-none">
                                    Amount {!! $sortIcon('amount') !!}
                                </a>
                            </th>
                            <th>Currency</th>
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
                        </tr>
                    </thead>

                    <tbody>

                        @php $i = ($payments->currentPage() - 1) * $payments->perPage() + 1; @endphp

                        @foreach ($payments as $payment)
                        <tr>
                            <td>{{ $i++ }}</td>

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
                                {{ $payment->currency === 'USD' ? '$' : '₹' }}{{ $payment->amount  }}
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

    {{-- Pagination --}}
    <div class="d-flex justify-content-center mt-4">
        {{ $payments->links() }}
    </div>

</div>

@endsection

@push('scripts')
@endpush
