@extends('layouts.app')

@section('title', 'Terms of Service')

@section('content')
    <div class="prose max-w-none dark:prose-invert">
        <h1>Terms of Service</h1>

        <p>Last updated: {{ date('F j, Y') }}</p>

        <p>Welcome to {{ config('app.name') }}. By using our service you agree to the following terms. Please read them carefully.</p>

        <h2>Use of Service</h2>
        <p>You may use MeetFlow to create and manage bookings, subject to applicable laws and these Terms. You are responsible for any content you provide and for maintaining the security of your account.</p>

        <h2>Payments and Refunds</h2>
        <p>Payments are processed via third-party gateways. Refunds are managed according to the refund policies communicated during booking.</p>

        <h2>Intellectual Property</h2>
        <p>All intellectual property rights in the service and its content are owned by or licensed to us.</p>

        <h2>Limitation of Liability</h2>
        <p>To the maximum extent permitted by law, we are not liable for indirect or consequential losses arising from use of the service.</p>

        <h2>Changes</h2>
        <p>We may modify these Terms from time to time. Continued use constitutes acceptance of the updated Terms.</p>

        <h2>Contact</h2>
        <p>For questions about these Terms, contact the site administrator.</p>
    </div>
@endsection
