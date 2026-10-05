@extends('admin.layouts.app')

@section('title', 'Orders')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Orders</h4>
        <a href="{{ route('admin.orders.export', request()->query()) }}" class="btn btn-outline-primary">Export CSV</a>
    </div>

    <div class="row mb-3">
        <div class="col-md-3">
            <div class="card shadow-sm"><div class="card-body">
                <div class="text-muted small">To ship</div>
                <div class="h4 mb-0">{{ $stats['to_ship'] }}</div>
            </div></div>
        </div>
        <div class="col-md-9">
            <div class="card shadow-sm"><div class="card-body">
                <div class="text-muted small">Paid revenue</div>
                <div class="h5 mb-0">
                    @forelse($stats['revenue'] as $currency => $total)
                        <span class="me-3">{{ trim(config('cms.currencies.' . $currency, $currency)) }}{{ number_format($total, 2) }}</span>
                    @empty
                        <span class="text-muted">No paid orders yet</span>
                    @endforelse
                </div>
            </div></div>
        </div>
    </div>

    <form method="GET" class="row g-2 mb-3">
        <div class="col-12 col-xl-4"><input type="text" name="q" class="form-control" placeholder="Order no., name, email, phone" value="{{ request('q') }}"></div>
        <div class="col-6 col-xl-2">
            <select name="status" class="form-select">
                <option value="">All statuses</option>
                @foreach($statuses as $key => $label)
                    <option value="{{ $key }}" {{ request('status') === $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-6 col-xl-2">
            <select name="country_id" class="form-select">
                <option value="">All countries</option>
                @foreach($countries as $country)
                    <option value="{{ $country->id }}" {{ (int) request('country_id') === $country->id ? 'selected' : '' }}>{{ $country->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-6 col-xl-2"><input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}"></div>
        <div class="col-6 col-xl-2"><input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}"></div>
        <div class="col-auto">
            <button class="btn btn-primary">Filter</button>
            <a href="{{ route('admin.orders.index') }}" class="btn btn-light">Reset</a>
        </div>
    </form>

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Order</th>
                        <th>Date</th>
                        <th>Customer</th>
                        <th>Country</th>
                        <th>Items</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th class="text-end"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        <tr>
                            <td><a href="{{ route('admin.orders.show', $order) }}"><strong>{{ $order->order_number }}</strong></a></td>
                            <td>{{ $order->created_at->format('d M Y, H:i') }}</td>
                            <td>{{ $order->customer_name }}<br><small class="text-muted">{{ $order->customer_email }}</small></td>
                            <td>{{ $order->country?->name ?? '—' }}</td>
                            <td>{{ $order->items_count }}</td>
                            <td>{{ $order->currency_symbol }}{{ number_format($order->total, 2) }}</td>
                            <td>
                                @php
                                    $badge = ['paid' => 'bg-info', 'processing' => 'bg-warning text-dark', 'shipped' => 'bg-primary', 'delivered' => 'bg-success', 'refunded' => 'bg-secondary', 'cancelled' => 'bg-secondary', 'failed' => 'bg-danger'][$order->status] ?? 'bg-light text-dark';
                                @endphp
                                <span class="badge {{ $badge }}">{{ $order->status_label }}</span>
                            </td>
                            <td class="text-end"><a href="{{ route('admin.orders.show', $order) }}" class="btn btn-sm btn-outline-primary">View</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center py-4 text-muted">No orders found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($orders->hasPages())
        <div class="mt-4">{{ $orders->links() }}</div>
    @endif
</div>
@endsection
