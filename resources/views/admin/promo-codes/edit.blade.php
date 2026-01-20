@extends('admin.layouts.app')

@section('title', 'Edit Promo Code')

@section('content')

<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Edit Promo Code: {{ $promoCode->code }}</h4>
        <a href="{{ route('admin.promo-codes.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to List
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">

            <form action="{{ route('admin.promo-codes.update', $promoCode) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row">
                    {{-- Code --}}
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Promo Code <span class="text-danger">*</span></label>
                        <input
                            type="text"
                            name="code"
                            class="form-control @error('code') is-invalid @enderror"
                            value="{{ old('code', $promoCode->code) }}"
                            required
                            style="text-transform: uppercase;"
                        >
                        <div class="form-text">Will be converted to uppercase automatically</div>
                        @error('code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Description --}}
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Description</label>
                        <input
                            type="text"
                            name="description"
                            class="form-control @error('description') is-invalid @enderror"
                            value="{{ old('description', $promoCode->description) }}"
                            placeholder="Brief description (optional)"
                        >
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    {{-- Discount Type --}}
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Discount Type <span class="text-danger">*</span></label>
                        <select
                            name="discount_type"
                            id="discount_type"
                            class="form-select @error('discount_type') is-invalid @enderror"
                            required
                        >
                            <option value="percentage" {{ old('discount_type', $promoCode->discount_type) == 'percentage' ? 'selected' : '' }}>Percentage (%)</option>
                            <option value="fixed" {{ old('discount_type', $promoCode->discount_type) == 'fixed' ? 'selected' : '' }}>Fixed Amount (₹)</option>
                        </select>
                        @error('discount_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Discount Value --}}
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Discount Value <span class="text-danger">*</span></label>
                        <input
                            type="number"
                            name="discount_value"
                            id="discount_value"
                            class="form-control @error('discount_value') is-invalid @enderror"
                            value="{{ old('discount_value', $promoCode->discount_value) }}"
                            step="0.01"
                            min="0"
                            required
                        >
                        <div class="form-text" id="discount_help">Enter percentage or amount</div>
                        @error('discount_value')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    {{-- Min Booking Amount --}}
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Min Booking Amount (₹)</label>
                        <input
                            type="number"
                            name="min_booking_amount"
                            class="form-control @error('min_booking_amount') is-invalid @enderror"
                            value="{{ old('min_booking_amount', $promoCode->min_booking_amount) }}"
                            step="0.01"
                            min="0"
                            placeholder="Optional"
                        >
                        <div class="form-text">Minimum booking value required</div>
                        @error('min_booking_amount')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Max Discount Amount --}}
                    <div class="col-md-4 mb-3" id="max_discount_container">
                        <label class="form-label">Max Discount Amount (₹)</label>
                        <input
                            type="number"
                            name="max_discount_amount"
                            class="form-control @error('max_discount_amount') is-invalid @enderror"
                            value="{{ old('max_discount_amount', $promoCode->max_discount_amount) }}"
                            step="0.01"
                            min="0"
                            placeholder="Optional"
                        >
                        <div class="form-text">For percentage discounts only</div>
                        @error('max_discount_amount')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Usage Limit --}}
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Usage Limit</label>
                        <input
                            type="number"
                            name="usage_limit"
                            class="form-control @error('usage_limit') is-invalid @enderror"
                            value="{{ old('usage_limit', $promoCode->usage_limit) }}"
                            min="1"
                            placeholder="Unlimited"
                        >
                        <div class="form-text">Current usage: {{ $promoCode->usage_count }}</div>
                        @error('usage_limit')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    {{-- Valid From --}}
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Valid From</label>
                        <input
                            type="datetime-local"
                            name="valid_from"
                            class="form-control @error('valid_from') is-invalid @enderror"
                            value="{{ old('valid_from', $promoCode->valid_from ? $promoCode->valid_from->format('Y-m-d\TH:i') : '') }}"
                        >
                        <div class="form-text">Leave empty for immediate activation</div>
                        @error('valid_from')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Valid Until --}}
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Valid Until</label>
                        <input
                            type="datetime-local"
                            name="valid_until"
                            class="form-control @error('valid_until') is-invalid @enderror"
                            value="{{ old('valid_until', $promoCode->valid_until ? $promoCode->valid_until->format('Y-m-d\TH:i') : '') }}"
                        >
                        <div class="form-text">Leave empty for no expiry</div>
                        @error('valid_until')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- Is Active --}}
                <div class="mb-4">
                    <div class="form-check">
                        <input
                            type="checkbox"
                            name="is_active"
                            id="is_active"
                            class="form-check-input"
                            value="1"
                            {{ old('is_active', $promoCode->is_active) ? 'checked' : '' }}
                        >
                        <label class="form-check-label" for="is_active">
                            Active (users can use this promo code)
                        </label>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('admin.promo-codes.index') }}" class="btn btn-secondary">
                        Cancel
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Promo Code
                    </button>
                </div>

            </form>

        </div>
    </div>

</div>

<script>
    // Toggle max discount field visibility based on discount type
    document.getElementById('discount_type').addEventListener('change', function() {
        const maxDiscountContainer = document.getElementById('max_discount_container');
        const discountHelp = document.getElementById('discount_help');

        if (this.value === 'percentage') {
            maxDiscountContainer.style.display = 'block';
            discountHelp.textContent = 'Enter percentage value (e.g., 20 for 20% off)';
        } else {
            maxDiscountContainer.style.display = 'none';
            discountHelp.textContent = 'Enter fixed discount amount in ₹';
        }
    });

    // Trigger on page load
    document.getElementById('discount_type').dispatchEvent(new Event('change'));
</script>

@endsection
