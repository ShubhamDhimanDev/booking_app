@extends('admin.layout.app')

@section('title', 'Payment Gateway Settings')

@section('content')
<div class="container-fluid">

    <h4 class="mb-4">Payment Gateway Settings</h4>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.payment-gateway.update') }}">
                @csrf
                @method('PUT')

                {{-- Active Gateway Selection --}}
                <div class="mb-3">
                    <label class="form-label">Active Payment Gateway</label>
                    <select name="gateway" class="form-select">
                        @foreach($gateways as $gateway)
                            <option value="{{ $gateway }}" @if($current === $gateway) selected @endif>
                                {{ ucfirst($gateway) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Razorpay Section --}}
                <div class="mb-4 p-3 border rounded">
                    <h5 class="mb-3">Razorpay Configuration</h5>
                    <div class="form-text mb-3">Credentials are stored encrypted in the database.</div>

                    <div class="mb-3">
                        <label for="razorpay_key_id" class="form-label">Key ID</label>
                        <input
                            type="text"
                            id="razorpay_key_id"
                            name="razorpay_key_id"
                            value="{{ old('razorpay_key_id', $settings['razorpay']['key_id'] ?? '') }}"
                            placeholder="Enter Razorpay Key ID"
                            class="form-control"
                        >
                    </div>

                    <div class="mb-3">
                        <label for="razorpay_key_secret" class="form-label">Key Secret</label>
                        <input
                            type="password"
                            id="razorpay_key_secret"
                            name="razorpay_key_secret"
                            value="{{ old('razorpay_key_secret', $settings['razorpay']['key_secret'] ?? '') }}"
                            placeholder="Enter Razorpay Key Secret (leave blank to keep existing)"
                            class="form-control"
                        >
                    </div>
                </div>

                {{-- PayU Section --}}
                <div class="mb-4 p-3 border rounded">
                    <h5 class="mb-3">PayU Configuration</h5>
                    <div class="form-text mb-3">Credentials are stored encrypted in the database.</div>

                    <div class="mb-3">
                        <label for="payu_merchant_key" class="form-label">Merchant Key</label>
                        <input
                            type="text"
                            id="payu_merchant_key"
                            name="payu_merchant_key"
                            value="{{ old('payu_merchant_key', $settings['payu']['merchant_key'] ?? '') }}"
                            placeholder="Enter PayU Merchant Key"
                            class="form-control"
                        >
                    </div>

                    <div class="mb-3">
                        <label for="payu_merchant_salt" class="form-label">Merchant Salt</label>
                        <input
                            type="password"
                            id="payu_merchant_salt"
                            name="payu_merchant_salt"
                            value="{{ old('payu_merchant_salt', $settings['payu']['merchant_salt'] ?? '') }}"
                            placeholder="Enter PayU Merchant Salt (leave blank to keep existing)"
                            class="form-control"
                        >
                    </div>
                </div>

                <button type="submit" class="btn btn-primary px-4">Save Settings</button>
            </form>
        </div>
    </div>

</div>
@endsection
