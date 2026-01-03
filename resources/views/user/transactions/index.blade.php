@extends('layouts.app')

@section('title', 'Transactions')

@section('content')
<div class="container-fluid">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">My Transactions</h4>
  </div>

  <div class="card">
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
          <thead class="table-light">
            <tr>
              <th>#</th>
              <th>Booking</th>
              <th>Amount</th>
              <th>Provider</th>
              <th>Status</th>
              <th>Created</th>
            </tr>
          </thead>
          <tbody>
            @forelse($payments as $p)
              <tr>
                <td>{{ $p->id }}</td>
                <td>{{ optional($p->booking)->id ?? 'N/A' }}</td>
                <td>{{ $p->amount }} {{ $p->currency }}</td>
                <td>{{ ucfirst($p->provider) }}</td>
                <td>{{ ucfirst($p->status) }}</td>
                <td>{{ $p->created_at->format('d M Y H:i') }}</td>
              </tr>
            @empty
              <tr><td colspan="6" class="text-center text-muted py-4">No transactions found.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection
