@extends('layouts.app')

@section('title', 'Promo Codes List')

@section('content')

<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Promo Codes</h4>

        <a href="{{ route('admin.promo-codes.create') }}" class="btn btn-primary">
            + Add New Promo Code
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body p-0">

            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Code</th>
                            <th>Discount</th>
                            <th>Usage</th>
                            <th>Valid Period</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse ($promoCodes as $promoCode)
                        <tr>
                            <td>{{ $loop->iteration + ($promoCodes->currentPage() - 1) * $promoCodes->perPage() }}</td>

                            <td>
                                <strong>{{ $promoCode->code }}</strong>
                                @if($promoCode->description)
                                    <br>
                                    <small class="text-muted">{{ $promoCode->description }}</small>
                                @endif
                            </td>

                            <td>
                                @if($promoCode->discount_type === 'percentage')
                                    <span class="badge bg-info">{{ $promoCode->discount_value }}% OFF</span>
                                    @if($promoCode->max_discount_amount)
                                        <br><small class="text-muted">Max: ₹{{ number_format($promoCode->max_discount_amount, 2) }}</small>
                                    @endif
                                @else
                                    <span class="badge bg-success">₹{{ number_format($promoCode->discount_value, 2) }} OFF</span>
                                @endif
                                @if($promoCode->min_booking_amount)
                                    <br><small class="text-muted">Min: ₹{{ number_format($promoCode->min_booking_amount, 2) }}</small>
                                @endif
                            </td>

                            <td>
                                {{ $promoCode->usage_count }}
                                @if($promoCode->usage_limit)
                                    / {{ $promoCode->usage_limit }}
                                @else
                                    / ∞
                                @endif
                            </td>

                            <td>
                                @if($promoCode->valid_from)
                                    <small>From: {{ $promoCode->valid_from->format('d M Y') }}</small><br>
                                @endif
                                @if($promoCode->valid_until)
                                    <small>Until: {{ $promoCode->valid_until->format('d M Y') }}</small>
                                @else
                                    <small>No expiry</small>
                                @endif
                            </td>

                            <td>
                                @if($promoCode->isValid())
                                    <span class="badge bg-success">Active</span>
                                @elseif(!$promoCode->is_active)
                                    <span class="badge bg-secondary">Inactive</span>
                                @elseif($promoCode->usage_limit && $promoCode->usage_count >= $promoCode->usage_limit)
                                    <span class="badge bg-warning">Limit Reached</span>
                                @elseif($promoCode->valid_until && $promoCode->valid_until->isPast())
                                    <span class="badge bg-danger">Expired</span>
                                @else
                                    <span class="badge bg-warning">Inactive</span>
                                @endif
                            </td>

                            <td class="text-end">
                                <div class="btn-group" role="group">
                                    <a href="{{ route('admin.promo-codes.edit', $promoCode) }}"
                                       class="btn btn-sm btn-outline-primary"
                                       title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>

                                    <form action="{{ route('admin.promo-codes.destroy', $promoCode) }}"
                                          method="POST"
                                          class="d-inline"
                                          onsubmit="return confirm('Are you sure you want to delete this promo code?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="btn btn-sm btn-outline-danger"
                                                title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>

                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <p class="text-muted mb-0">No promo codes found.</p>
                            </td>
                        </tr>
                        @endforelse

                    </tbody>
                </table>
            </div>

        </div>
    </div>

    @if($promoCodes->hasPages())
        <div class="mt-4">
            {{ $promoCodes->links() }}
        </div>
    @endif

</div>

@endsection
