@extends('admin.layouts.app')

@section('content')

<div class="row">
</div>

<div class="row mb-4">
    <div class="col-md-3">
        <div class="card p-3">
            <h5 class="mb-2">Total (This month)</h5>
            <div class="h3">{{ $analytics['total_this_month'] ?? 0 }}</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card p-3">
            <h5 class="mb-2">Held (This month)</h5>
            <div class="h3">{{ $analytics['held_this_month'] ?? 0 }}</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card p-3">
            <h5 class="mb-2">Upcoming (This month)</h5>
            <div class="h3">{{ $analytics['upcoming_this_month'] ?? 0 }}</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card p-3">
            <h5 class="mb-2">Cancelled (This month)</h5>
            <div class="h3">{{ $analytics['cancelled_this_month'] ?? 0 }}</div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Upcoming Sessions</h4>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Event</th>
                                <th>Booker</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($upcoming as $u)
                                <tr>
                                    <td>{{ $u->id }}</td>
                                    <td>{{ optional($u->event)->title }}</td>
                                    <td>{{ $u->booker_name }}</td>
                                    <td>{{ $u->booked_at_date }}</td>
                                    <td>{{ $u->booked_at_time }}</td>
                                    <td>{{ ucfirst($u->status) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="6">No upcoming sessions.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Export Sessions</h4>
                <form method="GET" action="{{ route('admin.dashboard.export') }}">
                    <div class="mb-2">
                        <label>Start</label>
                        <input type="date" name="start" class="form-control" />
                    </div>
                    <div class="mb-2">
                        <label>End</label>
                        <input type="date" name="end" class="form-control" />
                    </div>
                    <button class="btn btn-primary">Export CSV</button>
                </form>
                <hr />
                <h5>Quick stats</h5>
                <p>Sessions last 7 days: {{ $analytics['sessions_last_7_days'] ?? 0 }}</p>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Bookings (Last 7 days)</h5>
                        <canvas id="bookingsWeeklyChart" height="120"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Payments (Last 6 months)</h5>
                        <canvas id="paymentsMonthlyChart" height="120"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Bookings (Last 6 months)</h5>
                        <canvas id="bookingsMonthlyChart" height="80"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Past Sessions</h4>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Event</th>
                                <th>Booker</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($past as $p)
                                <tr>
                                    <td>{{ $p->id }}</td>
                                    <td>{{ optional($p->event)->title }}</td>
                                    <td>{{ $p->booker_name }}</td>
                                    <td>{{ $p->booked_at_date }}</td>
                                    <td>{{ $p->booked_at_time }}</td>
                                    <td>{{ ucfirst($p->status) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="6">No past sessions found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    (function(){
        // data from backend
        const bookingsWeeklyLabels = {!! json_encode($bookingsLast7Labels ?? []) !!};
        const bookingsWeeklyData = {!! json_encode($bookingsLast7 ?? []) !!};

        const bookingsMonthlyLabels = {!! json_encode($bookingsLast6Labels ?? []) !!};
        const bookingsMonthlyData = {!! json_encode($bookingsLast6Months ?? []) !!};

        const paymentsMonthlyLabels = {!! json_encode($paymentsLast6Labels ?? []) !!};
        const paymentsMonthlyData = {!! json_encode($paymentsLast6 ?? []) !!};

        // Bookings weekly line
        const ctxWeek = document.getElementById('bookingsWeeklyChart');
        if (ctxWeek) {
            new Chart(ctxWeek.getContext('2d'), {
                type: 'line',
                data: {
                    labels: bookingsWeeklyLabels,
                    datasets: [{
                        label: 'Bookings',
                        data: bookingsWeeklyData,
                        borderColor: '#b28631',
                        backgroundColor: 'rgba(178,134,49,0.12)',
                        fill: true,
                        tension: 0.3
                    }]
                },
                options: { responsive: true, plugins: { legend: { display: false } } }
            });
        }

        // Payments monthly bar
        const ctxPay = document.getElementById('paymentsMonthlyChart');
        if (ctxPay) {
            new Chart(ctxPay.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: paymentsMonthlyLabels,
                    datasets: [{
                        label: 'Payments (amount)',
                        data: paymentsMonthlyData,
                        backgroundColor: 'rgba(178,134,49,0.8)'
                    }]
                },
                options: { responsive: true, plugins: { legend: { display: false } } }
            });
        }

        // Bookings monthly bar
        const ctxMonth = document.getElementById('bookingsMonthlyChart');
        if (ctxMonth) {
            new Chart(ctxMonth.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: bookingsMonthlyLabels,
                    datasets: [{
                        label: 'Bookings',
                        data: bookingsMonthlyData,
                        backgroundColor: 'rgba(178,134,49,0.6)'
                    }]
                },
                options: { responsive: true, plugins: { legend: { display: false } } }
            });
        }
    })();
</script>
@endpush
